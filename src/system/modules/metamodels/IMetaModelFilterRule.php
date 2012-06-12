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
if (!defined('TL_ROOT')) {
	die('You cannot access this file directly!');
}

/**
 * This is the MetaModel filter interface.
 *
 * @package	   MetaModels
 * @subpackage Interfaces
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
interface IMetaModelFilterRule
{
	/**
	 * Fetch the ids for all matches for this filter rule.
	 * 
	 * If no entries have been found, the result is an empty array.
	 * If no filtering was applied and therefore all ids shall be reported as valid, the return value of NULL is allowed.
	 * 
	 * @return int[]|null
	 */
	public function getMatchingIds();
}

?>
