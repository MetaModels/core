<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2018 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels;

use Doctrine\Common\Cache\Cache;
use MetaModels\Attribute\IAttributeFactory;
use MetaModels\Filter\Setting\IFilterSettingFactory;
use MetaModels\Render\Setting\IRenderSettingFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Reference implementation of IMetaModelsServiceContainer.
 */
class MetaModelsServiceContainer implements IMetaModelsServiceContainer
{
    /**
     * The factory to use.
     *
     * @var IFactory
     */
    protected $factory;

    /**
     * The factory to use.
     *
     * @var IAttributeFactory
     */
    protected $attributeFactory;

    /**
     * The filter setting factory.
     *
     * @var IFilterSettingFactory
     */
    protected $filterFactory;

    /**
     * The render setting factory.
     *
     * @var IRenderSettingFactory
     */
    protected $renderFactory;

    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * The Contao database instance to use.
     *
     * @var \Contao\Database
     */
    protected $database;

    /**
     * The cache in use.
     *
     * @var Cache
     */
    protected $cache;

    /**
     * Registered services.
     *
     * @var object[]
     */
    protected $services;

    /**
     * Set the factory to use.
     *
     * @param IFactory $factory The factory in use.
     *
     * @return MetaModelsServiceContainer
     */
    public function setFactory(IFactory $factory)
    {
        $this->factory = $factory;
        $factory->setServiceContainer($this);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFactory()
    {
        return $this->factory;
    }

    /**
     * Set the factory to use.
     *
     * @param IAttributeFactory $factory The factory in use.
     *
     * @return MetaModelsServiceContainer
     */
    public function setAttributeFactory(IAttributeFactory $factory)
    {
        $this->attributeFactory = $factory;
        $factory->setServiceContainer($this);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeFactory()
    {
        return $this->attributeFactory;
    }

    /**
     * Set the filter setting factory.
     *
     * @param IFilterSettingFactory $filterFactory The factory.
     *
     * @return MetaModelsServiceContainer
     */
    public function setFilterFactory($filterFactory)
    {
        $this->filterFactory = $filterFactory;
        $filterFactory->setServiceContainer($this);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterFactory()
    {
        return $this->filterFactory;
    }

    /**
     * Set the filter setting factory.
     *
     * @param IRenderSettingFactory $renderFactory The factory.
     *
     * @return MetaModelsServiceContainer
     */
    public function setRenderSettingFactory($renderFactory)
    {
        $this->renderFactory = $renderFactory;
        $renderFactory->setServiceContainer($this);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRenderSettingFactory()
    {
        return $this->renderFactory;
    }

    /**
     * Set the event dispatcher.
     *
     * @param EventDispatcherInterface $dispatcher The event dispatcher.
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
     */
    public function getEventDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * Set the Contao database instance.
     *
     * @param \Contao\Database $database The contao database instance.
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
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * Retrieve the cache to use.
     *
     * @return Cache
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Set the cache to use.
     *
     * @param Cache $cache The cache instance.
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
     * @throws \InvalidArgumentException When the passed service is not an object and no service name has been passed.
     */
    public function setService($service, $serviceName = null)
    {
        if ($serviceName === null) {
            if (!is_object($service) || $service instanceof \Closure) {
                throw new \InvalidArgumentException(
                    'Service name must be given to ' . __CLASS__ . '::setService when not passing a class instance.'
                );
            }

            $serviceName = get_class($service);
        }

        $this->services[$serviceName] = $service;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getService($serviceName)
    {
        return isset($this->services[(string) $serviceName]) ? $this->services[(string) $serviceName] : null;
    }
}
