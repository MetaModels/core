<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2018 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\FilterSetting;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionChain;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Legend;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\LegendInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PaletteInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Property;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use MetaModels\DcGeneral\DataDefinition\Palette\Condition\Property\FilterSettingTypeSubPaletteCondition;
use MetaModels\Filter\Setting\IFilterSettingFactory;

/**
 * This takes care of injecting the sub palettes.
 */
class SubPaletteSubscriber
{
    /**
     * The filter setting factory.
     *
     * @var IFilterSettingFactory
     */
    private $filterFactory;

    /**
     * Create a new instance.
     *
     * @param IFilterSettingFactory $filterFactory The filter setting factory.
     */
    public function __construct(IFilterSettingFactory $filterFactory)
    {
        $this->filterFactory = $filterFactory;
    }

    /**
     * Prepares the sub palettes e. g. add option for translated attributes for different filter types.
     *
     * @param BuildDataDefinitionEvent $event The event.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function prepareSubPalettes(BuildDataDefinitionEvent $event)
    {
        $container = $event->getContainer();
        if ($container->getName() !== 'tl_metamodel_filtersetting') {
            return;
        }

        foreach ($this->filterFactory->getTypeNames() as $filterType) {
            $paletteName = $filterType . '_palettes';

            if (!isset($GLOBALS['TL_DCA']['tl_metamodel_filtersetting'][$paletteName])) {
                continue;
            }

            $palettes    = $container->getPalettesDefinition();
            $typeLegends = $GLOBALS['TL_DCA']['tl_metamodel_filtersetting'][$paletteName];

            foreach ($palettes->getPalettes() as $palette) {
                $this->createConditionsForPalette($palette, $typeLegends);
            }
        }
    }

    /**
     * Create all conditions for the given palette.
     *
     * @param PaletteInterface $palette     The palette.
     * @param array            $typeLegends The type legends.
     *
     * @return void
     */
    private function createConditionsForPalette(PaletteInterface $palette, array $typeLegends)
    {
        $conditions = [];

        foreach ($typeLegends as $value => $legends) {
            // We use an immutable implementation. Using the same condition is save here.
            $valueCondition = new FilterSettingTypeSubPaletteCondition($this->filterFactory, $value);

            foreach ($legends as $legendName => $legendProperties) {
                $legend = $this->getLegend($palette, $legendName);

                foreach ($legendProperties as $propertyName) {
                    $this
                        ->getConditionChain($legend, $propertyName, $conditions)
                        ->addCondition($valueCondition);
                }
            }
        }
    }

    /**
     * Get a legend. Create it if not exists.
     *
     * @param PaletteInterface $palette    The palette.
     * @param string           $legendName The name of the legend.
     *
     * @return LegendInterface
     */
    private function getLegend(PaletteInterface $palette, $legendName)
    {
        if ($palette->hasLegend($legendName)) {
            return $palette->getLegend($legendName);
        }

        $legend = new Legend($legendName);
        $palette->addLegend($legend);

        return $legend;
    }

    /**
     * Get the property chain condition for the property.
     *
     * @param LegendInterface $legend       The legend.
     * @param string          $propertyName The legend property name.
     * @param array           $conditions   Conditions assigned to the properties in this palette.
     *
     * @return PropertyConditionChain
     */
    private function getConditionChain(LegendInterface $legend, $propertyName, array &$conditions)
    {
        // Cache condition chain for each legend property.
        if (isset($conditions[$legend->getName()][$propertyName])) {
            return $conditions[$legend->getName()][$propertyName];
        }

        $property = $this->getLegendProperty($legend, $propertyName);

        // There is no condition assigned to the property. Create an condition chain with an and conjunction
        // and add the condition condition chain for the sub palette with an or condition to it.
        $condition = $this->getVisibleCondition($property);

        $orCondition = new PropertyConditionChain();
        $orCondition->setConjunction(PropertyConditionChain::OR_CONJUNCTION);

        $conditions[$legend->getName()][$propertyName] = $orCondition;

        $condition->addCondition($orCondition);

        return $orCondition;
    }

    /**
     * Get a property from a legend. Create if not exists.
     *
     * @param LegendInterface $legend       The legend.
     * @param string          $propertyName The property name.
     *
     * @return PropertyInterface
     */
    private function getLegendProperty(LegendInterface $legend, $propertyName)
    {
        if ($legend->hasProperty($propertyName)) {
            $property = $legend->getProperty($propertyName);
        } else {
            $property = new Property($propertyName);
            $legend->addProperty($property);
        }

        return $property;
    }

    /**
     * Get the visible condition for a property. Create it if not exists.
     *
     * @param PropertyInterface $property Palette property.
     *
     * @return PropertyConditionChain
     */
    private function getVisibleCondition($property)
    {
        $condition = $property->getVisibleCondition();
        if ($condition instanceof PropertyConditionChain) {
            return $condition;
        }

        $conditionChain = new PropertyConditionChain();
        $property->setVisibleCondition($conditionChain);

        if ($condition) {
            $conditionChain->addCondition($condition);
        }

        return $conditionChain;
    }
}
