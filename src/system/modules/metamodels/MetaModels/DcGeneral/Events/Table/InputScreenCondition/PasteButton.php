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
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\DcGeneral\Events\Table\InputScreenCondition;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPasteButtonEvent;

/**
 * Calculate the paste button for a given property condition.
 */
class PasteButton
{
    /**
     * Generate the paste button.
     *
     * @param GetPasteButtonEvent $event The event.
     *
     * @return void
     */
    public static function generate(GetPasteButtonEvent $event)
    {
        $environment = $event->getEnvironment();
        $model       = $event->getModel();
        $clipboard   = $environment->getClipboard();

        // Disable all buttons if there is a circular reference.
        if (($clipboard->isCut()
            && ($event->getCircularReference() || in_array($model->getId(), $clipboard->getContainedIds())))
        ) {
            $event
                ->setPasteAfterDisabled(true)
                ->setPasteIntoDisabled(true);

            return;
        }

        $flags = $GLOBALS['METAMODELS']['inputscreen_conditions'][$model->getProperty('type')];
        // If setting does not support children, omit them.
        if ($model->getId() &&
            (!$flags['nestingAllowed'])
        ) {
            $event->setPasteIntoDisabled(true);
            return;
        }

        if (isset($flags['maxChildren'])
            && count($event->getEnvironment()->getController()->assembleAllChildrenFrom($model)) > $flags['maxChildren']
        ) {
            $event->setPasteIntoDisabled(true);
        }
    }
}
