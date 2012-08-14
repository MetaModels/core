<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package	   MetaModels
 * @subpackage Interfaces
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
 * Interface for "complex" MeatModel attributes.
 * Complex attributes are attributes that can not be fetched with a simple "SELECT colName FROM cat_table" and therefore need
 * to be handled differently.
 * 
 * @package	   MetaModels
 * @subpackage Interfaces
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
interface IMetaModelAttributeComplex extends IMetaModelAttribute
{

	/**
	 * This method is called to retrieve the data for certain items from the database.
	 * 
	 * @param int[] $arrIds the ids of the items to retrieve.
	 * 
	 * @return mixed[] the nature of the resulting array is a mapping from id => "native data" where
	 *                 the definition of "native data" is only of relevance to the given item.
	 */
	public function getDataFor($arrIds);

	/**
	 * This method is called to store the data for certain items to the database.
	 * 
	 * @param mixed[int] $arrValues the values to be stored into database. Mapping is item id=>value
	 * 
	 * @return void
	 */
	public function setDataFor($arrValues);

	/**
	 * Remove values for items.
	 */
//	 public function unsetDataFor($arrIds);
}

?>