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

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;

/**
 * Manipulate the data definition for the property "backendsection" in table "tl_dca".
 *
 * @package MetaModels\DcGeneral\Events\Table\InputScreen
 */

class PropertyBackendSection
{
	/**
	 * Retrieve a list of all backend sections, like "content", "system" etc.
	 *
	 * @param GetPropertyOptionsEvent $event The event.
	 *
	 * @return void
	 */
	public static function getSections(GetPropertyOptionsEvent $event)
	{
		$event->setOptions(array_keys($GLOBALS['BE_MOD']));
	}
}
