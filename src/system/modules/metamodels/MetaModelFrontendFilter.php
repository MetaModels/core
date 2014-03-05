<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package    MetaModels
 * @subpackage FrontendFilter
 * @author     Christian de la Haye <service@delahaye.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

/**
 * FE-filtering for Contao MetaModels
 *
 * @package	   MetaModels
 * @subpackage FrontendFilter
 * @author     Christian de la Haye <service@delahaye.de>
 */
class MetaModelFrontendFilter extends Frontend
{

	/**
	 * Filter config
	 */
	protected $objFilterConfig;

	protected $arrFilters = array();

	protected $formId = 'mm_filter_';

	/**
	 * parameters to reset by the filter
	 */
	protected $arrResetKeys = array('FORM_SUBMIT');

	/**
	 * parameters to link thru the filter
	 */
	protected $arrPreserveKeys = array();

	protected $arrPreserveParams = array();


	/**
	 * Configure the filter module
	 */
	public function getMetaModelFrontendFilter($objFilterConfig)
	{
		$this->objFilterConfig = $objFilterConfig;

		$this->objFilterConfig->arrJumpTo = $GLOBALS['objPage']->row();

		if ($this->objFilterConfig->metamodel_jumpTo)
		{
			// page to jump to when filter submit
			$objPage = $this->Database->prepare("SELECT id, alias FROM tl_page WHERE id=?")
				->limit(1)
				->execute($this->objFilterConfig->metamodel_jumpTo);
			if ($objPage->numRows)
			{
				$this->objFilterConfig->arrJumpTo = $objPage->row();
			}
		}

		$this->formId .= $this->objFilterConfig->id;
		return $this->getFilters();
	}

	/**
	 * generate an url determined by the given params and configured jumpTo page.
	 *
	 * @param array $arrParams the URL parameters to use.
	 *
	 * @return string the generated URL.
	 *
	 */
	protected function getJumpToUrl($arrParams)
	{
		$strFilterAction = '';
		foreach ($arrParams as $strName => $varParam)
		{
			// skip the magic "language" parameter.
			if (($strName == 'language') && $GLOBALS['TL_CONFIG']['addLanguageToUrl'])
			{
				continue;
			}

			$strValue = $varParam;

			if (is_array($varParam))
			{
				$strValue = implode(',', array_filter($varParam));
			}

			$strValue = str_replace(array('/', '\''), array('-slash-', '-apos-'), $strValue);

			if (strlen($strValue))
			{
				// Shift auto_item to the front.
				if ($strName == 'auto_item')
				{
					$strFilterAction = '/' . $strValue . $strFilterAction;
					continue;
				}

				$strFilterAction .= sprintf(($GLOBALS['TL_CONFIG']['disableAlias'] ? '&amp;%s=%s' : '/%s/%s'), $strName, urlencode($strValue));
			}
		}
		return $strFilterAction;
	}

	/**
	 * Redirect the browser to the url determined by the given params (configured jumpTo page will get used).
	 *
	 * This will exit the script!
	 *
	 * @param array $arrParams the URL parameters to use.
	 *
	 */
	protected function redirectPost($arrParams)
	{
		// now translate all params to a valid url and redirect us to it.
		$this->redirect($this->Environment->base . $this->generateFrontendUrl($this->objFilterConfig->arrJumpTo, $this->getJumpToUrl($arrParams)));
	}

	protected function getWantedNames()
	{
		return (array)unserialize($this->objFilterConfig->metamodel_fef_params);
	}

	protected function getParams()
	{
		$arrWantedParam = $this->getWantedNames();

		$arrMyParams = $arrOtherParams = array();

		if ($_GET)
		{
			foreach (array_keys($_GET) as $strParam)
			{
				if(in_array($strParam, $arrWantedParam))
				{
					$arrMyParams[$strParam] = $this->Input->get($strParam);
				}
				// add only to the array if param is not page
				elseif($strParam != 'page')
				{
					$arrOtherParams[$strParam] = $this->Input->get($strParam);
				}
			}
		}

		// if POST, translate to proper GET url
		if ($_POST && ($this->Input->post('FORM_SUBMIT') == $this->formId))
		{
			foreach (array_keys($_POST) as $strParam)
			{
				if(in_array($strParam, $arrWantedParam))
				{
					$arrMyParams[$strParam] = $this->Input->post($strParam);
				}
			}
		}

		return array
		(
			'filter' => $arrMyParams,
			'other' => $arrOtherParams,
			'all' => array_merge($arrOtherParams, $arrMyParams)
		);
	}

	/**
	 * Get the filters
	 */
	protected function getFilters()
	{
		$strAction = '';

		/**
		 * @var IMetaModelFilterSettings
		 */
		$objFilterSetting = MetaModelFilterSettingsFactory::byId($this->objFilterConfig->metamodel_filtering);
		
		$objFrontendFilterOptions = new MetaModelFrontendFilterOptions();
		$objFrontendFilterOptions->setAutoSubmit($this->objFilterConfig->metamodel_fef_autosubmit ? true : false);
		$objFrontendFilterOptions->setHideClearFilter($this->objFilterConfig->metamodel_fef_hideclearfilter ? true : false);
		$objFrontendFilterOptions->setShowCountValues($this->objFilterConfig->metamodel_available_values ? true : false);
		
		$arrJumpTo = $this->objFilterConfig->arrJumpTo;
	
		$arrParams = $this->getParams();

		$arrWidgets = $objFilterSetting->getParameterFilterWidgets($arrParams['all'], $arrJumpTo, $objFrontendFilterOptions);

		// filter the widgets we do not want to show.
		$arrWanted = $this->getWantedNames();

		// if we have POST data, we need to redirect now.
		if ($_POST && ($this->Input->post('FORM_SUBMIT') == $this->formId))
		{
			$arrRedirectParams = $arrParams['other'];
			foreach ($arrWanted as $strWidget)
			{
				$arrFilter = $arrWidgets[$strWidget];
				if (!empty($arrFilter['urlvalue']))
				{
					$arrRedirectParams[$strWidget] = $arrFilter['urlvalue'];
				}
			}
			$this->redirectPost($arrRedirectParams);
		}

		$arrRendered = array();

		// render the widgets through the filter templates.
		foreach($this->getWantedNames() as $strWidget)
		{
			$arrFilter = $arrWidgets[$strWidget];

			$strTemplate = $arrFilter['raw']['eval']['template'];

			// parse sub template
			$objSubTemplate            = new FrontendTemplate($strTemplate ? $strTemplate : 'mm_filteritem_default');

			$objSubTemplate->setData($arrFilter);
			$objSubTemplate->submit    = $objFrontendFilterOptions->isAutoSubmit();

			$arrFilter['value'] = $objSubTemplate->parse();

			$arrRendered[$strWidget] = $arrFilter;
		}

		// return filter data
		return array(
			'action'     => $this->generateFrontendUrl($arrJumpTo, $this->getJumpToUrl($arrParams['other'])),
			'formid'     => $this->formId,
			'filters'    => $arrRendered,
			'submit'     => ($objFrontendFilterOptions->isAutoSubmit() ? '' : $GLOBALS['TL_LANG']['metamodels_frontendfilter']['submit'])
		);
	}
	
	/**
	 * Add the "clear all Filter"
	 * 
	 * @param string $strContent
	 * @param string $strTemplate
	 * @return string
	 */
	public function generateClearAll($strContent, $strTemplate)
	{
		if (substr($strTemplate, 0, 7) === 'fe_page')
		{
			if (preg_match_all('#\[\[\[metamodelfrontendfilterclearall::(ce|mod)::([^\]]*)\]\]\]#', $strContent, $arrMatches))
			{
				for($i = 0; $i < count($arrMatches); $i = $i + 3)
				{
					switch ($arrMatches[$i + 1][0])
					{
						case 'ce':
							$objDbResult = Database::getInstance()
								->prepare('SELECT * FROM tl_content WHERE id=?')
								->execute($arrMatches[$i + 2][0]);
							
							// Check if we have a ce element.
							if($objDbResult->numRows == 0)
							{
								$strContent = str_replace($arrMatches[$i][0], '', $strContent);
								break;
							}	
							
							// Get instance and call generate function.
							$objCE = new ContentMetaModelFrontendClearAll($objDbResult);
							$strContent = str_replace($arrMatches[$i][0], $objCE->generateReal(), $strContent);
							break;
							
						case 'mod':
							$objDbResult = Database::getInstance()
								->prepare('SELECT * FROM tl_module WHERE id=?')
								->execute($arrMatches[$i + 2]);
							
							// Check if we have a mod element.
							if($objDbResult->numRows == 0)
							{
								$strContent = str_replace($arrMatches[$i][0], '', $strContent);
								break;
							}
							
							// Get instance and call generate function.
							$objCE = new ContentMetaModelFrontendClearAll($objDbResult);
							$strContent = str_replace($arrMatches[$i][0], $objCE->generateReal(), $strContent);
							break;
					}
				}
			}
		}

		return $strContent;
	}

}
