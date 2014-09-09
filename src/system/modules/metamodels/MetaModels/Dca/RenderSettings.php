<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Dca;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\DcGeneral\DC_General;

/**
 * This class is used from DCA tl_metamodel_rendersetting for various callbacks.
 *
 * @package    MetaModels
 * @subpackage Backend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class RenderSettings
{
    /**
     * Return the link picker wizard.
     *
     * @param DC_General $dc The DC_General currently in use.
     *
     * @return string
     */
    public function pagePicker(DC_General $dc)
    {
        $environment = $dc->getEnvironment();

        if (version_compare(VERSION, '3.0', '<'))
        {
            $event = new GenerateHtmlEvent(
                'pickpage.gif',
                $environment->getTranslator()->translate('MSC.pagepicker'),
                'style="vertical-align:top;cursor:pointer" onclick="Backend.pickPage(\'ctrl_' . $dc->inputName . '\')"'
            );

            $environment->getEventPropagator()->propagate(ContaoEvents::IMAGE_GET_HTML, $event);

            return ' ' . $event->getHtml();
        }

        $url = sprintf('%scontao/page.php?do=metamodels&table=tl_metamodel_rendersettings&field=ctrl_%s',
            \Environment::get('base'),
            $dc->inputName
        );

        $options = sprintf(
            "{'width':765,'title':'%s','url':'%s','id':'%s','tag':'ctrl_%s','self':this}",
            $environment->getTranslator()->translate('MOD.page.0'),
            $url,
            $dc->inputName,
            $dc->inputName
        );

        $event = new GenerateHtmlEvent(
            'pickpage.gif',
            $environment->getTranslator()->translate('MSC.pagepicker'),
            'style="vertical-align:top;cursor:pointer"'
        );

        $environment->getEventPropagator()->propagate(ContaoEvents::IMAGE_GET_HTML, $event);

        return sprintf(' <a href="%s"%s>%s</a>',
            $url,
            ' onclick="Backend.openModalSelector(' . $options . '); return false;"',
            $event->getHtml()
        );
    }
}

