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
 * @author     David Maack <david.maack@arcor.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Frank Mueller <frank.mueller@linking-you.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Andreas Fischer <anfischer@kaffee-partner.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Attribute;

use Contao\System;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use MetaModels\IMetaModel;
use MetaModels\ITranslatedMetaModel;

/**
 * This is the MetaModelAttribute class for handling translated attributes that reference another table.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
abstract class TranslatedReference extends BaseComplex implements ITranslated
{
    /**
     * Database connection.
     *
     * @var Connection
     */
    protected $connection;

    /**
     * Instantiate an MetaModel attribute.
     *
     * Note that you should not use this directly but use the factory classes to instantiate attributes.
     *
     * @param IMetaModel      $objMetaModel The MetaModel instance this attribute belongs to.
     * @param array           $arrData      The information array, for attribute information, refer to documentation of
     *                                      table tl_metamodel_attribute and documentation of the certain attribute
     *                                      classes for information what values are understood.
     * @param Connection|null $connection   Database connection.
     */
    public function __construct(IMetaModel $objMetaModel, $arrData = [], Connection $connection = null)
    {
        parent::__construct($objMetaModel, $arrData);

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
     * Retrieve the name of the table that contains the data for this reference.
     *
     * @return string
     */
    abstract protected function getValueTable();

    /**
     * Build a where clause for the given id(s) and language code.
     *
     * @param QueryBuilder             $queryBuilder           The query builder for the query  being build.
     * @param list<string>|string|null $mixIds                 One, none or many ids to use.
     * @param list<non-empty-string>   $mixLangCode            The language code/s to use, optional.
     *
     * @return void
     */
    private function buildWhere(
        QueryBuilder $queryBuilder,
        array|string|null $mixIds,
        array $mixLangCode,
        string $alias
    ): void {
        $queryBuilder
            ->andWhere($alias . '.att_id = :att_id')
            ->setParameter('att_id', $this->get('id'));

        if (null !== $mixIds) {
            if (\is_array($mixIds)) {
                if ([] === $mixIds) {
                    $queryBuilder
                        ->andWhere('1=0');
                } else {
                    $queryBuilder
                        ->andWhere($alias . '.item_id IN (:item_ids)')
                        ->setParameter('item_ids', $mixIds, ArrayParameterType::STRING);
                }
            } else {
                $queryBuilder
                    ->andWhere($alias . '.item_id = :item_id')
                    ->setParameter('item_id', $mixIds);
            }
        }

        if ([] !== $mixLangCode) {
            $queryBuilder
                ->andWhere($alias . '.langcode IN (:langcode)')
                ->setParameter('langcode', $mixLangCode, ArrayParameterType::STRING);
        }
    }

    /**
     * Retrieve the values to be used in the INSERT or UPDATE SQL for the given parameters.
     *
     * @param array{value: mixed, ...<string, mixed>} $arrValue    The native value of the attribute.
     * @param string                                  $intId       The id of the item to be saved.
     * @param string                                  $strLangCode The language code of the language the value is in.
     *
     * @return array
     *
     * @throws \InvalidArgumentException When the passed value is not null and not an array.
     */
    protected function getSetValues($arrValue, $intId, $strLangCode)
    {
        /**
         * @psalm-suppress DocblockTypeContradiction
         * @psalm-suppress RedundantConditionGivenDocblockType
         * Remove when we have strict type hints in the method signature.
         */
        if (($arrValue === null) || !\is_array($arrValue)) {
            throw new \InvalidArgumentException(\sprintf('Invalid value provided: %s', \var_export($arrValue, true)));
        }

        return [
            'tstamp'   => \time(),
            'value'    => (string) $arrValue['value'],
            'att_id'   => $this->get('id'),
            'langcode' => $strLangCode,
            'item_id'  => $intId,
        ];
    }

    /**
     * Retrieve the columns to be used for key and value when retrieving the filter options.
     *
     * Returned array must contain two elements having the keys "key" and "value".
     *
     * @return array
     */
    protected function getOptionizer()
    {
        return [
            'key'   => 'value',
            'value' => 'value'
        ];
    }


    /**
     * {@inheritDoc}
     */
    public function valueToWidget($varValue)
    {
        return $varValue['value'] ?? null;
    }

    /**
     * {@inheritDoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function widgetToValue($varValue, $itemId)
    {
        return [
            'tstamp' => \time(),
            'value'  => $varValue,
            'att_id' => $this->get('id'),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getDataFor($arrIds)
    {
        /** @psalm-suppress DeprecatedMethod */
        $strActiveLanguage = $this->getActiveLanguage();
        /** @psalm-suppress DeprecatedMethod */
        $strFallbackLanguage = $this->getFallbackLanguage();

        $arrReturn = $this->getTranslatedDataFor($arrIds, $strActiveLanguage);

        // Second round, fetch fallback languages if not all items could be resolved.
        if ((\count($arrReturn) < \count($arrIds)) && ($strActiveLanguage !== $strFallbackLanguage)) {
            $arrFallbackIds = [];
            foreach ($arrIds as $intId) {
                if (empty($arrReturn[$intId])) {
                    $arrFallbackIds[] = $intId;
                }
            }

            if ($arrFallbackIds) {
                $arrFallbackData = $this->getTranslatedDataFor($arrFallbackIds, $strFallbackLanguage ?? '');
                // Cannot use array_merge here as it would renumber the keys.
                foreach ($arrFallbackData as $intId => $arrValue) {
                    $arrReturn[$intId] = $arrValue;
                }
            }
        }
        return $arrReturn;
    }

    /**
     * Determine the available languages.
     *
     * @return list<string>
     *
     * @throws \RuntimeException When an untranslated MetaModel is encountered.
     */
    private function determineLanguages()
    {
        $metaModel = $this->getMetaModel();
        if ($metaModel instanceof ITranslatedMetaModel) {
            return $metaModel->getLanguages();
        }

        /** @psalm-suppress DeprecatedMethod */
        $languages = $this->getMetaModel()->getAvailableLanguages();
        if ($languages === null) {
            throw new \RuntimeException(
                'MetaModel ' . $this->getMetaModel()->getName() . ' does not seem to be translated.'
            );
        }

        return $languages;
    }

    /**
     * {@inheritDoc}
     */
    public function setDataFor($arrValues)
    {
        foreach ($this->determineLanguages() as $strLangCode) {
            $this->setTranslatedDataFor($arrValues, $strLangCode);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function unsetDataFor($arrIds)
    {
        foreach ($this->determineLanguages() as $strLangCode) {
            $this->unsetValueFor($arrIds, $strLangCode);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function searchFor($strPattern)
    {
        return $this->searchForInLanguages($strPattern, [$this->getActiveLanguage()]);
    }

    /**
     * {@inheritDoc}
     */
    public function searchForInLanguages($strPattern, $arrLanguages = [])
    {
        if (empty($optionizer = $this->getOptionizer())) {
            return [];
        }

        $procedure  = 't.' . $optionizer['value'] . ' LIKE :pattern';
        $strPattern = \str_replace(['*', '?'], ['%', '_'], $strPattern);

        $queryBuilder = $this->connection->createQueryBuilder()
            ->select('DISTINCT t.item_id')
            ->from($this->getValueTable(), 't')
            ->andWhere($procedure)
            ->setParameter('pattern', $strPattern);

        $this->buildWhere($queryBuilder, null, $arrLanguages, 't');

        return $queryBuilder->executeQuery()->fetchFirstColumn();
    }

    /**
     * {@inheritDoc}
     */
    public function sortIds($idList, $strDirection)
    {
        $builder = $this->connection->createQueryBuilder();
        $expr    = $builder->expr();
        $builder
            ->select('IF(t2.item_id IS NOT NULL, t2.item_id, t1.item_id)')
            ->from($this->getValueTable(), 't1')
            ->leftJoin(
                't1',
                $this->getValueTable(),
                't2',
                (string) $expr->and(
                    $expr->eq('t1.att_id', 't2.att_id'),
                    $expr->eq('t1.item_id', 't2.item_id'),
                    $expr->eq('t2.langcode', ':langcode'),
                )
            )
            ->where($expr->eq('t1.att_id', ':att_id'))
            ->andWhere($expr->in('t1.item_id', ':id_list'))
            ->andWhere($expr->in('t1.langcode', ':langfallbackcode'))
            ->setParameter('langcode', $this->getActiveLanguage())
            ->setParameter('langfallbackcode', $this->getFallbackLanguage())
            ->setParameter('att_id', $this->get('id'))
            ->setParameter('id_list', \array_unique($idList), ArrayParameterType::STRING);

        return $builder->executeQuery()->fetchFirstColumn();
    }

    /**
     * {@inheritDoc}
     */
    public function getFilterOptions($idList, $usedOnly, &$arrCount = null)
    {
        $queryBuilder = $this->connection->createQueryBuilder()
            ->select('t.*')
            ->from($this->getValueTable(), 't');

        $this->buildWhere($queryBuilder, $idList, [$this->getActiveLanguage()], 't');

        $statement     = $queryBuilder->executeQuery();
        $arrOptionizer = $this->getOptionizer();

        $arrReturn = [];
        while ($objValue = $statement->fetchAssociative()) {
            $arrReturn[$objValue[$arrOptionizer['key']]] = $objValue[$arrOptionizer['value']];
        }
        return $arrReturn;
    }

    /**
     * {@inheritDoc}
     */
    public function setTranslatedDataFor($arrValues, $strLangCode)
    {
        if ('' === $strLangCode) {
            throw new \InvalidArgumentException('Empty language code provided.');
        }
        // First off determine those to be updated and those to be inserted.
        $arrIds      = \array_keys($arrValues);
        $arrExisting = $this->fetchExistingIdsFor($arrIds, $strLangCode);
        $arrNewIds   = \array_diff($arrIds, $arrExisting);

        // Update existing values - delete if empty.
        foreach ($arrExisting as $intId) {
            $queryBuilder = $this->connection->createQueryBuilder();
            $this->buildWhere($queryBuilder, $intId, [$strLangCode], 't');
            $itemValues = $arrValues[$intId] ?? [];
            if ($this->isValidItemValue($itemValues)) {
                $queryBuilder->update($this->getValueTable(), 't');

                foreach ($this->getSetValues($itemValues, $intId, $strLangCode) as $name => $value) {
                    $queryBuilder
                        ->set('t.' . $name, ':' . $name)
                        ->setParameter($name, $value);
                }
            } else {
                $queryBuilder->delete($this->getValueTable());
            }

            $queryBuilder->executeQuery();
        }

        // Insert the new values.
        foreach ($arrNewIds as $intId) {
            $itemValues = $arrValues[$intId] ?? [];
            if (!$this->isValidItemValue($itemValues)) {
                continue;
            }

            $this->connection->insert($this->getValueTable(), $this->getSetValues($itemValues, $intId, $strLangCode));
        }
    }

    /**
     * Filter the item ids for ids that exist in the database.
     *
     * @param list<string> $idList   The id list.
     * @param string       $langCode The language code.
     *
     * @return string[]
     */
    protected function fetchExistingIdsFor($idList, $langCode)
    {
        if ('' === $langCode) {
            throw new \InvalidArgumentException('Empty language code provided.');
        }
        $queryBuilder = $this
            ->connection
            ->createQueryBuilder()
            ->select('t.item_id')
            ->from($this->getValueTable(), 't');
        $this->buildWhere($queryBuilder, $idList, [$langCode], 't');

        return $queryBuilder->executeQuery()->fetchFirstColumn();
    }

    /**
     * {@inheritDoc}
     */
    public function getTranslatedDataFor($arrIds, $strLangCode)
    {
        if ('' === $strLangCode) {
            throw new \InvalidArgumentException('Empty language code provided.');
        }
        $queryBuilder = $this->connection->createQueryBuilder()
            ->select('t.*')
            ->from($this->getValueTable(), 't');

        $this->buildWhere($queryBuilder, $arrIds, [$strLangCode], 't');

        $statement = $queryBuilder->executeQuery();
        $arrReturn = [];
        while ($value = $statement->fetchAssociative()) {
            $arrReturn[$value['item_id']] = $value;
        }
        return $arrReturn;
    }

    /**
     * {@inheritDoc}
     */
    public function unsetValueFor($arrIds, $strLangCode)
    {
        if ('' === $strLangCode) {
            throw new \InvalidArgumentException('Empty language code provided.');
        }
        $queryBuilder = $this->connection->createQueryBuilder()->delete($this->getValueTable(), 't');
        $this->buildWhere($queryBuilder, $arrIds, [$strLangCode], 't');

        $queryBuilder->executeQuery();
    }

    /**
     * Retrieve the current language of the MetaModel we are attached to.
     *
     * @return non-empty-string
     */
    private function getActiveLanguage(): string
    {
        $metaModel = $this->getMetaModel();
        if (!$metaModel instanceof ITranslatedMetaModel) {
            /** @psalm-suppress DeprecatedMethod */
            $activeLanguage = $metaModel->getActiveLanguage();
            assert('' !== $activeLanguage);

            return $activeLanguage;
        }
        $activeLanguage = $metaModel->getLanguage();
        assert('' !== $activeLanguage);

        return $activeLanguage;
    }

    /**
     * Retrieve the main language of the MetaModel we are attached to.
     *
     * @return string|null
     */
    private function getFallbackLanguage(): ?string
    {
        $metaModel = $this->getMetaModel();
        if (!$metaModel instanceof ITranslatedMetaModel) {
            /** @psalm-suppress DeprecatedMethod */
            return $metaModel->getFallbackLanguage();
        }

        return $metaModel->getMainLanguage();
    }

    /** @psalm-assert-if-true array{value: non-empty-string, ...<string, mixed>} $itemValues */
    private function isValidItemValue(array $itemValues): bool
    {
        return \array_key_exists('value', $itemValues)
               && \is_string($itemValues['value'])
               && '' !== $itemValues['value'];
    }
}
