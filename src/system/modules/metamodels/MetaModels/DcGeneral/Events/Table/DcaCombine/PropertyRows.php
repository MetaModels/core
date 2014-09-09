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

namespace MetaModels\DcGeneral\Events\Table\DcaCombine;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;

/**
 * Handle events for tl_metamodel_dca_combine.rows.
 *
 * @package MetaModels\DcGeneral\Events\Table\DcaCombine
 */
class PropertyRows
{
    /**
     * Handle event to update the sorting for DCA combinations.
     *
     * @param EncodePropertyValueFromWidgetEvent $event The event.
     *
     * @return void
     */
    public static function fixSorting(EncodePropertyValueFromWidgetEvent $event)
    {
        $values = $event->getValue();

        $i    = 0;
        $time = time();
        foreach (array_keys($values) as $key)
        {
            $values[$key]['sorting'] = $i;
            $values[$key]['tstamp']  = $time;

            $i += 128;
        }

        $event->setValue($values);
    }
}
