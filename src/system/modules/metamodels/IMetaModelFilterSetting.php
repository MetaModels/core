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
 * This interface handles the abstraction for a single filter setting.
 *
 * @package	   MetaModels
 * @subpackage Interfaces
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
interface IMetaModelFilterSetting
{

	/**
	 * Tells the filter setting to add all of its rules to the passed filter object.
	 *
	 * The filter rules can evaluate the also passed filter url.
	 *
	 * A filter url hereby is a simple hash of name => value layout, it may eventually be interpreted
	 * by attributes via IMetaModelAttribute::searchFor() method.
	 *
	 * @param IMetaModelFilter $objFilter    The filter to append the rules to.
	 *
	 * @param string[string]   $arrFilterUrl The parameters to evaluate.
	 * @todo: we might want to change the name $arrFilterUrl to $arrFilterParams or something like that.
	 *
	 * @return void
	 */
	public function prepareRules(IMetaModelFilter $objFilter, $arrFilterUrl);

	/**
	 * Generate all URL parameters understood/required by this filter setting.
	 *
	 * This method is being called when a frontend "jumpTo" URL is being generated and the
	 * parameters have to be fetched.
	 *
	 * @param IMetaModelItem           $objItem          The item to fetch the values from.
	 *
	 * @param IMetaModelRenderSettings $objRenderSetting The render setting to be applied.
	 *
	 * @return array An array containing all the URL parameters needed by this filter setting.
	 */
	public function generateFilterUrlFrom(IMetaModelItem $objItem, IMetaModelRenderSettings $objRenderSetting);


	/**
	 * Retrieve a list of all registered parameters from the setting.
	 *
	 * @return array
	 */
	public function getParameters();

	/**
	 * Retrieve a list of all registered parameters from the setting as DCA compatible arrays.
	 *
	 * These parameters may be overridden by modules and content elements and the like.
	 *
	 * @return array
	 */
	public function getParameterDCA();

	/**
	 * Retrieve the names of all parameters for listing in frontend filter configuration.
	 *
	 * @return string[string] the parameters as array. parametername => label
	 */
	public function getParameterFilterNames();

	/**
	 * Retrieve a list of filter widgets for all registered parameters as form field arrays.
	 *
	 * @param array $arrIds        The ids matching the current filter values.
	 *
	 * @param array $arrFilterUrl  The current filter url.
	 *
	 * @param array $arrJumpTo     The jumpTo page (array, row data from tl_page).
	 *
	 * @param bool  $blnAutoSubmit Tells wheter the filter shall perform auto submitting or not.
	 * 
	 * @param bool $$blnHideClearFilter Tells whether the filter shall hide the "no Filter" value
	 *
	 * @return array
	 */
	public function getParameterFilterWidgets($arrIds, $arrFilterUrl, $arrJumpTo, $blnAutoSubmit, $blnHideClearFilter);
}

