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
	 * @param mixed[int] $arrValues the values to be stored into database.
	 * 
	 * @return void
	 */
	public function setDataFor($arrValues);

	/**
	 * This parses the given URL and compiles a proper filter that can later be used in
	 * the {@link IMetaModelAttribute::getIdsFromFilter()} method.
	 * 
	 * If an filter is empty, return null as value and the MetaModel will ignore the filter condition.
	 * 
	 * @param array $arrUrlParams the url params as key => value pairs.
	 * 
	 * @return mixed|null a filter setting that can be understood by this fieldtype.
	 */
	public function parseFilterUrl($arrUrlParams);

	/**
	 * This method retrieves the ids of all items that match the given filter.
	 * The MetaModel will call {@link IMetaModelAttribute::parseFilterUrl())} when filtering by URL
	 * and feed this function with the result.
	 * 
	 * If no entries have been found, the result is an empty array.
	 * If no filtering was applied and therefore all ids shall be reported as valid, the return value of NULL is allowed.
	 * 
	 * @param array $arrFilter mapping of a filter retrieved by calling the {@link IMetaModelAttribute::parseFilterUrl())} filters.
	 * 
	 * @return int[]|null all matching ids from the MetaModel according to the given filter or null if no filtering is applied.
	 */
	public function getIdsFromFilter($arrFilter);

	/**
	 * Sorts the given array list by field value in the given direction.
	 * 
	 * @param int[]  $arrIds       a list of Ids from the MetaModel table.
	 * 
	 * @param string $strDirection the direction for sorting. either 'ASC' or 'DESC', as in plain SQL.
	 * 
	 * @return int[] the sorted integer array.
	 */
	public function sortIds($arrIds, $strDirection);
}

?>