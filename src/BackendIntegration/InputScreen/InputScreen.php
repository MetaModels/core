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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Alexander Menk <a.menk@imi.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\BackendIntegration\InputScreen;

use Contao\StringUtil;
use Contao\System;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ConditionChainInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\NotCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionChain;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionInterface;
use MetaModels\Events\CreatePropertyConditionEvent;
use MetaModels\Helper\LocaleUtil;
use MetaModels\IMetaModel;
use MetaModels\IMetaModelsServiceContainer;
use MetaModels\ITranslatedMetaModel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Implementation of IInputScreen.
 *
 * @psalm-import-type TLegend from IInputScreen
 *
 * @deprecated This class will get removed.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @psalm-suppress DeprecatedInterface
 */
class InputScreen implements IInputScreen
{
    /**
     * The service container.
     *
     * @psalm-suppress DeprecatedInterface
     *
     * @var IMetaModelsServiceContainer
     */
    protected $container;

    /**
     * The data for the input screen.
     *
     * @var array
     */
    protected $data;

    /**
     * The legends contained within the input screen.
     *
     * @var array<string, TLegend>
     */
    protected $legends = [];

    /**
     * The properties contained within the input screen.
     *
     * @var array
     */
    protected $properties = [];

    /**
     * The conditions.
     *
     * @var ConditionChainInterface[]
     */
    protected $conditions = [];

    /**
     * Grouping and sorting information.
     *
     * @var list<IInputScreenGroupingAndSorting>
     */
    protected $groupSort = [];

    /**
     * Simple map from property setting id to property name.
     *
     * @var array
     */
    protected $propertyMap = [];

    /**
     * Simple map from property name to property setting id.
     *
     * @var array
     */
    protected $propertyMap2 = [];

    /**
     * Create a new instance.
     *
     * @param IMetaModelsServiceContainer $container    The service container.
     * @param array                       $data         The information about the input screen.
     * @param array                       $propertyRows The information about all contained properties.
     * @param array                       $conditions   The property condition information.
     * @param array                       $groupSort    The grouping and sorting information.
     *
     * @psalm-suppress DeprecatedInterface
     */
    public function __construct($container, $data, $propertyRows, $conditions, $groupSort)
    {
        $this->data      = $data;
        $this->container = $container;

        $this->transformConditions($conditions);
        $this->translateRows($propertyRows);
        $this->transformGroupSort($groupSort);
    }

    /**
     * Transform a legend information into the property legends.
     *
     * @param array      $legend    The legend to transform.
     * @param IMetaModel $metaModel The metamodel the legend belongs to.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function translateLegend($legend, $metaModel)
    {
        $arrLegend = StringUtil::deserialize($legend['legendtitle']);
        if (\is_array($arrLegend)) {
            $strLegend = $this->extractLegendName($arrLegend, $metaModel);
        } else {
            $strLegend = $legend['legendtitle'] ?: 'legend';
        }

        $legendName = StringUtil::standardize($strLegend);

        $this->legends[$legendName] = [
            'name'       => $strLegend,
            'visible'    => !(isset($legend['legendhide']) && (bool) $legend['legendhide']),
            'properties' => []
        ];

        return $legendName;
    }

    /**
     * Extract the legend name in the current requested language.
     *
     * @param array<string, string> $legend    The legend name list.
     * @param IMetaModel            $metaModel The metamodel the legend belongs to.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function extractLegendName(array $legend, IMetaModel $metaModel): string
    {
        // Current backend language.
        // @deprecated usage of TL_LANGUAGE - remove for Contao 5.0.
        $language = LocaleUtil::formatAsLocale($GLOBALS['TL_LANGUAGE']);
        if (null !== ($result = $legend[$language] ?? null)) {
            return $result;
        }
        // Is it a regional locale?
        if (\str_contains($language, '_')) {
            $chunks   = \explode('_', $language);
            $language = \array_shift($chunks);
            unset($chunks);
            if (null !== ($result = $legend[$language] ?? null)) {
                return $result;
            }
        }

        // Try fallback language then.
        if ($metaModel instanceof ITranslatedMetaModel) {
            if (null !== ($result = ($legend[$metaModel->getMainLanguage()] ?? null))) {
                return $result;
            }
        } else {
            /** @psalm-suppress DeprecatedMethod */
            if (null !== ($result = ($legend[(string) $metaModel->getFallbackLanguage()] ?? null))) {
                return $result;
            }
        }

        // Last resort, simply "legend". See issue #926.
        return 'legend' . (\count($this->legends) + 1);
    }

    /**
     * Translate a property.
     *
     * @param array      $property  The property information to transform.
     * @param IMetaModel $metaModel The MetaModel the property belongs to.
     * @param string     $legend    The legend the property belongs to.
     *
     * @return bool
     */
    protected function translateProperty($property, $metaModel, $legend)
    {
        $attribute = $metaModel->getAttributeById((int) $property['attr_id']);

        // Dead meat.
        if (!$attribute) {
            return false;
        }

        $propName = $attribute->getColName();

        $this->legends[$legend]['properties'][] = $propName;

        $this->properties[$propName] = [
            'info' => $attribute->getFieldDefinition($property),
        ];

        return true;
    }

    /**
     * Apply legend conditions to its attribute.
     *
     * @param int $attributeId    The attribute setting id.
     * @param int $activeLegendId The legend setting id.
     *
     * @return void
     */
    protected function applyLegendConditions($attributeId, $activeLegendId)
    {
        // No conditions for legend defined.
        if (!isset($this->conditions[$activeLegendId])) {
            return;
        }

        if (!isset($this->conditions[$attributeId])) {
            $this->conditions[$attributeId] = new PropertyConditionChain();
        }

        $this->conditions[$attributeId]->addCondition($this->conditions[$activeLegendId]);
    }

    /**
     * Translate database rows into legend and property information.
     *
     * @param array $rows The database rows.
     *
     * @return void
     *
     * @throws \RuntimeException When an unknown palette rendering mode is encountered
     *                           (neither 'legend' nor 'attribute').
     */
    protected function translateRows($rows)
    {
        $metaModel      = $this->getMetaModel();
        $activeLegend   = $this->translateLegend(
            ['legendtitle' => $metaModel->getName(), 'legendhide' => false],
            $metaModel
        );
        $activeLegendId = null;

        // First pass, fetch all attribute names.
        $columnNames = [];
        foreach ($rows as $row) {
            if ($row['dcatype'] != 'attribute') {
                continue;
            }

            $attribute = $metaModel->getAttributeById((int) $row['attr_id']);
            if ($attribute) {
                $columnNames[$row['id']] = $attribute->getColName();
            }
        }

        $this->propertyMap  = $columnNames;
        $this->propertyMap2 = array_flip($columnNames);

        // Second pass, translate all information into local properties.
        foreach ($rows as $row) {
            switch ($row['dcatype']) {
                case 'legend':
                    $activeLegend   = $this->translateLegend($row, $metaModel);
                    $activeLegendId = $row['id'];
                    break;

                case 'attribute':
                    $exists = $this->translateProperty($row, $metaModel, $activeLegend);

                    if ($exists && null !== $activeLegendId) {
                        $this->applyLegendConditions($row['id'], $activeLegendId);
                    }

                    break;

                default:
                    throw new \RuntimeException('Unknown palette rendering mode ' . $row['dcatype']);
            }
        }
    }

    /**
     * Transform a single condition into a valid condition object.
     *
     * @param array $condition The condition to transform.
     *
     * @return PropertyConditionInterface
     *
     * @throws \RuntimeException When a condition has not been transformed into a valid handling instance.
     */
    protected function transformCondition($condition)
    {
        $dispatcher = System::getContainer()->get('event_dispatcher');
        assert($dispatcher instanceof EventDispatcherInterface);

        /** @psalm-suppress DeprecatedClass */
        $event = new CreatePropertyConditionEvent($condition, $this->getMetaModel());

        /**
         * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
         * @psalm-suppress DeprecatedClass
         */
        $dispatcher->dispatch($event, CreatePropertyConditionEvent::NAME);

        if (($instance = $event->getInstance()) === null) {
            throw new \RuntimeException(\sprintf(
                'Condition of type %s could not be transformed to an instance.',
                $condition['type']
            ));
        }

        return $instance;
    }

    /**
     * Transform the given condition array into real conditions.
     *
     * @param array $conditions The property condition information.
     *
     * @return void
     */
    protected function transformConditions($conditions)
    {
        // First pass, sort them into pid.
        $sorted = [];
        $byPid  = [];
        foreach ($conditions as $condition) {
            $sorted[$condition['id']]   = $condition;
            $byPid[$condition['pid']][] = $condition['id'];
        }

        $instances = [];
        // Second pass, handle them.
        foreach ($sorted as $id => $condition) {
            $instances[$id] = $this->transformCondition($condition);
        }

        // Sort all conditions into their parents.
        foreach ($byPid as $pid => $ids) {
            foreach ($ids as $id) {
                $settingId = $sorted[$id]['settingId'];
                if (!isset($this->conditions[$settingId])) {
                    $this->conditions[$settingId] = new PropertyConditionChain();
                }
                $result    = $this->conditions[$settingId];
                $condition = $instances[$id];
                $parent    = ($pid === 0) ? $result : $instances[$pid];

                // have other classes in the future.
                if ($parent instanceof ConditionChainInterface) {
                    $parent->addCondition($condition);
                } elseif ($parent instanceof NotCondition) {
                    $parent->setCondition($condition);
                }
            }
        }
    }

    /**
     * Transform the grouping and sorting modes.
     *
     * @param array $rows The rows from the Database to convert.
     *
     * @return void
     */
    protected function transformGroupSort($rows)
    {
        foreach ($rows as $row) {
            $this->groupSort[] = new InputScreenGroupingAndSorting($row, $this);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getId()
    {
        return (int) $this->data['id'];
    }

    /**
     * {@inheritDoc}
     */
    public function getLegends()
    {
        return $this->legends;
    }

    /**
     * {@inheritDoc}
     */
    public function getLegendNames()
    {
        return \array_keys($this->legends);
    }

    /**
     * {@inheritDoc}
     */
    public function getLegend($name)
    {
        return $this->legends[$name];
    }

    /**
     * {@inheritDoc}
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * {@inheritDoc}
     */
    public function getProperty($name)
    {
        return $this->properties[$name];
    }

    /**
     * {@inheritDoc}
     */
    public function getPropertyNames()
    {
        $result = [];
        foreach ($this->getLegends() as $legend) {
            $result = \array_merge($result, $legend['properties']);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getConditionsFor($name)
    {
        $property = $this->propertyMap2[$name];

        return $this->conditions[$property] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function getGroupingAndSorting()
    {
        return $this->groupSort;
    }

    /**
     * {@inheritDoc}
     *
     * @throws \RuntimeException When the instance could not be retrieved.
     */
    public function getMetaModel()
    {
        /** @psalm-suppress DocblockTypeContradiction */
        if (null === $this->data) {
            throw new \RuntimeException(
                'No input screen data available, did you forget to define the view combinations?'
            );
        }

        /** @psalm-suppress DeprecatedMethod */
        $factory   = $this->container->getFactory();
        $metaModel = $factory->getMetaModel($factory->translateIdToMetaModelName($this->data['pid']));

        if ($metaModel === null) {
            throw new \RuntimeException('Could not retrieve MetaModel with id ' . $this->data['pid']);
        }

        return $metaModel;
    }

    /**
     * {@inheritDoc}
     */
    public function getIcon()
    {
        // Determine image to use.
        if ($this->data['backendicon']) {
            return $this->data['backendicon'];
        }

        return '';
    }

    /**
     * {@inheritDoc}
     */
    public function getBackendSection()
    {
        return trim($this->data['backendsection'] ?? '');
    }

    /**
     * {@inheritDoc}
     */
    public function getBackendCaption()
    {
        return StringUtil::deserialize($this->data['backendcaption'] ?? [], true);
    }

    /**
     * {@inheritDoc}
     */
    public function getParentTable()
    {
        return $this->data['ptable'] ?? null;
    }

    /**
     * {@inheritDoc}
     */
    public function isStandalone()
    {
        return $this->data['rendertype'] == 'standalone';
    }

    /**
     * {@inheritDoc}
     */
    public function getRenderMode()
    {
        // If we have variant overwrite all modes and set mode 5 - tree mode.
        $objMetaModels = $this->getMetaModel();
        if ($objMetaModels->hasVariants()) {
            return 'hierarchical';
        }

        return $this->data['rendermode'] ?? '';
    }

    /**
     * {@inheritDoc}
     */
    public function isHierarchical()
    {
        return $this->getRenderMode() === 'hierarchical';
    }

    /**
     * {@inheritDoc}
     */
    public function isParented()
    {
        return $this->getRenderMode() === 'parented';
    }

    /**
     * {@inheritDoc}
     */
    public function isFlat()
    {
        return $this->getRenderMode() === 'flat';
    }

    /**
     * {@inheritDoc}
     */
    public function isEditable()
    {
        return (bool) $this->data['iseditable'];
    }

    /**
     * {@inheritDoc}
     */
    public function isCreatable()
    {
        return (bool) $this->data['iscreatable'];
    }

    /**
     * {@inheritDoc}
     */
    public function isDeletable()
    {
        return (bool) $this->data['isdeleteable'];
    }

    /**
     * {@inheritDoc}
     */
    public function getPanelLayout()
    {
        return $this->data['panelLayout'] ?? '';
    }

    /**
     * {@inheritDoc}
     */
    public function isShowColumns()
    {
        return (bool) $this->data['showColumns'];
    }
}
