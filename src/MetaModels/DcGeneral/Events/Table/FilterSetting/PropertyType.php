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

namespace MetaModels\DcGeneral\Events\Table\FilterSetting;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;

/**
 * Handle events for property tl_metamodel_filtersetting.type.
 */
class PropertyType
{
    /**
     * Provide options for default selection.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public static function getOptions(GetPropertyOptionsEvent $event)
    {
        $translator = $event->getEnvironment()->getTranslator();
        $options    = array();

        foreach (array_keys($GLOBALS['METAMODELS']['filters']) as $filter) {
            $options[$filter] = $translator->translate('typenames.' . $filter, 'tl_metamodel_filtersetting');
        }

        $event->setOptions($options);
    }
}
