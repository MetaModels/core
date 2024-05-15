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
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\ViewCombination;

use Contao\StringUtil;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use MetaModels\IFactory;
use MetaModels\IMetaModel;
use MetaModels\ITranslatedMetaModel;

/**
 * This class obtains information from the database about input screens.
 */
class InputScreenInformationBuilder
{
    /**
     * The database connection.
     *
     * @var Connection
     */
    private Connection $connection;

    /**
     * The MetaModels factory.
     *
     * @var IFactory
     */
    private IFactory $factory;

    /**
     * Create a new instance.
     *
     * @param Connection $connection The database connection.
     * @param IFactory   $factory    The MetaModels factory.
     */
    public function __construct(Connection $connection, IFactory $factory)
    {
        $this->connection = $connection;
        $this->factory    = $factory;
    }

    /**
     * Fetch information about all input screens.
     *
     * @return array
     *
     * @throws Exception
     */
    public function fetchAllInputScreensForTable(string $tableName): array
    {
        $builder = $this->connection->createQueryBuilder();
        $screens = $builder
            ->select('d.*')
            ->from('tl_metamodel_dca', 'd')
            ->leftJoin('d', 'tl_metamodel', 'm', 'm.id=d.pid')
            ->where($builder->expr()->in('m.tableName', ':tableName'))
            ->setParameter('tableName', $tableName)
            ->orderBy('m.sorting')
            ->executeQuery()
            ->fetchAllAssociative();

        $result = [];
        foreach ($screens as $screen) {
            $result[$screen['id']] = $this->prepareInputScreen($tableName, $screen);
            // FIXME: simplify prepareInputScreen to not translate the values inline but define translation keys.
        }

        return $result;
    }

    /**
     * Fetch information about an input screen.
     *
     * @param array $idList The ids of the input screens to obtain (table name => id).
     *
     * @return array
     * @throws Exception
     */
    public function fetchInputScreens($idList): array
    {
        $idList  = \array_filter($idList);
        $builder = $this->connection->createQueryBuilder();
        $screens = $builder
            ->select('d.*')
            ->from('tl_metamodel_dca', 'd')
            ->leftJoin('d', 'tl_metamodel', 'm', 'm.id=d.pid')
            ->where($builder->expr()->in('d.id', ':idList'))
            ->setParameter('idList', $idList, ArrayParameterType::STRING)
            ->orderBy('m.sorting')
            ->executeQuery()
            ->fetchAllAssociative();

        $result = [];
        $keys   = \array_flip($idList);
        foreach ($screens as $screen) {
            $metaModelName          = $keys[$screen['id']];
            $result[$metaModelName] = $this->prepareInputScreen($metaModelName, $screen);
        }

        return $result;
    }

    /**
     * Prepare the input screen data.
     *
     * @param string $modelName The MetaModel name.
     * @param array  $screen    The screen meta data.
     *
     * @return array{
     *   meta: array,
     *   properties: array,
     *   conditions: list<array{setting_attr_id: string, ...<string, string>}>,
     *   groupSort: array,
     *   label: array<string, string>,
     *   description: array<string, string>,
     *   legends: array,
     * }
     *
     * @throws \InvalidArgumentException When the MetaModel can not be retrieved.
     */
    private function prepareInputScreen(string $modelName, array $screen): array
    {
        if (null === $metaModel = $this->factory->getMetaModel($modelName)) {
            throw new \InvalidArgumentException('Could not retrieve MetaModel ' . $modelName);
        }
        $caption     = ['' => $metaModel->getName()];
        $description = ['' => ''];
        $fallback    = null;
        /**
         * @psalm-suppress DeprecatedMethod
         * @psalm-suppress TooManyArguments
         */
        if ($metaModel instanceof ITranslatedMetaModel) {
            $fallback = $metaModel->getMainLanguage();
        } elseif ($metaModel->isTranslated(false)) {
            /** @psalm-suppress DeprecatedMethod */
            $fallback = $metaModel->getFallbackLanguage();
        }
        foreach (StringUtil::deserialize($screen['backendcaption'], true) as $languageEntry) {
            $langCode               = $languageEntry['langcode'];
            $caption[$langCode]     = !empty($label = $languageEntry['label']) ? $label : $caption[''];
            $description[$langCode] = !empty($title = $languageEntry['description']) ? $title : $description[''];
            if ($fallback === $langCode) {
                $caption['']     = $label;
                $description[''] = $title;
            }
        }

        $result = [
            'meta'        => $screen,
            'properties'  => $this->fetchPropertiesFor($screen['id'], $metaModel),
            'conditions'  => $this->fetchConditions($screen['id']),
            'groupSort'   => $this->fetchGroupSort($screen['id'], $metaModel),
            'label'       => $caption,
            'description' => $description
        ];

        $bySetting         = $this->buildConditionTree($result['conditions']);
        $result['legends'] = $this->convertLegends($result['properties'], $metaModel, $bySetting);

        return $result;
    }

    /**
     * Build condition tree.
     *
     * @param array $conditions The condition information.
     *
     * @return array
     */
    private function buildConditionTree(array $conditions): array
    {
        // Build condition tree.
        $conditionMap = [];
        $bySetting    = [];
        foreach ($conditions as $condition) {
            unset($converted);
            $conditionId        = $condition['id'];
            $conditionPid       = $condition['pid'];
            $conditionSettingId = $condition['settingId'];
            // Check if already mapped, if so, we need to set the values.
            if (\array_key_exists($conditionId, $conditionMap)) {
                $converted = &$conditionMap[$conditionId];
                foreach ($condition as $key => $value) {
                    $converted[$key] = $value;
                }
            } else {
                $converted                  = \array_slice($condition, 0);
                $conditionMap[$conditionId] = &$converted;
            }
            // Is on root level - add to setting now.
            if (empty($conditionPid)) {
                /** @psalm-suppress UnsupportedReferenceUsage */
                $bySetting[$conditionSettingId][] = &$converted;
                continue;
            }
            // Is a child, check if parent already added.
            if (!isset($conditionMap[$conditionPid])) {
                $temp                        = ['children' => []];
                $conditionMap[$conditionPid] = &$temp;
            }
            // Add child to parent now.
            /** @psalm-suppress UnsupportedReferenceUsage */
            $conditionMap[$conditionPid]['children'][] = &$converted;
        }

        return $bySetting;
    }

    /**
     * Fetch all properties for the passed input screen.
     *
     * @param string     $inputScreenId The input screen to obtain properties for.
     * @param IMetaModel $metaModel     The MetaModel to fetch properties for.
     *
     * @return array
     * @throws Exception
     */
    private function fetchPropertiesFor(string $inputScreenId, IMetaModel $metaModel): array
    {
        $builder = $this->connection->createQueryBuilder();

        return \array_map(
            static function ($column) use ($inputScreenId, $metaModel) {
                if ('attribute' !== $column['dcatype']) {
                    return $column;
                }
                if (!($attribute = $metaModel->getAttributeById((int)$column['attr_id']))) {
                    // @codingStandardsIgnoreStart
                    @trigger_error(
                        'Unknown attribute "' . $column['attr_id'] . '" in input screen "' . $inputScreenId . '"',
                        E_USER_WARNING
                    );

                    // @codingStandardsIgnoreEnd
                    return $column;
                }

                $column = \array_merge(
                    $column,
                    $attribute->getFieldDefinition($column),
                    ['col_name' => $attribute->getColName()]
                );

                return $column;
            },
            $builder
                ->select('t.*')
                ->from('tl_metamodel_dcasetting', 't')
                ->where('t.pid=:pid')
                ->andWhere('t.published=:published')
                ->setParameter('pid', $inputScreenId)
                ->setParameter('published', 1)
                ->orderBy('t.sorting')
                ->executeQuery()
                ->fetchAllAssociative()
        );
    }

    /**
     * Fetch conditions for an input screen.
     *
     * @param string $inputScreenId The input screen to obtain conditions for.
     *
     * @return list<array{setting_attr_id: string, ...<string, string>}>
     */
    private function fetchConditions(string $inputScreenId): array
    {
        $builder = $this->connection->createQueryBuilder();

        return $builder
            ->select('cond.*', 'setting.attr_id AS setting_attr_id')
            ->from('tl_metamodel_dcasetting_condition', 'cond')
            ->leftJoin('cond', 'tl_metamodel_dcasetting', 'setting', 'cond.settingId=setting.id')
            ->leftJoin('setting', 'tl_metamodel_dca', 'dca', 'setting.pid=dca.id')
            ->where('cond.enabled=1')
            ->andWhere('setting.published=1')
            ->andWhere('dca.id=:screenId')
            ->setParameter('screenId', $inputScreenId)
            ->orderBy('cond.pid')
            ->addOrderBy('cond.sorting')
            ->executeQuery()
            ->fetchAllAssociative();
    }

    /**
     * Fetch groupings for an input screen.
     *
     * @param string     $inputScreenId The input screen to obtain properties for.
     * @param IMetaModel $metaModel     The MetaModel to fetch properties for.
     *
     * @return array
     *
     * @throws Exception
     */
    private function fetchGroupSort(string $inputScreenId, IMetaModel $metaModel): array
    {
        $builder = $this->connection->createQueryBuilder();

        return \array_map(
            static function ($information) use ($inputScreenId, $metaModel) {
                $information['isdefault']      = (bool)$information['isdefault'];
                $information['ismanualsort']   = (bool)$information['ismanualsort'];
                $information['rendergrouplen'] = (int)$information['rendergrouplen'];
                if ($information['ismanualsort']) {
                    $information['rendergrouptype'] = 'none';
                }

                if (!empty($information['rendersortattr'])) {
                    if (!($attribute = $metaModel->getAttributeById((int)$information['rendersortattr']))) {
                        // @codingStandardsIgnoreStart
                        @trigger_error(
                            \sprintf(
                                'Unknown attribute "%1$s" in group sorting "%2$s.%3$s"',
                                $information['rendersortattr'],
                                $inputScreenId,
                                $information['id']
                            ),
                            E_USER_WARNING
                        );

                        // @codingStandardsIgnoreEnd
                        return $information;
                    }
                    $information['col_name'] = $attribute->getColName();
                }

                return $information;
            },
            $builder
                ->select('t.*')
                ->from('tl_metamodel_dca_sortgroup', 't')
                ->where('t.pid=:screenId')
                ->setParameter('screenId', $inputScreenId)
                ->orderBy('t.sorting')
                ->executeQuery()
                ->fetchAllAssociative()
        );
    }

    /**
     * Convert property list to legend list.
     *
     * @param array      $properties The property and legend information.
     * @param IMetaModel $metaModel  The MetaModel to fetch properties for.
     * @param array      $conditions The conditions for the entries.
     *
     * @return array
     */
    private function convertLegends(array $properties, IMetaModel $metaModel, array $conditions): array
    {
        $result = [];
        /**
         * @psalm-suppress DeprecatedMethod
         * @psalm-suppress TooManyArguments
         */
        $trans  = (($metaModel instanceof ITranslatedMetaModel) || $metaModel->isTranslated(false));
        $label  = $this->buildLabel($trans, $metaModel);
        $legend = [
            'label'      => $label,
            'hide'       => false,
            'properties' => []
        ];

        $condition = function (array $property) use ($conditions): ?array {
            if (!isset($conditions[($property['id'])])) {
                return null;
            }

            return [
                'type'     => 'conditionand',
                'children' => $conditions[$property['id']]
            ];
        };

        $fallbackLanguage = null;
        /**
         * @psalm-suppress DeprecatedMethod
         * @psalm-suppress TooManyArguments
         */
        if ($metaModel instanceof ITranslatedMetaModel) {
            $fallbackLanguage = $metaModel->getMainLanguage();
        } elseif ($metaModel->isTranslated(false)) {
            /** @psalm-suppress DeprecatedMethod */
            $fallbackLanguage = $metaModel->getFallbackLanguage();
        }
        foreach ($properties as $property) {
            switch ($property['dcatype']) {
                case 'legend':
                    $this->convertLegend($property, $fallbackLanguage, $condition, $legend, $result);
                    break;
                case 'attribute':
                    $this->convertAttribute($property, $condition, $legend);
                    break;
                default:
                    break;
            }
        }
        if (!empty($legend['properties'])) {
            $result['legend' . (\count($result) + 1)] = $legend;
        }

        return $result;
    }

    /**
     * Build the label.
     *
     * @param bool       $isTranslated Flag if the MetaModel is translated.
     * @param IMetaModel $metaModel    The MetaModel instance.
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function buildLabel(bool $isTranslated, IMetaModel $metaModel): array
    {
        if ($isTranslated) {
            $label = [];
            /** @psalm-suppress DeprecatedMethod */
            foreach (
                ($metaModel instanceof ITranslatedMetaModel)
                ? $metaModel->getLanguages()
                : ($metaModel->getAvailableLanguages() ?? []) as $availableLanguage
            ) {
                $label[$availableLanguage] = $metaModel->getName();
            }
            $label['default'] = $metaModel->getName();

            return $label;
        }

        return ['default' => $metaModel->getName()];
    }

    /**
     * Convert a legend property.
     *
     * @param array       $property  The property information to convert.
     * @param string|null $fallback  The fallback language if the MetaModel is translated or null otherwise.
     * @param callable    $condition The condition transformer.
     * @param array       $legend    The current legend information.
     * @param array       $result    The resulting information.
     *
     * @return void
     */
    private function convertLegend(array $property, ?string $fallback, $condition, array &$legend, array &$result)
    {
        if (!empty($legend['properties'])) {
            $result['legend' . (\count($result) + 1)] = $legend;
        }
        if (null === $fallback) {
            $label = ['default' => $property['legendtitle']];
        } else {
            $label            = unserialize($property['legendtitle'], ['allowed_classes' => false]);
            $label['default'] = $label[$fallback];
        }
        $legend = [
            'label'      => $label,
            'hide'       => (bool) $property['legendhide'],
            'properties' => [],
            'condition'  => $condition($property)
        ];
    }

    /**
     * Convert an attribute property.
     *
     * @param array    $property  The property information to convert.
     * @param callable $condition The condition transformer.
     * @param array    $legend    The current legend information.
     *
     * @return void
     */
    private function convertAttribute(array $property, $condition, array &$legend)
    {
        if (!isset($property['col_name'])) {
            return;
        }
        $legend['properties'][] = [
            'name'      => $property['col_name'],
            'condition' => $condition($property)
        ];
    }
}
