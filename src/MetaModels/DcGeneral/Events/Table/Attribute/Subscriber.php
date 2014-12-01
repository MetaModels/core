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
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostPersistModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PreDeleteModelEvent;
use MetaModels\Attribute\IAttribute;
use MetaModels\Attribute\IAttributeFactory;
use MetaModels\Dca\Helper;
use MetaModels\DcGeneral\Events\BaseSubscriber;
use MetaModels\DcGeneral\Events\BreadCrumb\BreadCrumbAttributes;
use MetaModels\Helper\TableManipulation;

/**
 * Handles event operations on tl_metamodel_attribute.
 */
class Subscriber extends BaseSubscriber
{
    /**
     * Register all listeners to handle creation of a data container.
     *
     * @return void
     */
    protected function registerEventsInDispatcher()
    {
        $serviceContainer = $this->getServiceContainer();
        $this
            ->addListener(
                GetBreadcrumbEvent::NAME,
                function (GetBreadcrumbEvent $event) use ($serviceContainer) {
                    if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_attribute')) {
                        return;
                    }
                    $subscriber = new BreadCrumbAttributes($serviceContainer);
                    $subscriber->getBreadcrumb($event);
                }
            )
            ->addListener(
                ModelToLabelEvent::NAME,
                array($this, 'modelToLabel')
            )
            ->addListener(
                GetPropertyOptionsEvent::NAME,
                array($this, 'getOptions')
            )
            ->addListener(
                DecodePropertyValueForWidgetEvent::NAME,
                array($this, 'decodeNameValue')
            )
            ->addListener(
                EncodePropertyValueFromWidgetEvent::NAME,
                array($this, 'encodeNameValue')
            )
            ->addListener(
                BuildWidgetEvent::NAME,
                array($this, 'buildNameWidget')
            )
            ->addListener(
                DecodePropertyValueForWidgetEvent::NAME,
                array($this, 'decodeDescriptionValue')
            )
            ->addListener(
                EncodePropertyValueFromWidgetEvent::NAME,
                array($this, 'encodeDescriptionValue')
            )
            ->addListener(
                BuildWidgetEvent::NAME,
                array($this, 'buildDescriptionWidget')
            )
            ->addListener(
                EncodePropertyValueFromWidgetEvent::NAME,
                array($this, 'encodeColumnNameValue')
            )
            ->addListener(
                PostPersistModelEvent::NAME,
                array($this, 'handleUpdateAttribute')
            )
            ->addListener(
                PreDeleteModelEvent::NAME,
                array($this, 'handleDeleteAttribute')
            );
    }

    /**
     * Draw the attribute in the backend listing.
     *
     * @param ModelToLabelEvent $event The event.
     *
     * @return void
     */
    public function modelToLabel(ModelToLabelEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_attribute')) {
            return;
        }

        $services     = $this->getServiceContainer();
        $factory      = $services->getAttributeFactory();
        $modelFactory = $services->getFactory();
        $model        = $event->getModel();
        $type         = $model->getProperty('type');
        $image        = '<img src="' . $factory->getIconForType($type) . '" />';
        $data         = $model->getPropertiesAsArray();
        $metaModel    = $modelFactory->getMetaModel($modelFactory->translateIdToMetaModelName($data['pid']));
        $attribute    = $factory->createAttribute($data, $metaModel);

        if (!$attribute) {
            $translator = $event
                ->getEnvironment()
                ->getTranslator();

            $event
                ->setLabel(
                    '<div class="field_heading cte_type"><strong>%s</strong> <em>[%s]</em></div>
                    <div class="field_type block">
                        <strong>%s</strong><br />
                    </div>'
                )
                ->setArgs(
                    array
                    (
                        $translator->translate('error_unknown_attribute.0', 'tl_metamodel_attribute'),
                        $type,
                        $translator->translate('error_unknown_attribute.1', 'tl_metamodel_attribute', array($type)),
                    )
                );
            return;
        }

        $colName        = $attribute->getColName();
        $name           = $attribute->getName();
        $arrDescription = deserialize($attribute->get('description'));
        if (is_array($arrDescription)) {
            $description = $arrDescription[$attribute->getMetaModel()->getActiveLanguage()];
            if (!$description) {
                $description = $arrDescription[$attribute->getMetaModel()->getFallbackLanguage()];
            }
        } else {
            $description = $attribute->getName();
        }

        $event
            ->setLabel(
                '<div class="field_heading cte_type"><strong>%s</strong> <em>[%s]</em></div>
                <div class="field_type block">
                    %s<strong>%s</strong> - %s
                </div>'
            )
            ->setArgs(array(
                $colName,
                $type,
                $image,
                $name,
                $description
            ));
    }

    /**
     * Provide options for attribute type selection.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     */
    public function getOptions(GetPropertyOptionsEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_attribute')
            || ($event->getPropertyName() !== 'type')) {
            return;
        }

        $services         = $this->getServiceContainer();
        $translator       = $event->getEnvironment()->getTranslator();
        $attributeFactory = $services->getAttributeFactory();
        $modelFactory     = $services->getFactory();
        $metaModelName    = $modelFactory->translateIdToMetaModelName($event->getModel()->getProperty('pid'));
        $objMetaModel     = $modelFactory->getMetaModel($metaModelName);
        $flags            = IAttributeFactory::FLAG_ALL_UNTRANSLATED;
        if ($objMetaModel->isTranslated()) {
            $flags |= IAttributeFactory::FLAG_INCLUDE_TRANSLATED;
        }

        $options = array();
        foreach ($attributeFactory->getTypeNames($flags) as $attributeType) {
            $options[$attributeType] = $translator->translate(
                'typeOptions.' . $attributeType,
                'tl_metamodel_attribute'
            );
        }

        $event->setOptions($options);
    }

    /**
     * Decode the given value from a serialized language array into the real language array.
     *
     * @param DecodePropertyValueForWidgetEvent $event The event.
     *
     * @return void
     */
    protected function decodeValue(DecodePropertyValueForWidgetEvent $event)
    {
        $metaModel = $this->getMetaModelById($event->getModel()->getProperty('pid'));

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
    protected function encodeValue(EncodePropertyValueFromWidgetEvent $event)
    {
        $metaModel = $this->getMetaModelById($event->getModel()->getProperty('pid'));

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
    protected function buildWidget(BuildWidgetEvent $event)
    {
        $metaModel = $this->getMetaModelById($event->getModel()->getProperty('pid'));

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

    /**
     * Decode the given value from a serialized language array into the real language array.
     *
     * @param DecodePropertyValueForWidgetEvent $event The event.
     *
     * @return void
     */
    public function decodeNameValue(DecodePropertyValueForWidgetEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_attribute')
            || ($event->getProperty() !== 'name')) {
            return;
        }

        $this->decodeValue($event);
    }

    /**
     * Encode the given value from a real language array into a serialized language array.
     *
     * @param EncodePropertyValueFromWidgetEvent $event The event.
     *
     * @return void
     */
    public function encodeNameValue(EncodePropertyValueFromWidgetEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_attribute')
            || ($event->getProperty() !== 'name')) {
            return;
        }

        $this->encodeValue($event);
    }

    /**
     * Build the widget for the MCW.
     *
     * @param BuildWidgetEvent $event The event.
     *
     * @return void
     */
    public function buildNameWidget(BuildWidgetEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_attribute')
            || ($event->getProperty()->getName() !== 'name')) {
            return;
        }

        $this->buildWidget($event);
    }

    /**
     * Decode the given value from a serialized language array into the real language array.
     *
     * @param DecodePropertyValueForWidgetEvent $event The event.
     *
     * @return void
     */
    public function decodeDescriptionValue(DecodePropertyValueForWidgetEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_attribute')
            || ($event->getProperty() !== 'description')) {
            return;
        }

        $this->decodeValue($event);
    }

    /**
     * Encode the given value from a real language array into a serialized language array.
     *
     * @param EncodePropertyValueFromWidgetEvent $event The event.
     *
     * @return void
     */
    public function encodeDescriptionValue(EncodePropertyValueFromWidgetEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_attribute')
            || ($event->getProperty() !== 'description')) {
            return;
        }

        $this->encodeValue($event);
    }

    /**
     * Build the widget for the MCW.
     *
     * @param BuildWidgetEvent $event The event.
     *
     * @return void
     */
    public function buildDescriptionWidget(BuildWidgetEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_attribute')
            || ($event->getProperty()->getName() !== 'description')) {
            return;
        }

        $this->buildWidget($event);
    }

    /**
     * Encode the given value from a real language array into a serialized language array.
     *
     * @param EncodePropertyValueFromWidgetEvent $event The event.
     *
     * @return void
     *
     * @throws \RuntimeException When the column name is illegal or duplicate.
     */
    public function encodeColumnNameValue(EncodePropertyValueFromWidgetEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_attribute')
            || ($event->getProperty() !== 'colname')) {
            return;
        }

        $oldColumnName = $event->getModel()->getProperty($event->getProperty());
        $columnName    = $event->getValue();
        $metaModel     = $this->getMetaModelById($event->getModel()->getProperty('pid'));

        if ((!$columnName) || $oldColumnName !== $columnName) {
            TableManipulation::checkColumnDoesNotExist($metaModel->getTableName(), $columnName);

            $colNames = array_keys($metaModel->getAttributes());
            if (in_array($columnName, $colNames)) {
                throw new \RuntimeException(
                    sprintf(
                        $event->getEnvironment()->getTranslator()->translate('columnExists', 'ERR'),
                        $columnName,
                        $metaModel->getTableName()
                    )
                );
            }
        }
    }

    /**
     * Check if either type or colname have been changed within the model.
     *
     * @param PostPersistModelEvent $event The event.
     *
     * @return bool
     */
    protected static function isAttributeNameOrTypeChanged($event)
    {
        $old     = $event->getOriginalModel();
        $new     = $event->getModel();
        $oldType = $old ? $old->getProperty('type') : null;
        $newType = $new->getProperty('type');
        $oldName = $old ? $old->getProperty('colname') : null;
        $newName = $new->getProperty('colname');

        return ($oldType !== $newType) || ($oldName !== $newName);
    }

    /**
     * Create an attribute from the passed data.
     *
     * @param array|null $information The information.
     *
     * @return IAttribute|null
     */
    protected function createAttributeInstance($information)
    {
        if (empty($information)) {
            return null;
        }

        $services         = $this->getServiceContainer();
        $attributeFactory = $services->getAttributeFactory();
        $modelFactory     = $services->getFactory();
        $name             = $modelFactory->translateIdToMetaModelName($information['pid']);

        return $attributeFactory->createAttribute($information, $modelFactory->getMetaModel($name));
    }

    /**
     * Handle the update of an attribute and all attached data.
     *
     * @param PostPersistModelEvent $event The event.
     *
     * @return void
     */
    public function handleUpdateAttribute(PostPersistModelEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_attribute')) {
            return;
        }

        $old         = $event->getOriginalModel();
        $new         = $event->getModel();
        $oldInstance = $old ? $this->createAttributeInstance($old->getPropertiesAsArray()) : null;
        $newInstance = $this->createAttributeInstance($new->getPropertiesAsArray());

        // If type or column name has been changed, destroy old data and initialize new.
        if (self::isAttributeNameOrTypeChanged($event)) {
            // Destroy old instance.
            if ($oldInstance) {
                $oldInstance->destroyAUX();
            }

            // Create new instance aux info.
            if ($newInstance) {
                $newInstance->initializeAUX();
            }
        }

        if ($newInstance) {
            // Now loop over all values and update the meta in the instance.
            foreach ($new->getPropertiesAsArray() as $strKey => $varValue) {
                $newInstance->handleMetaChange($strKey, $varValue);
            }
        }
    }

    /**
     * Handle the deletion of an attribute and all attached data.
     *
     * @param PreDeleteModelEvent $event The event.
     *
     * @return void
     */
    public function handleDeleteAttribute(PreDeleteModelEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_attribute')) {
            return;
        }

        $attribute = $this->createAttributeInstance($event->getModel()->getPropertiesAsArray());

        if ($attribute) {
            $attribute->destroyAUX();
        }
    }
}
