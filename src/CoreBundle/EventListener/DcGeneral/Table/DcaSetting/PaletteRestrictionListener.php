<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2017 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\DcaSetting;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\ConditionChainInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionChain;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyValueCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Legend;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\LegendInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PaletteInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Property;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use MetaModels\DcGeneral\DataDefinition\Palette\Condition\Property\InputScreenAttributeIs;

/**
 * This builds the palette conditions as specified by the configuration.
 */
class PaletteRestrictionListener
{
    /**
     * Build the data definition palettes.
     *
     * @param BuildDataDefinitionEvent $event The event.
     *
     * @return void
     */
    public function handle(BuildDataDefinitionEvent $event)
    {
        if (($event->getContainer()->getName() !== 'tl_metamodel_dcasetting')) {
            return;
        }

        $palettes = $event->getContainer()->getPalettesDefinition();
        $legend   = null;

        $subSelectPalettes = $this->getSubSelectPalettes();
        foreach ($palettes->getPalettes() as $palette) {
            $condition = new PropertyValueCondition('dcatype', 'attribute');
            $legend    = $this->getLegend('functions', $palette, $legend);
            $property  = $this->getProperty('readonly', $legend);
            $this->addCondition($property, $condition);
            $legend   = $this->getLegend('title', $palette, $legend);
            $property = $this->getProperty('attr_id', $legend);
            $this->addCondition($property, $condition);

            $condition = new PropertyValueCondition('dcatype', 'legend');
            $legend    = $this->getLegend('title', $palette);
            $property  = $this->getProperty('legendtitle', $legend);

            $this->addCondition($property, $condition);
            $property = $this->getProperty('legendhide', $legend);
            $this->addCondition($property, $condition);

            foreach ($subSelectPalettes as $typeName => $paletteInfo) {
                foreach ($paletteInfo as $legendName => $properties) {
                    foreach ($properties as $propertyName) {
                        $condition = new InputScreenAttributeIs($typeName);
                        $legend    = $this->getLegend($legendName, $palette);
                        $property  = $this->getProperty($propertyName, $legend);
                        $this->addCondition($property, $condition);
                    }
                }
            }
        }
    }

    /**
     * Obtain the sub select palette settings.
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function getSubSelectPalettes()
    {
        if (!isset($GLOBALS['TL_DCA']['tl_metamodel_dcasetting']['metasubselectpalettes']['attr_id'])) {
            return [];
        }

        return (array) $GLOBALS['TL_DCA']['tl_metamodel_dcasetting']['metasubselectpalettes']['attr_id'];
    }

    /**
     * Retrieve the legend with the given name.
     *
     * @param string           $name       Name of the legend.
     *
     * @param PaletteInterface $palette    The palette.
     *
     * @param LegendInterface  $prevLegend The previous legend.
     *
     * @return LegendInterface
     */
    private function getLegend($name, $palette, $prevLegend = null)
    {
        if (!$palette->hasLegend($name)) {
            $palette->addLegend(new Legend($name), $prevLegend);
        }

        return $palette->getLegend($name);
    }

    /**
     * Retrieve a property from a legend or create a new one.
     *
     * @param string          $name   The legend name.
     *
     * @param LegendInterface $legend The legend instance.
     *
     * @return PropertyInterface
     */
    private function getProperty($name, $legend)
    {
        foreach ($legend->getProperties() as $property) {
            if ($property->getName() == $name) {
                return $property;
            }
        }

        $property = new Property($name);
        $legend->addProperty($property);

        return $property;
    }

    /**
     * Add a condition to a property.
     *
     * @param PropertyInterface  $property  The property.
     *
     * @param ConditionInterface $condition The condition to add.
     *
     * @return void
     */
    private function addCondition($property, $condition)
    {
        $currentCondition = $property->getVisibleCondition();
        if ((!($currentCondition instanceof ConditionChainInterface))
            || ($currentCondition->getConjunction() != ConditionChainInterface::OR_CONJUNCTION)
        ) {
            if ($currentCondition === null) {
                $currentCondition = new PropertyConditionChain(array($condition));
            } else {
                $currentCondition = new PropertyConditionChain(array($currentCondition, $condition));
            }
            $currentCondition->setConjunction(ConditionChainInterface::OR_CONJUNCTION);
            $property->setVisibleCondition($currentCondition);
        } else {
            $currentCondition->addCondition($condition);
        }
    }
}
