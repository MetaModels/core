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
			if ($arrMyFilterUrl[$objAttribute->getColName()])
			{
				$objFilterRule = new MetaModelFilterRuleSearchAttribute($objAttribute, $arrMyFilterUrl[$objAttribute->getColName()], $this->getMetaModel()->getAvailableLanguages());
				if ($objFilterRule)
				{
					$objFilter->addFilterRule($objFilterRule);
					return;
				}
			}

			//we found an attribute but no match in URL. So ignore this filtersetting if allow_empty is set
			if ($this->get('allow_empty'))
			{
				$objFilter->addFilterRule(new MetaModelFilterRuleStaticIdList(NULL));
				return;
			}
		}
		// either no attribute found or no match in url, do not return anyting.
		$objFilter->addFilterRule(new MetaModelFilterRuleStaticIdList(array()));
	}

	public function generateFilterUrlFrom(IMetaModelItem $objItem, IMetaModelRenderSettings $objRenderSetting)
	{
		$objAttribute = $this->getMetaModel()->getAttributeById($this->get('attr_id'));
		if ($objAttribute)
		{
			// TODO: shall we omit returning of empty values?
			$arrResult = $objItem->parseAttribute($objAttribute->getColName(), 'text', $objRenderSetting);
			return array(($this->get('urlparam')?$this->get('urlparam'):$objAttribute->getColName()) => urlencode($arrResult['text']));
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getParameters()
	{
		if ($this->get('urlparam'))
		{
			return $this->get('urlparam');
		} else {
			$objAttribute = $this->getMetaModel()->getAttributeById($this->get('attr_id'));
			return $objAttribute->getColName();
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getParameterDCA()
	{
		$objAttribute = $this->getMetaModel()->getAttributeById($this->get('attr_id'));
		$arrOptions = $objAttribute->getFilterOptions();

		$strParamName = $this->get('urlparam') ? $this->get('urlparam') : $objAttribute->getColName();

		return array(
			$strParamName => array
			(
				'label'   => sprintf('Filter value for attribute %s', $objAttribute->getName()),
				'inputType'    => 'select',
				'options' => $arrOptions,
			)
		);
	}
}

?>