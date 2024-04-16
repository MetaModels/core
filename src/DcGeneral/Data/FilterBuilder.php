<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2024 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\DcGeneral\Data;

use Contao\System;
use ContaoCommunityAlliance\DcGeneral\Data\ConfigInterface;
use Doctrine\DBAL\Connection;
use MetaModels\Attribute\IAttribute;
use MetaModels\Filter\IFilter;
use MetaModels\Filter\Rules\Comparing\GreaterThan;
use MetaModels\Filter\Rules\Comparing\LessThan;
use MetaModels\Filter\Rules\Condition\ConditionAnd;
use MetaModels\Filter\Rules\Condition\ConditionOr;
use MetaModels\Filter\Rules\SearchAttribute;
use MetaModels\Filter\Rules\SimpleQuery;
use MetaModels\IMetaModel;
use MetaModels\IMetaModelsServiceContainer;
use MetaModels\ITranslatedMetaModel;

/**
 * Class to generate a MetaModels filter from a data configuration.
 *
 * @psalm-type TFilterANDOR=array{operation: 'AND'|'OR', children: list<array<string, mixed>>}
 * @psalm-type TFilterCMP=array{operation: "="|">"|"<", property: string, value: string|int|float}
 * @psalm-type TFilterIN=array{operation: 'IN', property: string, values: list<string|int|float>}
 * @psalm-type TFilterLIKE=array{operation: 'LIKE', property: string, value: string}
 * @psalm-type TFilterForProperty=TFilterCMP|TFilterIN|TFilterLIKE
 * @psalm-type TFilter=TFilterANDOR|TFilterForProperty
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FilterBuilder
{
    /**
     * The MetaModel instance.
     *
     * @var IMetaModel
     */
    protected $metaModel;

    /**
     * The configuration.
     *
     * @var ConfigInterface
     */
    protected $configuration;

    /**
     * Database connection.
     *
     * @var Connection
     */
    protected $connection;

    /**
     * Generate a filter from a passed configuration.
     *
     * @param IMetaModel      $metaModel     The MetaModel.
     * @param ConfigInterface $configuration The data configuration.
     * @param Connection|null $connection    The database connection.
     */
    public function __construct(IMetaModel $metaModel, ConfigInterface $configuration, Connection $connection = null)
    {
        $this->metaModel     = $metaModel;
        $this->configuration = $configuration;

        if (null === $connection) {
            // @codingStandardsIgnoreStart
            @trigger_error(
                'Connection is missing. It has to be passed in the constructor. Fallback will be dropped.',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
            $connection = System::getContainer()->get('database_connection');
            assert($connection instanceof Connection);
        }
        $this->connection = $connection;
    }

    /**
     * Retrieve the MetaModel.
     *
     * @return IMetaModel
     */
    protected function getMetaModel()
    {
        return $this->metaModel;
    }

    /**
     * Retrieve the service container.
     *
     * @return IMetaModelsServiceContainer
     *
     * @psalm-suppress DeprecatedInterface
     */
    protected function getServiceContainer()
    {
        /** @psalm-suppress DeprecatedMethod */
        return $this->getMetaModel()->getServiceContainer();
    }

    /**
     * Retrieve the Database.
     *
     * @return \Contao\Database
     */
    protected function getDatabase()
    {
        /** @psalm-suppress DeprecatedMethod */
        return $this->getServiceContainer()->getDatabase();
    }

    /**
     * Build the sub query for a comparing operator like =,<,>.
     *
     * @param IAttribute $attribute The attribute.
     * @param IFilter    $filter    The filter to add the operations to.
     * @param TFilterCMP $operation The operation to convert.
     *
     * @return void
     *
     * @throws \RuntimeException When the operation can not be parsed.
     */
    private function getFilterForComparingOperator($attribute, IFilter $filter, $operation)
    {
        switch ($operation['operation']) {
            case '=':
                $filter->addFilterRule($this->buildSearchAttributeFilterRule($attribute, $operation['value']));
                return;

            case '>':
                $filter->addFilterRule(new GreaterThan(
                    $attribute,
                    $operation['value']
                ));
                return;

            case '<':
                $filter->addFilterRule(new LessThan(
                    $attribute,
                    $operation['value']
                ));
                return;

            default:
                throw new \RuntimeException(
                    'Error processing filter array - unknown operation ' .
                    \var_export($operation['operation'], true),
                    1
                );
        }
    }

    /**
     * Return the filter query for a "foo IN ('a', 'b')" filter.
     *
     * @param IFilter   $filter    The filter to add the operations to.
     * @param TFilterIN $operation The operation to convert.
     *
     * @return void
     *
     * @throws \RuntimeException When the operation can not be parsed.
     */
    private function getFilterForInList(IFilter $filter, $operation)
    {
        // Rewrite the IN operation to a rephrased term: "(x=a) OR (x=b) OR ...".
        $subRules = [];
        foreach ($operation['values'] as $varValue) {
            $subRules[] = [
                'property'  => $operation['property'],
                'operation' => '=',
                'value'     => $varValue
            ];
        }

        $this->getAndOrFilter($filter, ['operation' => 'OR', 'children'  => $subRules]);
    }

    /**
     * Return the filter query for a "foo LIKE '%ba_r%'" filter.
     *
     * The searched value may contain the wildcards '*' and '?' which will get converted to proper SQL.
     *
     * @param IAttribute  $attribute The attribute.
     * @param IFilter     $filter    The filter to add the operations to.
     * @param TFilterLIKE $operation The operation to convert.
     *
     * @return void
     *
     * @throws \RuntimeException When the operation can not be parsed.
     */
    private function getFilterForLike($attribute, IFilter $filter, $operation)
    {
        $filter->addFilterRule($this->buildSearchAttributeFilterRule($attribute, $operation['value']));
    }

    /**
     * Calculate a native SQL sub procedure.
     *
     * @param FilterBuilderSql $procedure The procedure to which to append to.
     * @param array            $children  The children to calculate.
     *
     * @return array
     */
    protected function buildNativeSqlProcedure(FilterBuilderSql $procedure, $children): array
    {
        $skipped   = [];
        $metaModel = $this->getMetaModel();
        $tableName = $metaModel->getTableName();
        foreach ($children as $child) {
            assert(\is_array($child));
            // If there is an attribute contained within this rule, skip it.
            if (isset($child['property']) && $metaModel->hasAttribute($child['property'])) {
                $skipped[] = $child;

                continue;
            }

            // Try to parse the sub procedure and extract as much as possible.
            if (('AND' === $child['operation']) || ('OR' === $child['operation'])) {
                if (empty($child['children'])) {
                    continue;
                }

                $subProcedure = new FilterBuilderSql($tableName, $child['operation'], $this->connection, 't.');
                $subSkipped   = $this->buildNativeSqlProcedure($subProcedure, $child['children']);

                if (\count($subSkipped) !== \count($child['children'])) {
                    $procedure->addSubProcedure($subProcedure);
                }

                if (!empty($subSkipped)) {
                    $skipped += $subSkipped;
                }

                continue;
            }

            $procedure->addChild($child);
        }

        return $skipped;
    }

    /**
     * Method to optimize as many system column lookup filters as possible into a combined filter rule.
     *
     * @param ConditionAnd|ConditionOr $filterRule The filter to which the optimized rule shall be added to.
     * @param array                    $children   The children to parse.
     * @param string                   $operation  The operation to parse (AND or OR).
     *
     * @return array
     */
    protected function optimizedFilter($filterRule, $children, $operation)
    {
        $procedure = new FilterBuilderSql($this->getMetaModel()->getTableName(), $operation, $this->connection, 't.');
        $skipped   = $this->buildNativeSqlProcedure($procedure, $children);

        if (null !== ($rule = $procedure->build())) {
            $filterRule->addChild($this->getMetaModel()->getEmptyFilter()->addFilterRule($rule));
        }

        return $skipped;
    }

    /**
     * Build an AND or OR query.
     *
     * @param IFilter      $filter    The filter to add the operations to.
     * @param TFilterANDOR $operation The operation to convert.
     *
     * @return void
     */
    protected function getAndOrFilter(IFilter $filter, $operation)
    {
        if ([] === $operation['children']) {
            return;
        }

        if ($operation['operation'] === 'AND') {
            $filterRule = new ConditionAnd();
        } else {
            $filterRule = new ConditionOr();
        }
        $filter->addFilterRule($filterRule);

        $children = $this->optimizedFilter($filterRule, $operation['children'], $operation['operation']);

        foreach ($children as $child) {
            $subFilter = $this->getMetaModel()->getEmptyFilter();
            $filterRule->addChild($subFilter);
            $this->calculateSubfilter($child, $subFilter);
        }
    }

    /**
     * Retrieve the attribute for a filter operation.
     *
     * @param TFilter $operation The operation to retrieve the attribute for.
     *
     * @return IAttribute
     *
     * @throws \InvalidArgumentException When the attribute can not be retrieved.
     */
    protected function getAttributeFromFilterOperation($operation)
    {
        if (null === ($property = $operation['property'] ?? null)) {
            throw new \InvalidArgumentException('Missing key "property" in ' . var_export($operation, true));
        }
        $attribute = $this->getMetaModel()->getAttribute($property);

        if ($attribute === null) {
            throw new \InvalidArgumentException('Attribute ' . $property . ' not found.');
        }

        return $attribute;
    }

    /**
     * Combine a filter in standard filter array notation.
     *
     * Supported operations are:
     * operation      needed arguments     argument type.
     * AND
     *                'children'           array
     * OR
     *                'children'           array
     * =
     *                'property'           string (the name of a property)
     *                'value'              literal
     * >
     *                'property'           string (the name of a property)
     *                'value'              literal
     * <
     *                'property'           string (the name of a property)
     *                'value'              literal
     * IN
     *                'property'           string (the name of a property)
     *                'values'             array of literal
     *
     * @param TFilter $operation The filter to be combined into the passed filter object.
     * @param IFilter $filter    The filter object where the rules shall get appended to.
     *
     * @return void
     *
     * @throws \RuntimeException When an improper filter condition is encountered, an exception is thrown.
     */
    private function calculateSubfilter(array $operation, IFilter $filter): void
    {
        switch ($operation['operation']) {
            case 'AND':
            case 'OR':
                $this->assertValidAndOr($operation);
                $this->getAndOrFilter($filter, $operation);
                break;

            case '=':
            case '>':
            case '<':
                $this->assertValidCompareOperation($operation);
                $this->getFilterForComparingOperator(
                    $this->getAttributeFromFilterOperation($operation),
                    $filter,
                    $operation
                );
                break;

            case 'IN':
                $this->assertValidInList($operation);
                $this->getFilterForInList($filter, $operation);
                break;

            case 'LIKE':
                $this->assertValidLike($operation);
                $this->getFilterForLike(
                    $this->getAttributeFromFilterOperation($operation),
                    $filter,
                    $operation
                );
                break;

            default:
                throw new \RuntimeException(
                    'Error processing filter array - unknown operation ' . \var_export($operation, true),
                    1
                );
        }
    }

    /**
     * Prepare a filter and return it.
     *
     * @return IFilter
     */
    public function build()
    {
        $filter = $this->getMetaModel()->getEmptyFilter();

        /** @var null|list<TFilter> $filterRules */
        $filterRules = $this->configuration->getFilter();
        if (null !== $filterRules) {
            $this->calculateSubfilter(['operation' => 'AND', 'children' => $filterRules], $filter);
        }

        return $filter;
    }

    /**
     * @psalm-suppress DeprecatedMethod
     * @psalm-suppress TooManyArguments
     */
    private function buildSearchAttributeFilterRule(IAttribute $attribute, string $value): SearchAttribute
    {
        $languages = [];
        $metaModel = $attribute->getMetaModel();
        if ($metaModel instanceof ITranslatedMetaModel) {
            $languages = $metaModel->getLanguages();
        } elseif ($metaModel->isTranslated(false)) {
            $languages = $this->getMetaModel()->getAvailableLanguages() ?? [];
        }

        return new SearchAttribute($attribute, $value, \array_values(\array_filter($languages)));
    }

    /** @psalm-assert TFilterANDOR $filter */
    private function assertValidAndOr(array $filter): void
    {
        assert(\is_array($filter['children'] ?? null));
        assert(\in_array($filter['operation'], ['AND', 'OR'], true));
    }

    /** @psalm-assert TFilterCMP $filter */
    private function assertValidCompareOperation(array $filter): void
    {
        assert(\is_string($filter['property'] ?? null));
        assert(
            \is_string($filter['value'] ?? null)
            || \is_int($filter['value'] ?? null)
            || \is_float($filter['value'] ?? null)
        );
        assert(\in_array($filter['operation'], ['<', '=', '>'], true));
    }

    /** @psalm-assert TFilterIN $filter */
    private function assertValidInList(array $filter): void
    {
        assert(\is_string($filter['property'] ?? null));
        assert(
            \is_string($filter['value'] ?? null)
            || \is_int($filter['value'] ?? null)
            || \is_float($filter['value'] ?? null)
        );
        assert($filter['operation'] === 'IN');
    }

    /** @psalm-assert TFilterLIKE $filter */
    private function assertValidLike(array $filter): void
    {
        assert(\is_string($filter['property'] ?? null));
        assert(\is_string($filter['value'] ?? null));
        assert($filter['operation'] === 'LIKE');
    }
}
