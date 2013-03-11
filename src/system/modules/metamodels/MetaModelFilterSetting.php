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
 * Base class for filter setting implementation.
 *
 * @package	   MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
abstract class MetaModelFilterSetting implements IMetaModelFilterSetting
{
	/**
	 * The parenting filter setting container this setting belongs to.
	 *
	 * @var IMetaModelFilterSettings
	 */
	protected $objFilterSettings = null;

	protected $arrData = array();

	public function __construct($objFilterSetting, $arrData)
	{
		$this->objFilterSetting = $objFilterSetting;
		$this->arrData = $arrData;
	}

	public function get($strKey)
	{
		return $this->arrData[$strKey];
	}

	protected function getFilterSettings()
	{
		return $this->objFilterSetting;
	}

	/**
	 * Return the MetaModel instance this filter setting relates to.
	 *
	 * @return IMetaModel
	 */
	protected function getMetaModel()
	{
		return $this->objFilterSetting->getMetaModel();
	}

	/**
	 * Returns if the given value is currently active in the given filter settings.
	 *
	 * @param array  $arrWidget    the widget information.
	 *
	 * @param array  $arrFilterUrl the filter url parameters to use.
	 *
	 * @param string $strKeyOption the option value to determine.
	 *
	 * @return bool  true if the given value is mentioned in the given filter parameters, false otherwise.
	 *
	 */
	protected function isActiveFrontendFilterValue($arrWidget, $arrFilterUrl, $strKeyOption)
	{
		return $arrFilterUrl[$arrWidget['eval']['urlparam']] == $strKeyOption ? true : false;
	}

	/**
	 * Translate an option to a proper url value to be used in the filter url.
	 * Overriding this method allows to toggle the value in the url in addition to extract
	 * or inject a value into an "combined" filter url parameter (like tags i.e.)
	 *
	 * @param array  $arrWidget    the widget information.
	 *
	 * @param array  $arrFilterUrl the filter url parameters to use.
	 *
	 * @param string $strKeyOption the option value to determine.
	 *
	 * @return string the filter url value to use for link gererating.
	 */
	protected function getFrontendFilterValue($arrWidget, $arrFilterUrl, $strKeyOption)
	{
		// toggle if active.
		if ($this->isActiveFrontendFilterValue($arrWidget, $arrFilterUrl, $strKeyOption))
		{
			return '';
		} else {
			return $strKeyOption;
		}
	}

	/**
	 * Generate the options for the frontend widget as the frontend templates expect them.
	 *
	 * The returning array will be made of option arrays containing the following fields:
	 * * key    The option value as raw key from the options array in the given widget information.
	 * * value  The value to show as option label.
	 * * href   The URL to use to activate this value in the filter.
	 * * active Boolean determining if this value is the current active option in the widget.
	 * * class  The CSS class to use. Contains active if the option is active or is empty otherwise.
	 *
	 *
	 * @param array  $arrFilterUrl the filter url parameters to use.
	 *
	 * @param string $strKeyOption the option value to determine.
	 *
	 * @return array the filter option values to use in the mm_filteritem_* templates.
	 */
	protected function prepareFrontendFilterOptions($arrWidget, $arrFilterUrl, $arrJumpTo, $blnAutoSubmit)
	{
		$arrOptions = array();
		if(!$arrWidget['options'])
		{
			return $arrOptions;
		}
		$objController = MetaModelController::getInstance();

		$strFilterAction = '';

		$blnFound = false;

		// create base url containing for preserving the current filter on unrelated widgets and modules.
		// The URL parameter concerning us will be masked via %s to be used later on in a sprintf().
		foreach($arrFilterUrl as $strKeyOption=>$strOption)
		{
			// skip the magic "language" parameter.
			if (($strKeyOption == 'language') && $GLOBALS['TL_CONFIG']['addLanguageToUrl'])
			{
				continue;
			}

			if($strKeyOption != $arrWidget['eval']['urlparam'])
			{
				if (!empty($arrFilterUrl[$strKeyOption]))
				{
					$strValue = is_array($arrFilterUrl[$strKeyOption]) ? implode(',', array_filter($arrFilterUrl[$strKeyOption])) : $arrFilterUrl[$strKeyOption];
					$strFilterAction .= '/'.$strKeyOption.'/'. str_replace('%', '%%', urlencode($strValue));
				}
			} else {
				$strFilterAction .= '%s';
				$blnFound = true;
			}
		}

		// If we have not found our parameter in the URL, we add it as %s now to be able to populate it via sprintf() below.
		if (!$blnFound)
		{
			$strFilterAction .= '%s';
		}

		// If no jumpTo-page has been provided, we use the current page.
		if (!$arrJumpTo)
		{
			$arrJumpTo = $GLOBALS['objPage']->row();
		}

		if ($arrWidget['eval']['includeBlankOption'])
		{
			$blnActive = $this->isActiveFrontendFilterValue($arrWidget, $arrFilterUrl, '');

			$arrOptions[] = array
			(
				'key'    => '',
				'value'  => ($arrWidget['eval']['blankOptionLabel'] ? $arrWidget['eval']['blankOptionLabel'] : $GLOBALS['TL_LANG']['metamodels_frontendfilter']['do_not_filter']),
				'href'   => $objController->generateFrontendUrl($arrJumpTo, sprintf($strFilterAction, '')),
				'active' => $blnActive,
				'class'  => 'doNotFilter'.($blnActive ? ' active' : ''),
			);
		}

		foreach ($arrWidget['options'] as $strKeyOption=>$strOption)
		{
			$strValue = urlencode($this->getFrontendFilterValue($arrWidget, $arrFilterUrl, $strKeyOption));
			$blnActive = $this->isActiveFrontendFilterValue($arrWidget, $arrFilterUrl, $strKeyOption);

			$arrOptions[] = array
			(
				'key'    => $strKeyOption,
				'value'  => $strOption,
				'href'   => $objController->generateFrontendUrl($arrJumpTo, sprintf($strFilterAction, $strValue ? ('/'.$arrWidget['eval']['urlparam'].'/'.$strValue) : '')),
				'active' => $blnActive,
				'class'  => $strKeyOption.($blnActive ? ' active' : '')
				);
		}
		return $arrOptions;
	}

	protected function prepareFrontendFilterWidget($arrWidget, $arrFilterUrl, $arrJumpTo, $blnAutoSubmit)
	{
		$strClass = $GLOBALS['TL_FFL'][$arrWidget['inputType']];

		// no widget? no output! that's it.
		if (!$strClass)
		{
			return array();
		}

		// determine current value
		$arrWidget['value'] = $arrFilterUrl[$arrWidget['eval']['urlparam']];

		$arrData = MetaModelController::getInstance()->prepareForWidget($arrWidget, $arrWidget['eval']['urlparam'], $arrWidget['value']);

		if ($blnAutoSubmit && TL_MODE == 'FE')
		{
			$GLOBALS['TL_JAVASCRIPT']['metamodels'] = 'system/modules/metamodels/html/metamodels.js';
		}

		$objWidget = new $strClass($arrData);

		$strField = $objWidget->generate();

		return array
		(
			'class'      => sprintf(
				'mm_%s %s%s%s',
				$arrWidget['inputType'],
				$arrWidget['eval']['urlparam'],
				(($arrWidget['value']!==NULL) ? ' used':' unused'),
				($blnAutoSubmit ? ' submitonchange' : '')
			),
			'label'      => $objWidget->generateLabel(),
			'formfield'  => $strField,
			'raw'        => $arrWidget,
			'urlparam'   => $arrWidget['eval']['urlparam'],
			'options'    => $this->prepareFrontendFilterOptions($arrWidget, $arrFilterUrl, $arrJumpTo, $blnAutoSubmit),
			'autosubmit' => $blnAutoSubmit,
			'urlvalue'   => array_key_exists('urlvalue', $arrWidget) ? $arrWidget['urlvalue'] : $arrWidget['value']
		);
	}

	//////////////////////////////////////////////////////////////////////////////
	// IMetaModelFilterSetting
	//////////////////////////////////////////////////////////////////////////////

	public function generateFilterUrlFrom(IMetaModelItem $objItem, IMetaModelRenderSettings $objRenderSetting)
	{
		return array();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getParameters()
	{
		return array();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getParameterDCA()
	{
		return array();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getParameterFilterNames()
	{
		return array();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getParameterFilterWidgets($arrIds, $arrFilterUrl, $arrJumpTo, $blnAutoSubmit)
	{
		return array();
	}
}

