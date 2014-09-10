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

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;

/**
 * Handle events for property tl_metamodel_dcasetting_condition.type.
 */
class PropertyType
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
        $translator = $event->getEnvironment()->getTranslator();
        $options    = array();

        foreach (array_keys((array)$GLOBALS['METAMODELS']['inputscreen_conditions']) as $condition) {
            $options[$condition] = $translator->translate(
                'conditionnames.' . $condition,
                'tl_metamodel_dcasetting_condition'
            );
        }

        $event->setOptions($options);
    }
}
