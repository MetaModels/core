<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2024 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels;

use Closure;
use Contao\BackendUser;
use Contao\Database;
use Contao\FrontendUser;
use Contao\System;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use Doctrine\Common\Cache\Cache;
use Doctrine\DBAL\Connection;
use InvalidArgumentException;
use MetaModels\Attribute\IAttributeFactory;
use MetaModels\BackendIntegration\ViewCombinations;
use MetaModels\Filter\Setting\IFilterSettingFactory;
use MetaModels\Render\Setting\IRenderSettingFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Reference implementation of IMetaModelsServiceContainer.
 *
 * @deprecated The service container will get removed, use the symfony service container instead.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @psalm-suppress DeprecatedInterface
 * @psalm-suppress MissingConstructor
 */
class MetaModelsServiceContainer implements IMetaModelsServiceContainer
{
    /**
     * The factory to use.
     *
     * @var IFactory|callable
     */
    protected $factory;

    /**
     * The factory to use.
     *
     * @var IAttributeFactory|callable
     */
    protected $attributeFactory;

    /**
     * The filter setting factory.
     *
     * @var IFilterSettingFactory|callable
     */
    protected $filterFactory;

    /**
     * The render setting factory.
     *
     * @var IRenderSettingFactory|callable
     */
    protected $renderFactory;

    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface|callable
     */
    protected $dispatcher;

    /**
     * The Contao database instance to use.
     *
     * @var Database|callable
     */
    protected $database;

    /**
     * The cache in use.
     *
     * @var Cache|callable
     */
    protected $cache;

    /**
     * Registered services.
     *
     * @var array<string, object|callable|mixed>
     */
    protected $services;

    /**
     * Set the factory to use.
     *
     * @param IFactory|callable $factory The factory in use.
     *
     * @return MetaModelsServiceContainer
     */
    public function setFactory($factory)
    {
        $this->factory = $factory;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated The service container will get removed, use the symfony service container instead.
     */
    public function getFactory()
    {
        // @codingStandardsIgnoreStart
        @trigger_error(
            '"' .__METHOD__ . '" is deprecated as the service container will get removed.',
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd

        if (\is_callable($this->factory)) {
            $this->factory = \call_user_func($this->factory);
            $this->factory->setServiceContainer($this, false);
        }

        return $this->factory;
    }

    /**
     * Set the factory to use.
     *
     * @param IAttributeFactory|callable $factory The factory in use.
     *
     * @return MetaModelsServiceContainer
     */
    public function setAttributeFactory($factory)
    {
        $this->attributeFactory = $factory;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated The service container will get removed, use the symfony service container instead.
     */
    public function getAttributeFactory()
    {
        // @codingStandardsIgnoreStart
        @trigger_error(
            '"' .__METHOD__ . '" is deprecated as the service container will get removed.',
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd

        if (\is_callable($this->attributeFactory)) {
            $this->attributeFactory = \call_user_func($this->attributeFactory);
            $this->attributeFactory->setServiceContainer($this, false);
        }

        return $this->attributeFactory;
    }

    /**
     * Set the filter setting factory.
     *
     * @param IFilterSettingFactory|callable $filterFactory The factory.
     *
     * @return MetaModelsServiceContainer
     */
    public function setFilterFactory($filterFactory)
    {
        $this->filterFactory = $filterFactory;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated The service container will get removed, use the symfony service container instead.
     */
    public function getFilterFactory()
    {
        // @codingStandardsIgnoreStart
        @trigger_error(
            '"' .__METHOD__ . '" is deprecated as the service container will get removed.',
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd

        if (\is_callable($this->filterFactory)) {
            $this->filterFactory = \call_user_func($this->filterFactory);
            $this->filterFactory->setServiceContainer($this, false);
        }

        return $this->filterFactory;
    }

    /**
     * Set the filter setting factory.
     *
     * @param IRenderSettingFactory|callable $renderFactory The factory.
     *
     * @return MetaModelsServiceContainer
     */
    public function setRenderSettingFactory($renderFactory)
    {
        $this->renderFactory = $renderFactory;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated The service container will get removed, use the symfony service container instead.
     */
    public function getRenderSettingFactory()
    {
        // @codingStandardsIgnoreStart
        @trigger_error(
            '"' .__METHOD__ . '" is deprecated as the service container will get removed.',
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd

        if (\is_callable($this->renderFactory)) {
            $this->renderFactory = \call_user_func($this->renderFactory);
            $this->renderFactory->setServiceContainer($this, false);
        }

        return $this->renderFactory;
    }

    /**
     * Set the event dispatcher.
     *
     * @param EventDispatcherInterface|callable $dispatcher The event dispatcher.
     *
     * @return MetaModelsServiceContainer
     */
    public function setEventDispatcher($dispatcher)
    {
        $this->dispatcher = $dispatcher;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated The service container will get removed, use the symfony service container instead.
     */
    public function getEventDispatcher()
    {
        // @codingStandardsIgnoreStart
        @trigger_error(
            '"' .__METHOD__ . '" is deprecated as the service container will get removed.',
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd

        if (\is_callable($this->dispatcher)) {
            $this->dispatcher = \call_user_func($this->dispatcher);
        }

        return $this->dispatcher;
    }

    /**
     * Set the Contao database instance.
     *
     * @param Database|callable $database The contao database instance.
     *
     * @return MetaModelsServiceContainer
     */
    public function setDatabase($database)
    {
        $this->database = $database;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated The service container will get removed, use the symfony service container instead.
     */
    public function getDatabase()
    {
        // @codingStandardsIgnoreStart
        @trigger_error(
            '"' .__METHOD__ . '" is deprecated as the service container will get removed.',
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd

        if (\is_callable($this->database)) {
            $this->database = \call_user_func($this->database);
        }

        return $this->database;
    }

    /**
     * Retrieve the cache to use.
     *
     * @return Cache
     *
     * @deprecated The service container will get removed, use the symfony service container instead.
     */
    public function getCache()
    {
        // @codingStandardsIgnoreStart
        @trigger_error(
            '"' .__METHOD__ . '" is deprecated as the service container will get removed.',
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd

        if (\is_callable($this->cache)) {
            $this->cache = \call_user_func($this->cache);
        }

        return $this->cache;
    }

    /**
     * Set the cache to use.
     *
     * @param Cache|callable $cache The cache instance.
     *
     * @return MetaModelsServiceContainer
     */
    public function setCache($cache)
    {
        $this->cache = $cache;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @throws InvalidArgumentException When the passed service is not an object and no service name has been passed.
     *
     * @deprecated The service container will get removed, use the symfony service container instead.
     */
    public function setService($service, $serviceName = null)
    {
        // @codingStandardsIgnoreStart
        @trigger_error(
            '"' .__METHOD__ . '" is deprecated as the service container will get removed.',
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd
        if ($serviceName === null) {
            if (!\is_object($service) || $service instanceof Closure) {
                throw new InvalidArgumentException(
                    'Service name must be given to ' . __CLASS__ . '::setService when not passing a class instance.'
                );
            }

            $serviceName = \get_class($service);
        }

        $this->services[$serviceName] = $service;

        return $this;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     *
     * @deprecated The service container will get removed, use the symfony service container instead.
     */
    public function getService($serviceName)
    {
        // @codingStandardsIgnoreStart
        @trigger_error(
            '"' .__METHOD__ . '" is deprecated as the service container will get removed.',
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd

        // Hacked in here as initialization is dead now.
        if (!isset($this->services[$serviceName]) && 'metamodels-view-combinations' === $serviceName) {
            $determinator = System::getContainer()->get('cca.dc-general.scope-matcher');
            assert($determinator instanceof RequestScopeDeterminator);
            $connection = System::getContainer()->get('database_connection');
            assert($connection instanceof Connection);
            switch (true) {
                case $determinator->currentScopeIsFrontend():
                    /** @psalm-suppress DeprecatedClass */
                    $this->services['metamodels-view-combinations'] =
                        new FrontendIntegration\ViewCombinations(
                            $this,
                            FrontendUser::getInstance(),
                            $connection
                        );
                    break;
                case $determinator->currentScopeIsBackend():
                    /** @psalm-suppress DeprecatedClass */
                    $this->services['metamodels-view-combinations'] =
                        new ViewCombinations(
                            $this,
                            BackendUser::getInstance(),
                            $connection
                        );
                    break;
                default:
            }
        }

        return ($this->services[$serviceName] ?? null);
    }
}
