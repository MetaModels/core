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

namespace MetaModels\DcGeneral\Events\Table\Attribute;

use ContaoCommunityAlliance\DcGeneral\Event\PreDeleteModelEvent;
use MetaModels\Attribute\Factory;

/**
 * Handles delete operation on tl_metamodel_attribute.
 *
 * @package MetaModels\DcGeneral\Events\Table\Attribute
 */
class DeleteAttribute
{
    /**
     * Handle the deletion of an attribute and all attached data.
     *
     * @param PreDeleteModelEvent $event The event.
     *
     * @return void
     */
    public static function handle(PreDeleteModelEvent $event)
    {
        $newInstance = Factory::createFromArray($event->getModel()->getPropertiesAsArray());

        if ($newInstance)
        {
            $newInstance->destroyAUX();
        }
    }
}
