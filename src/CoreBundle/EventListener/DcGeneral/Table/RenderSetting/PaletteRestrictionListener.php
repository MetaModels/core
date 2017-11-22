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

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\RenderSetting;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\ConditionChainInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette\PaletteConditionChain;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionChain;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Legend;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\LegendInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PaletteInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Property;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use MetaModels\DcGeneral\DataDefinition\Palette\Condition\Palette\RenderSettingAttributeIs as PaletteCondition;
use MetaModels\DcGeneral\DataDefinition\Palette\Condition\Property\RenderSettingAttributeIs as PropertyCondition;

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
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function handle(BuildDataDefinitionEvent $event)
    {
        if (($event->getContainer()->getName() !== 'tl_metamodel_rendersetting')) {
            return;
        }

        $palettes = $event->getContainer()->getPalettesDefinition();

        foreach ($palettes->getPalettes() as $palette) {
            if ($palette->getName() !== 'default') {
                $paletteCondition = $palette->getCondition();
                if (!($paletteCondition instanceof ConditionChainInterface)
                    || ($paletteCondition->getConjunction() !== PaletteConditionChain::OR_CONJUNCTION)
                ) {
                    $paletteCondition = new PaletteConditionChain(
                        $paletteCondition ? array($paletteCondition) : array(),
                        PaletteConditionChain::OR_CONJUNCTION
                    );
                    $palette->setCondition($paletteCondition);
                }
                $paletteCondition->addCondition(new PaletteCondition($palette->getName()));
            }

            $this->buildMetaPaletteConditions(
                $palette, (array)
                $GLOBALS['TL_DCA']['tl_metamodel_rendersetting']['metapalettes']
            );
        }
    }

    /**
     * Apply conditions for meta palettes of the certain render setting types.
     *
     * @param PaletteInterface $palette      The palette.
     * @param array            $metaPalettes The meta palette information.
     *
     * @return void
     */
    private function buildMetaPaletteConditions($palette, $metaPalettes)
    {
        foreach ($metaPalettes as $typeName => $paletteInfo) {
            if ($typeName == 'default') {
                continue;
            }

            if (preg_match('#^(\w+) extends (\w+)$#', $typeName, $matches)) {
                $typeName = $matches[1];
            }

            foreach ($paletteInfo as $legendName => $properties) {
                foreach ($properties as $propertyName) {
                    $condition = new PropertyCondition($typeName);
                    $legend    = $this->getLegend($legendName, $palette);
                    $property  = $this->getProperty($propertyName, $legend);
                    $this->addCondition($property, $condition);
                }
            }
        }
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
        if ($name[0] == '+') {
            $name = substr($name, 1);
        }

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
