<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2024 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\MetaModel;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetSelectModeButtonsEvent;

use function array_key_exists;

class SelectModeButtonsListener extends AbstractAbstainingListener
{
    public function __construct(RequestScopeDeterminator $scopeDeterminator)
    {
        parent::__construct($scopeDeterminator);
    }

    /**
     * Delete copy button at edit all.
     *
     * @param GetSelectModeButtonsEvent $event The event.
     *
     * @return void
     */
    public function handle(GetSelectModeButtonsEvent $event): void
    {
        if (!$this->wantToHandle($event)) {
            return;
        }

        $buttons = $event->getButtons();
        if (array_key_exists('copy', $buttons)) {
            unset($buttons['copy']);
            $event->setButtons($buttons);
        }
    }
}
