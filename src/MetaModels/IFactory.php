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
 * This is the MetaModel factory interface.
 *
 * To create a MetaModel instance, create an instance of the factory.
 * and call {@link \MetaModels\IFactory::byTableName()}.
 *
 * If you only have the id of a MetaModel, you can translate it to the MetaModel-name by invoking
 * {@link \MetaModels\IFactory::translateIdToMetaModelName()} and then perform just as normal.
 *
 * @package    MetaModels
 * @subpackage Interfaces
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
interface IFactory extends IServiceContainerAware
{
    /**
     * Create a MetaModel instance from the id.
     *
     * @param int $intId The id of the MetaModel.
     *
     * @return IMetaModel the instance of the MetaModel or null if not found.
     *
     * @deprecated To create an instance use methods translateIdToMetaModelName() and createMetaModel().
     */
    public static function byId($intId);

    /**
     * Create a MetaModel instance from the table name.
     *
     * @param string $strTableName The name of the table.
     *
     * @return IMetaModel the instance of the MetaModel or null if not found.
     *
     * @deprecated To create an instance use method createMetaModel().
     */
    public static function byTableName($strTableName);

    /**
     * Query for all known MetaModel database tables.
     *
     * @return string[] all MetaModel table names as string array.
     *
     * @deprecated To retrieve all names use method collectNames().
     */
    public static function getAllTables();

    /**
     * Retrieve the event dispatcher.
     *
     * @return IMetaModelsServiceContainer
     */
    public function getServiceContainer();

    /**
     * Translate a MetaModel id to the corresponding name of the MetaModel.
     *
     * @param string $metaModelId The id of the MetaModel.
     *
     * @return string The name of the MetaModel.
     */
    public function translateIdToMetaModelName($metaModelId);

    /**
     * Create a MetaModel instance.
     *
     * @param string $metaModelName The name of the MetaModel to create.
     *
     * @return IMetaModel|null
     */
    public function getMetaModel($metaModelName);

    /**
     * Query for all known MetaModel names.
     *
     * @return string[] all MetaModel names as string array.
     */
    public function collectNames();
}
