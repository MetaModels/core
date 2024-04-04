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
 * @author     David Maack <david.maack@arcor.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
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
 *
 * @psalm-suppress DeprecatedInterface
 */
class Factory implements IFactory
{
    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private EventDispatcherInterface $dispatcher;

    /**
     * The service container.
     *
     * @var IMetaModelsServiceContainer|null
     *
     * @psalm-suppress DeprecatedInterface
     */
    private IMetaModelsServiceContainer|null $serviceContainer = null;

    /**
     * The already translated MetaModel names.
     *
     * @var string[]
     */
    private array $lookupMap = [];

    /**
     * Create a new instance.
     *
     * @param EventDispatcherInterface $dispatcher The event dispatcher.
     */
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Set the service container.
     *
     * @param IMetaModelsServiceContainer $serviceContainer  The service container to use.
     * @param bool                        $deprecationNotice The flag to trigger error.
     *
     * @return Factory
     *
     * @deprecated The service container will get removed, use the symfony service container instead.
     *
     * @psalm-suppress DeprecatedInterface
     */
    public function setServiceContainer(IMetaModelsServiceContainer $serviceContainer, $deprecationNotice = true)
    {
        if ($deprecationNotice) {
            // @codingStandardsIgnoreStart
            @trigger_error(
                '"' .__METHOD__ . '" is deprecated and will get removed.',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
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
     *
     * @psalm-suppress DeprecatedInterface
     */
    public function getServiceContainer()
    {
        if (null === $this->serviceContainer) {
            throw new \RuntimeException('Deprecated service container is not set anymore by default.');
        }

        // @codingStandardsIgnoreStart
        @trigger_error(
            '"' .__METHOD__ . '" is deprecated - use the services from the service container.',
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd
        return $this->serviceContainer;
    }

    /**
     * {@inheritdoc}
     */
    public function translateIdToMetaModelName($metaModelId)
    {
        if (!isset($this->lookupMap[$metaModelId])) {
            $event = new GetMetaModelNameFromIdEvent($metaModelId);

            $this->dispatcher->dispatch($event, $event::NAME);
            $translated = $event->getMetaModelName();
            if (null === $translated) {
                throw new \RuntimeException('Failed to convert id ' . $metaModelId . ' to table name.');
            }

            $this->lookupMap[$metaModelId] = $translated;
        }

        return $this->lookupMap[$metaModelId];
    }

    /**
     * {@inheritdoc}
     */
    public function getMetaModel($metaModelName)
    {
        $event = new CreateMetaModelEvent($this, $metaModelName);

        $this->dispatcher->dispatch($event, $event::NAME);

        return $event->getMetaModel();
    }

    /**
     * {@inheritdoc}
     */
    public function collectNames()
    {
        $event = new CollectMetaModelTableNamesEvent($this);

        $this->dispatcher->dispatch($event, $event::NAME);

        return $event->getMetaModelNames();
    }
}
