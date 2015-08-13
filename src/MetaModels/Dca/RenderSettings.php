<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2015 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  2012-2015 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\Dca;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\DcGeneral\DC_General;

/**
 * This class is used from DCA tl_metamodel_rendersetting for various callbacks.
 */
class RenderSettings
{
    /**
     * Return the link picker wizard.
     *
     * @param DC_General $dataContainer The DC_General currently in use.
     *
     * @return string
     */
    public function pagePicker(DC_General $dataContainer)
    {
        $environment = $dataContainer->getEnvironment();

        if (version_compare(VERSION, '3.0', '<')) {
            $event = new GenerateHtmlEvent(
                'pickpage.gif',
                $environment->getTranslator()->translate('MSC.pagepicker'),
                'style="vertical-align:top;cursor:pointer" onclick="Backend.pickPage(\'ctrl_' .
                $dataContainer->inputName . '\')"'
            );

            $environment->getEventDispatcher()->dispatch(ContaoEvents::IMAGE_GET_HTML, $event);

            return ' ' . $event->getHtml();
        }

        $url = sprintf(
            '%scontao/page.php?do=metamodels&table=tl_metamodel_rendersettings&field=ctrl_%s',
            \Environment::get('base'),
            $dataContainer->inputName
        );

        $options = sprintf(
            "{'width':765,'title':'%s','url':'%s','id':'%s','tag':'ctrl_%s','self':this}",
            $environment->getTranslator()->translate('MOD.page.0'),
            $url,
            $dataContainer->inputName,
            $dataContainer->inputName
        );

        $event = new GenerateHtmlEvent(
            'pickpage.gif',
            $environment->getTranslator()->translate('MSC.pagepicker'),
            'style="vertical-align:top;cursor:pointer"'
        );

        $environment->getEventDispatcher()->dispatch(ContaoEvents::IMAGE_GET_HTML, $event);

        return sprintf(
            ' <a href="%s"%s>%s</a>',
            $url,
            ' onclick="Backend.openModalSelector(' . $options . '); return false;"',
            $event->getHtml()
        );
    }
}
