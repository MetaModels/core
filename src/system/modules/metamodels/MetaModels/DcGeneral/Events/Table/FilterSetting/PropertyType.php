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

namespace MetaModels\DcGeneral\Events\Table\FilterSetting;

use DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;

class PropertyType
{
	/**
	 * provide options for default selection
	 *
	 * @param \DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent $event
	 *
	 * @throws \Exception
	 */
	public static function getOptions(GetPropertyOptionsEvent $event)
	{
		$translator = $event->getEnvironment()->getTranslator();
		$options    = array();

		foreach (array_keys($GLOBALS['METAMODELS']['filters']) as $filter)
		{
			$options[$filter] = $translator->translate('typenames.' . $filter, 'tl_metamodel_filtersetting');
		}

		$event->setOptions($options);
	}
}
