<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2017 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels;

use MetaModels\Events\CollectMetaModelTableNamesEvent;
use MetaModels\Events\CreateMetaModelEvent;
use MetaModels\Events\GetMetaModelNameFromIdEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This is the MetaModel factory interface.
 *
 * To create a MetaModel instance, call @link{MetaModelFactory::getMetaModel()}
 */
class Factory implements IFactory
{
    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * The service container.
     *
     * @var IMetaModelsServiceContainer
     */
    private $serviceContainer;

    /**
     * The already translated MetaModel names.
     *
     * @var string[]
     */
    private $lookupMap = array();

    /**
     * Create a new instance.
     *
     * @param EventDispatcherInterface $dispatcher The event dispatcher.
     */
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher       = $dispatcher;
    }

    /**
     * Set the service container.
     *
     * @param IMetaModelsServiceContainer $serviceContainer The service container to use.
     *
     * @return Factory
     *
     * @deprecated The service container will get removed, use the symfony service container instead.
     */
    public function setServiceContainer(IMetaModelsServiceContainer $serviceContainer, $deprecationNotice = true)
    {
        if ($deprecationNotice) {
            @trigger_error(
                '"' .__METHOD__ . '" is deprecated and will get removed.',
                E_USER_DEPRECATED
            );
        }

        $this->serviceContainer = $serviceContainer;

        return $this;
    }

    /**
     * Retrieve the service container.
     *
     * @return IMetaModelsServiceContainer
     *
     * @deprecated The service container will get removed, use the symfony service container instead.
     */
    public function getServiceContainer()
    {
        @trigger_error(
            '"' .__METHOD__ . '" is deprecated - use the services from the service container.',
            E_USER_DEPRECATED
        );
        return $this->serviceContainer;
    }

    /**
     * {@inheritdoc}
     */
    public function translateIdToMetaModelName($metaModelId)
    {
        if (!isset($this->lookupMap[$metaModelId])) {
            $event = new GetMetaModelNameFromIdEvent($metaModelId);

            $this->dispatcher->dispatch($event::NAME, $event);

            $this->lookupMap[$metaModelId] = $event->getMetaModelName();
        }

        return $this->lookupMap[$metaModelId];
    }

    /**
     * {@inheritdoc}
     */
    public function getMetaModel($metaModelName)
    {
        $event = new CreateMetaModelEvent($this, $metaModelName);

        $this->dispatcher->dispatch($event::NAME, $event);

        $metaModel = $event->getMetaModel();

        return $metaModel;
    }

    /**
     * {@inheritdoc}
     */
    public function collectNames()
    {
        $event = new CollectMetaModelTableNamesEvent($this);

        $this->dispatcher->dispatch($event::NAME, $event);

        return $event->getMetaModelNames();
    }
}
