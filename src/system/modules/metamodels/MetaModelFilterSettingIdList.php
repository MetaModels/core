<?php

class MetaModelFilterSettingIdList extends MetaModelFilterSetting
{
	public function prepareRules(IMetaModelFilter $objFilter, $arrFilterUrl)
	{
		if ($this->get('items'))
		{
			$arrItems = explode(',', (string)$this->get('items'));
		} else {
			$arrItems = array();
		}
		$objFilter->addFilterRule(new MetaModelFilterRuleStaticIdList($arrItems));
	}
}

?>