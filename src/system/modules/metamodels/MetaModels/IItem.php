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
 * Interface for a MetaModel item.
 *
 * @package    MetaModels
 * @subpackage Interface
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
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
     * @param string                                 $strOutputFormat Optional, the desired output format (default: text).
     *
     * @param \MetaModels\Render\Setting\ICollection $objSettings     The render settings to use optional (default: null).
     *
     * @return array attribute name => format => value
     */
    public function parseValue($strOutputFormat = 'text', $objSettings = null);

    /**
     * Renders a single attribute in the given output format.
     *
     * For further information {@see IMetaModelAttribute::parseValue()}.
     *
     * @param string                                 $strAttributeName The desired attribute.
     *
     * @param string                                 $strOutputFormat  Optional, the desired output format (default: text).
     *
     * @param \MetaModels\Render\Setting\ICollection $objSettings      The render settings to use optional (default: null).
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

