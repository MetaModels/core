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

use DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;

class PropertyRte
	extends InputScreenBase
{
	public static function getOptions(GetPropertyOptionsEvent $event)
	{
		$configs=array();
		foreach(glob(TL_ROOT . '/system/config/tiny*.php') as $name)
		{
			$name = basename($name);
			if((strpos($name, 'tiny')===0) && (substr($name, -4, 4)=='.php'))
				$configs[]=substr($name, 0, -4);
		}
		$event->setOptions($configs);
	}
}
