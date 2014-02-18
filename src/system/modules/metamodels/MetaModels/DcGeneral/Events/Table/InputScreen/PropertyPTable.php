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

namespace MetaModels\DcGeneral\Events\Table\InputScreen;

use DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use DcGeneral\Data\ModelInterface;
use DcGeneral\DataDefinition\Palette\Condition\Property\PropertyValueCondition;
use DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionChain;
use DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use MetaModels\Factory;
use MetaModels\IMetaModel;

/**
 * Manipulate the data definition for the property "ptable" in table "tl_dca".
 *
 * @package MetaModels\DcGeneral\Events\Table\InputScreen
 */
class PropertyPTable
{
	/**
	 * Return the MetaModel currently in scope of the given model.
	 *
	 * @param ModelInterface $model The model that holds the information about the input screen.
	 *
	 * @return IMetaModel The MetaModel instance.
	 */
	protected static function getMetaModel(ModelInterface $model)
	{
		if ($model->getProperty('pid'))
		{
			return Factory::byId($model->getProperty('pid'));
		}

		return null;
	}

	/**
	 * Returns an array with all valid tables that can be used as parent table.
	 *
	 * Excludes the metamodel table itself in ctable mode, as that one would be "selftree" then and not ctable.
	 *
	 * @param GetPropertyOptionsEvent $event The event.
	 *
	 * @return void
	 */
	public static function getTables(GetPropertyOptionsEvent $event)
	{
		$currentTable = '';
		if ($event->getModel()->getProperty('rendertype') == 'ctable')
		{
			$currentTable = self::getMetaModel($event->getModel())->getTableName();
		}

		$tables = array();
		foreach (\Database::getInstance()->listTables() as $table)
		{
			if (!($currentTable && ($currentTable == $table)))
			{
				$tables[$table] = $table;
			}
		}

		$event->setOptions($tables);
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
				if ($property->getName() != 'ptable')
				{
					continue;
				}

				$chain = $property->getVisibleCondition();
				if (!($chain
					&& ($chain instanceof PropertyConditionChain)
					&& $chain->getConjunction() == PropertyConditionChain::AND_CONJUNCTION
				))
				{
					$chain = new PropertyConditionChain(
						array($property->getVisibleCondition()),
						PropertyConditionChain::AND_CONJUNCTION
					);

					$property->setVisibleCondition($chain);
				}

				$chain->addCondition(new PropertyValueCondition('rendertype', 'ctable'));
			}
		}
	}
}
