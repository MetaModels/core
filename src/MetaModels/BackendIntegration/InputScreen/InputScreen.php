<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2019 The MetaModels team.
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
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\BackendIntegration\InputScreen;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\ConditionChainInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\NotCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionChain;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionInterface;
use MetaModels\Events\CreatePropertyConditionEvent;
use MetaModels\IMetaModel;
use MetaModels\IMetaModelsServiceContainer;

/**
 * Implementation of IInputScreen.
 */
class InputScreen implements IInputScreen
{
    /**
     * The service container.
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
     * @var array
     */
    protected $legends = array();

    /**
     * The properties contained within the input screen.
     *
     * @var array
     */
    protected $properties = array();

    /**
     * The conditions.
     *
     * @var ConditionChainInterface[]
     */
    protected $conditions = array();

    /**
     * Grouping and sorting information.
     *
     * @var IInputScreenGroupingAndSorting[]
     */
    protected $groupSort = array();

    /**
     * Simple map from property setting id to property name.
     *
     * @var array
     */
    protected $propertyMap = array();

    /**
     * Simple map from property name to property setting id.
     *
     * @var array
     */
    protected $propertyMap2 = array();

    /**
     * Create a new instance.
     *
     * @param IMetaModelsServiceContainer $container    The service container.
     *
     * @param array                       $data         The information about the input screen.
     *
     * @param array                       $propertyRows The information about all contained properties.
     *
     * @param array                       $conditions   The property condition information.
     *
     * @param array                       $groupSort    The grouping and sorting information.
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
     *
     * @param IMetaModel $metaModel The metamodel the legend belongs to.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function translateLegend($legend, $metaModel)
    {
        $arrLegend = deserialize($legend['legendtitle']);
        if (is_array($arrLegend)) {
            // Try to use the language string from the array.
            $strLegend = $arrLegend[$GLOBALS['TL_LANGUAGE']];
            if (!$strLegend) {
                // Use the fallback.
                $strLegend = $arrLegend[$metaModel->getFallbackLanguage()];
                if (!$strLegend) {
                    // Last resort, simply "legend". See issue #926.
                    $strLegend = 'legend' . (count($this->legends) + 1);
                }
            }
        } else {
            $strLegend = $legend['legendtitle'] ? $legend['legendtitle'] : 'legend';
        }

        $legendName = standardize($strLegend);

        $this->legends[$legendName] = array
        (
            'name'       => $strLegend,
            'visible'    => !(isset($legend['legendhide']) && (bool) $legend['legendhide']),
            'properties' => array()
        );

        return $legendName;
    }

    /**
     * Translate a property.
     *
     * @param array      $property  The property information to transform.
     *
     * @param IMetaModel $metaModel The MetaModel the property belongs to.
     *
     * @param string     $legend    The legend the property belongs to.
     *
     * @return bool
     */
    protected function translateProperty($property, $metaModel, $legend)
    {
        $attribute = $metaModel->getAttributeById($property['attr_id']);

        // Dead meat.
        if (!$attribute) {
            return false;
        }

        $propName = $attribute->getColName();

        $this->legends[$legend]['properties'][] = $propName;

        $this->properties[$propName] = array
        (
            'info'       => $attribute->getFieldDefinition($property),
        );

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
            array('legendtitle' => $metaModel->getName(), 'legendhide' => false),
            $metaModel
        );
        $activeLegendId = null;

        // First pass, fetch all attribute names.
        $columnNames = array();
        foreach ($rows as $row) {
            if ($row['dcatype'] != 'attribute') {
                continue;
            }

            $attribute = $metaModel->getAttributeById($row['attr_id']);
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

                    if ($exists && $activeLegendId) {
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
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function transformCondition($condition)
    {
        $dispatcher = $GLOBALS['container']['event-dispatcher'];
        $event      = new CreatePropertyConditionEvent($condition, $this->getMetaModel());

        /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher */
        $dispatcher->dispatch(CreatePropertyConditionEvent::NAME, $event);

        if ($event->getInstance() === null) {
            throw new \RuntimeException(sprintf(
                'Condition of type %s could not be transformed to an instance.',
                $condition['type']
            ));
        }

        return $event->getInstance();
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
        $sorted = array();
        $byPid  = array();
        foreach ($conditions as $i => $condition) {
            $sorted[$condition['id']]   = $conditions[$i];
            $byPid[$condition['pid']][] = $condition['id'];
        }

        $instances = array();
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
                $parent    = ($pid == 0) ? $result : $instances[$pid];

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
        return $this->data['id'];
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
        return array_keys($this->legends);
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
        return isset($this->properties[$name]) ? $this->properties[$name] : null;
    }

    /**
     * {@inheritDoc}
     */
    public function getPropertyNames()
    {
        $result = array();
        foreach ($this->getLegends() as $legend) {
            $result = array_merge($result, $legend['properties']);
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function getConditionsFor($name)
    {
        $property = $this->propertyMap2[$name];
        return isset($this->conditions[$property]) ? $this->conditions[$property] : null;
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
        if (null === $this->data) {
            throw new \RuntimeException(
                'No input screen data available, did you forget to define the view combinations?'
            );
        }

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

        return null;
    }

    /**
     * {@inheritDoc}
     */
    public function getBackendSection()
    {
        return trim($this->data['backendsection']);
    }

    /**
     * {@inheritDoc}
     */
    public function getBackendCaption()
    {
        return deserialize($this->data['backendcaption'], true);
    }

    /**
     * {@inheritDoc}
     */
    public function getParentTable()
    {
        return $this->data['ptable'];
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

        return $this->data['rendermode'];
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
        return $this->data['panelLayout'];
    }

    /**
     * {@inheritDoc}
     */
    public function isShowColumns()
    {
        return (bool) $this->data['showColumns'];
    }
}
