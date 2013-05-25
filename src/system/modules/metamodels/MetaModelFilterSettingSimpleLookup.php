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
 * Filter setting implementation performing a search for a value on a
 * configured attribute.
 *
 * @package	   MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
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
	 * Determine if this filter setting shall return all matches if no url param has been specified.
	 *
	 * @return bool true if all matches shall be returned, false otherwise.
	 */
	public function allowEmpty()
	{
		return (bool)$this->get('allow_empty');
	}

	/**
	 * internal helper function for descendant classes to retrieve the options.
	 *
	 */
	protected function getParameterFilterOptions($objAttribute, $arrIds)
	{
		$arrOptions = $objAttribute->getFilterOptions(
			$this->get('onlypossible') ? $arrIds : NULL,
			(bool)$this->get('onlyused')
		);

		// Remove empty values.
		foreach ($arrOptions as $mixOptionKey => $mixOptions)
		{
			// Remove html/php tags.
			$mixOptions = strip_tags($mixOptions);
			$mixOptions = trim($mixOptions);

			if($mixOptions === '' ||$mixOptions === null)
			{
				unset($arrOptions[$mixOptionKey]);
			}
		}

		return $arrOptions;
	}

	/**
	 * Determine if this filter setting shall be available for frontend filter widget generating.
	 *
	 * @return bool true if available, false otherwise.
	 */
	public function enableFEFilterWidget()
	{
		// TODO: better use a seperate checkbox or the like? For the moment, this has to be overridden by sub classes.
		return (bool)$this->get('predef_param');
	}

	//////////////////////////////////////////////////////////////////////////////
	// IMetaModelFilterSetting
	//////////////////////////////////////////////////////////////////////////////

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

			if (!$arrFilterValue && $this->get('defaultid'))
			{
				$arrFilterValue = $this->get('defaultid');
			}

			if ($arrFilterValue)
			{
				$arrLanguages = ($objMetaModel->isTranslated() && $this->get('all_langs')) ? $objMetaModel->getAvailableLanguages() : array($objMetaModel->getActiveLanguage());
				$objFilterRule = new MetaModelFilterRuleSearchAttribute($objAttribute, $arrFilterValue, $arrLanguages);
				$objFilter->addFilterRule($objFilterRule);
				return;
			}

			//we found an attribute but no match in URL. So ignore this filtersetting if allow_empty is set
			if ($this->allowEmpty())
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
			return array($this->getParamName() => urlencode($arrResult['text']));
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
		$arrOptions = $objAttribute->getFilterOptions(NULL, true);

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

	public function getParameterFilterNames()
	{
		if ($strParamName = $this->getParamName())
		{
			return array(
				$strParamName => ($this->get('label') ? $this->get('label') : $this->getMetaModel()->getAttributeById($this->get('attr_id'))->getName())
			);
		} else {
			return array();
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getParameterFilterWidgets($arrIds, $arrFilterUrl, $arrJumpTo, $blnAutoSubmit, $blnHideClearFilter)
	{
		// if defined as static, return nothing as not to be manipulated via editors.
		if (!$this->enableFEFilterWidget())
		{
			return array();
		}

		$objAttribute = $this->getMetaModel()->getAttributeById($this->get('attr_id'));

		$GLOBALS['MM_FILTER_PARAMS'][] = $this->getParamName();

		$arrWidget = array(
				'label'     => array(
					// TODO: make this multilingual.
					($this->get('label') ? $this->get('label') : $objAttribute->getName()),
					'GET: ' . $this->getParamName()
					),
				'inputType'    => 'select',
				'options' => $this->getParameterFilterOptions($objAttribute, $arrIds),
				'eval' => array(
					'includeBlankOption' => ($this->get('blankoption') && !$blnHideClearFilter ? true : false),
					'blankOptionLabel'   => &$GLOBALS['TL_LANG']['metamodels_frontendfilter']['do_not_filter'],
					'colname'            => $objAttribute->getColname(),
					'urlparam'           => $this->getParamName(),
					'onlyused'           => $this->get('onlyused'),
					'onlypossible'       => $this->get('onlypossible'),
					'template'           => $this->get('template'),
				)
		);

		return array
		(
			$this->getParamName() => $this->prepareFrontendFilterWidget($arrWidget, $arrFilterUrl, $arrJumpTo, $blnAutoSubmit)
		);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getReferencedAttributes()
	{
		if (!($this->get('attr_id') && ($objAttribute = $this->getMetaModel()->getAttributeById($this->get('attr_id')))))
		{
			return array();
		}
		return array($objAttribute->getColName());
	}
}

