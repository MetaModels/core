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

namespace MetaModels\DcGeneral\Events\Table\InputScreens;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ManipulateWidgetEvent;

/**
 * Handle events for tl_metamodel_dcasetting.tl_class.
 *
 * @package MetaModels\DcGeneral\Events\Table\InputScreens
 */
class PropertyTlClass
{
    /**
     * Build the wizard string.
     *
     * @param ManipulateWidgetEvent $event The event.
     *
     * @return void
     */
    public static function getWizard(ManipulateWidgetEvent $event)
    {
        $url = 'system/modules/metamodels/popup.php?tbl=%s&fld=%s&inputName=ctrl_%s&id=%s&item=PALETTE_STYLE_PICKER';
        if (version_compare(VERSION, '3.0', '<')) {
            $link = ' <a href="' . $url . '" data-lightbox="files 768 80%%">%s</a>';
        } else {
            $link = ' <a href="javascript:Backend.openModalIframe({url:\'' .
                $url .
                '\',width:790,title:\'Stylepicker\'});">%s</a>';
        }

        /** @var GenerateHtmlEvent $imageEvent */
        $imageEvent = $event->getEnvironment()->getEventDispatcher()->dispatch(
            ContaoEvents::IMAGE_GET_HTML,
            new GenerateHtmlEvent(
                'system/modules/metamodels/assets/images/icons/dca_wizard.png',
                $event->getEnvironment()->getTranslator()->translate('stylepicker', 'tl_metamodel_dcasetting'),
                'style="vertical-align:top;"'
            )
        );

        $event->getWidget()->wizard = sprintf(
            $link,
            $event->getEnvironment()->getDataDefinition()->getName(),
            $event->getProperty()->getName(),
            $event->getProperty()->getName(),
            $event->getModel()->getId(),
            $imageEvent->getHtml()
        );
    }
}
