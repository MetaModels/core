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

namespace MetaModels\DcGeneral\Events\Table\RenderSettings;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;

/**
 * Handle event to draw a render setting.
 *
 * @package MetaModels\DcGeneral\Events\Table\RenderSettings
 */
class DrawSetting
{
    /**
     * Draw the render setting.
     *
     * @param ModelToLabelEvent $event The event.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public static function modelToLabel(ModelToLabelEvent $event)
    {
        if ($event->getModel()->getProperty('isdefault')) {
            $event->setLabel(
                $event->getLabel() .
                ' <span style="color:#b3b3b3; padding-left:3px">[' . $GLOBALS['TL_LANG']['MSC']['fallback'] . ']</span>'
            );
        }
    }
}
