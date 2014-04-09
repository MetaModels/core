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

namespace MetaModels\DcGeneral\Events\Table\MetaModels;

use ContaoCommunityAlliance\DcGeneral\Event\PostPersistModelEvent;
use MetaModels\Helper\TableManipulation;

/**
 * Handles update operation on tl_metamodel.
 *
 * @package MetaModels\DcGeneral\Events\Table\MetaModels
 */
class UpdateMetaModel
{
	/**
	 * Handle the update of a MetaModel and all attached data.
	 *
	 * @param PostPersistModelEvent $event The event.
	 *
	 * @return void
	 */
	public static function handle(PostPersistModelEvent $event)
	{
		$old      = $event->getOriginalModel();
		$new      = $event->getModel();
		$oldTable = $old ? $old->getProperty('tableName') : null;
		$newTable = $new->getProperty('tableName');

		// Table name changed?
		if ($oldTable !== $newTable)
		{
			if ($oldTable && \Database::getInstance()->tableExists($oldTable, null, true))
			{
				TableManipulation::renameTable($oldTable, $newTable);
				// TODO: notify attributes that the MetaModel has changed its table name.

			} else {
				TableManipulation::createTable($newTable);
			}
		}

		TableManipulation::setVariantSupport($newTable, $new->getProperty('varsupport'));
	}
}
