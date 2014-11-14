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

namespace MetaModels\Helper;

use MetaModels\Events\MetaModelsBootEvent;
use MetaModels\IMetaModelsServiceContainer;
use MetaModels\MetaModelsEvents;

/**
 * Base event listener to boot up a MetaModelServiceContainer.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class SubSystemBoot
{
    /**
     * Local wrapper function to retrieve the current execution mode of Contao.
     *
     * @return string
     */
    protected function getMode()
    {
        return defined('TL_MODE') ? TL_MODE : '';
    }

    /**
     * Get the Contao database instance.
     *
     * @return \Contao\Database
     */
    protected function getDatabase()
    {
        return \Database::getInstance();
    }

    /**
     * Retrieve the service container from the global DIC.
     *
     * @return IMetaModelsServiceContainer
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function getServiceContainer()
    {
        return $GLOBALS['container']['metamodels-service-container'];
    }

    /**
     * Boot up the system and initialize a service container.
     *
     * @return void
     */
    public function boot()
    {
        $container  = $this->getServiceContainer();
        $dispatcher = $container->getEventDispatcher();
        $event      = new MetaModelsBootEvent($container);

        $dispatcher->dispatch(MetaModelsEvents::SUBSYSTEM_BOOT, $event);

        if ($mode = $this->getMode()) {
            $eventName = MetaModelsEvents::SUBSYSTEM_BOOT_FRONTEND;

            if ($mode === 'BE') {
                $eventName = MetaModelsEvents::SUBSYSTEM_BOOT_BACKEND;
            }

            $dispatcher->dispatch($eventName, $event);
        }
    }
}
