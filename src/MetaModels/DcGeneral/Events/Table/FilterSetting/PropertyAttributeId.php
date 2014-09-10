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

namespace MetaModels\DcGeneral\Events\Table\FilterSetting;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use MetaModels\Filter\Setting\Factory as FilterFactory;

/**
 * Event handlers for tl_metamodel_filtersetting.attr_id.
 *
 * @package MetaModels\DcGeneral\Events\Table\FilterSetting
 */
class PropertyAttributeId
{
    /**
     * Retrieve the MetaModel attached to the model filter setting.
     *
     * @param ModelInterface $model The model for which to retrieve the MetaModel.
     *
     * @return \MetaModels\IMetaModel
     */
    public static function getMetaModel(ModelInterface $model)
    {
        $filterSetting = FilterFactory::byId($model->getProperty('fid'));
        return $filterSetting->getMetaModel();
    }

    /**
     * Prepares a option list with alias => name connection for all attributes.
     *
     * This is used in the attr_id select box.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     */
    public static function getOptions(GetPropertyOptionsEvent $event)
    {
        $result = array();
        $model  = $event->getModel();

        $metaModel  = self::getMetaModel($model);
        $typeFilter = $GLOBALS['METAMODELS']['filters'][$model->getProperty('type')]['attr_filter'];

        foreach ($metaModel->getAttributes() as $attribute) {
            $typeName = $attribute->get('type');

            if ($typeFilter && (!in_array($typeName, $typeFilter))) {
                continue;
            }

            $strSelectVal          = $metaModel->getTableName() .'_' . $attribute->getColName();
            $result[$strSelectVal] = $attribute->getName() . ' [' . $typeName . ']';
        }

        $event->setOptions($result);
    }

    /**
     * Translates an attribute id to a generated alias {@see getAttributeNames()}.
     *
     * @param DecodePropertyValueForWidgetEvent $event The event.
     *
     * @return void
     */
    public static function decodeValue(DecodePropertyValueForWidgetEvent $event)
    {
        $model     = $event->getModel();
        $metaModel = self::getMetaModel($model);
        $value     = $event->getValue();

        if (!($metaModel && $value)) {
            return;
        }

        $attribute = $metaModel->getAttributeById($value);
        if ($attribute) {
            $event->setValue($metaModel->getTableName() .'_' . $attribute->getColName());
        }
    }

    /**
     * Translates an generated alias {@see getAttributeNames()} to the corresponding attribute id.
     *
     * @param EncodePropertyValueFromWidgetEvent $event The event.
     *
     * @return void
     */
    public static function encodeValue(EncodePropertyValueFromWidgetEvent $event)
    {
        $model     = $event->getModel();
        $metaModel = self::getMetaModel($model);
        $value     = $event->getValue();

        if (!($metaModel && $value)) {
            return;
        }

        $value = str_replace($metaModel->getTableName() . '_', '', $value);

        $attribute = $metaModel->getAttribute($value);

        if ($attribute) {
            $event->setValue($attribute->get('id'));
        }
    }
}
