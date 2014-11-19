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

namespace MetaModels\DcGeneral\Events;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\BuildWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPasteButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ManipulateWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use ContaoCommunityAlliance\DcGeneral\DcGeneralEvents;
use ContaoCommunityAlliance\DcGeneral\Event\PostPersistModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PreDeleteModelEvent;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\PopulateEnvironmentEvent;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\PreCreateDcGeneralEvent;
use MetaModels\DcGeneral\Dca\Builder\Builder;
use MetaModels\DcGeneral\Events\Table\InputScreen\PropertyPTable;
use MetaModels\DcGeneral\Events\Table\InputScreens\BuildPalette;
use MetaModels\DcGeneral\Events\Table\RenderSetting\RenderSettingBuildPalette;
use MetaModels\Factory;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetOperationButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetGlobalButtonEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Central event subscriber implementation.
 *
 * @package MetaModels\DcGeneral\Events
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
        $this->registerTableMetaModelsEvents();
        $this->registerTableMetaModelAttributeEvents();
        $this->registerTableMetaModelDcaEvents();

        $dispatcher = $this->serviceContainer->getEventDispatcher();
        self::registerBuildDataDefinitionFor(
            'tl_metamodel_dca_combine',
            $dispatcher,
            __CLASS__ . '::registerTableMetaModelDcaCombineEvents'
        );
        self::registerBuildDataDefinitionFor(
            'tl_metamodel_dcasetting',
            $dispatcher,
            array($this, 'registerTableMetaModelDcaSettingEvents')
        );
        self::registerBuildDataDefinitionFor(
            'tl_metamodel_dcasetting_condition',
            $dispatcher,
            array($this, 'registerTableMetaModelDcaSettingConditionsEvents')
        );
        self::registerBuildDataDefinitionFor(
            'tl_metamodel_filter',
            $dispatcher,
            array($this, 'registerTableMetaModelFilterEvents')
        );
        self::registerBuildDataDefinitionFor(
            'tl_metamodel_filtersetting',
            $dispatcher,
            array($this, 'registerTableMetaModelFilterSettingEvents')
        );
        self::registerBuildDataDefinitionFor(
            'tl_metamodel_rendersetting',
            $dispatcher,
            array($this, 'registerTableMetaModelRenderSettingEvents')
        );
        self::registerBuildDataDefinitionFor(
            'tl_metamodel_rendersettings',
            $dispatcher,
            array($this, 'registerTableMetaModelRenderSettingsEvents')
        );
    }

    /**
     * Register the events for table tl_metamodel.
     *
     * @return void
     */
    private function registerTableMetaModelsEvents()
    {
        new \MetaModels\DcGeneral\Events\Table\MetaModels\Subscriber($this->getServiceContainer());
    }

    /**
     * Register the events for table tl_metamodel_attribute.
     *
     * @return void
     */
    private function registerTableMetaModelAttributeEvents()
    {
        new \MetaModels\DcGeneral\Events\Table\Attribute\Subscriber($this->getServiceContainer());
    }

    /**
     * Register the events for table tl_metamodel_dca.
     *
     * @return void
     */
    public function registerTableMetaModelDcaEvents()
    {
        new \MetaModels\DcGeneral\Events\Table\InputScreen\Subscriber($this->getServiceContainer());
    }

    /**
     * Register the events for table tl_metamodel_dca_combine.
     *
     * @return void
     */
    public function registerTableMetaModelDcaCombineEvents()
    {
        static $registered;
        if ($registered) {
            return;
        }
        $registered = true;
        $dispatcher = func_get_arg(2);

        self::registerListeners(
            array(
                GetBreadcrumbEvent::NAME => self::createClosure(
                    'MetaModels\DcGeneral\Events\BreadCrumb\BreadCrumbDcaCombine',
                    'getBreadcrumb'
                ),
            ),
            $dispatcher,
            array('tl_metamodel_dca_combine')
        );

        self::registerListeners(
            array(
                GetPropertyOptionsEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\DcaCombine\PropertyFeGroup::getOptions',
            ),
            $dispatcher,
            array('tl_metamodel_dca_combine', 'rows', 'fe_group')
        );

        self::registerListeners(
            array(
                GetPropertyOptionsEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\DcaCombine\PropertyBeGroup::getOptions',
            ),
            $dispatcher,
            array('tl_metamodel_dca_combine', 'rows', 'be_group')
        );

        self::registerListeners(
            array(
                GetPropertyOptionsEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\DcaCombine\PropertyDcaId::getOptions',
            ),
            $dispatcher,
            array('tl_metamodel_dca_combine', 'rows', 'dca_id')
        );

        self::registerListeners(
            array(
                GetPropertyOptionsEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\DcaCombine\PropertyViewId::getOptions',
            ),
            $dispatcher,
            array('tl_metamodel_dca_combine', 'rows', 'view_id')
        );

        self::registerListeners(
            array(
                EncodePropertyValueFromWidgetEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\DcaCombine\PropertyRows::fixSorting',
            ),
            $dispatcher,
            array('tl_metamodel_dca_combine', 'rows')
        );
    }

    /**
     * Register the events for table tl_metamodel_dcasetting.
     *
     * @param BuildDataDefinitionEvent $event The event being processed.
     *
     * @return void
     */
    public function registerTableMetaModelDcaSettingEvents(BuildDataDefinitionEvent $event)
    {
        static $registered;
        if ($registered) {
            return;
        }
        $registered = true;
        $dispatcher = func_get_arg(2);

        self::registerListeners(
            array(
                ModelToLabelEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\InputScreens\ModelToLabel::handleModelToLabel',
                GetBreadcrumbEvent::NAME
                    => self::createClosure(
                        'MetaModels\DcGeneral\Events\BreadCrumb\BreadCrumbInputScreen',
                        'getBreadcrumb'
                    ),
                DcGeneralEvents::ACTION
                    => 'MetaModels\DcGeneral\Events\Table\InputScreens\ProcessAddAll::handleAddAll',
            ),
            $dispatcher,
            array('tl_metamodel_dcasetting')
        );

        // Save and load callbacks.
        self::registerListeners(
            array(
                DecodePropertyValueForWidgetEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\InputScreens\PropertyLegendTitle::decodeValue',
                EncodePropertyValueFromWidgetEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\InputScreens\PropertyLegendTitle::encodeValue',
                BuildWidgetEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\InputScreens\PropertyLegendTitle::buildWidget',
            ),
            $dispatcher,
            array('tl_metamodel_dcasetting', 'legendtitle')
        );

        self::registerListeners(
            array(
                GetPropertyOptionsEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\InputScreens\PropertyAttribute::getOptions',
            ),
            $dispatcher,
            array('tl_metamodel_dcasetting', 'attr_id')
        );

        self::registerListeners(
            array(
                ManipulateWidgetEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\InputScreens\PropertyTlClass::getWizard',
            ),
            $dispatcher,
            array('tl_metamodel_dcasetting', 'tl_class')
        );

        self::registerListeners(
            array(
                GetPropertyOptionsEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\InputScreens\PropertyRte::getOptions',
            ),
            $dispatcher,
            array('tl_metamodel_dcasetting', 'rte')
        );

        BuildPalette::build($event);
    }

    /**
     * Register the events for table tl_metamodel_dcasetting_condition.
     *
     * @return void
     */
    public function registerTableMetaModelDcaSettingConditionsEvents()
    {
        static $registered;
        if ($registered) {
            return;
        }
        $registered = true;
        $dispatcher = func_get_arg(2);

        self::registerListeners(
            array(
                ModelToLabelEvent::NAME
                => 'MetaModels\DcGeneral\Events\Table\InputScreenCondition\ModelToLabel::handleModelToLabel',
            ),
            $dispatcher,
            array('tl_metamodel_dcasetting_condition')
        );

        self::registerListeners(
            array(
                GetPasteButtonEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\InputScreenCondition\PasteButton::generate',
            ),
            $dispatcher,
            array('tl_metamodel_dcasetting_condition')
        );

        self::registerListeners(
            array(
                GetPropertyOptionsEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\InputScreenCondition\PropertyType::getOptions',
            ),
            $dispatcher,
            array('tl_metamodel_dcasetting_condition', 'type')
        );

        self::registerListeners(
            array(
                GetPropertyOptionsEvent::NAME
                => 'MetaModels\DcGeneral\Events\Table\InputScreenCondition\PropertyAttributeId::getOptions',
                DecodePropertyValueForWidgetEvent::NAME
                => 'MetaModels\DcGeneral\Events\Table\InputScreenCondition\PropertyAttributeId::decodeValue',
                EncodePropertyValueFromWidgetEvent::NAME
                => 'MetaModels\DcGeneral\Events\Table\InputScreenCondition\PropertyAttributeId::encodeValue'
            ),
            $dispatcher,
            array('tl_metamodel_dcasetting_condition', 'attr_id')
        );

        self::registerListeners(
            array(
                GetPropertyOptionsEvent::NAME
                => 'MetaModels\DcGeneral\Events\Table\InputScreenCondition\PropertyValue::getOptions',
                DecodePropertyValueForWidgetEvent::NAME
                => 'MetaModels\DcGeneral\Events\Table\InputScreenCondition\PropertyValue::decodeValue',
                EncodePropertyValueFromWidgetEvent::NAME
                => 'MetaModels\DcGeneral\Events\Table\InputScreenCondition\PropertyValue::encodeValue',
            ),
            $dispatcher,
            array('tl_metamodel_dcasetting_condition', 'value')
        );
    }


    /**
     * Register the events for table tl_metamodel_filter.
     *
     * @return void
     */
    public function registerTableMetaModelFilterEvents()
    {
        static $registered;
        if ($registered) {
            return;
        }
        $registered = true;
        $dispatcher = func_get_arg(2);

        self::registerListeners(
            array(
                GetBreadcrumbEvent::NAME
                    => self::createClosure(
                        'MetaModels\DcGeneral\Events\BreadCrumb\BreadCrumbFilter',
                        'getBreadcrumb'
                    ),
            ),
            $dispatcher,
            array('tl_metamodel_filter')
        );
    }

    /**
     * Register the events for table tl_metamodel_filtersetting.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function registerTableMetaModelFilterSettingEvents()
    {
        static $registered;
        if ($registered) {
            return;
        }
        $registered = true;
        $dispatcher = func_get_arg(2);

        self::registerListeners(
            array(
                GetPasteButtonEvent::NAME => 'MetaModels\DcGeneral\Events\Table\FilterSetting\PasteButton::generate',
            ),
            $dispatcher,
            array('tl_metamodel_filtersetting')
        );

        self::registerListeners(
            array(
                GetPropertyOptionsEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\FilterSetting\PropertyDefaultId::getOptions',
            ),
            $dispatcher,
            array('tl_metamodel_filtersetting', 'defaultid')
        );

        self::registerListeners(
            array(
                GetPropertyOptionsEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\FilterSetting\PropertyType::getOptions',
            ),
            $dispatcher,
            array('tl_metamodel_filtersetting', 'type')
        );

        self::registerListeners(
            array(
                GetPropertyOptionsEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\FilterSetting\PropertyTemplate::getOptions',
            ),
            $dispatcher,
            array('tl_metamodel_filtersetting', 'template')
        );

        self::registerListeners(
            array(
                GetPropertyOptionsEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\FilterSetting\PropertyAttributeId::getOptions',
                DecodePropertyValueForWidgetEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\FilterSetting\PropertyAttributeId::decodeValue',
                EncodePropertyValueFromWidgetEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\FilterSetting\PropertyAttributeId::encodeValue'
            ),
            $dispatcher,
            array('tl_metamodel_filtersetting', 'attr_id')
        );

        foreach ($GLOBALS['METAMODELS']['filters'] as $typeName => $information) {
            if (isset($information['info_callback'])) {
                self::registerListeners(
                    array(
                        ModelToLabelEvent::NAME => $information['info_callback']
                    ),
                    $dispatcher,
                    array('tl_metamodel_filtersetting', $typeName)
                );
            }
        }

        self::registerListeners(
            array(
                ModelToLabelEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\FilterSetting\DrawSetting::modelToLabel',
                GetBreadcrumbEvent::NAME
                    => self::createClosure(
                        'MetaModels\DcGeneral\Events\BreadCrumb\BreadCrumbFilterSetting',
                        'getBreadcrumb'
                    )
            ),
            $dispatcher,
            array('tl_metamodel_filtersetting')
        );
    }

    /**
     * Register the events for table tl_metamodel_rendersetting.
     *
     * @param BuildDataDefinitionEvent $event The event being processed.
     *
     * @return void
     */
    public function registerTableMetaModelRenderSettingEvents(BuildDataDefinitionEvent $event)
    {
        static $registered;
        if ($registered) {
            return;
        }
        $registered = true;
        $dispatcher = func_get_arg(2);

        self::registerListeners(
            array(
                GetBreadcrumbEvent::NAME
                    => self::createClosure(
                        'MetaModels\DcGeneral\Events\BreadCrumb\BreadCrumbRenderSetting',
                        'getBreadcrumb'
                    ),
                ModelToLabelEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\RenderSetting\DrawSetting::modelToLabel',
                DcGeneralEvents::ACTION
                    => 'MetaModels\DcGeneral\Events\Table\RenderSetting\ProcessAddAll::handleAddAll',
            ),
            $dispatcher,
            array('tl_metamodel_rendersetting')
        );

        self::registerListeners(
            array(
                GetPropertyOptionsEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\RenderSetting\PropertyTemplate::getOptions',
            ),
            $dispatcher,
            array('tl_metamodel_rendersetting', 'template')
        );

        self::registerListeners(
            array(
                GetPropertyOptionsEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\RenderSetting\PropertyAttribute::getOptions',
            ),
            $dispatcher,
            array('tl_metamodel_rendersetting', 'attr_id')
        );

        RenderSettingBuildPalette::build($event);
    }

    /**
     * Register the events for table tl_metamodel_rendersettings.
     *
     * @return void
     */
    public function registerTableMetaModelRenderSettingsEvents()
    {
        static $registered;
        if ($registered) {
            return;
        }
        $registered = true;
        $dispatcher = func_get_arg(2);

        self::registerListeners(
            array(
                GetBreadcrumbEvent::NAME
                    => self::createClosure(
                        'MetaModels\DcGeneral\Events\BreadCrumb\BreadCrumbRenderSettings',
                        'getBreadcrumb'
                    ),
                PostPersistModelEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\RenderSettings\UpdateRenderSettings::handle',
                ModelToLabelEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\RenderSettings\DrawSetting::modelToLabel'
            ),
            $dispatcher,
            array('tl_metamodel_rendersettings')
        );

        self::registerListeners(
            array(
                DecodePropertyValueForWidgetEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\RenderSettings\PropertyJumpTo::decodeValue',
                EncodePropertyValueFromWidgetEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\RenderSettings\PropertyJumpTo::encodeValue',
                BuildWidgetEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\RenderSettings\PropertyJumpTo::buildWidget',
            ),
            $dispatcher,
            array('tl_metamodel_rendersettings', 'jumpTo')
        );

        self::registerListeners(
            array(
                GetPropertyOptionsEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\RenderSettings\PropertyTemplate::getOptions',
            ),
            $dispatcher,
            array('tl_metamodel_rendersettings', 'template')
        );

        self::registerListeners(
            array(
                GetPropertyOptionsEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\RenderSettings\PropertyCssFiles::getOptions',
            ),
            $dispatcher,
            array('tl_metamodel_rendersettings', 'additionalCss', 'file')
        );
        self::registerListeners(
            array(
                GetPropertyOptionsEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\RenderSettings\PropertyJsFiles::getOptions',
            ),
            $dispatcher,
            array('tl_metamodel_rendersettings', 'additionalJs', 'file')
        );
    }
}
