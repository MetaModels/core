<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\DcGeneral\Events\Table\InputScreens;

use DcGeneral\DataDefinition\ConditionChainInterface;
use DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionChain;
use DcGeneral\DataDefinition\Palette\Condition\Property\PropertyValueCondition;
use DcGeneral\DataDefinition\Palette\Legend;
use DcGeneral\DataDefinition\Palette\LegendInterface;
use DcGeneral\DataDefinition\Palette\PaletteInterface;
use DcGeneral\DataDefinition\Palette\Property;
use DcGeneral\DataDefinition\Palette\PropertyInterface;
use DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use MetaModels\DcGeneral\DataDefinition\Palette\Condition\Property\InputScreenAttributeIs;

class BuildPalette
{
	/**
	 * @param string           $name
	 *
	 * @param PaletteInterface $palette
	 *
	 * @param LegendInterface  $prevLegend
	 *
	 * @return LegendInterface
	 */
	public static function getLegend($name, $palette, $prevLegend = null)
	{
		if (!$palette->hasLegend($name))
		{
			$palette->addLegend(new Legend($name), $prevLegend);
		}

		return $palette->getLegend($name);
	}

	/**
	 * @param string          $name
	 *
	 * @param LegendInterface $legend
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
	 * @param PropertyInterface $property
	 *
	 * @param                   $condition
	 */
	public static function addCondition($property, $condition)
	{
		$currentCondition = $property->getVisibleCondition();
		if ((!($currentCondition instanceof ConditionChainInterface)) || ($currentCondition->getConjunction() != ConditionChainInterface::OR_CONJUNCTION))
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
	 * @param BuildDataDefinitionEvent $event
	 */
	public static function build(BuildDataDefinitionEvent $event)
	{
		$palettes = $event->getContainer()->getPalettesDefinition();
		$legend   = null;

		foreach ($palettes->getPalettes() as $palette)
		{
			$condition = new PropertyValueCondition('dcatype', 'attribute');
			$legend    = self::getLegend('functions', $palette, $legend);
			$property = self::getProperty('readonly', $legend);
			self::addCondition($property, $condition);
			$legend    = self::getLegend('title', $palette, $legend);
			$property = self::getProperty('attr_id', $legend);
			self::addCondition($property, $condition);

			$condition = new PropertyValueCondition('dcatype', 'legend');
			$legend    = self::getLegend('title', $palette);
			$property  = self::getProperty('legendtitle', $legend);

			self::addCondition($property, $condition);
			$property = self::getProperty('legendhide', $legend);
			self::addCondition($property, $condition);

			foreach ($GLOBALS['TL_DCA']['tl_metamodel_dcasetting']['metasubselectpalettes']['attr_id'] as $typeName => $paletteInfo)
			{
				foreach ($paletteInfo as $legendName => $properties)
				{
					foreach ($properties as $propertyName)
					{
						$condition = new InputScreenAttributeIs($typeName);
						$legend    = self::getLegend($legendName, $palette);
						$property  = self::getProperty($propertyName, $legend);
						self::addCondition($property, $condition);
					}
				}
			}
		}
	}
}
