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
	 * @param IMetaModelFilter $objFilter    the filter to append the rules to.
	 *
	 * @param string[string]   $arrFilterUrl the parameters to evaluate.
	 */
	public function prepareRules(IMetaModelFilter $objFilter, $arrFilterUrl);

	/**
	 * obsolete?
	 */
	public function generateFilterUrlFrom(IMetaModelItem $objItem, IMetaModelRenderSettings $objRenderSetting);
}

?>