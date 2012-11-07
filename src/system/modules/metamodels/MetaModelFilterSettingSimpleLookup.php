<?php

class MetaModelFilterSettingSimpleLookup extends MetaModelFilterSetting
{
	/**
	 * {@inheritdoc}
	 */
	protected function getParamName()
	{
		if ($this->get('urlparam'))
		{
			return $this->get('urlparam');
		}

		$objAttribute = $this->getMetaModel()->getAttributeById($this->get('attr_id'));
		if ($objAttribute)
		{
			return $objAttribute->getColName();
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function prepareRules(IMetaModelFilter $objFilter, $arrFilterUrl)
	{
		$objMetaModel = $this->getMetaModel();
		$objAttribute = $objMetaModel->getAttributeById($this->get('attr_id'));
		$strParam = $this->getParamName();
		if ($objAttribute && $strParam)
		{
			$arrFilterValue = $arrFilterUrl[$strParam];
			if ($arrFilterValue)
			{
				$arrLanguages = ($objMetaModel->isTranslated() && $this->get('all_langs')) ? $objMetaModel->getAvailableLanguages() : array($objMetaModel->getActiveLanguage());
				$objFilterRule = new MetaModelFilterRuleSearchAttribute($objAttribute, $arrFilterValue, $arrLanguages);
				$objFilter->addFilterRule($objFilterRule);
				return;
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

	/**
	 * {@inheritdoc}
	 */
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
		return ($strParamName = $this->getParamName()) ? array($strParamName) : array();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getParameterDCA()
	{
		// if defined as static, return nothing as not to be manipulated via editors.
		if (!$this->get('predef_param'))
		{
			return array();
		}

		$objAttribute = $this->getMetaModel()->getAttributeById($this->get('attr_id'));
		$arrOptions = $objAttribute->getFilterOptions();

		return array(
			$this->getParamName() => array
			(
				'label'   => array(
					sprintf($GLOBALS['TL_LANG']['MSC']['metamodel_filtersettings_parameter']['simplelookup'][0], $objAttribute->getName()),
					sprintf($GLOBALS['TL_LANG']['MSC']['metamodel_filtersettings_parameter']['simplelookup'][1], $objAttribute->getName())
				),
				'inputType'    => 'select',
				'options' => $arrOptions,
				'eval' => array('includeBlankOption' => true, 'style' => 'min-width:450px;margin-bottom:16px;margin-right:10px;')
			)
		);
	}
}

?>