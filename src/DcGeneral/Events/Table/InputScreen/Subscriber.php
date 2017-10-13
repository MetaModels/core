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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\DcGeneral\Events\Table\InputScreen;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ManipulateWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionChain;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyValueCondition;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use MetaModels\DcGeneral\Events\BaseSubscriber;
use MetaModels\DcGeneral\Events\BreadCrumb\BreadCrumbInputScreens;

/**
 * Handles event operations on tl_metamodel_dca.
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
                    if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_dca')) {
                        return;
                    }
                    $subscriber = new BreadCrumbInputScreens($serviceContainer);
                    $subscriber->getBreadcrumb($event);
                }
            )
            ->addListener(
                BuildDataDefinitionEvent::NAME,
                array($this, 'setParentTableVisibility')
            )
            ->addListener(
                ModelToLabelEvent::NAME,
                array($this, 'modelToLabel')
            )
            ->addListener(
                ManipulateWidgetEvent::NAME,
                array($this, 'getPanelLayoutWizard')
            )
            ->addListener(
                GetPropertyOptionsEvent::NAME,
                array($this, 'getBackendSections')
            )
            ->addListener(
                GetPropertyOptionsEvent::NAME,
                array($this, 'getParentTables')
            )
            ->addListener(
                GetPropertyOptionsEvent::NAME,
                array($this, 'getRenderTypes')
            )
            ->addListener(
                GetPropertyOptionsEvent::NAME,
                array($this, 'getRenderModes')
            );
    }

    /**
     * Set the visibility condition for the widget.
     *
     * @param BuildDataDefinitionEvent $event The event.
     *
     * @return void
     */
    public function setParentTableVisibility(BuildDataDefinitionEvent $event)
    {
        foreach ($event->getContainer()->getPalettesDefinition()->getPalettes() as $palette) {
            foreach ($palette->getProperties() as $property) {
                if ($property->getName() != 'ptable') {
                    continue;
                }

                $chain = $property->getVisibleCondition();
                if (!($chain
                    && ($chain instanceof PropertyConditionChain)
                    && $chain->getConjunction() == PropertyConditionChain::AND_CONJUNCTION
                )) {
                    $chain = new PropertyConditionChain(
                        array($property->getVisibleCondition()),
                        PropertyConditionChain::AND_CONJUNCTION
                    );

                    $property->setVisibleCondition($chain);
                }

                $chain->addCondition(new PropertyValueCondition('rendertype', 'ctable'));
            }
        }
    }

    /**
     * Render the html for the input screen.
     *
     * @param ModelToLabelEvent $event The event.
     *
     * @return void
     */
    public function modelToLabel(ModelToLabelEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_dca')) {
            return;
        }

        $environment = $event->getEnvironment();
        $translator  = $environment->getTranslator();
        $model       = $event->getModel();

        if (!$model->getProperty('isdefault')) {
            return;
        }

        $event
            ->setArgs(array_merge($event->getArgs(), array($translator->translate('MSC.fallback'))))
            ->setLabel(sprintf('%s <span style="color:#b3b3b3; padding-left:3px">[%%s]</span>', $event->getLabel()));
    }

    /**
     * Calculate the wizard.
     *
     * @param ManipulateWidgetEvent $event The event.
     *
     * @return void
     */
    public function getPanelLayoutWizard(ManipulateWidgetEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_dca')
        || ($event->getProperty()->getName() !== 'panelLayout')) {
            return;
        }

        $url = 'system/modules/metamodels/popup.php?' .
            'tbl=%1$s' .
            '&fld=%2$s' .
            '&inputName=ctrl_%3$s' .
            '&id=%4$s' .
            '&item=PALETTE_PANEL_PICKER';

        $link = ' <a href="' . $url . '" onclick="Backend.getScrollOffset();Backend.openModalIframe({' .
            '\'width\':765,' .
            '\'title\':\'%6$s\',' .
            '\'url\':this.href,' .
            '\'id\':\'%4$s\'' .
            '});return false">%5$s</a>';

        $imageEvent = new GenerateHtmlEvent(
            'bundles/metamodelscore/images/icons/panel_layout.png',
            $event->getEnvironment()->getTranslator()->translate('panelpicker', 'tl_metamodel_dca'),
            'style="vertical-align:top;"'
        );

        $event->getEnvironment()->getEventDispatcher()->dispatch(ContaoEvents::IMAGE_GET_HTML, $imageEvent);

        $event->getWidget()->wizard = sprintf(
            $link,
            $event->getEnvironment()->getDataDefinition()->getName(),
            $event->getProperty()->getName(),
            $event->getProperty()->getName(),
            $event->getModel()->getId(),
            $imageEvent->getHtml(),
            addslashes($event->getEnvironment()->getTranslator()->translate('panelpicker', 'tl_metamodel_dca'))
        );
    }

    /**
     * Retrieve a list of all backend sections, like "content", "system" etc.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function getBackendSections(GetPropertyOptionsEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_dca')
            || ($event->getPropertyName() !== 'backendsection')) {
            return;
        }

        $event->setOptions(array_keys($GLOBALS['BE_MOD']));
    }

    /**
     * Returns an array with all valid tables that can be used as parent table.
     *
     * Excludes the metamodel table itself in ctable mode, as that one would be "selftree" then and not ctable.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     */
    public function getParentTables(GetPropertyOptionsEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_dca')
            || ($event->getPropertyName() !== 'ptable')) {
            return;
        }

        $currentTable = '';
        if ($event->getModel()->getProperty('rendertype') == 'ctable') {
            $currentTable = $this
                ->getServiceContainer()
                ->getFactory()
                ->translateIdToMetaModelName($event->getModel()->getProperty('pid'));
        }

        $tables = array();
        foreach ($this->getServiceContainer()->getDatabase()->listTables() as $table) {
            if (!($currentTable && ($currentTable == $table))) {
                $tables[$table] = $table;
            }
        }

        $event->setOptions($tables);
    }

    /**
     * Populates an array with all valid "rendertype".
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     */
    public function getRenderTypes(GetPropertyOptionsEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_dca')
            || ($event->getPropertyName() !== 'rendertype')) {
            return;
        }

        $event->setOptions(array('standalone', 'ctable'));
    }

    /**
     * Retrieve a list of all render modes.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     */
    public function getRenderModes(GetPropertyOptionsEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_dca')
            || ($event->getPropertyName() !== 'rendermode')) {
            return;
        }

        $translator = $event->getEnvironment()->getTranslator();
        $options    = array(
            'flat'         => $translator->translate('rendermodes.flat', 'tl_metamodel_dca'),
            'hierarchical' => $translator->translate('rendermodes.hierarchical', 'tl_metamodel_dca'),
        );

        if ($event->getModel()->getProperty('rendertype') == 'ctable') {
            $options['parented'] = $translator->translate('rendermodes.parented', 'tl_metamodel_dca');
        }

        $event->setOptions($options);
    }
}
