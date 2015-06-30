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
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */
namespace MetaModels\DcGeneral\Events\MetaModel;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetOperationButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\ViewHelpers;
use MetaModels\DcGeneral\DataDefinition\IMetaModelDataDefinition;
use MetaModels\DcGeneral\Events\BaseSubscriber;

/**
 * Class CutButton handles the cut button for a metamodels item view.
 *
 * @package MetaModels\DcGeneral\Events\MetaModel
 */
class CutButton extends BaseSubscriber
{
    /**
     * Register all listeners.
     *
     * @return void
     */
    protected function registerEventsInDispatcher()
    {
        $this->addListener(
            GetOperationButtonEvent::NAME,
            array($this, 'handle')
        );
    }

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

        if ($command->getName() === 'cut') {
            $sortingProperty = ViewHelpers::getManualSortingProperty($event->getEnvironment());

            if (!$sortingProperty) {
                $event->setDisabled(true);
            }
        }
    }
}
