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
use MetaModels\DcGeneral\Events\BreadCrumb\BreadCrumbFilter;
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
        $this->registerTableMetaModelDcaCombineEvents();
        $this->registerTableMetaModelDcaSettingEvents();
        $this->registerTableMetaModelDcaSettingConditionsEvents();
        $this->registerTableMetaModelFilterEvents();
        $this->registerTableMetaModelFilterSettingEvents();
        $this->registerTableMetaModelRenderSettingEvents();
        $this->registerTableMetaModelRenderSettingsEvents();
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
        new \MetaModels\DcGeneral\Events\Table\DcaCombine\Subscriber($this->getServiceContainer());
    }

    /**
     * Register the events for table tl_metamodel_dcasetting.
     *
     * @return void
     */
    public function registerTableMetaModelDcaSettingEvents()
    {
        new \MetaModels\DcGeneral\Events\Table\InputScreens\Subscriber($this->getServiceContainer());
    }

    /**
     * Register the events for table tl_metamodel_dcasetting_condition.
     *
     * @return void
     */
    public function registerTableMetaModelDcaSettingConditionsEvents()
    {
        new \MetaModels\DcGeneral\Events\Table\InputScreenCondition\Subscriber($this->getServiceContainer());
    }

    /**
     * Register the events for table tl_metamodel_filter.
     *
     * @return void
     */
    public function registerTableMetaModelFilterEvents()
    {
        $serviceContainer = $this->getServiceContainer();
        $this
            ->addListener(
                GetBreadcrumbEvent::NAME,
                function (GetBreadcrumbEvent $event) use ($serviceContainer) {
                    if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_filter')) {
                        return;
                    }
                    $subscriber = new BreadCrumbFilter($serviceContainer);
                    $subscriber->getBreadcrumb($event);
                }
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
        new \MetaModels\DcGeneral\Events\Table\FilterSetting\Subscriber($this->getServiceContainer());
    }

    /**
     * Register the events for table tl_metamodel_rendersetting.
     *
     * @return void
     */
    public function registerTableMetaModelRenderSettingEvents()
    {
        new \MetaModels\DcGeneral\Events\Table\RenderSetting\Subscriber($this->getServiceContainer());
    }

    /**
     * Register the events for table tl_metamodel_rendersettings.
     *
     * @return void
     */
    public function registerTableMetaModelRenderSettingsEvents()
    {
        new \MetaModels\DcGeneral\Events\Table\RenderSettings\Subscriber($this->getServiceContainer());
    }
}
