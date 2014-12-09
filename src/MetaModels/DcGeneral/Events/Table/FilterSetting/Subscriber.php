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

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Backend\AddToUrlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPasteButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\Translator\TranslatorInterface;
use MetaModels\BackendIntegration\TemplateList;
use MetaModels\DcGeneral\Events\BaseSubscriber;
use MetaModels\DcGeneral\Events\BreadCrumb\BreadCrumbFilterSetting;
use MetaModels\IMetaModel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Handles event operations on tl_metamodel_filtersetting.
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
                    if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_filtersetting')) {
                        return;
                    }
                    $subscriber = new BreadCrumbFilterSetting($serviceContainer);
                    $subscriber->getBreadcrumb($event);
                }
            )
            ->addListener(
                GetPasteButtonEvent::NAME,
                array($this, 'generatePasteButton')
            )
            ->addListener(
                GetPropertyOptionsEvent::NAME,
                array($this, 'getDefaultIdOptions')
            )
            ->addListener(
                GetPropertyOptionsEvent::NAME,
                array($this, 'getTypeOptions')
            )
            ->addListener(
                GetPropertyOptionsEvent::NAME,
                array($this, 'getTemplateOptions')
            )
            ->addListener(
                GetPropertyOptionsEvent::NAME,
                array($this, 'getAttributeIdOptions')
            )
            ->addListener(
                DecodePropertyValueForWidgetEvent::NAME,
                array($this, 'decodeAttributeIdValue')
            )
            ->addListener(
                EncodePropertyValueFromWidgetEvent::NAME,
                array($this, 'encodeAttributeIdValue')
            );

        $this->registerModelRenderers();
    }

    /**
     * Register the events for rendering a model.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function registerModelRenderers()
    {
        $serviceContainer = $this->getServiceContainer();
        foreach ($GLOBALS['METAMODELS']['filters'] as $typeName => $information) {
            if (isset($information['info_callback'])) {
                $this->addListener(
                    ModelToLabelEvent::NAME,
                    function (
                        ModelToLabelEvent $event,
                        $eventName,
                        EventDispatcherInterface $dispatcher
                    ) use (
                        $typeName,
                        $information,
                        $serviceContainer
                    ) {
                        if (($event->getEnvironment()->getDataDefinition()->getName()
                                !== 'tl_metamodel_filtersetting')
                            || ($event->getModel()->getProperty('type') !== $typeName)
                        ) {
                            return;
                        }
                        if (is_string($information['info_callback'])) {
                            $list     = explode('::', $information['info_callback'], 2);
                            $instance = new $list[0]($serviceContainer);
                            $instance->$list[1]($event, $eventName, $dispatcher);
                        } else {
                            call_user_func($information['info_callback'], $event, $eventName, $dispatcher);
                        }
                    }
                );
            }
        }

        $this->addListener(
            ModelToLabelEvent::NAME,
            array($this, 'modelToLabel')
        );
    }

    /**
     * Generate the paste button.
     *
     * @param GetPasteButtonEvent $event The event.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function generatePasteButton(GetPasteButtonEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_filtersetting')) {
            return;
        }

        $environment = $event->getEnvironment();
        $model       = $event->getModel();
        $clipboard   = $environment->getClipboard();

        // Disable all buttons if there is a circular reference.
        if (($clipboard->isCut()
            && ($event->isCircularReference() || in_array($model->getId(), $clipboard->getContainedIds())))
        ) {
            $event
                ->setPasteAfterDisabled(true)
                ->setPasteIntoDisabled(true);

            return;
        }

        // If setting does not support children, omit them.
        if ($model->getId() && (!$GLOBALS['METAMODELS']['filters'][$model->getProperty('type')]['nestingAllowed'])) {
            $event->setPasteIntoDisabled(true);
        }
    }

    /**
     * Retrieve the MetaModel attached to the model filter setting.
     *
     * @param ModelInterface $model The model for which to retrieve the MetaModel.
     *
     * @return IMetaModel
     */
    public function getMetaModel(ModelInterface $model)
    {
        $filterSetting = $this->getServiceContainer()->getFilterFactory()->createCollection($model->getProperty('fid'));

        return $filterSetting->getMetaModel();
    }

    /**
     * Ensure that all options have a value.
     *
     * @param array $options  The options to be cleaned.
     *
     * @param bool  $onlyUsed Determines if only "used" values shall be returned.
     *
     * @param array $count    Array for the counted values.
     *
     * @return array
     */
    protected function cleanDefaultIdOptions($options, $onlyUsed, $count)
    {
        // Remove empty values.
        foreach ($options as $mixKey => $mixValue) {
            // Remove html/php tags.
            $mixValue = trim(strip_tags($mixValue));

            if (($mixValue === '') || ($mixValue === null) || ($onlyUsed && ($count[$mixKey] === 0))) {
                unset($options[$mixKey]);
            }
        }

        return $options;
    }

    /**
     * Provide options for default selection.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     */
    public function getDefaultIdOptions(GetPropertyOptionsEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_filtersetting')
            || ($event->getPropertyName() !== 'defaultid')) {
            return;
        }

        $model = $event->getModel();

        $event->getEnvironment()->getInputProvider();

        $metaModel = $this->getMetaModel($model);

        if (!$metaModel) {
            return;
        }

        $attribute = $metaModel->getAttributeById($model->getProperty('attr_id'));
        if (!$attribute) {
            return;
        }

        $onlyUsed = $model->getProperty('onlyused') ? true : false;
        $count    = array();
        $options  = $attribute->getFilterOptions(null, $onlyUsed, $count);
        $event->setOptions($this->cleanDefaultIdOptions($options, $onlyUsed, $count));
    }

    /**
     * Provide options for default selection.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function getTypeOptions(GetPropertyOptionsEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_filtersetting')
            || ($event->getPropertyName() !== 'type')) {
            return;
        }

        $translator = $event->getEnvironment()->getTranslator();
        $options    = array();

        foreach (array_keys($GLOBALS['METAMODELS']['filters']) as $filter) {
            $options[$filter] = $translator->translate('typenames.' . $filter, 'tl_metamodel_filtersetting');
        }

        $event->setOptions($options);
    }

    /**
     * Provide options for default selection.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     */
    public function getTemplateOptions(GetPropertyOptionsEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_filtersetting')
            || ($event->getPropertyName() !== 'template')) {
            return;
        }

        $list = new TemplateList();
        $list->setServiceContainer($this->getServiceContainer());
        $event->setOptions($list->getTemplatesForBase('mm_filteritem_'));
    }

    /**
     * Prepares a option list with alias => name connection for all attributes.
     *
     * This is used in the attr_id select box.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function getAttributeIdOptions(GetPropertyOptionsEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_filtersetting')
            || ($event->getPropertyName() !== 'attr_id')) {
            return;
        }

        $result = array();
        $model  = $event->getModel();

        $metaModel  = $this->getMetaModel($model);
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
    public function decodeAttributeIdValue(DecodePropertyValueForWidgetEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_filtersetting')
            || ($event->getProperty() !== 'attr_id')) {
            return;
        }

        $model     = $event->getModel();
        $metaModel = $this->getMetaModel($model);
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
    public function encodeAttributeIdValue(EncodePropertyValueFromWidgetEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_filtersetting')
            || ($event->getProperty() !== 'attr_id')) {
            return;
        }

        $model     = $event->getModel();
        $metaModel = $this->getMetaModel($model);
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

    /**
     * Retrieve the comment for the label.
     *
     * @param ModelInterface      $model      The filter setting to render.
     *
     * @param TranslatorInterface $translator The translator in use.
     *
     * @return string
     */
    public function getLabelComment(ModelInterface $model, TranslatorInterface $translator)
    {
        if ($model->getProperty('comment')) {
            return sprintf(
                $translator->translate('typedesc._comment_', 'tl_metamodel_filtersetting'),
                specialchars($model->getProperty('comment'))
            );
        }
        return '';
    }

    /**
     * Retrieve the image for the label.
     *
     * @param ModelInterface $model The filter setting to render.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function getLabelImage(ModelInterface $model)
    {
        $type  = $model->getProperty('type');
        $image = $GLOBALS['METAMODELS']['filters'][$type]['image'];

        if (!$image || !file_exists(TL_ROOT . '/' . $image)) {
            $image = 'system/modules/metamodels/assets/images/icons/filter_default.png';
        }

        if (!$model->getProperty('enabled')) {
            $intPos = strrpos($image, '.');
            if ($intPos !== false) {
                $image = substr_replace($image, '_1', $intPos, 0);
            }
        }
        $dispatcher = $this->getServiceContainer()->getEventDispatcher();

        /** @var AddToUrlEvent $urlEvent */
        $urlEvent = $dispatcher->dispatch(
            ContaoEvents::BACKEND_ADD_TO_URL,
            new AddToUrlEvent('act=edit&amp;id='.$model->getId())
        );

        /** @var GenerateHtmlEvent $imageEvent */
        $imageEvent = $dispatcher->dispatch(
            ContaoEvents::IMAGE_GET_HTML,
            new GenerateHtmlEvent($image)
        );

        return sprintf(
            '<a href="%s">%s</a>',
            $urlEvent->getUrl(),
            $imageEvent->getHtml()
        );
    }

    /**
     * Retrieve the label text for a filter setting.
     *
     * @param TranslatorInterface $translator The translator in use.
     *
     * @param ModelInterface      $model      The filter setting to render.
     *
     * @return mixed|string
     */
    public function getLabelText(TranslatorInterface $translator, ModelInterface $model)
    {
        $type  = $model->getProperty('type');
        $label = $translator->translate('typenames.' . $type, 'tl_metamodel_filtersetting');
        if ($label == 'typenames.' . $type) {
            return $type;
        }
        return $label;
    }

    /**
     * Retrieve the label pattern.
     *
     * @param TranslatorInterface $translator The translator in use.
     *
     * @param ModelInterface      $model      The filter setting to render.
     *
     * @return string
     */
    public function getLabelPattern(TranslatorInterface $translator, ModelInterface $model)
    {
        $type     = $model->getProperty('type');
        $combined = 'typedesc.' . $type;

        if (($resultPattern = $translator->translate($combined, 'tl_metamodel_filtersetting')) == $combined) {
            $resultPattern = $translator->translate('typedesc._default_', 'tl_metamodel_filtersetting');
        }

        return $resultPattern;
    }

    /**
     * Render a model that has an attribute and url param attached.
     *
     * @param ModelToLabelEvent $event The Event.
     *
     * @return void
     */
    public function modelToLabelWithAttributeAndUrlParam(ModelToLabelEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_filtersetting')) {
            return;
        }

        $translator = $event->getEnvironment()->getTranslator();
        $model      = $event->getModel();
        $metamodel  = $this->getMetaModel($model->getProperty('fid'));
        $attribute  = $metamodel->getAttributeById($model->getProperty('attr_id'));

        if ($attribute) {
            $attributeName = $attribute->getColName();
        } else {
            $attributeName = $model->getProperty('attr_id');
        }

        $event
            ->setLabel($this->getLabelPattern($translator, $model))
            ->setArgs(array(
                $this->getLabelImage($model),
                $this->getLabelText($translator, $model),
                $this->getLabelComment($model, $translator),
                $attributeName,
                ($model->getProperty('urlparam') ? $model->getProperty('urlparam') : $attributeName)
            ))
            ->stopPropagation();
    }

    /**
     * Render a filter setting into html.
     *
     * @param ModelToLabelEvent $event The event.
     *
     * @return void
     */
    public function modelToLabel(ModelToLabelEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_filtersetting')) {
            return;
        }

        $translator = $event->getEnvironment()->getTranslator();
        $model      = $event->getModel();

        $event
            ->setLabel($this->getLabelPattern($translator, $model))
            ->setArgs(array(
                $this->getLabelImage($model),
                $this->getLabelText($translator, $model),
                $this->getLabelComment($model, $translator),
                $model->getProperty('type')
            ));
    }
}
