<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2023 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\DcGeneral\Events;

use MetaModels\IMetaModel;
use MetaModels\IMetaModelsServiceContainer;

/**
 * Base class for central event subscriber implementation.
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren) - this is a stupid general purpose class.
 */
class BaseSubscriber
{
    /**
     * The MetaModel service container.
     *
     * @var IMetaModelsServiceContainer
     *
     * @psalm-suppress DeprecatedInterface
     */
    protected $serviceContainer;

    /**
     * Create a new instance.
     *
     * @param IMetaModelsServiceContainer $serviceContainer The MetaModel service container.
     *
     * @psalm-suppress DeprecatedInterface
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
     *
     * @psalm-suppress DeprecatedInterface
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
        /** @psalm-suppress DeprecatedMethod */
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
     * @param callable $listener  The listener to register.
     * @param int      $priority  The priority.
     *
     * @return BaseSubscriber
     */
    public function addListener($eventName, $listener, $priority = 200)
    {
        /** @psalm-suppress DeprecatedMethod */
        $dispatcher = $this->getServiceContainer()->getEventDispatcher();
        $dispatcher->addListener($eventName, $listener, $priority);

        return $this;
    }

    /**
     * Retrieve the MetaModel with the given id.
     *
     * @param string $modelId The model being processed.
     *
     * @return IMetaModel|null
     */
    protected function getMetaModelById($modelId)
    {
        $services = $this->getServiceContainer();
        /** @psalm-suppress DeprecatedMethod */
        $modelFactory = $services->getFactory();
        $name         = $modelFactory->translateIdToMetaModelName($modelId);

        return $modelFactory->getMetaModel($name);
    }
}
