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

class MetaModelFilterSettingConditionOr extends MetaModelFilterSettingWithChilds
{
	public function prepareRules(IMetaModelFilter $objFilter, $arrFilterUrl)
	{
		$objFilterRule = new MetaModelFilterRuleOR();
		foreach ($this->arrChilds as $objChildSetting)
		{
			$objSubFilter = new MetaModelFilter($this->getMetaModel());
			$objChildSetting->prepareRules($objSubFilter, $arrFilterUrl);
			$objFilterRule->addChild($objSubFilter);
		}
		$objFilter->addFilterRule($objFilterRule);
	}
}

?>