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

namespace MetaModels\DcGeneral\Events\Table\DcaCombine;

use DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;

/**
 * Handle events for tl_metamodel_dca_combine.dca_id.
 *
 * @package MetaModels\DcGeneral\Events\Table\DcaCombine
 */
class PropertyDcaId
{
	/**
	 * Get all options for the frontend user groups.
	 *
	 * @param GetPropertyOptionsEvent $event The event.
	 *
	 * @return void
	 */
	public static function getOptions(GetPropertyOptionsEvent $event)
	{
		$inputScreens = \Database::getInstance()
			->prepare('SELECT id,name FROM tl_metamodel_dca WHERE pid=?')
			->execute($event->getModel()->getProperty('id'));

		$result = array();
		while ($inputScreens->next())
		{
			$result[$inputScreens->id] = $inputScreens->name;
		}

		$event->setOptions($result);
	}
}
