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
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

/**
 * This is the main MetaModels attribute interface.
 * To create {@link MetaModelAttribute} instances, use a {@link IMetaModelAttributeFactory}
 * This interface handles all general purpose attribute management and interfacing.
 *
 * @package	   MetaModels
 * @subpackage Interfaces
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
interface IMetaModelAttribute
{

	/**
	 * Queries the attribute for it's column name within it's MetaModel.
	 *
	 * @return string the attribute's column name.
	 */
	public function getColName();

	/**
	 * Queries the attribute for it's parent MetaModel instance.
	 *
	 * @return IMetaModel the MetaModel instance.
	 */
	public function getMetaModel();

	/**
	 * Retrieve a meta information setting.
	 *
	 * @param string $strKey The meta information name that shall be retrieved.
	 *
	 * @return mixed
	 */
	public function get($strKey);

	/**
	 * Override a meta information setting.
	 *
	 * All changes to an attribute via set() are considered to be non persistent and therefore will not update any
	 * structural information or auxiliary properties that might be needed within the attribute type.
	 *
	 * For persistent updates, use {@link IMetaModelAttribute::handleMetaChange()} instead.
	 *
	 * @param string $strKey   The meta information name that shall be set.
	 *
	 * @param mixed  $varValue The value to set.
	 *
	 * @return IMetaModelAttribute Instance of this attribute, for chaining support.
	 */
	public function set($strKey, $varValue);

	/**
	 * Updates the meta information of the attribute.
	 *
	 * This tells the attribute to perform any actions that must be done to correctly initialize the new value
	 * and to perform any action to undo the changes that had been done for the previous value.
	 * i.e.: when an attribute type needs columns in an an auxiliary table, these will have to be updated herein.
	 *
	 * This method may throw an exception, when the new value is invalid or any problems appear, the MetaModelAttribute
	 * will then keep the old meta value.
	 *
	 * @param string $strMetaName Name of the meta information that shall be updated.
	 *
	 * @param mixed  $varNewValue The new value for this meta information.
	 *
	 * @return IMetaModelAttribute The instance of this attribute, to support chaining.
	 */
	public function handleMetaChange($strMetaName, $varNewValue);

	/**
	 * Delete all auxiliary data like a column in the metamodel table or references in another table etc.
	 *
	 * @return void
	 */
	public function destroyAUX();

	/**
	 * Create auxiliary data like a column in the metamodel table or references in another table etc.
	 *
	 * @return void
	 */
	public function initializeAUX();

	/**
	 * Returns all valid settings for the attribute type.
	 *
	 * @return array All valid setting names, this reensembles the columns in tl_metamodel_attribute
	 *               this attribute class understands.
	 */
	public function getAttributeSettingNames();

	/**
	 * This generates the field definition for use in a DCA.
	 *
	 * It also sets the proper language variables (if not already set per dcaconfig.php or similar).
	 * Using the optional override parameter, settings known by this attribute can be overridden for the
	 * generating of the output array.
	 *
	 * @param string[string] $arrOverrides The values to override, for a list of valid parameters, call
	 *                                     getAttributeSettingNames().
	 *
	 * @return array The DCA array to use as $GLOBALS['TL_DCA']['tablename']['fields']['attribute-name]
	 */
	public function getFieldDefinition($arrOverrides = array());

	/**
	 * This generates the field definition for use in a DCA.
	 *
	 * The result contains all relevant settings for this field in an DCA for the given table
	 * and MAY override anything like palettes, subpalettes, field definitions etc.
	 * Due to the fact that it calls getFieldDefinition() internally, the result at least contains
	 * the sub array 'fields' with the information of this field's settings.
	 *
	 * @param string[string] $arrOverrides See documentation in getFieldDefinition() method.
	 *
	 * @return array The DCA array to use as $GLOBALS['tablename']
	 *
	 * @link IMetaModelAttribute::getFieldDefinition() is used internally for generating the result.
	 */
	public function getItemDCA($arrOverrides = array());

	/**
	 * Convert native attribute value to widget value.
	 *
	 * This is used for transferring a native attribute value to a value that the widget,
	 * generated from the information obtained via {@link IMetaModelAttribute::getFieldDefinition()}
	 * can handle.
	 *
	 * @param mixed $varValue The value to be transformed.
	 *
	 * @return mixed The resulting widget compatible value
	 */
	public function valueToWidget($varValue);

	/**
	 * Convert a value obtained from a widget to a native value of this attribute.
	 *
	 * This is used for transferring a value that has been retrieved from a widget into native attribute
	 * value.
	 *
	 * @param mixed $varValue The value to be transformed.
	 *
	 * @param int   $intId    The id of the item the value belongs to.
	 *
	 * @return mixed The resulting native value
	 */
	public function widgetToValue($varValue, $intId);

	/**
	 * This method is called to store the data for certain items to the database.
	 *
	 * @param mixed[int] $arrValues The values to be stored into database. Mapping is item id=>value.
	 *
	 * @return void
	 */
	public function setDataFor($arrValues);

	/**
	 * Retrieve an instance containing the default render settings for an attribute of this type.
	 *
	 * @return object
	 */
	public function getDefaultRenderSettings();

	/**
	 * Transform a value into real data.
	 *
	 * The returned array at least transports an string in the key 'text' which SHOULD be
	 * useful when being echo'ed in a template and the raw value in the section 'raw'.
	 * Each attribute class MAY return as many other values in this array with custom keys as it wants.
	 *
	 * @param array  $arrRowData      The (native) row data from the MetaModel table.
	 *
	 * @param string $strOutputFormat The desired output format.
	 *
	 * @param object $objSettings     Custom settings to be passed to the renderer.
	 *
	 * @return array An array with all the converted data.
	 */
	public function parseValue($arrRowData, $strOutputFormat = 'text', $objSettings = null);

	/**
	 * Sorts the given array list by field value in the given direction.
	 *
	 * @param int[]  $arrIds       A list of Ids from the MetaModel table.
	 *
	 * @param string $strDirection The direction for sorting. either 'ASC' or 'DESC', as in plain SQL.
	 *
	 * @return int[] The sorted integer array.
	 */
	public function sortIds($arrIds, $strDirection);

	/**
	 * Retrieve the filter options of this attribute.
	 *
	 * Retrieve values for use in filter options, that will be understood by DC_ filter
	 * panels and frontend filter select boxes.
	 * One can influence the amound of returned entries with the two parameters.
	 * For the id list, the value "null" represents (as everywhere in MetaModels) all entries.
	 * An empty array will return no entries at all.
	 * The parameter "used only" determines, if only really attached values shall be returned.
	 * This is only relevant, when using "null" as id list for attributes that have preconfigured
	 * values like select lists and tags i.e.
	 *
	 * @param array $arrIds   The ids of items that the values shall be fetched from.
	 *
	 * @param bool  $usedOnly Determines if only "used" values shall be returned.
	 *
	 * @return array All options matching the given conditions as name => value.
	 */
	public function getFilterOptions($arrIds, $usedOnly);

	/**
	 * Search all items that match the given expression.
	 *
	 * The standard wildcards * (many characters) and ? (a single character) are supported.
	 *
	 * @param string $strPattern The text to search for. This may contain wildcards.
	 *
	 * @return int[] the ids of matching items.
	 */
	public function searchFor($strPattern);

	/**
	 * Called by the MetaModel after an item has been saved.
	 *
	 * Useful for alias fields, edit counters etc.
	 *
	 * @param IMetaModelItem $objItem The item that has just been saved.
	 *
	 * @return void
	 */
	public function modelSaved($objItem);
}

