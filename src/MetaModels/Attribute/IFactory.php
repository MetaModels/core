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
 *
 * @deprecated Use IAttributeFactory instead.
 */
interface IFactory extends IAttributeFactory
{
    /**
     * Instantiate a attribute from an array.
     *
     * @param array $arrData The attribute information data.
     *
     * @return IAttribute|null The instance of the attribute or NULL if the class could not be determined
     *
     * @deprecated Use an instance of the factory and method createAttribute().
     */
    public static function createFromArray($arrData);

    /**
     * Instantiate a attribute from an array.
     *
     * @param \Database\Result $objRow The attribute information data.
     *
     * @return IAttribute|null The instance of the attribute or NULL if the class could not be determined.
     *
     * @deprecated Use an instance of the factory and method createAttribute().
     */
    public static function createFromDB($objRow);

    /**
     * Instantiate all attributes for the given MetaModel instance.
     *
     * @param IMetaModel $objMetaModel The MetaModel instance for which all attributes shall be returned.
     *
     * @return IAttribute[] The instances of the attributes.
     *
     * @deprecated Use an instance of the factory and method createAttribute().
     */
    public static function getAttributesFor($objMetaModel);

    /**
     * Returns an array of all registered attribute types.
     *
     * @return string[] All attribute types.
     *
     * @deprecated Will not be in available anymore - if you need this, file a ticket.
     */
    public static function getAttributeTypes();

    /**
     * Checks whether the given attribute type name is registered in the system.
     *
     * @param string $strFieldType The attribute type name to check.
     *
     * @return bool True if the attribute type is valid, false otherwise.
     *
     * @deprecated Will not be in available anymore - if you need this, file a ticket.
     */
    public static function isValidAttributeType($strFieldType);
}
