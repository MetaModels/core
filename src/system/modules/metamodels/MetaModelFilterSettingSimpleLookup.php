<?php

class MetaModelFilterSettingSimpleLookup extends MetaModelFilterSetting
{
	public function prepareRules(IMetaModelFilter $objFilter, $arrFilterUrl)
	{
		$objAttribute = $this->getMetaModel()->getAttributeById($this->get('attr_id'));
		if ($objAttribute)
		{
			$arrMyFilterUrl = array_slice($arrFilterUrl, 0);
			if ($this->get('urlparam') && $arrFilterUrl[$this->get('urlparam')])
			{
				$arrMyFilterUrl[$objAttribute->getColName()] = $arrFilterUrl[$this->get('urlparam')];
			}
			// call prepare now.
			$objFilterRule = $objAttribute->parseFilterUrl($arrMyFilterUrl);
			if ($objFilterRule)
			{
				$objFilter->addFilterRule($objFilterRule);
			}
		}
	}

	public function generateFilterUrlFrom(IMetaModelItem $objItem)
	{
		$objAttribute = $this->getMetaModel()->getAttributeById($this->get('attr_id'));
		if ($objAttribute)
		{
			// TODO: we need a proper filter setting here as otherwise the templates are not used.
			$arrResult = $objItem->parseAttribute($objAttribute->getColName(), 'text');
			return array(($this->get('urlparam')?$this->get('urlparam'):$objAttribute->getColName()) => urlencode($arrResult['text']));
		}
	}
}

?>