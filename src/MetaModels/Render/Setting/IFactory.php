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

namespace MetaModels\Render\Setting;

use MetaModels\IMetaModel;

/**
 * This is the factory interface for render settings.
 *
 * To create a IFactory instance, call {@link Factory::byId()}
 *
 * @package    MetaModels
 * @subpackage Interfaces
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
// FIXME: make this a real factory, like the MetaModels factory and attribute factory.
interface IFactory
{
    /**
     * Load all render information from the database and push the contained information into the settings object.
     *
     * You should not call this method directly but rather use {@link IFactory::byId} instead.
     *
     * @param IMetaModel  $objMetaModel The MetaModel information for which the setting shall be retrieved.
     *
     * @param ICollection $objSetting   The render setting instance to be populated.
     *
     * @return void
     */
    public static function collectAttributeSettings(IMetaModel $objMetaModel, $objSetting);

    /**
     * Create a ICollection instance from the id.
     *
     * @param IMetaModel $objMetaModel The MetaModel information for which the setting shall be retrieved.
     *
     * @param int        $intId        The id of the ICollection.
     *
     * @return ICollection the instance of the render setting collection or null if not found.
     *
     * @deprecated Will get moved to a real factory.
     */
    public static function byId(IMetaModel $objMetaModel, $intId = 0);
}
