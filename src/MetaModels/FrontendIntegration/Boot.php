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

namespace MetaModels\FrontendIntegration;

use MetaModels\Events\MetaModelsBootEvent;

/**
 * This class is used in the frontend to build the menu.
 */
class Boot
{
    /**
     * Boot the system in the frontend.
     *
     * @param MetaModelsBootEvent $event The event.
     *
     * @return void
     */
    public function perform(MetaModelsBootEvent $event)
    {
        // Perform frontend boot tasks.
    }
}
