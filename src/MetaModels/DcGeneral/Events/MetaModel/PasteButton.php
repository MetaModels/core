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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\DcGeneral\Events\MetaModel;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\BaseView;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPasteButtonEvent;
use MetaModels\Factory;

/**
 * This class handles the paste into and after button activation and deactivation for all MetaModels being edited.
 *
 * @package MetaModels\DcGeneral\Events\MetaModel
 */
class PasteButton extends BaseView
{
    /**
     * Handle the paste into and after buttons.
     *
     * @param GetPasteButtonEvent $event The event.
     *
     * @return void
     *
     * @throws \RuntimeException When more than one model is contained within the clipboard.
     */
    public static function handle(GetPasteButtonEvent $event)
    {
        // FIXME: make non static and use service container.
        $environment = $event->getEnvironment();
        $model       = $event->getModel();
        $clipboard   = $environment->getClipboard();
        $contained   = $event->getContainedModels();
        $disablePA   = true;
        $disablePI   = true;

        if (count($contained) !== 1) {
            throw new \RuntimeException('Paste multiple is not supported at the moment, sorry.');
        }

        // We assume we only have either varbase or non varbase items in the clipboard,
        // mixed contents are not supported.
        $containedModel = $contained->get(0);

        if ($containedModel && $containedModel->getId() && !$event->isCircularReference()) {
            if (Factory::byTableName($model->getProviderName())->hasVariants()) {
                if ((
                        ($containedModel->getProperty('varbase') == 1)
                        || !$containedModel->getId()
                    )
                    && (!$event->isCircularReference())
                    && $model->getProperty('varbase') == 1
                ) {
                    // Insert new items only after bases.
                    // Insert a varbase after any other varbase, for sorting.
                    $disablePA = false;
                } elseif ($containedModel->getProperty('varbase') == 0
                    && $containedModel->getProperty('vargroup') == $model->getProperty('vargroup')
                    && $containedModel->getProperty('varbase') != 1
                ) {
                    // Move items in their vargroup and only there.
                    $disablePA = false;
                }

                $disablePI = ($model->getProperty('varbase') != 1) || ($containedModel->getProperty('varbase'));
            } else {
                $disablePA = ($model->getId() == $containedModel->getId());
                $disablePI = $event->isCircularReference();
            }
        } elseif ($model == null && $containedModel->getProperty('varbase') == 0) {
            $disablePA = true;
        } else {
            $disablePA = false;
            // The following rules apply:
            // 1. Variant bases must not get pasted into anything.
            // 2. If we are not in create mode, disable the paste into for the item itself.
            $disablePI = ($containedModel->getProperty('varbase') == 1)
                || ($clipboard->getMode() != 'create' && $containedModel->getId() == $model->getId());
        }

        $event
            ->setPasteAfterDisabled($disablePA)
            ->setPasteIntoDisabled($disablePI);
    }
}
