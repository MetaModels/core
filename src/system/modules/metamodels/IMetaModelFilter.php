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
 * This is the MetaModel filter interface.
 *
 * @package	   MetaModels
 * @subpackage Interfaces
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
interface IMetaModelFilter
{
	/**
	 * Adds a filter rule to this filter chain.
	 *
	 * @param IMetaModelFilterRule $objFilterRule the filter rule to add.
	 */
	public function addFilterRule(IMetaModelFilterRule $objFilterRule);

	/**
	 * Narrow down the list of Ids that match the given filter.
	 *
	 * @return int[]|null all matching Ids or null if all ids did match.
	 */
	public function getMatchingIds();
}

?>
