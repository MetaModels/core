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
 * @package    MetaModels
 * @subpackage Core
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
 *
 * @deprecated The service container will get removed, use the symfony service container instead.
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
        $factory->setServiceContainer($this, false);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFactory()
    {
        @trigger_error(
            '"' .__METHOD__ . '" is deprecated as the service container will get removed.',
            E_USER_DEPRECATED
        );
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
        $factory->setServiceContainer($this, false);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeFactory()
    {
        @trigger_error(
            '"' .__METHOD__ . '" is deprecated as the service container will get removed.',
            E_USER_DEPRECATED
        );
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
        $filterFactory->setServiceContainer($this, false);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterFactory()
    {
        @trigger_error(
            '"' .__METHOD__ . '" is deprecated as the service container will get removed.',
            E_USER_DEPRECATED
        );
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
        $renderFactory->setServiceContainer($this, false);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getRenderSettingFactory()
    {
        @trigger_error(
            '"' .__METHOD__ . '" is deprecated as the service container will get removed.',
            E_USER_DEPRECATED
        );
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
        @trigger_error(
            '"' .__METHOD__ . '" is deprecated as the service container will get removed.',
            E_USER_DEPRECATED
        );
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
        @trigger_error(
            '"' .__METHOD__ . '" is deprecated as the service container will get removed.',
            E_USER_DEPRECATED
        );
        return $this->database;
    }

    /**
     * Retrieve the cache to use.
     *
     * @return Cache
     */
    public function getCache()
    {
        @trigger_error(
            '"' .__METHOD__ . '" is deprecated as the service container will get removed.',
            E_USER_DEPRECATED
        );
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
        @trigger_error(
            '"' .__METHOD__ . '" is deprecated as the service container will get removed.',
            E_USER_DEPRECATED
        );
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
        @trigger_error(
            '"' .__METHOD__ . '" is deprecated as the service container will get removed.',
            E_USER_DEPRECATED
        );

        // Hacked in here as initialization is dead now.
        if (!isset($this->services[(string) $serviceName]) && 'metamodels-view-combinations' === $serviceName) {
            $determinator = \System::getContainer()->get('cca.dc-general.scope-matcher');
            switch (true) {
                case $determinator->currentScopeIsFrontend():
                    $this->services['metamodels-view-combinations'] =
                        new \MetaModels\FrontendIntegration\ViewCombinations(
                            $this,
                            $GLOBALS['container']['user']
                        );
                    break;
                case $determinator->currentScopeIsBackend():
                    $this->services['metamodels-view-combinations'] =
                        new \MetaModels\BackendIntegration\ViewCombinations(
                            $this,
                            $GLOBALS['container']['user']
                        );
                    break;
                default:
            }
        }

        return isset($this->services[(string) $serviceName]) ? $this->services[(string) $serviceName] : null;
    }
}
