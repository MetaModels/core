<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2017 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\DcGeneral\Events\Table\SearchablePages;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\BuildWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyValueCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\NotCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionChain;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use MetaModels\DcGeneral\Events\BaseSubscriber;
use MetaModels\DcGeneral\Events\BreadCrumb\BreadCrumbSearchablePages;

/**
 * Handles event operations on tl_metamodel_searchable_pages.
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
                    if ($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_searchable_pages') {
                        return;
                    }
                    $subscriber = new BreadCrumbSearchablePages($serviceContainer);
                    $subscriber->getBreadcrumb($event);
                }
            )
            ->addListener(
                GetPropertyOptionsEvent::NAME,
                array($this, 'getFilterOptions')
            )
            ->addListener(
                GetPropertyOptionsEvent::NAME,
                array($this, 'getRenderSettingsOptions')
            )
            ->addListener(
                BuildDataDefinitionEvent::NAME,
                array($this, 'visibleFilterParams')
            )
            ->addListener(
                BuildWidgetEvent::NAME,
                array($this, 'buildFilterParamsFor')
            );
    }

    /**
     * Provide options for filter list.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     */
    public function getFilterOptions(GetPropertyOptionsEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_searchable_pages')
            || ($event->getPropertyName() !== 'filter')
        ) {
            return;
        }

        $model = $event->getModel();
        $pid   = $model->getProperty('pid');
        if (empty($pid)) {
            return;
        }

        $filter = $this
            ->getDatabase()
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
    public function getRenderSettingsOptions(GetPropertyOptionsEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_searchable_pages')
            || ($event->getPropertyName() !== 'rendersetting')
        ) {
            return;
        }

        $model = $event->getModel();
        $pid   = $model->getProperty('pid');
        if (empty($pid)) {
            return;
        }

        $renderSettings = $this
            ->getDatabase()
            ->prepare('SELECT id, name FROM tl_metamodel_rendersettings WHERE pid=?')
            ->execute($pid);

        $options = array();
        while ($renderSettings->next()) {
            $options[$renderSettings->id] = $renderSettings->name;
        }

        $event->setOptions($options);
    }

    /**
     * Set the filter params visible or not.
     *
     * @param BuildDataDefinitionEvent $event The event.
     *
     * @return void
     */
    public function visibleFilterParams(BuildDataDefinitionEvent $event)
    {
        if ($event->getContainer()->getName() !== 'tl_metamodel_searchable_pages') {
            return;
        }

        foreach ($event->getContainer()->getPalettesDefinition()->getPalettes() as $palette) {
            foreach ($palette->getProperties() as $property) {
                if ($property->getName() != 'filterparams') {
                    continue;
                }

                $chain = $property->getVisibleCondition();
                if (!($chain
                    && ($chain instanceof PropertyConditionChain)
                    && $chain->getConjunction() == PropertyConditionChain::AND_CONJUNCTION
                )
                ) {
                    $chain = new PropertyConditionChain(
                        $chain ?: array(),
                        PropertyConditionChain::AND_CONJUNCTION
                    );

                    $property->setVisibleCondition($chain);
                }

                $chain->addCondition(new NotCondition(new PropertyValueCondition('filter', 0)));
                break;
            }
        }
    }

    /**
     * Build the filter params for the widget.
     *
     * @param BuildWidgetEvent $event The event.
     *
     * @return void
     */
    public function buildFilterParamsFor(BuildWidgetEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_searchable_pages')
            || ($event->getProperty()->getName() !== 'filterparams')
        ) {
            return;
        }

        $model = $event->getModel();

        $objFilterSettings = $this->getServiceContainer()->getFilterFactory()->createCollection(
            $model->getProperty('filter')
        );

        $extra              = $event->getProperty()->getExtra();
        $extra['subfields'] = $objFilterSettings->getParameterDCA();
        $event->getProperty()->setExtra($extra);
    }
}
