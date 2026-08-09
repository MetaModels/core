<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2026 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2026 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\MetaModel;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetOperationButtonsEvent;

/**
 * Sorts the operation buttons of the MetaModels listing by how often they are needed.
 *
 * The six area buttons are used constantly, deleting almost never. Grouping them right after the
 * edit button turns them into one closed block instead of leaving them interleaved with Contao's
 * standard operations.
 */
class OperationButtonOrderListener extends AbstractAbstainingListener
{
    /**
     * The wanted order, given as command names.
     *
     * "pasteNew" is not a command - the ButtonRenderer appends it after the commands have been
     * built. Anything not listed here keeps its relative order and follows at the end, so
     * operations added by other bundles do not get lost.
     *
     * @var list<string>
     */
    private const REIHENFOLGE = [
        'edit',
        'fields',
        'rendersettings',
        'dca',
        'searchable_pages',
        'filter',
        'dca_combine',
        'pasteNew',
        'cut',
        'delete',
        'show',
    ];

    /**
     * Reorder the buttons.
     *
     * @param GetOperationButtonsEvent $event The event.
     *
     * @return void
     */
    public function handle(GetOperationButtonsEvent $event)
    {
        if (!$this->wantToHandle($event)) {
            return;
        }

        $buttons = $event->getButtons();
        $sorted  = [];

        foreach (self::REIHENFOLGE as $name) {
            if (isset($buttons[$name])) {
                $sorted[$name] = $buttons[$name];
                unset($buttons[$name]);
            }
        }

        $event->setButtons(array_merge($sorted, $buttons));
    }
}
