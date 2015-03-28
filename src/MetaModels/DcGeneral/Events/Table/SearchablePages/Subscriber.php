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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\DcGeneral\Events\Table\SearchablePages;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\BuildWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyValueCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\NotCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionChain;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use MetaModels\DcGeneral\Events\BaseSubscriber;

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
        $this
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

                $chain->addCondition(new NotCondition(new PropertyValueCondition('filter', '')));
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
