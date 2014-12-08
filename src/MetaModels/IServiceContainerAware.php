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

/**
 * Reference implementation of IMetaModelsServiceContainer.
 */
interface IServiceContainerAware
{
    /**
     * Set the service container to use.
     *
     * @param IMetaModelsServiceContainer $serviceContainer The service container.
     *
     * @return IServiceContainerAware
     */
    public function setServiceContainer(IMetaModelsServiceContainer $serviceContainer);

    /**
     * Retrieve the service container in use.
     *
     * @return IMetaModelsServiceContainer|null
     */
    public function getServiceContainer();
}
