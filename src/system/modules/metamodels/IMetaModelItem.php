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

}

?>