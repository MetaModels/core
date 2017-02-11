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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  2012-2015 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels;

use MetaModels\Render\Setting\ICollection;

/**
 * Interface for a MetaModel item.
 */
interface IItem
{
    /**
     * Return the native value of an attribute.
     *
     * @param string $strAttributeName The name of the attribute.
     *
     * @return mixed
     */
    public function get($strAttributeName);

    /**
     * Set the native value of an attribute.
     *
     * @param string $strAttributeName The name of the attribute.
     *
     * @param mixed  $varValue         The value of the attribute.
     *
     * @return \MetaModels\IItem
     */
    public function set($strAttributeName, $varValue);

    /**
     * Fetch the meta model that represents this item.
     *
     * @return \MetaModels\IMetaModel The instance.
     */
    public function getMetaModel();

    /**
     * Fetch the meta model attribute instance with the given name.
     *
     * @param string $strAttributeName The name of the attribute.
     *
     * @return \MetaModels\Attribute\IAttribute The instance.
     */
    public function getAttribute($strAttributeName);

    /**
     * Check if the given attribute is set. This mean if in the data array
     * is the filed set or not. If the attribute is not loaded the function
     * will return false.
     *
     * @param string $strAttributeName The desired attribute.
     *
     * @return bool True means the data is set, on load of the item or at any time.
     *              False means the attribute is not set.
     */
    public function isAttributeSet($strAttributeName);

    /**
     * Return a list of the col names from the attributes which are set.
     * Including all meta fields as well.
     *
     * @return array
     */
    public function getSetAttributes();

    /**
     * Determines if this item is a variant of another item.
     *
     * @return bool True if it is an variant, false otherwise
     */
    public function isVariant();

    /**
     * Determines if this item is variant base of other items.
     *
     * Note: this does not mean that there actually exist variants of
     * this item. It merely simply states, that this item is able
     * to function as variant base for other items.
     *
     * @return bool true if it is an variant base, false otherwise.
     */
    public function isVariantBase();

    /**
     * Fetch the meta model variants for this item.
     *
     * @param \MetaModels\Filter\IFilter $objFilter The filter settings to be applied.
     *
     * @return \MetaModels\IItems A list of all variants for this item.
     */
    public function getVariants($objFilter);

    /**
     * Fetch the meta model variant base for this item. For a non-variant item the variant base is the item itself.
     *
     * @return \MetaModels\IItem The variant base.
     */
    public function getVariantBase();

    /**
     * Save the current data for every attribute to the data sink.
     *
     * @return void
     */
    public function save();

    /**
     * Renders the item in the given output format.
     *
     * For further information {@see IMetaModelAttribute::parseValue()}.
     *
     * @param string      $strOutputFormat Optional, the desired output format (default: text).
     *
     * @param ICollection $objSettings     The render settings to use optional (default: null).
     *
     * @return array attribute name => format => value
     */
    public function parseValue($strOutputFormat = 'text', $objSettings = null);

    /**
     * Renders a single attribute in the given output format.
     *
     * For further information {@see IMetaModelAttribute::parseValue()}.
     *
     * @param string      $strAttributeName The desired attribute.
     *
     * @param string      $strOutputFormat  Optional, the desired output format (default: text).
     *
     * @param ICollection $objSettings      The render settings to use optional (default: null).
     *
     * @return array format=>value
     */
    public function parseAttribute($strAttributeName, $strOutputFormat = 'text', $objSettings = null);

    /**
     * Returns a new item containing the same values as this item but no id.
     *
     * This is useful when creating new items that shall be based upon another item
     *
     * @return \MetaModels\IItem the new copy.
     */
    public function copy();

    /**
     * Returns a new item containing the same values as this item but no id.
     *
     * Additionally, the item will be a variant child of this item.
     *
     * NOTE: if this item is not a variant base itself, this item will return a item
     * that is a child of this items variant base. i.e. exact clone.
     *
     * @return \MetaModels\IItem the new copy.
     */
    public function varCopy();
}
