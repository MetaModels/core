<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package	   MetaModels
 * @subpackage Backend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

/**
 * This filter condition generates a "AND" condition from all child filter settings.
 * The generated rule will only return ids that are mentioned in ALL child rules.
 *
 * @package	   MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class MetaModelFilterSettingConditionAnd extends MetaModelFilterSettingWithChilds
{
	public function prepareRules(IMetaModelFilter $objFilter, $arrFilterUrl)
	{
		$objSubFilter = new MetaModelFilter($this->getMetaModel());
		foreach ($this->arrChilds as $objChildSetting)
		{
			$objChildSetting->prepareRules($objSubFilter, $arrFilterUrl);
		}
		// TODO: we might rather want to implement a new combiner rule that handles this here.
		$objFilterRule = new MetaModelFilterRuleStaticIdList($objSubFilter->getMatchingIds());
		$objFilter->addFilterRule($objFilterRule);
	}
}

?>