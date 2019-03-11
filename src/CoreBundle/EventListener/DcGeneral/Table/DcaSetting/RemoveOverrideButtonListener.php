<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2019 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\DcaSetting;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetSelectModeButtonsEvent;

/**
 * This class takes care of removing the override button when select mode is active.
 */
class RemoveOverrideButtonListener
{
    /**
     * Remove the select button when in list view and override/edit all is active.
     *
     * @param GetSelectModeButtonsEvent $event The event.
     *
     * @return void
     */
    public function removeButton(GetSelectModeButtonsEvent $event)
    {
        if (('tl_metamodel_dcasetting' !== $event->getEnvironment()->getDataDefinition()->getName())
            || ('select' !== $event->getEnvironment()->getInputProvider()->getParameter('act'))
        ) {
            return;
        }

        $buttons = $event->getButtons();
        unset($buttons['override']);

        $event->setButtons($buttons);
    }
}
