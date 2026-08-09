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
     * What goes in front of the area block.
     *
     * @var list<string>
     */
    private const VORNE = ['edit'];

    /**
     * The six areas of MetaModels, in the order they belong in.
     *
     * @var list<string>
     */
    private const BEREICHE = [
        'fields',
        'rendersettings',
        'dca',
        'searchable_pages',
        'filter',
        'dca_combine',
    ];

    /**
     * What follows the area block.
     *
     * "pasteNew" is not a command - the ButtonRenderer appends it after the commands have been
     * built, as it does with the paste buttons.
     *
     * @var list<string>
     */
    private const HINTEN = ['pasteNew', 'cut', 'delete', 'show', 'pasteafter', 'pasteinto'];

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

        $vorne  = $this->pick($buttons, self::VORNE);
        $hinten = $this->pick($buttons, self::HINTEN);
        $block  = $this->pick($buttons, self::BEREICHE);

        // Was jetzt noch übrig ist, stammt von einem anderen Bundle - etwa die Merkliste. Solche
        // Operationen führen ebenfalls in einen Bereich des MetaModels und gehören deshalb an das
        // Ende des Blocks, nicht hinter das Löschen. Ihre Reihenfolge untereinander bleibt.
        $event->setButtons(array_merge($vorne, $block, $buttons, $hinten));
    }

    /**
     * Take the named buttons out of the list, in the order given.
     *
     * @param array<string, string> $buttons The remaining buttons, reduced by what was taken.
     * @param list<string>          $namen   The names to look for.
     *
     * @return array<string, string>
     */
    private function pick(array &$buttons, array $namen)
    {
        $genommen = [];
        foreach ($namen as $name) {
            if (isset($buttons[$name])) {
                $genommen[$name] = $buttons[$name];
                unset($buttons[$name]);
            }
        }

        return $genommen;
    }
}
