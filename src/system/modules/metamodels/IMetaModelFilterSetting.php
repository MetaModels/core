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
if (!defined('TL_ROOT'))
{
	die('You cannot access this file directly!');
}

/**
 * This interface handles the abstraction for a single filter setting.
 * dunn
 * @package	   MetaModels
 * @subpackage Interfaces
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
interface IMetaModelFilterSetting
{

	/**
	 * Tells the filter setting to add all of its rules to the passed filter object.
	 * The filter rules can evaluate the also passed filter url.
	 *
	 * A filter url hereby is a simple hash of name => value layout, it may eventually be interpreted
	 * by attributes via IMetaModelAttribute::searchFor() method.
	 * @todo: we might want to change the name $arrFilterUrl to $arrFilterParams or something like that.
	 *
	 * @param IMetaModelFilter $objFilter    the filter to append the rules to.
	 *
	 * @param string[string]   $arrFilterUrl the parameters to evaluate.
	 *
	 * @return void
	 */
	public function prepareRules(IMetaModelFilter $objFilter, $arrFilterUrl);

	/**
	 * TODO: obsolete?
	 */
	public function generateFilterUrlFrom(IMetaModelItem $objItem, IMetaModelRenderSettings $objRenderSetting);


	/**
	 * Retrieve a list of all registered parameters from the setting.
	 *
	 * @return array
	 */
	public function getParameters();

	/**
	 * Retrieve a list of all registered parameters from the setting as DCA compatible arrays, these parameters may be overridden by modules and content elements and the like.
	 *
	 * @return array
	 */
	public function getParameterDCA();
}

?>