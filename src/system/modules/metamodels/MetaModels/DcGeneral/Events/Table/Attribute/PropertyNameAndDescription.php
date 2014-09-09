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

namespace MetaModels\DcGeneral\Events\Table\Attribute;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\BuildWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use MetaModels\Dca\Helper;

/**
 * Handle events for properties "name" and "description".
 *
 * @package MetaModels\DcGeneral\Events\Table\Attribute
 */
class PropertyNameAndDescription
    extends AttributeBase
{
    /**
     * Decode the given value from a serialized language array into the real language array.
     *
     * @param DecodePropertyValueForWidgetEvent $event The event.
     *
     * @return void
     */
    public static function decodeValue(DecodePropertyValueForWidgetEvent $event)
    {
        $metaModel = self::getMetaModelFromModel($event->getModel());

        $values = Helper::decodeLangArray($event->getValue(), $metaModel);

        $event->setValue($values);
    }

    /**
     * Encode the given value from a real language array into a serialized language array.
     *
     * @param EncodePropertyValueFromWidgetEvent $event The event.
     *
     * @return void
     */
    public static function encodeValue(EncodePropertyValueFromWidgetEvent $event)
    {
        $metaModel = self::getMetaModelFromModel($event->getModel());

        $values = Helper::encodeLangArray($event->getValue(), $metaModel);

        $event->setValue($values);
    }

    /**
     * Build the widget for the MCW.
     *
     * @param BuildWidgetEvent $event The event.
     *
     * @return void
     */
    public static function buildWidget(BuildWidgetEvent $event)
    {
        $metaModel = self::getMetaModelFromModel($event->getModel());

        Helper::prepareLanguageAwareWidget(
            $event->getEnvironment(),
            $event->getProperty(),
            $metaModel,
            $event->getEnvironment()->getTranslator()->translate('name_langcode', 'tl_metamodel_attribute'),
            $event->getEnvironment()->getTranslator()->translate('name_value', 'tl_metamodel_attribute'),
            false,
            $event->getModel()->getProperty('legendtitle')
        );
    }
}
