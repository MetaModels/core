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

namespace MetaModels\Filter\Setting;

/**
 * This is the factory interface to query instances of filter settings.
 * Usually this is only used internally from within the MetaModel class.
 */
interface IFilterSettingTypeFactory
{
    /**
     * Return the type name - this is the internal type name used by MetaModels.
     *
     * @return string
     */
    public function getTypeName();

    /**
     * Retrieve the (relative to TL_ROOT) path to a icon for the type.
     *
     * @return string
     */
    public function getTypeIcon();

    /**
     * Create a new instance with the given information.
     *
     * @param array       $information    The filter setting information.
     *
     * @param ICollection $filterSettings The filter setting instance the filter setting shall be created for.
     *
     * @return ISimple|null
     */
    public function createInstance($information, $filterSettings);

    /**
     * Check if the type allows children.
     *
     * @return bool
     */
    public function isNestedType();

    /**
     * Return the maximum amount of children that can be added to this setting (only valid when isNestedType() == true).
     *
     * @return int|null
     */
    public function getMaxChildren();

    /**
     * Retrieve the list of known attribute types.
     *
     * @return string[] The list of attribute names or null if no attributes are allowed.
     */
    public function getKnownAttributeTypes();

    /**
     * Retrieve the list of known attribute types.
     *
     * @param string $typeName The attribute type name.
     *
     * @return IFilterSettingTypeFactory
     */
    public function addKnownAttributeType($typeName);
}
