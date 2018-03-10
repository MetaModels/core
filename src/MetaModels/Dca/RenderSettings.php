<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2018 The MetaModels team.
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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
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
     * Return the link picker wizard - this is called from Multi column wizard in render settings jumpTo page handling.
     *
     * We should change this callback to event handlers as soon as MCW understands how DcGeneral events work.
     * So far MCW does not build sub widgets the MCW way, therefore we need to keep this as it is, despite the fact that
     * we are jumping multiple hoops with the inline javascript code to obtain the value.
     *
     * @param DC_General $dataContainer The DC_General currently in use.
     *
     * @return string
     */
    public function pagePicker(DC_General $dataContainer)
    {
        $environment = $dataContainer->getEnvironment();

        $url = sprintf(
            '%scontao/page.php?do=metamodels&table=tl_metamodel_rendersettings&field=ctrl_%s',
            \Environment::get('base'),
            $dataContainer->inputName
        );

        $event = new GenerateHtmlEvent(
            'pickpage.gif',
            $environment->getTranslator()->translate('MSC.pagepicker'),
            'style="vertical-align:top;cursor:pointer"'
        );

        $environment->getEventDispatcher()->dispatch(ContaoEvents::IMAGE_GET_HTML, $event);

        return sprintf(
            ' <a href="%1$s" onclick="Backend.openModalSelector(' .
            '{\'width\':765,' .
            '\'title\':\'%2$s\',' .
            '\'url\': this.href + \'&value=\' + ' .
            // We have no access to the current value as MCW does not understand DCG so far. So we do it all in JS.
            '(/{{link_url::([^}]*)}}/.test($(\'ctrl_%3$s\').value)' .
                ' ? /{{link_url::([^}]*)}}/.exec($(\'ctrl_%3$s\').value)[1]' .
                ' : \'\'),' .
            '\'id\':\'%3$s\',\'tag\':\'ctrl_%3$s\',\'self\':this}' .
            '); return false;">%4$s</a>',
            $url,
            specialchars(str_replace('\'', '\\\'', $environment->getTranslator()->translate('MOD.page.0'))),
            $dataContainer->inputName,
            $event->getHtml()
        );
    }
}
