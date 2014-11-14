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

namespace MetaModels\Events;

use MetaModels\IMetaModelsServiceContainer;
use Symfony\Component\EventDispatcher\Event;

/**
 * This event is triggered when a metamodels sub system is booted.
 *
 * It holds the MetaModels service container to use.
 *
 * @see MetaModelsEvents::SUBSYSTEM_BOOT
 *
 * @see MetaModelsEvents::SUBSYSTEM_BOOT_FRONTEND
 *
 * @see MetaModelsEvents::SUBSYSTEM_BOOT_BACKEND
 */
class MetaModelsBootEvent extends Event
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
    }

    /**
     * Retrieve the service container.
     *
     * @return IMetaModelsServiceContainer
     */
    public function getServiceContainer()
    {
        return $this->serviceContainer;
    }
}
