<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package	   MetaModels
 * @subpackage Interface
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  CyberSpectrum
 * @license    private
 * @filesource
 */
if (!defined('TL_ROOT'))
{
	die('You cannot access this file directly!');
}

/**
 * Interface for a MetaModel item.
 *
 * @package	   MetaModels
 * @subpackage Interface
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
interface IMetaModelItem
{
	/**
	 * return the native value of an attibute
	 *
	 * @param string $strAttributeName the name of the attribute
	 *
	 * @return mixed
	 */
	public function get($strAttributeName);

	/**
	 * set the native value of an attibute
	 *
	 * @param string $strAttributeName the name of the attribute
	 *
	 * @param mixed $varValue the value of the attribute
	 */
	public function set($strAttributeName, $varValue);

	/**
	 * fetch the meta model that repesents this item.
	 *
	 * @return IMetaModel the instance.
	 */
	public function getMetaModel();

	/**
	 * fetch the meta model attribute instance with the given name.
	 *
	 * @param string $strAttributeName the name of the attribute.
	 *
	 * @return IMetaModelAttribute the instance.
	 */
	public function getAttribute($strAttributeName);

	/**
	 * determines if this item is a variant of another item.
	 *
	 * @return bool true if it is an variant, false otherwise
	 */
	public function isVariant();

	/**
	 * determines if this item is variant base of other items.
	 * Note: this does not mean that there actually exist variants of
	 * this item. It merely simply states, that this item is able
	 * to function as variant base for other items.
	 *
	 * @return bool true if it is an variant base, false otherwise
	 */
	public function isVariantBase();

	/**
	 * fetch the meta model variants for this item.
	 *
	 * @param IMetaModelFilter $objFilter the filter settings to be applied.
	 *
	 * @return IMetaModelItems a list of all variants for this item.
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
	 * @see IMetaModelAttribute::parseValue() for further information.
	 *
	 * @param string                   $strOutputFormat optional, the desired output format (default: text).
	 *
	 * @param IMetaModelRenderSettings $objSettings The render settings to use optional (default: null).
	 *
	 * @return array attribute name => format => value
	 *
	 */
	public function parseValue($strOutputFormat = 'text', $objSettings = NULL);

	/**
	 * Renders a single attribute in the given output format.
	 * @see IMetaModelAttribute::parseValue() for further information.
	 *
	 * @param string                   $strAttributeName the desired attribute.
	 *
	 * @param string                   $strOutputFormat  optional, the desired output format (default: text).
	 *
	 * @param IMetaModelRenderSettings $objSettings      The render settings to use optional (default: null).
	 *
	 * @return array format=>value
	 */
	public function parseAttribute($strAttributeName, $strOutputFormat = 'text', $objSettings = NULL);

	/**
	 * Returns a new item containing the same values as this item but no id.
	 *
	 * This is useful when creating new items that shall be based upon anothe item
	 *
	 * @return IMetaModelItem the new copy.
	 */
	public function copy();

	/**
	 * Returns a new item containing the same values as this item but no id.
	 * Additionally, the item will be a variant child of this item.
	 *
	 * NOTE: if this item is not a variant base itself, this item will return a item
	 * that is a child of this items variant base. i.e. excact clone.
	 *
	 * @return IMetaModelItem the new copy.
	 */
	public function varCopy();
}

?>