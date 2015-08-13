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

namespace MetaModels\Attribute;

use MetaModels\IMetaModel;

/**
 * This is the factory interface to query instances of attributes.
 * Usually this is only used internally from within the MetaModel class.
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
