<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\DcGeneral\Events\Table\InputScreen;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use MetaModels\Factory;

/**
 * Manipulate the data definition for the property "mode" in table "tl_dca".
 *
 * @package MetaModels\DcGeneral\Events\Table\InputScreen
 */
class PropertyMode
{
	/**
	 * Return all valid modes for the current MetaModels rendertype.
	 *
	 * @param GetPropertyOptionsEvent $event The event.
	 *
	 * @return void
	 */
	public static function getValidModes(GetPropertyOptionsEvent $event)
	{
		switch ($event->getModel()->getProperty('rendertype'))
		{
			case 'ctable':
				$arrResult = array('mode_3', 'mode_4', 'mode_6');
				break;
			case 'standalone':
				$arrResult = array('mode_0', 'mode_1', 'mode_2', 'mode_5');

				// Allow tree mode only when no variants are in place.
				if (!Factory::byId($event->getModel()->getProperty('pid'))->hasVariants())
				{
					$arrResult[] = 'mode_6';
				}

				break;
			default:
				$arrResult = array();
				break;
		}

		$event->setOptions($arrResult);
	}

	/**
	 * Prefix the given value with "mode_" to prevent the DC from using numeric ids.
	 *
	 * @param DecodePropertyValueForWidgetEvent $event The event.
	 *
	 * @return void
	 */
	public static function decodeMode(DecodePropertyValueForWidgetEvent $event)
	{
		$event->setValue('mode_' . $event->getValue('mode'));
	}

	/**
	 * Strip the mode prefix from the given value.
	 *
	 * @param EncodePropertyValueFromWidgetEvent $event The event.
	 *
	 * @return void
	 */
	public static function encodeMode(EncodePropertyValueFromWidgetEvent $event)
	{
		$arrSplit = explode('_', $event->getValue('mode'));

		$event->setValue($arrSplit[1]);
	}
}
