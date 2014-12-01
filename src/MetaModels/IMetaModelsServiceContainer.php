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

namespace MetaModels;

use MetaModels\Attribute\IAttributeFactory;
use MetaModels\Filter\Setting\IFilterSettingFactory;
use MetaModels\Render\Setting\IRenderSettingFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This interface provides access to most of the needed services within MetaModels.
 *
 * @package MetaModels
 */
interface IMetaModelsServiceContainer
{
    /**
     * Retrieve the MetaModels factory.
     *
     * @return IFactory
     */
    public function getFactory();

    /**
     * Retrieve the MetaModels factory.
     *
     * @return IAttributeFactory
     */
    public function getAttributeFactory();

    /**
     * Retrieve the filter settings factory.
     *
     * @return IFilterSettingFactory
     */
    public function getFilterFactory();

    /**
     * Retrieve the render settings factory.
     *
     * @return IRenderSettingFactory
     */
    public function getRenderSettingFactory();

    /**
     * Retrieve the event dispatcher.
     *
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher();

    /**
     * Retrieve the system database.
     *
     * @return \Contao\Database
     */
    public function getDatabase();

    /**
     * Add a service to the container.
     *
     * Using this method you can store custom services in the container that are unknown to the MetaModels subsystem.
     *
     * @param object      $service     The service to add.
     *
     * @param null|string $serviceName The service name to use (defaults to null in which case the class name of the
     *                                 service will get used).
     *
     * @return MetaModelsServiceContainer
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
     * @return object
     */
    public function getService($serviceName);
}
