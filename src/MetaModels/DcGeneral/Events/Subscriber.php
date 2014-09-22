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

use ContaoCommunityAlliance\Contao\EventDispatcher\Event\CreateEventDispatcherEvent;
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
use MetaModels\DcGeneral\Events\Table\InputScreenSortGroup\PropertyRenderGroupType;
use MetaModels\DcGeneral\Events\Table\RenderSetting\RenderSettingBuildPalette;
use MetaModels\Factory;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetOperationButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetGlobalButtonEvent;

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
     * @param CreateEventDispatcherEvent $event The event being processed.
     *
     * @return void
     */
    public static function registerEvents(CreateEventDispatcherEvent $event)
    {
        $dispatcher = $event->getEventDispatcher();
        // Handlers for build data definition.
        self::registerBuildDataDefinitionFor(
            'tl_metamodel',
            $dispatcher,
            __CLASS__ . '::registerTableMetaModelsEvents'
        );
        self::registerBuildDataDefinitionFor(
            'tl_metamodel_attribute',
            $dispatcher,
            __CLASS__ . '::registerTableMetaModelAttributeEvents'
        );
        self::registerBuildDataDefinitionFor(
            'tl_metamodel_dca',
            $dispatcher,
            __CLASS__ . '::registerTableMetaModelDcaEvents'
        );
        self::registerBuildDataDefinitionFor(
            'tl_metamodel_dca_sortgroup',
            $dispatcher,
            __CLASS__ . '::registerTableMetaModelDcaSortGroupEvents'
        );
        self::registerBuildDataDefinitionFor(
            'tl_metamodel_dca_combine',
            $dispatcher,
            __CLASS__ . '::registerTableMetaModelDcaCombineEvents'
        );
        self::registerBuildDataDefinitionFor(
            'tl_metamodel_dcasetting',
            $dispatcher,
            __CLASS__ . '::registerTableMetaModelDcaSettingEvents'
        );
        self::registerBuildDataDefinitionFor(
            'tl_metamodel_dcasetting_condition',
            $dispatcher,
            __CLASS__ . '::registerTableMetaModelDcaSettingConditionsEvents'
        );
        self::registerBuildDataDefinitionFor(
            'tl_metamodel_filter',
            $dispatcher,
            __CLASS__ . '::registerTableMetaModelFilterEvents'
        );
        self::registerBuildDataDefinitionFor(
            'tl_metamodel_filtersetting',
            $dispatcher,
            __CLASS__ . '::registerTableMetaModelFilterSettingEvents'
        );
        self::registerBuildDataDefinitionFor(
            'tl_metamodel_rendersetting',
            $dispatcher,
            __CLASS__ . '::registerTableMetaModelRenderSettingEvents'
        );
        self::registerBuildDataDefinitionFor(
            'tl_metamodel_rendersettings',
            $dispatcher,
            __CLASS__ . '::registerTableMetaModelRenderSettingsEvents'
        );

        self::registerListeners(
            array(
                PreCreateDcGeneralEvent::NAME => __CLASS__ . '::preCreateDcGeneral'
            ),
            $dispatcher
        );
    }

    /**
     * Register the events for table tl_metamodel.
     *
     * @return void
     */
    public static function registerTableMetaModelsEvents()
    {
        static $registered;
        if ($registered) {
            return;
        }
        $registered = true;
        $dispatcher = func_get_arg(2);

        self::registerListeners(
            array(
                GetOperationButtonEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\MetaModels\Subscriber::getOperationButton',
                GetGlobalButtonEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\MetaModels\Subscriber::getGlobalButton',
                ModelToLabelEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\MetaModels\Subscriber::modelToLabel',
                GetBreadcrumbEvent::NAME => self::createClosure(
                    'MetaModels\DcGeneral\Events\BreadCrumb\BreadCrumbMetaModels',
                    'getBreadcrumb'
                ),
            ),
            $dispatcher,
            array('tl_metamodel')
        );

        // Save and load callbacks.
        self::registerListeners(
            array(
                DecodePropertyValueForWidgetEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\MetaModels\Subscriber::fixLangArray',
                EncodePropertyValueFromWidgetEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\MetaModels\Subscriber::unfixLangArray',
            ),
            $dispatcher,
            array('tl_metamodel', 'languages')
        );

        // Save callbacks.
        self::registerListeners(
            array(
                EncodePropertyValueFromWidgetEvent::NAME
                => 'MetaModels\DcGeneral\Events\Table\MetaModels\Subscriber::ensureTableNamePrefix',
            ),
            $dispatcher,
            array('tl_metamodel', 'tableName')
        );

        // Global table events.
        self::registerListeners(
            array(
                PostPersistModelEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\MetaModels\UpdateMetaModel::handle',
                PreDeleteModelEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\MetaModels\DeleteMetaModel::handle',
            ),
            $dispatcher,
            array('tl_metamodel')
        );
    }

    /**
     * Register the events for table tl_metamodel_attribute.
     *
     * @return void
     */
    public static function registerTableMetaModelAttributeEvents()
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
                        'MetaModels\DcGeneral\Events\BreadCrumb\BreadCrumbAttributes',
                        'getBreadcrumb'
                    ),
                ModelToLabelEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\Attribute\DrawAttribute::modelToLabel',
            ),
            $dispatcher,
            array('tl_metamodel_attribute')
        );

        self::registerListeners(
            array(
                GetPropertyOptionsEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\Attribute\AttributeType::getOptions',
            ),
            $dispatcher,
            array('tl_metamodel_attribute', 'type')
        );

        // Save and load callbacks.
        self::registerListeners(
            array(
                DecodePropertyValueForWidgetEvent::NAME
                => 'MetaModels\DcGeneral\Events\Table\Attribute\PropertyNameAndDescription::decodeValue',
                EncodePropertyValueFromWidgetEvent::NAME
                => 'MetaModels\DcGeneral\Events\Table\Attribute\PropertyNameAndDescription::encodeValue',
                BuildWidgetEvent::NAME
                => 'MetaModels\DcGeneral\Events\Table\Attribute\PropertyNameAndDescription::buildWidget',
            ),
            $dispatcher,
            array('tl_metamodel_attribute', 'name')
        );

        // Save and load callbacks.
        self::registerListeners(
            array(
                DecodePropertyValueForWidgetEvent::NAME
                => 'MetaModels\DcGeneral\Events\Table\Attribute\PropertyNameAndDescription::decodeValue',
                EncodePropertyValueFromWidgetEvent::NAME
                => 'MetaModels\DcGeneral\Events\Table\Attribute\PropertyNameAndDescription::encodeValue',
                BuildWidgetEvent::NAME
                => 'MetaModels\DcGeneral\Events\Table\Attribute\PropertyNameAndDescription::buildWidget',
            ),
            $dispatcher,
            array('tl_metamodel_attribute', 'description')
        );

        // Save and load callbacks.
        self::registerListeners(
            array(
                EncodePropertyValueFromWidgetEvent::NAME
                => 'MetaModels\DcGeneral\Events\Table\Attribute\PropertyColName::encodeValue',
            ),
            $dispatcher,
            array('tl_metamodel_attribute', 'colname')
        );

        // Global table events.
        self::registerListeners(
            array(
                PostPersistModelEvent::NAME
                => 'MetaModels\DcGeneral\Events\Table\Attribute\UpdateAttribute::handle',
                PreDeleteModelEvent::NAME
                => 'MetaModels\DcGeneral\Events\Table\Attribute\DeleteAttribute::handle',

            ),
            $dispatcher,
            array('tl_metamodel_attribute')
        );
    }

    /**
     * Register the events for table tl_metamodel_dca.
     *
     * @param BuildDataDefinitionEvent $event The event being processed.
     *
     * @return void
     */
    public static function registerTableMetaModelDcaEvents(BuildDataDefinitionEvent $event)
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
                    => 'MetaModels\DcGeneral\Events\Table\InputScreen\ModelToLabel::render',
                PostPersistModelEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\InputScreen\UpdateInputScreen::handle',
                GetBreadcrumbEvent::NAME
                    => self::createClosure(
                        'MetaModels\DcGeneral\Events\BreadCrumb\BreadCrumbInputScreens',
                        'getBreadcrumb'
                    ),
            ),
            $dispatcher,
            array('tl_metamodel_dca')
        );

        self::registerListeners(
            array(
                ManipulateWidgetEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\InputScreen\PropertyPanelLayout::getWizard',
            ),
            $dispatcher,
            array('tl_metamodel_dca', 'panelLayout')
        );

        self::registerListeners(
            array(
                GetPropertyOptionsEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\InputScreen\PropertyBackendSection::getSections',
            ),
            $dispatcher,
            array('tl_metamodel_dca', 'backendsection')
        );

        self::registerListeners(
            array(
                GetPropertyOptionsEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\InputScreen\PropertyMode::getValidModes',
                DecodePropertyValueForWidgetEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\InputScreen\PropertyMode::decodeMode',
                EncodePropertyValueFromWidgetEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\InputScreen\PropertyMode::encodeMode',
            ),
            $dispatcher,
            array('tl_metamodel_dca', 'mode')
        );

        self::registerListeners(
            array(
                GetPropertyOptionsEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\InputScreen\PropertyPTable::getTables',
            ),
            $dispatcher,
            array('tl_metamodel_dca', 'ptable')
        );

        self::registerListeners(
            array(
                GetPropertyOptionsEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\InputScreen\PropertyRenderType::getRenderTypes',
            ),
            $dispatcher,
            array('tl_metamodel_dca', 'rendertype')
        );

        self::registerListeners(
            array(
                GetPropertyOptionsEvent::NAME
                    => 'MetaModels\DcGeneral\Events\Table\InputScreen\PropertyRenderMode::getModes',
            ),
            $dispatcher,
            array('tl_metamodel_dca', 'rendermode')
        );

        PropertyPTable::setVisibility($event);
    }

    /**
     * Register the events for table tl_metamodel_dca.
     *
     * @param BuildDataDefinitionEvent $event The event being processed.
     *
     * @return void
     */
    public static function registerTableMetaModelDcaSortGroupEvents(BuildDataDefinitionEvent $event)
    {
        static $registered;
        if ($registered) {
            return;
        }
        $registered = true;
        $dispatcher = func_get_arg(2);

        self::registerListeners(
            array(
                GetPropertyOptionsEvent::NAME
                => 'MetaModels\DcGeneral\Events\Table\InputScreenSortGroup\PropertyRenderGroupAttribute::getOptions',
                DecodePropertyValueForWidgetEvent::NAME
                => 'MetaModels\DcGeneral\Events\Table\InputScreenSortGroup\PropertyRenderGroupAttribute::decodeValue',
                EncodePropertyValueFromWidgetEvent::NAME
                => 'MetaModels\DcGeneral\Events\Table\InputScreenSortGroup\PropertyRenderGroupAttribute::encodeValue'
            ),
            $dispatcher,
            array('tl_metamodel_dca_sortgroup', 'rendergroupattr')
        );

        self::registerListeners(
            array(
                GetPropertyOptionsEvent::NAME
                => 'MetaModels\DcGeneral\Events\Table\InputScreenSortGroup\PropertyRenderSortAttribute::getOptions',
                DecodePropertyValueForWidgetEvent::NAME
                => 'MetaModels\DcGeneral\Events\Table\InputScreenSortGroup\PropertyRenderSortAttribute::decodeValue',
                EncodePropertyValueFromWidgetEvent::NAME
                => 'MetaModels\DcGeneral\Events\Table\InputScreenSortGroup\PropertyRenderSortAttribute::encodeValue'
            ),
            $dispatcher,
            array('tl_metamodel_dca_sortgroup', 'rendersortattr')
        );

        PropertyRenderGroupType::setVisibility($event);
    }

    /**
     * Register the events for table tl_metamodel_dca_combine.
     *
     * @return void
     */
    public static function registerTableMetaModelDcaCombineEvents()
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
    public static function registerTableMetaModelDcaSettingEvents(BuildDataDefinitionEvent $event)
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
    public static function registerTableMetaModelDcaSettingConditionsEvents()
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
    public static function registerTableMetaModelFilterEvents()
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
    public static function registerTableMetaModelFilterSettingEvents()
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
    public static function registerTableMetaModelRenderSettingEvents(BuildDataDefinitionEvent $event)
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
    public static function registerTableMetaModelRenderSettingsEvents()
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

    /**
     * Determine if a MetaModel is being loaded and if so, populate the container.
     *
     * @param PreCreateDcGeneralEvent $event The event.
     *
     * @return void
     */
    public static function preCreateDcGeneral(PreCreateDcGeneralEvent $event)
    {
        $factory    = $event->getFactory();
        $name       = $factory->getContainerName();
        $dispatcher = func_get_arg(2);

        if (!in_array($name, Factory::getAllTables())) {
            return;
        }

        $generator = new Builder();

        $dispatcher->addListener(
            sprintf('%s[%s]', BuildDataDefinitionEvent::NAME, $name),
            array($generator, 'build'),
            $generator::PRIORITY
        );
        $dispatcher->addListener(
            sprintf('%s[%s]', PopulateEnvironmentEvent::NAME, $name),
            array($generator, 'populate'),
            $generator::PRIORITY
        );

        $factory->setContainerClassName('MetaModels\DcGeneral\DataDefinition\MetaModelDataDefinition');
    }
}
