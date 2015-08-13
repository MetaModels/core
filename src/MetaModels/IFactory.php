<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2015 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2015 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
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
