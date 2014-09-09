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

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use MetaModels\Dca\Helper;

/**
 * Handle events related to tl_metamodel_rendersettings.template
 *
 * @package MetaModels\DcGeneral\Events\Table\RenderSettings
 */
class PropertyTemplate
{
    /**
     * Provide options for default selection.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     */
    public static function getOptions(GetPropertyOptionsEvent $event)
    {
        $options = Helper::getTemplatesForBase('metamodel_');

        $event->setOptions($options);
    }
}
