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

namespace MetaModels\DcGeneral\Events\Table\RenderSettings;

use DcGeneral\Event\PostPersistModelEvent;

/**
 * Handles update operation on tl_metamodel_rendersettings.
 *
 * @package MetaModels\DcGeneral\Events\Table\MetaModels
 */
class UpdateRenderSettings
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
		$new = $event->getModel();

		if (!$new->getProperty('isdefault'))
		{
			return;
		}

		$foo =
		\Database::getInstance()
			->prepare('UPDATE tl_metamodel_rendersettings
					SET isdefault = \'\'
					WHERE pid=?
						AND id<>?
						AND isdefault=1')
			->execute(
				$new->getProperty('pid'),
				$new->getId()
			);
	}
}
