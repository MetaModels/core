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
 * This filter condition generates a "OR" condition from all child filter settings.
 * The generated rule will return ids that are mentioned in ANY of the child rules.
 *
 * @package	   MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class MetaModelFilterSettingConditionOr extends MetaModelFilterSettingWithChilds
{
	/**
	 * Generates the filter rules based upon the given filter url.
	 *
	 * @param IMetaModelFilter $objFilter    The filter to append the rules to.
	 *
	 * @param string[string]   $arrFilterUrl The parameters to evaluate.
	 *
	 * @return void
	 */
	public function prepareRules(IMetaModelFilter $objFilter, $arrFilterUrl)
	{
		$objFilterRule = new MetaModelFilterRuleOR($this->get('stop_after_match'));
		foreach ($this->arrChilds as $objChildSetting)
		{
			$objSubFilter = new MetaModelFilter($this->getMetaModel());
			$objChildSetting->prepareRules($objSubFilter, $arrFilterUrl);
			$objFilterRule->addChild($objSubFilter);
		}
		$objFilter->addFilterRule($objFilterRule);
	}
}

