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
 * @author     David Maack <david.maack@arcor.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Filter\Setting;

use MetaModels\IMetaModelsServiceContainer;

/**
 * This is the IMetaModelFilter factory interface.
 *
 * To create a IMetaModelFilter instance, call {@link MetaModelFilter::byId()}
 *
 * @deprecated use the factory from the service container.
 */
class Factory extends FilterSettingFactory implements IFactory
{
    /**
     * Keeps track of all filter settings instances to save DB lookup queries.
     *
     * @var ICollection[]
     */
    protected static $arrInstances = array();

    /**
     * Create a IMetaModelFilter instance from the id.
     *
     * @param int $intId The id of the IMetaModelFilter.
     *
     * @return ICollection the instance of the IMetaModelFilterSettings or null if not found.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     *
     * @deprecated use the factory from the service container.
     */
    public static function byId($intId)
    {
        /** @var IMetaModelsServiceContainer $serviceContainer */
        $serviceContainer = $GLOBALS['container']['metamodels-service-container'];

        return $serviceContainer->getFilterFactory()->createCollection($intId);
    }
}
