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

namespace MetaModels\DcGeneral\Events\Table\Attribute;

use ContaoCommunityAlliance\DcGeneral\Event\PostPersistModelEvent;
use MetaModels\Attribute\Factory;

/**
 * Handles update operation on tl_metamodel_attribute.
 *
 * @package MetaModels\DcGeneral\Events\Table\Attribute
 */
class UpdateAttribute
{
	/**
	 * Handle the update of an attribute and all attached data.
	 *
	 * @param PostPersistModelEvent $event The event.
	 *
	 * @return void
	 */
	public static function handle(PostPersistModelEvent $event)
	{
		$old         = $event->getOriginalModel();
		$new         = $event->getModel();
		$oldType     = $old ? $old->getProperty('type') : null;
		$newType     = $new->getProperty('type');
		$oldName     = $old ? $old->getProperty('colname') : null;
		$newName     = $new->getProperty('colname');
		$oldInstance = $old ? Factory::createFromArray($old->getPropertiesAsArray()) : null;
		$newInstance = Factory::createFromArray($new->getPropertiesAsArray());

		// If type or column name has been changed, destroy old data and initialize new.
		if (($oldType !== $newType) || ($oldName !== $newName))
		{
			// Destroy old instance.
			if ($oldInstance)
			{
				$oldInstance->destroyAUX();
			}

			// Create new instance aux info.
			if ($newInstance)
			{
				$newInstance->initializeAUX();
			}
		}

		if ($newInstance)
		{
			// Now loop over all values and update the meta in the instance.
			foreach ($new->getPropertiesAsArray() as $strKey => $varValue)
			{
				$newInstance->handleMetaChange($strKey, $varValue);
			}
		}
	}
}
