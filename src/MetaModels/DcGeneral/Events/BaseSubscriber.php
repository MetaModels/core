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

namespace MetaModels\DcGeneral\Events;

use MetaModels\IMetaModel;
use MetaModels\IMetaModelsServiceContainer;

/**
 * Base class for central event subscriber implementation.
 *
 * @package MetaModels\DcGeneral\Events
 */
class BaseSubscriber
{
    /**
     * The MetaModel service container.
     *
     * @var IMetaModelsServiceContainer
     */
    protected $serviceContainer;

    /**
     * Create a new instance.
     *
     * @param IMetaModelsServiceContainer $serviceContainer The MetaModel service container.
     */
    public function __construct(IMetaModelsServiceContainer $serviceContainer)
    {
        $this->serviceContainer = $serviceContainer;

        $this->registerEventsInDispatcher();
    }

    /**
     * Retrieve the service container.
     *
     * @return IMetaModelsServiceContainer
     */
    protected function getServiceContainer()
    {
        return $this->serviceContainer;
    }

    /**
     * Retrieve the database.
     *
     * @return \Contao\Database
     */
    protected function getDatabase()
    {
        return $this->getServiceContainer()->getDatabase();
    }

    /**
     * Register all listeners.
     *
     * @return void
     */
    protected function registerEventsInDispatcher()
    {
        // No op.
    }

    /**
     * Register multiple event listeners.
     *
     * @param string   $eventName The event name to register.
     *
     * @param callable $listener  The listener to register.
     *
     * @param int      $priority  The priority.
     *
     * @return BaseSubscriber
     */
    public function addListener($eventName, $listener, $priority = 200)
    {
        $dispatcher = $this->getServiceContainer()->getEventDispatcher();
        $dispatcher->addListener($eventName, $listener, $priority);

        return $this;
    }

    /**
     * Retrieve the MetaModel with the given id.
     *
     * @param int $modelId The model being processed.
     *
     * @return IMetaModel
     */
    protected function getMetaModelById($modelId)
    {
        $services     = $this->getServiceContainer();
        $modelFactory = $services->getFactory();
        $name         = $modelFactory->translateIdToMetaModelName($modelId);

        return $modelFactory->getMetaModel($name);
    }
}
