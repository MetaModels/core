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

use DcGeneral\Contao\View\Contao2BackendView\Event\BuildWidgetEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use MetaModels\Dca\Helper;

class PropertyLegendTitle
	extends InputScreenBase
{
	/**
	 * @param \DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent $event
	 */
	public static function decodeValue(DecodePropertyValueForWidgetEvent $event)
	{
		$metaModel = self::getMetaModelFromModel($event->getModel());

		$values = Helper::decodeLangArray($event->getValue(), $metaModel);

		$event->setValue($values);
	}

	/**
	 * @param \DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent $event
	 */
	public static function encodeValue(EncodePropertyValueFromWidgetEvent $event)
	{
		$metaModel = self::getMetaModelFromModel($event->getModel());

		$values = Helper::encodeLangArray($event->getValue(), $metaModel);

		$event->setValue($values);
	}

	public static function buildWidget(BuildWidgetEvent $event)
	{
		$metaModel = self::getMetaModelFromModel($event->getModel());

		\MetaModels\Dca\Helper::prepareLanguageAwareWidget(
			$event->getEnvironment(),
			$event->getProperty(),
			$metaModel,
			$event->getEnvironment()->getTranslator()->translate('name_langcode', 'tl_metamodel_dcasetting'),
			$event->getEnvironment()->getTranslator()->translate('name_value', 'tl_metamodel_dcasetting'),
			false,
			$event->getModel()->getProperty('legendtitle')
		);
	}
}
