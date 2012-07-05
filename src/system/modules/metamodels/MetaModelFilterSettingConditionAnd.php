<?php

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