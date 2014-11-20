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

namespace MetaModels\Filter\Setting;

/**
 * This is the filter settings factory interface.
 *
 * To create a filter settings instance, call {@link \MetaModels\Filter\Setting\Factory::byId()}
 *
 * @package    MetaModels
 * @subpackage Interfaces
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
interface IFactory
{
    /**
     * Create a IMetaModelFilterSettings instance from the id.
     *
     * @param int $intId The id of the IMetaModelFilterSettings.
     *
     * @return ICollection The instance of the filter settings or null if not found.
     *
     * @deprecated Will get moved to a real factory.
     */
    public static function byId($intId);
}
