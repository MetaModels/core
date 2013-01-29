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

		if ($this->objFilterConfig->jumpTo)
		{
			// page to jump to when filter submit
			$objPage = $this->Database->prepare("SELECT id, alias FROM tl_page WHERE id=?")
				->limit(1)
				->execute($this->objFilterConfig->jumpTo);
			if ($objPage->numRows)
			{
				$this->objFilterConfig->arrJumpTo = $objPage->row();
			}

		}

		// TODO: we should change this to POST data, definately better than redirecting different GET layouts around.
		// redirect to same page with nice urls on form_submit
		if($this->Input->get('FORM_SUBMIT')=='mm_fefilter')
		{
			$arrRequestParts = explode('?',$this->Environment->request);
			$this->redirect(urldecode($this->addToUrl($this->getRequestString($this, $_GET), $arrRequestParts[0])));
		}

		return $this->getFilters();
	}


	/**
	 * Get the filters
	 */
	protected function getFilters()
	{
		$strAction = '';

		// get filter settings
		/**
		 * @var IMetaModelFilterSettings
		 */
		$objFilterSetting = MetaModelFilterSettingsFactory::byId($this->objFilterConfig->metamodel_filtering);

		$arrParameters = $objFilterSetting->getParameterFilterWidgets($_GET, $this->objFilterConfig->arrJumpTo, $this->objFilterConfig->metamodel_fef_autosubmit);

		$arrAttributes = unserialize($this->objFilterConfig->metamodel_fef_params);

		foreach($arrParameters as $strKeyParameter=>$arrParameter)
		{
			if(!in_array($strKeyParameter, $arrAttributes))
			{
				unset($arrParameters[$strKeyParameter]);
			}
		}

		foreach($arrParameters as $key=>$val)
		{
			// we need the get parameter later originally
			$strGetParameter = $this->Input->get($key);

			// convert __-separated tags to array
			if($val['eval']['multiple'])
			{
				$arrTags = explode('__', $this->Input->get($key));
				$this->Input->setGet($key, $arrTags);
			}

			// if an array-parameter is --none-- redirect to url without this key
			if(is_array($this->Input->get($key)) && strpos($strGetParameter, '--none--')!==false)
			{
				$arrTmpGet = array();

				foreach($_GET as $strKeyGet=>$strValGet)
				{
					if($strKeyGet != $key)
					{
						$arrTmpGet[$strKeyGet] = $strValGet;
						$this->Input->setGet($strKeyGet, $strValGet);
					}
				}

				$arrRequestParts = explode('?',$this->Environment->request);
				$this->redirect($this->addToUrl($this->getRequestString($this, $arrTmpGet), $arrRequestParts[0]));
			}

			// if an array-parameter is --all-- redirect to url with all options checked
			if(is_array($this->Input->get($key)) && strpos($strGetParameter, '--all--')!==false)
			{
				$arrTmpGet = array();

				foreach($_GET as $strKeyGet=>$mixedValGet)
				{
					if($strKeyGet != $key)
					{
						$arrTmpGet[$strKeyGet] = $mixedValGet;
						$this->Input->setGet($strKeyGet, $mixedValGet);
					}
					else
					{
						$mixedValGet = array();
						foreach($val['options'] as $strKeyOption=>$strValOption)
						{
							$mixedValGet[] = $strKeyOption;
						}
						$arrTmpGet[$strKeyGet] = $mixedValGet;
						$this->Input->setGet($strKeyGet, $mixedValGet);
					}
				}

				$arrRequestParts = explode('?',$this->Environment->request);
				$this->redirect($this->addToUrl($this->getRequestString($this, $arrTmpGet), $arrRequestParts[0]));
			}

			$this->arrFilters[] = $val;

			// mark key as key to reset
			$this->arrResetKeys[] = $key;
		}

		// collect parameters to keep
		foreach($_GET as $strKeyGet=>$strValGet)
		{
			if(!in_array($strKeyGet, $this->arrResetKeys) && $strValGet)
			{
				$this->arrPreserveKeys[] = $strKeyGet;
			}
		}

		// filter out double parameters
		$this->arrPreserveKeys = is_array($this->arrPreserveKeys) ? array_unique($this->arrPreserveKeys) : array();

		// store values for parameters to keep
		foreach($this->arrPreserveKeys as $strKeyPreserveKey)
		{
			if($this->Input->get($strKeyPreserveKey))
			{
				$strAction .= '/'.$strKeyPreserveKey.'/';

				// convert __-separated tags to array
				if(strpos($this->Input->get($strKeyPreserveKey),'__'))
				{
					$arrTags = explode('__', $this->Input->get($strKeyPreserveKey));
					$this->Input->setGet($strKeyPreserveKey, $arrTags);
				}

				// tags
				if(is_array($this->Input->get($strKeyPreserveKey)))
				{
					$strTmpAction = '';

					foreach($this->Input->get($strKeyPreserveKey) as $tmpVal)
					{
						$this->arrPreserveParams[] =array(
							'key'   => $strKeyPreserveKey.'[]',
							'value' => $tmpVal
							);
						$strTmpAction .= ($strTmpAction ? '__':'').urlencode($tmpVal);
					}

					$strAction .= $strTmpAction;
				}
				else
				{
					// standard
					$this->arrPreserveParams[] =array(
						'key'   => $strKeyPreserveKey,
						'value' => $this->Input->get($strKeyPreserveKey)
						);

					$strAction .= urlencode($this->Input->get($strKeyPreserveKey));
				}
			}
		}

		// prepare action-urls for options via links and parse subtemplate
		foreach($this->arrFilters as $intKeyFilter=>$arrFilter)
		{
			// parse sub template
			$objSubTemplate            = new FrontendTemplate(($arrFilter['raw']['eval']['template'] ? $arrFilter['raw']['eval']['template'] : 'mm_filteritem_default'));

			$objSubTemplate->setData($arrFilter);
			$objSubTemplate->submit    = ($this->objFilterConfig->metamodel_fef_autosubmit ? true : false);
			$this->arrFilters[$intKeyFilter]['value'] = $objSubTemplate->parse();
		}

		// return filter data
		return array(
			'action'     => $this->generateFrontendUrl($this->objFilterConfig->arrJumpTo),
			'parameters' => $this->arrPreserveParams,
			'filter'     => $this->arrFilters,
			'submit'     => ($this->objFilterConfig->metamodel_fef_autosubmit ? '' : $GLOBALS['TL_LANG']['metamodels_frontendfilter']['submit'])
			);
	}


	/**
	 * get a request string for nice urls
	 */
	protected function getRequestString($objThis, $arrGet)
	{
		foreach($arrGet as $strGet=>$voidGetVal)
		{
			// discard the FORM_SUBMIT to avoid an endless redirect ;)
			if($strGet != 'FORM_SUBMIT')
			{
				// arrays
				if(is_array($objThis->Input->get($strGet)))
				{
					$strAdd2 = '';

					foreach($objThis->Input->get($strGet) as $strGetVal)
					{
						if($strGetVal)
						{
							$strAdd2 .= ($strAdd2 ? '__' : '').urlencode($strGetVal);
						}
					}

					if($strAdd2)
					{
						$strAdd .= ($strAdd ? '&':'').$strGet.'='.$strAdd2;
					}
				}
				// strings
				elseif($objThis->Input->get($strGet))
				{
					$strAdd .= ($strAdd ? '&':'').$strGet.'='.urlencode($objThis->Input->get($strGet));
				}
			}
		}

		return $strAdd;
	}
}

?>