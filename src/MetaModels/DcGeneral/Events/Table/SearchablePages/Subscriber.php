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

namespace MetaModels\DcGeneral\Events\Table\SearchablePages;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use MetaModels\DcGeneral\Events\BaseSubscriber;
use MetaModels\DcGeneral\Events\BreadCrumb\BreadCrumbRenderSetting;


/**
 * Handles event operations on tl_metamodel_rendersetting.
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
                GetPropertyOptionsEvent::NAME,
                array($this, 'getFilterOptions')
            )
            ->addListener(
                GetPropertyOptionsEvent::NAME,
                array($this, 'getRenderSettingsOptions')
            );
    }

    /**
     * Provide options for filter list.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     */
    public static function getFilterOptions(GetPropertyOptionsEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_searchable_pages')
            || ($event->getPropertyName() !== 'setFilter')) {
            return;
        }

        $model = $event->getModel();
        $pid   = $model->getProperty('pid');
        if (empty($pid)) {
            return;
        }

        $filter = \Database::getInstance()
            ->prepare('SELECT id, name FROM tl_metamodel_filter WHERE pid=?')
            ->execute($pid);

        $options = array();
        while ($filter->next()) {
            $options[$filter->id] = $filter->name;
        }

        $event->setOptions($options);
    }

    /**
     * Provide options for filter list.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     */
    public static function getRenderSettingsOptions(GetPropertyOptionsEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_searchable_pages')
            || ($event->getPropertyName() !== 'setRendersetting')) {
            return;
        }

        $model = $event->getModel();
        $pid   = $model->getProperty('pid');
        if (empty($pid)) {
            return;
        }

        $filter = \Database::getInstance()
            ->prepare('SELECT id, name FROM tl_metamodel_rendersettings WHERE pid=?')
            ->execute($pid);

        $options = array();
        while ($filter->next()) {
            $options[$filter->id] = $filter->name;
        }

        $event->setOptions($options);
    }


}
