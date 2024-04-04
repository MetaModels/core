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

namespace MetaModels;

use Doctrine\Common\Cache\Cache;
use MetaModels\Attribute\IAttributeFactory;
use MetaModels\Filter\Setting\IFilterSettingFactory;
use MetaModels\Render\Setting\IRenderSettingFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This interface provides access to most of the needed services within MetaModels.
 *
 * @deprecated The service container will get removed, use the symfony service container instead.
 */
interface IMetaModelsServiceContainer
{
    /**
     * Retrieve the MetaModels factory.
     *
     * @return IFactory
     *
     * @deprecated The service container will get removed, use the symfony service container instead.
     */
    public function getFactory();

    /**
     * Retrieve the MetaModels factory.
     *
     * @return IAttributeFactory
     *
     * @deprecated The service container will get removed, use the symfony service container instead.
     */
    public function getAttributeFactory();

    /**
     * Retrieve the filter settings factory.
     *
     * @return IFilterSettingFactory
     *
     * @deprecated The service container will get removed, use the symfony service container instead.
     */
    public function getFilterFactory();

    /**
     * Retrieve the render settings factory.
     *
     * @return IRenderSettingFactory
     *
     * @deprecated The service container will get removed, use the symfony service container instead.
     */
    public function getRenderSettingFactory();

    /**
     * Retrieve the event dispatcher.
     *
     * @return EventDispatcherInterface
     *
     * @deprecated The service container will get removed, use the symfony service container instead.
     */
    public function getEventDispatcher();

    /**
     * Retrieve the system database.
     *
     * @return \Contao\Database
     *
     * @deprecated The service container will get removed, use the symfony service container instead.
     */
    public function getDatabase();

    /**
     * The cache in use.
     *
     * @return Cache
     *
     * @deprecated The service container will get removed, use the symfony service container instead.
     */
    public function getCache();

    /**
     * Add a service to the container.
     *
     * Using this method you can store custom services in the container that are unknown to the MetaModels subsystem.
     *
     * @param object|callable|mixed $service     The service to add.
     *
     * @param null|string           $serviceName The service name to use (defaults to null in which case the class name
     *                                           of the service will get used if the passed service is an object).
     *
     * @return MetaModelsServiceContainer
     *
     * @deprecated The service container will get removed, use the symfony service container instead.
     *
     * @psalm-suppress DeprecatedClass
     */
    public function setService($service, $serviceName = null);

    /**
     * Retrieve a service from the environment.
     *
     * Using this method you can retrieve custom services from the container that are unknown to the MetaModels
     * subsystem.
     *
     * @param string $serviceName The name of the service to retrieve.
     *
     * @return object|callable|mixed
     *
     * @deprecated The service container will get removed, use the symfony service container instead.
     */
    public function getService($serviceName);
}
