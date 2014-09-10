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

namespace MetaModels\DcGeneral\Events\Table\InputScreenCondition;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\IdSerializer;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use MetaModels\Factory;

/**
 * Handle events for property tl_metamodel_dcasetting_condition.value.
 */
class PropertyValue
{
    /**
     * Retrieve the MetaModel attached to the model filter setting.
     *
     * @param EnvironmentInterface $interface The environment.
     *
     * @return \MetaModels\IMetaModel
     */
    public static function getMetaModel(EnvironmentInterface $interface)
    {
        $metaModelId = \Database::getInstance()
            ->prepare('SELECT id FROM tl_metamodel WHERE
                id=(SELECT pid FROM tl_metamodel_dca WHERE
                id=(SELECT pid FROM tl_metamodel_dcasetting WHERE id=?))')
            ->execute(IdSerializer::fromSerialized($interface->getInputProvider()->getParameter('pid'))->getId());

        return Factory::byId($metaModelId->id);
    }

    /**
     * Provide options for the values contained within a certain attribute.
     *
     * The values get prefixed with 'value_' to ensure numeric values are kept intact.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     */
    public static function getOptions(GetPropertyOptionsEvent $event)
    {
        $model     = $event->getModel();
        $metaModel = self::getMetaModel($event->getEnvironment());
        $attribute = $metaModel->getAttributeById($model->getProperty('attr_id'));

        if ($attribute) {
            $options = $attribute->getFilterOptions(null, false);
            $mangled = array();
            foreach ($options as $key => $option) {
                $mangled['value_' . $key] = $option;
            }

            $event->setOptions($mangled);
        }
    }


    /**
     * Translates an value to a generated alias to allow numeric values.
     *
     * @param DecodePropertyValueForWidgetEvent $event The event.
     *
     * @return void
     */
    public static function decodeValue(DecodePropertyValueForWidgetEvent $event)
    {
        $event->setValue('value_' . $event->getValue());
    }

    /**
     * Translates an generated alias to the corresponding value.
     *
     * @param EncodePropertyValueFromWidgetEvent $event The event.
     *
     * @return void
     */
    public static function encodeValue(EncodePropertyValueFromWidgetEvent $event)
    {
        $event->setValue(str_replace('value_', '', $event->getValue()));
    }
}
