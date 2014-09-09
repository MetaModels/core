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

namespace MetaModels\Attribute;

use MetaModels\IMetaModel;

/**
 * This is the factory interface to query instances of attributes.
 * Usually this is only used internally from within the MetaModel class.
 *
 * @package    MetaModels
 * @subpackage Interfaces
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
interface IFactory
{
    /**
     * Instantiate a attribute from an array.
     *
     * @param array $arrData The attribute information data.
     *
     * @return IAttribute|null The instance of the attribute or NULL if the class could not be determined
     */
    public static function createFromArray($arrData);

    /**
     * Instantiate a attribute from an array.
     *
     * @param \Database\Result $objRow The attribute information data.
     *
     * @return IAttribute|null The instance of the attribute or NULL if the class could not be determined.
     */
    public static function createFromDB($objRow);

    /**
     * Instantiate all attributes for the given MetaModel instance.
     *
     * @param IMetaModel $objMetaModel The MetaModel instance for which all attributes shall be returned.
     *
     * @return IAttribute[] The instances of the attributes.
     */
    public static function getAttributesFor($objMetaModel);

    /**
     * Returns an array of all registered attribute types.
     *
     * @return string[] All attribute types.
     */
    public static function getAttributeTypes();

    /**
     * Checks whether the given attribute type name is registered in the system.
     *
     * @param string $strFieldType The attribute type name to check.
     *
     * @return bool True if the attribute type is valid, false otherwise.
     */
    public static function isValidAttributeType($strFieldType);
}

