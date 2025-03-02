<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2025 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2025 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetSelectModeButtonsEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractEnvironmentAwareEvent;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractModelAwareEvent;

use function array_key_exists;
use function in_array;

class SelectModeButtonsListener
{
    public function __construct(
        private readonly RequestScopeDeterminator $scopeDeterminator
    ) {
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

    /**
     * Test if the event is for the correct table and in backend scope.
     *
     * @param AbstractEnvironmentAwareEvent $event The event to test.
     *
     * @return bool
     */
    protected function wantToHandle(AbstractEnvironmentAwareEvent $event)
    {
        if (!$this->scopeDeterminator->currentScopeIsBackend()) {
            return false;
        }

        $environment    = $event->getEnvironment();
        $dataDefinition = $environment->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        if (
            !in_array(
                $dataDefinition->getName(),
                [
                    'tl_metamodel',
                    'tl_metamodel_attribute',
                    'tl_metamodel_dca_sortgroup',
                    'tl_metamodel_dcasetting',
                    'tl_metamodel_dcasetting_condition',
                    'tl_metamodel_rendersetting',
                    'tl_metamodel_searchable_pages',
                ]
            )
        ) {
            return false;
        }

        if (
            ($event instanceof AbstractModelAwareEvent)
            && $dataDefinition->getName() !== $event->getModel()->getProviderName()
        ) {
            return false;
        }

        return true;
    }
}
