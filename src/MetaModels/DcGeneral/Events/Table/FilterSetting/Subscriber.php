<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2019 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Christopher Boelter <christopher@boelter.eu>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\DcGeneral\Events\Table\FilterSetting;

use ContaoCommunityAlliance\DcGeneral\Clipboard\Filter;
use ContaoCommunityAlliance\DcGeneral\Clipboard\ItemInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPasteButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
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
     *
     * @deprecated This is only present to support legacy drawing.
     */
    protected function registerModelRenderers()
    {
        if (empty($GLOBALS['METAMODELS']['filters'])) {
            return;
        }

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
    }

    /**
     * Generate the paste button.
     *
     * @param GetPasteButtonEvent $event The event.
     *
     * @return void
     */
    public function generatePasteButton(GetPasteButtonEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_filtersetting')) {
            return;
        }

        $environment = $event->getEnvironment();
        $model       = $event->getModel();
        $clipboard   = $environment->getClipboard();

        $filter = new Filter();
        $filter
            ->andModelIs(ModelId::fromModel($model))
            ->andActionIs(ItemInterface::CUT);

        // Disable all buttons if there is a circular reference.
        if ($event->isCircularReference() || !$clipboard->isEmpty($filter)) {
            $event
                ->setPasteAfterDisabled(true)
                ->setPasteIntoDisabled(true);

            return;
        }
        $factory = $this->getServiceContainer()->getFilterFactory()->getTypeFactory($model->getProperty('type'));

        // If setting does not support children, omit them.
        if ($model->getId() && !($factory && $factory->isNestedType())) {
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

        if (!($attributeId = $model->getProperty('attr_id'))) {
            return;
        }

        $attribute = $metaModel->getAttributeById($attributeId);
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
     */
    public function getTypeOptions(GetPropertyOptionsEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_filtersetting')
            || ($event->getPropertyName() !== 'type')) {
            return;
        }

        $translator = $event->getEnvironment()->getTranslator();
        $options    = array();
        $factory    = $this->getServiceContainer()->getFilterFactory();

        foreach ($factory->getTypeNames() as $filter) {
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
     */
    public function getAttributeIdOptions(GetPropertyOptionsEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_filtersetting')
            || ($event->getPropertyName() !== 'attr_id')) {
            return;
        }

        $result = array();
        $model  = $event->getModel();

        $metaModel   = $this->getMetaModel($model);
        $typeFactory = $this
            ->getServiceContainer()
            ->getFilterFactory()
            ->getTypeFactory($model->getProperty('type'));

        $typeFilter = null;
        if ($typeFactory) {
            $typeFilter = $typeFactory->getKnownAttributeTypes();
        }

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

        $value = substr($value, strlen($metaModel->getTableName() . '_'));

        $attribute = $metaModel->getAttribute($value);

        if ($attribute) {
            $event->setValue($attribute->get('id'));
        }
    }
}
