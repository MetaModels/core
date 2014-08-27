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

namespace MetaModels\DcGeneral\Events\Table\InputScreenSortGroup;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\IdSerializer;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyValueCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionChain;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use MetaModels\DcGeneral\DataDefinition\Palette\Condition\Property\InputScreenRenderModeIs;
use MetaModels\Factory;
use MetaModels\IMetaModel;

/**
 * Manipulate the data definition for the property "rendergrouptype" in table "tl_metamodel_dca_sortgroup".
 *
 * @package MetaModels\DcGeneral\Events\Table\InputScreen
 */
class PropertyRenderGroupType
{
	/**
	 * Add a visible condition.
	 *
	 * @param PropertyInterface  $property  The property.
	 *
	 * @param ConditionInterface $condition The condition to add.
	 *
	 * @return void
	 */
	protected static function addCondition(PropertyInterface $property, ConditionInterface $condition)
	{
		$chain = $property->getVisibleCondition();
		if (!($chain
			&& ($chain instanceof PropertyConditionChain)
			&& $chain->getConjunction() == PropertyConditionChain::AND_CONJUNCTION
		))
		{
			if ($property->getVisibleCondition())
			{
				$previous = array($property->getVisibleCondition());
			}
			else
			{
				$previous = array();
			}

			$chain = new PropertyConditionChain(
				$previous,
				PropertyConditionChain::AND_CONJUNCTION
			);

			$property->setVisibleCondition($chain);
		}

		$chain->addCondition($condition);
 	}

	/**
	 * Set the visibility condition for the widget.
	 *
	 * @param BuildDataDefinitionEvent $event The event.
	 *
	 * @return void
	 */
	public static function setVisibility(BuildDataDefinitionEvent $event)
	{
		foreach ($event->getContainer()->getPalettesDefinition()->getPalettes() as $palette)
		{
			foreach ($palette->getProperties() as $property)
			{
				if ($property->getName() != 'rendergrouptype')
				{
					continue;
				}

				self::addCondition($property,
					new PropertyConditionChain(array(
						new InputScreenRenderModeIs('flat'),
						new InputScreenRenderModeIs('parented'),
					),
					PropertyConditionChain::OR_CONJUNCTION
				));
			}
		}
	}
}
