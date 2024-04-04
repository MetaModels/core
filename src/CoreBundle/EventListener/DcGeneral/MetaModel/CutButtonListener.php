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
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\MetaModel;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetOperationButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ViewHelpers;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CommandInterface;
use MetaModels\DcGeneral\DataDefinition\IMetaModelDataDefinition;

/**
 * Class CutButtonListener handles the cut button for a metamodels item view.
 */
class CutButtonListener
{
    /**
     * Handle the event.
     *
     * @param GetOperationButtonEvent $event The event.
     *
     * @return void
     */
    public function handle(GetOperationButtonEvent $event)
    {
        if (!$event->getEnvironment()->getDataDefinition() instanceof IMetaModelDataDefinition) {
            return;
        }

        $command = $event->getCommand();
        assert($command instanceof CommandInterface);

        if ($command->getName() === 'cut') {
            $sortingProperty = ViewHelpers::getManualSortingProperty($event->getEnvironment());

            if (null === $sortingProperty) {
                $event->setDisabled(true);
            }
        }
    }
}
