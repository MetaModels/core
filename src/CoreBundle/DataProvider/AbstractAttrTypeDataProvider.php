<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2026 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2026 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\DataProvider;

use ContaoCommunityAlliance\DcGeneral\Data\ConfigInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultDataProvider;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultFilterOptionCollection;
use ContaoCommunityAlliance\DcGeneral\Data\FilterOptionCollectionInterface;

/**
 * Abstract base class for data providers that handle virtual panel properties mapped via attr_id.
 *
 * Virtual properties (no real database column):
 *   - attr_type   (filter)  → tl_metamodel_attribute.type  via attr_id
 *   - attr_name   (search)  → tl_metamodel_attribute.name  via attr_id
 *   - attr_colname (search) → tl_metamodel_attribute.colname via attr_id
 *
 * Each is rewritten as an "attr_id IN (...)" subquery before the SQL is executed.
 *
 * Concrete subclasses only need to implement {@see getMetaModelIdFromParentId()} to resolve
 * the MetaModel ID from a parent record ID, since the parent table differs per use-case.
 */
abstract class AbstractAttrTypeDataProvider extends DefaultDataProvider
{
    /**
     * Virtual properties searchable via LIKE → column in tl_metamodel_attribute.
     *
     * @psalm-suppress MissingClassConstType
     * @var array<string, string>
     */
    private const VIRTUAL_SEARCH_MAP = [
        'attr_type'    => 'type',
        'attr_name'    => 'name',
        'attr_colname' => 'colname',
    ];

    /**
     * Look up the MetaModel ID for a given parent record ID.
     *
     * Implementations query the appropriate parent table (e.g. tl_metamodel_dca or
     * tl_metamodel_rendersettings) to find the pid of that record, which is the MetaModel ID.
     */
    abstract protected function getMetaModelIdFromParentId(int $parentId): ?int;

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function fetchAll(ConfigInterface $config)
    {
        $filter = $config->getFilter();
        if (null !== $filter && [] !== $filter) {
            $config->setFilter($this->rewriteVirtualConditions($filter));
        }

        return parent::fetchAll($config);
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function getFilterOptions(ConfigInterface $config)
    {
        $fields = $config->getFields();
        if (null !== $fields && 1 === \count($fields) && 'attr_type' === $fields[0]) {
            return $this->buildAttrTypeOptions($config);
        }

        return parent::getFilterOptions($config);
    }

    // -------------------------------------------------------------------------
    // Filter rewriting
    // -------------------------------------------------------------------------

    /**
     * Walk the entire filter tree and replace every virtual condition with a
     * real "attr_id IN (...)" condition.
     *
     * @param array<array-key, mixed> $filter
     *
     * @return list<array<string, mixed>>
     */
    private function rewriteVirtualConditions(array $filter): array
    {
        $result = [];

        foreach ($filter as $condition) {
            if (!\is_array($condition)) {
                continue;
            }

            $operation = (string) ($condition['operation'] ?? '');
            $property  = (string) ($condition['property'] ?? '');

            if ('=' === $operation && 'attr_type' === $property) {
                $result[] = $this->buildAttrTypeEqualsCondition((string) $condition['value'], $filter);
                continue;
            }

            if ('LIKE' === $operation && isset(self::VIRTUAL_SEARCH_MAP[$property])) {
                $result[] = $this->buildVirtualLikeCondition($property, (string) $condition['value']);
                continue;
            }

            if (\in_array($operation, ['AND', 'OR'], true) && \is_array($condition['children'] ?? null)) {
                $condition['children'] = $this->rewriteVirtualConditions($condition['children']);
            }

            $result[] = $condition;
        }

        return $result;
    }

    /**
     * Build the rewritten condition for an attr_type equality match.
     *
     * @param array<array-key, mixed> $filter
     *
     * @return array<string, mixed>
     */
    private function buildAttrTypeEqualsCondition(string $type, array $filter): array
    {
        $parentId = $this->extractPropertyValue($filter, 'pid');
        $attrIds  = $this->getAttributeIdsByType($type, $parentId);

        return [] === $attrIds
            ? ['operation' => '=', 'property' => 'id', 'value' => -1]
            : ['operation' => 'IN', 'property' => 'attr_id', 'values' => $attrIds];
    }

    /**
     * Build the rewritten condition for a virtual property LIKE match.
     *
     * @return array<string, mixed>
     */
    private function buildVirtualLikeCondition(string $property, string $value): array
    {
        $column  = self::VIRTUAL_SEARCH_MAP[$property];
        $attrIds = $this->getAttributeIdsByLike($column, $value);

        return [] === $attrIds
            ? ['operation' => '=', 'property' => 'id', 'value' => -1]
            : ['operation' => 'IN', 'property' => 'attr_id', 'values' => $attrIds];
    }

    // -------------------------------------------------------------------------
    // Filter options for the attr_type dropdown
    // -------------------------------------------------------------------------

    /**
     * Build the filter option collection for the virtual 'attr_type' property.
     */
    private function buildAttrTypeOptions(ConfigInterface $config): FilterOptionCollectionInterface
    {
        $collection = new DefaultFilterOptionCollection();
        $parentId   = $this->extractPropertyValue($config->getFilter() ?? [], 'pid');
        if (null === $parentId) {
            return $collection;
        }

        $metaModelId = $this->getMetaModelIdFromParentId((int) $parentId);
        if (null === $metaModelId) {
            return $collection;
        }

        $types = $this->connection
            ->createQueryBuilder()
            ->select('DISTINCT type')
            ->from('tl_metamodel_attribute')
            ->where('pid = :pid')
            ->setParameter('pid', $metaModelId)
            ->orderBy('type')
            ->executeQuery()
            ->fetchFirstColumn();

        foreach ($types as $type) {
            $collection->add($type, $type);
        }

        return $collection;
    }

    // -------------------------------------------------------------------------
    // Database helpers
    // -------------------------------------------------------------------------

    /**
     * Recursively scan a filter array for the first '= X' value on the given property.
     *
     * @param array<array-key, mixed> $filter
     */
    private function extractPropertyValue(array $filter, string $property): mixed
    {
        foreach ($filter as $condition) {
            if (!\is_array($condition)) {
                continue;
            }

            $operation = (string) ($condition['operation'] ?? '');

            if ('=' === $operation && $property === ($condition['property'] ?? '')) {
                return $condition['value'];
            }

            if (\in_array($operation, ['AND', 'OR'], true) && \is_array($condition['children'] ?? null)) {
                $value = $this->extractPropertyValue($condition['children'], $property);
                if (null !== $value) {
                    return $value;
                }
            }
        }

        return null;
    }

    /**
     * Return attribute IDs matching a given type within the MetaModel of a parent record.
     *
     * @return list<int>
     */
    private function getAttributeIdsByType(string $type, mixed $parentId): array
    {
        if (null === $parentId) {
            return [];
        }

        $metaModelId = $this->getMetaModelIdFromParentId((int) $parentId);
        if (null === $metaModelId) {
            return [];
        }

        return $this->connection
            ->createQueryBuilder()
            ->select('id')
            ->from('tl_metamodel_attribute')
            ->where('type = :type AND pid = :pid')
            ->setParameter('type', $type)
            ->setParameter('pid', $metaModelId)
            ->executeQuery()
            ->fetchFirstColumn();
    }

    /**
     * Return attribute IDs whose $column matches a LIKE pattern (DC-General wildcard syntax: * → %).
     *
     * The pid filter is intentionally omitted: the outer pid = parentId condition already restricts
     * the result set to the correct MetaModel, so a cross-MetaModel match has no effect.
     *
     * @return list<int>
     */
    private function getAttributeIdsByLike(string $column, string $wildcardValue): array
    {
        $sqlPattern = \str_replace(['*', '?'], ['%', '_'], $wildcardValue);

        return $this->connection
            ->createQueryBuilder()
            ->select('id')
            ->from('tl_metamodel_attribute')
            ->where($column . ' LIKE :pattern')
            ->setParameter('pattern', $sqlPattern)
            ->executeQuery()
            ->fetchFirstColumn();
    }
}
