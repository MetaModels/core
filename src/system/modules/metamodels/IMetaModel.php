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
 * This is the main MetaModel interface.
 * 
 * This interface handles all attribute definition instantiation and can be queried for a view instance to certain entries.
 * dunn
 * @see MetaModelFactory::byId			to instantiate a MetaModel by its ID.
 * @see MetaModelFactory::byTableName	to instantiate a MetaModel by its table name.
 * @package	   MetaModels
 * @subpackage Interfaces
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
interface IMetaModel
{
	/**
	 * get a configuration setting.
	 * 
	 * @param string $strKey the key of the property that shall be fetched.
	 * 
	 * @return mixed the value provided during instantiation. This includes all attributes from the database table tl_metamodel.
	 */
	public function get($strKey);

	/**
	 * Retrieve the table name for this MetaModel.
	 * 
	 * @return string the table name for the metamodel table.
	 */
	public function getTableName();

	/**
	 * Returns the array of all attribute instances that are defined for this MetaModel instance.
	 * 
	 * @return IMetaModelAttribute[] as name=>instance
	 */
	public function getAttributes();

	/**
	 * Determines if this MetaModel instance is subject to translation.
	 * 
	 * @return bool true if the MetaModel is translated, false otherwise.
	 */
	public function isTranslated();

	/**
	 * Determines if this MetaModel instance is subject to variant handling.
	 * 
	 * @return bool true if variants are handled, false otherwise.
	 */
	public function hasVariants();

	/**
	 * Fetches all language codes that have been marked as available for translation in this MetaModel.
	 * 
	 * @return string[]|null an array containing all codes if the MetaModel is translated, null if translation is not active.
	 */
	public function getAvailableLanguages();

	/**
	 * Fetches the language code that has been marked as fallback language for translation in this MetaModel.
	 * 
	 * @return string[]|null if the MetaModel is translated an array containing all codes, null if translation is not active.
	 */
	public function getFallbackLanguage();

	/**
	 * Get the current active language.
	 * 
	 * @return string the language code.
	 */
	public function getActiveLanguage();

	/**
	 * Fetches the instance of the attribute with the given name.
	 * 
	 * @param string $strAttributeName the name of the attribute to search
	 * 
	 * @return IMetaModelAttribute the instance or null if not found.
	 */
	public function getAttribute($strAttributeName);

	/**
	 * Fetches the instance of the attribute with the given name.
	 * 
	 * @param int $intId the id of the attribute to search
	 * 
	 * @return IMetaModelAttribute the instance or null if not found.
	 */
	public function getAttributeById($intId);

	/**
	 * Search the MetaModel for the item with the given Id.
	 * 
	 * @param int $intId the Id to be searched.
	 * @return IMetaModelItem the item if found, NULL otherwise.
	 */
	public function findById($intId);

	/**
	 * Filter the MetaModel by the provided filter settings.
	 * 
	 * @param IMetaModelFilter|null $objFilter    the filter object to use or null if none.
	 * 
	 * @param string                $strSortBy    optional name of the attribute the entries shall be sorted
	 * 
	 * @param int                   $intOffset    optional offset for the first item.
	 * 
	 * @param int                   $intLimit     optional amount of items to retrieve.
	 * 
	 * @param string                $strSortOrder optional sorting direction, either 'ASC'(default) or 'DESC'.
	 * 
	 * @return IMetaModelItems the collection of IMetaModelItem instances that match the given filter.
	 */
	public function findByFilter($objFilter, $strSortBy = '', $intOffset = 0, $intLimit = 0, $strSortOrder = 'ASC');

	/**
	 * Fetch the amount of matching items against the given filter.
	 * 
	 * @param IMetaModelFilter|null $objFilter the filter object to use or null if none.
	 * 
	 * @return int the amount of matching items.
	 */
	public function getCount($objFilter);

	/**
	 * Get all variant base items, filtered by the provided filter settings.
	 * 
	 * @param IMetaModelFilter $objFilter the filter to use or null if no filtering.
	 * 
	 * @return IMetaModelItems the collection of IMetaModelItem instances that match the given filter.
	 */
	public function findVariantBase($objFilter);

	/**
	 * Get variants for the given ids, filtered by the provided filter settings.
	 * 
	 * @param array $arrIds the Ids of the base elements.
	 * 
	 * @param IMetaModelFilter $objFilter the filter to use or null if no filtering.
	 * 
	 * @return IMetaModelItems the collection of IMetaModelItem instances that match the given filter.
	 */
	public function findVariants($arrIds, $objFilter);

	/**
	 * Prepare the base filter object for this meta model.
	 * This object is produced by calling parseFlterUrl on all contained attributes with an empty filter url.
	 * This allows the attributes to generate basic filter options that must be applied globally on the MetaModel
	 * and hence allows functionality like "published"-attributes and the like.
	 * 
	 * @return IMetaModelFilter the base filter object.
	 */
	public function getBaseFilter();

	/**
	 * Generates a filter object that takes the given attributes into account.
	 * 
	 * @param array $arrAttributeNames all attributes that shall be evaluated in the filter.
	 * 
	 * @param array $arrFilterUrl      the filter url parameters (usually the contents of $_GET etc.)
	 * 
	 * @return IMetaModelFilter the generated filter object.
	 */
	public function prepareFilter($arrAttributeNames, $arrFilterUrl);
}

?>