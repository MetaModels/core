<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\DcGeneral\Events\Table\RenderSetting;

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
use MetaModels\DcGeneral\DataDefinition\Palette\Condition\Property\RenderSettingAttributeIs as PropertyCondition;
use MetaModels\DcGeneral\DataDefinition\Palette\Condition\Palette\RenderSettingAttributeIs as PaletteCondition;

/**
 * Handle events for tl_metamodel_rendersetting palette building.
 */
class RenderSettingBuildPalette
{
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
    public static function getLegend($name, $palette, $prevLegend = null)
    {
        if ($name[0] == '+')
        {
            $name = substr($name, 1);
        }

        if (!$palette->hasLegend($name))
        {
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
    public static function getProperty($name, $legend)
    {
        foreach ($legend->getProperties() as $property)
        {
            if ($property->getName() == $name)
            {
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
    public static function addCondition($property, $condition)
    {
        $currentCondition = $property->getVisibleCondition();
        if ((!($currentCondition instanceof ConditionChainInterface))
            || ($currentCondition->getConjunction() != ConditionChainInterface::OR_CONJUNCTION)
        )
        {
            if ($currentCondition === null)
            {
                $currentCondition = new PropertyConditionChain(array($condition));
            }
            else
            {
                $currentCondition = new PropertyConditionChain(array($currentCondition, $condition));
            }
            $currentCondition->setConjunction(ConditionChainInterface::OR_CONJUNCTION);
            $property->setVisibleCondition($currentCondition);
        }
        else
        {
            $currentCondition->addCondition($condition);
        }
    }

    /**
     * Build the data definition palettes.
     *
     * @param BuildDataDefinitionEvent $event The event.
     *
     * @return void
     */
    public static function build(BuildDataDefinitionEvent $event)
    {
        $palettes = $event->getContainer()->getPalettesDefinition();
        $legend   = null;

        foreach ($palettes->getPalettes() as $palette)
        {
            if ($palette->getName() !== 'default')
            {
                $paletteCondition = $palette->getCondition();
                if (!($paletteCondition instanceof ConditionChainInterface)
                    || ($paletteCondition->getConjunction() !== PaletteConditionChain::OR_CONJUNCTION)
                )
                {
                    $paletteCondition = new PaletteConditionChain(
                        $paletteCondition ? array($paletteCondition) : array(),
                        PaletteConditionChain::OR_CONJUNCTION
                    );
                    $palette->setCondition($paletteCondition);
                }
                $paletteCondition->addCondition(new PaletteCondition($palette->getName()));
            }

            foreach ((array)$GLOBALS['TL_DCA']['tl_metamodel_rendersetting']['metapalettes'] as
                $typeName => $paletteInfo)
            {
                if ($typeName == 'default')
                {
                    continue;
                }

                if (preg_match('#^(\w+) extends (\w+)$#', $typeName, $matches))
                {
                    $typeName = $matches[1];
                }

                foreach ($paletteInfo as $legendName => $properties)
                {
                    foreach ($properties as $propertyName)
                    {
                        $condition = new PropertyCondition($typeName);
                        $legend    = self::getLegend($legendName, $palette);
                        $property  = self::getProperty($propertyName, $legend);
                        self::addCondition($property, $condition);
                    }
                }
            }
        }
    }
}
