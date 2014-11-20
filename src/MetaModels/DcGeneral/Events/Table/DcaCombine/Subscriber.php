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

namespace MetaModels\DcGeneral\Events\Table\DcaCombine;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent;
use MenAtWork\MultiColumnWizard\Event\GetOptionsEvent;
use MetaModels\DcGeneral\Events\BaseSubscriber;
use MetaModels\DcGeneral\Events\BreadCrumb\BreadCrumbDcaCombine;

/**
 * Handles event operations on tl_metamodel_dca_combine.
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
                    if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_dca_combine')) {
                        return;
                    }
                    $subscriber = new BreadCrumbDcaCombine($serviceContainer);
                    $subscriber->getBreadcrumb($event);
                }
            )
            ->addListener(
                GetOptionsEvent::NAME,
                array($this, 'getBackendUserGroupOptions')
            )
            ->addListener(
                GetOptionsEvent::NAME,
                array($this, 'getFrontendUserGroupOptions')
            )
            ->addListener(
                GetOptionsEvent::NAME,
                array($this, 'getInputScreenOptions')
            )
            ->addListener(
                GetOptionsEvent::NAME,
                array($this, 'getRenderSettingsOptions')
            )
            ->addListener(
                EncodePropertyValueFromWidgetEvent::NAME,
                array($this, 'fixSortingInRows')
            );
    }

    /**
     * Get all options for the frontend user groups.
     *
     * @param string          $table The source table.
     *
     * @param GetOptionsEvent $event The event.
     *
     * @return void
     *
     * @throws \RuntimeException When an invalid table name.
     */
    public function getUserGroups($table, GetOptionsEvent $event)
    {
        if (!in_array($table, array('tl_user_group', 'tl_member_group'))) {
            throw new \RuntimeException('Unexpected table name ' . $table, 1);
        }
        $groups = $this->getDatabase()->execute(sprintf('SELECT id,name FROM %s', $table));

        $result = array();
        if ($table == 'tl_user_group') {
            $result[-1] = $event->getEnvironment()->getTranslator()->translate('sysadmin', 'tl_metamodel_dca_combine');
        }

        while ($groups->next()) {
            $result[$groups->id] = $groups->name;
        }

        $event->setOptions($result);
    }

    /**
     * Get all options for the backend user groups.
     *
     * @param GetOptionsEvent $event The event.
     *
     * @return void
     */
    public function getBackendUserGroupOptions(GetOptionsEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_dca_combine')
            || ($event->getPropertyName() !== 'rows')
            || ($event->getSubPropertyName() !== 'be_group')) {
            return;
        }

        $this->getUserGroups('tl_user_group', $event);
    }

    /**
     * Get all options for the frontend user groups.
     *
     * @param GetOptionsEvent $event The event.
     *
     * @return void
     */
    public function getFrontendUserGroupOptions(GetOptionsEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_dca_combine')
            || ($event->getPropertyName() !== 'rows')
            || ($event->getSubPropertyName() !== 'fe_group')) {
            return;
        }

        $this->getUserGroups('tl_member_group', $event);
    }

    /**
     * Get all options for the frontend user groups.
     *
     * @param GetOptionsEvent $event The event.
     *
     * @return void
     */
    public function getInputScreenOptions(GetOptionsEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_dca_combine')
            || ($event->getPropertyName() !== 'rows')
            || ($event->getSubPropertyName() !== 'dca_id')) {
            return;
        }

        $inputScreens = $this
            ->getDatabase()
            ->prepare('SELECT id,name FROM tl_metamodel_dca WHERE pid=?')
            ->execute($event->getModel()->getProperty('id'));

        $result = array();
        while ($inputScreens->next()) {
            $result[$inputScreens->id] = $inputScreens->name;
        }

        $event->setOptions($result);
    }

    /**
     * Get all options for the render settings.
     *
     * @param GetOptionsEvent $event The event.
     *
     * @return void
     */
    public function getRenderSettingsOptions(GetOptionsEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_dca_combine')
            || ($event->getPropertyName() !== 'rows')
            || ($event->getSubPropertyName() !== 'view_id')) {
            return;
        }

        $inputScreens = $this
            ->getDatabase()
            ->prepare('SELECT id,name FROM tl_metamodel_rendersettings WHERE pid=?')
            ->execute($event->getModel()->getProperty('id'));

        $result = array();
        while ($inputScreens->next()) {
            $result[$inputScreens->id] = $inputScreens->name;
        }

        $event->setOptions($result);
    }

    /**
     * Handle event to update the sorting for DCA combinations.
     *
     * @param EncodePropertyValueFromWidgetEvent $event The event.
     *
     * @return void
     */
    public function fixSortingInRows(EncodePropertyValueFromWidgetEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_dca_combine')
            || ($event->getProperty() !== 'rows')) {
            return;
        }

        $values = $event->getValue();

        $index = 0;
        $time  = time();
        foreach (array_keys($values) as $key) {
            $values[$key]['sorting'] = $index;
            $values[$key]['tstamp']  = $time;

            $index += 128;
        }

        $event->setValue($values);
    }
}
