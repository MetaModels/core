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

namespace MetaModels\FrontendIntegration;

use MetaModels\Filter\Setting\Factory as FilterFactory;
use MetaModels\FrontendIntegration\Content\FilterClearAll;
use MetaModels\Helper\ContaoController;

/**
 * FE-filtering for Contao MetaModels
 *
 * @package    MetaModels
 * @subpackage FrontendFilter
 * @author     Christian de la Haye <service@delahaye.de>
 */
class FrontendFilter
{
	/**
	 * Filter config.
	 *
	 * @var \ContentElement|\Module
	 */
	protected $objFilterConfig;

	/**
	 * The form id to use.
	 *
	 * @var string
	 */
	protected $formId = 'mm_filter_';

	/**
	 * Configure the filter module.
	 *
	 * @param \ContentElement|\Module $objFilterConfig The content element or module using this filter.
	 *
	 * @return array
	 */
	public function getMetaModelFrontendFilter($objFilterConfig)
	{
		$this->objFilterConfig = $objFilterConfig;

		$this->objFilterConfig->arrJumpTo = $GLOBALS['objPage']->row();

		if ($this->objFilterConfig->metamodel_jumpTo)
		{
			// Page to jump to when filter submit.
			$objPage = \Database::getInstance()->prepare('SELECT id, alias FROM tl_page WHERE id=?')
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
	 * Generate an url determined by the given params and configured jumpTo page.
	 *
	 * @param array $arrParams The URL parameters to use.
	 *
	 * @return string the generated URL.
	 */
	protected function getJumpToUrl($arrParams)
	{
		$strFilterAction = '';
		foreach ($arrParams as $strName => $varParam)
		{
			// Skip the magic "language" parameter.
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

				$strFilterAction .= sprintf(
					$GLOBALS['TL_CONFIG']['disableAlias'] ? '&amp;%s=%s' : '/%s/%s',
					$strName,
					rawurlencode($strValue)
				);
			}
		}
		return $strFilterAction;
	}

	/**
	 * Redirect the browser to the url determined by the given params (configured jumpTo page will get used).
	 *
	 * This will exit the script!
	 *
	 * @param array $arrParams The URL parameters to use.
	 *
	 * @return void
	 */
	protected function redirectPost($arrParams)
	{
		// Translate all params to a valid url and redirect us to it.
		ContaoController::getInstance()
			->redirect(
				\Environment::getInstance()->base .
				ContaoController::getInstance()
					->generateFrontendUrl(
						$this->objFilterConfig->arrJumpTo,
						$this->getJumpToUrl($arrParams)
					)
			);
	}

	/**
	 * Retrieve the list of parameter names that shall be evaluated.
	 *
	 * @return array
	 */
	protected function getWantedNames()
	{
		return (array)unserialize($this->objFilterConfig->metamodel_fef_params);
	}

	/**
	 * Retrieve the parameter values.
	 *
	 * @return array
	 */
	protected function getParams()
	{
		$arrWantedParam = $this->getWantedNames();

		$arrMyParams = $arrOtherParams = array();

		// @codingStandardsIgnoreStart - Loop over $_GET to get a list of all keys.
		if ($_GET)
		{
			foreach (array_keys($_GET) as $strParam)
			// @codingStandardsIgnoreEnd - Continue with style checking.
			{
				if (in_array($strParam, $arrWantedParam))
				{
					$arrMyParams[$strParam] = \Input::getInstance()->get($strParam);
				}
				// Add only to the array if param is not page.
				elseif($strParam != 'page')
				{
					$arrOtherParams[$strParam] = \Input::getInstance()->get($strParam);
				}
			}
		}

		// if POST, translate to proper GET url
		// @codingStandardsIgnoreStart - Loop over $_POST to get a list of all keys.
		if ($_POST && (\Input::getInstance()->post('FORM_SUBMIT') == $this->formId))
		{
			foreach (array_keys($_POST) as $strParam)
			// @codingStandardsIgnoreEnd - Continue with style checking.
			{
				if (in_array($strParam, $arrWantedParam))
				{
					$arrMyParams[$strParam] = \Input::getInstance()->post($strParam);
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
	 * Get the filters.
	 *
	 * @return array
	 */
	protected function getFilters()
	{
		$objFilterSetting = FilterFactory::byId($this->objFilterConfig->metamodel_filtering);

		$objFrontendFilterOptions = new FrontendFilterOptions();
		$objFrontendFilterOptions->setAutoSubmit($this->objFilterConfig->metamodel_fef_autosubmit ? true : false);
		$objFrontendFilterOptions->setHideClearFilter($this->objFilterConfig->metamodel_fef_hideclearfilter ? true : false);
		$objFrontendFilterOptions->setShowCountValues($this->objFilterConfig->metamodel_available_values ? true : false);

		$arrJumpTo = $this->objFilterConfig->arrJumpTo;

		$arrParams = $this->getParams();

		$arrWidgets = $objFilterSetting->getParameterFilterWidgets($arrParams['all'], $arrJumpTo, $objFrontendFilterOptions);

		// Filter the widgets we do not want to show.
		$arrWanted = $this->getWantedNames();

		// If we have POST data, we need to redirect now.
		// @codingStandardsIgnoreStart - Test $_POST to check if any data has been submitted.
		if ($_POST && (\Input::getInstance()->post('FORM_SUBMIT') == $this->formId))
		// @codingStandardsIgnoreEnd - Continue with style checking.
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

		// Render the widgets through the filter templates.
		foreach ($this->getWantedNames() as $strWidget)
		{
			$arrFilter      = $arrWidgets[$strWidget];
			$strTemplate    = $arrFilter['raw']['eval']['template'];
			$objSubTemplate = new \FrontendTemplate($strTemplate ? $strTemplate : 'mm_filteritem_default');

			$objSubTemplate->setData($arrFilter);

			$objSubTemplate->submit  = $objFrontendFilterOptions->isAutoSubmit();
			$arrFilter['value']      = $objSubTemplate->parse();
			$arrRendered[$strWidget] = $arrFilter;
		}

		// Return filter data.
		return array(
			'action'     => ContaoController::getInstance()->generateFrontendUrl(
					$arrJumpTo,
					$this->getJumpToUrl($arrParams['other'])
				),
			'formid'     => $this->formId,
			'filters'    => $arrRendered,
			'submit'     => (
				$objFrontendFilterOptions->isAutoSubmit()
					? ''
					: $GLOBALS['TL_LANG']['metamodels_frontendfilter']['submit']
				)
		);
	}

	/**
	 * Add the "clear all Filter".
	 *
	 * This is called via parseTemplate HOOK to inject the "clear all" filter into fe_page.
	 *
	 * @param string $strContent  The whole page content.
	 *
	 * @param string $strTemplate The name of the template being parsed.
	 *
	 * @return string
	 *
	 * @throws \RuntimeException When an invalid selector has been used (different than "ce" or "mod").
	 */
	public function generateClearAll($strContent, $strTemplate)
	{
		if (substr($strTemplate, 0, 7) === 'fe_page')
		{
			if (preg_match_all(
				'#\[\[\[metamodelfrontendfilterclearall::(ce|mod)::([^\]]*)\]\]\]#',
				$strContent,
				$arrMatches,
				PREG_SET_ORDER
			))
			{
				foreach ($arrMatches as $arrMatch)
				{
					switch ($arrMatch[1])
					{
						case 'ce':
							$objDbResult = \Database::getInstance()
								->prepare('SELECT * FROM tl_content WHERE id=?')
								->execute($arrMatch[2]);

							// Check if we have a ce element.
							if ($objDbResult->numRows == 0)
							{
								$strContent = str_replace($arrMatch[0], '', $strContent);
								break;
							}

							// Get instance and call generate function.
							$objCE      = new FilterClearAll($objDbResult);
							$strContent = str_replace($arrMatch[0], $objCE->generateReal(), $strContent);
							break;

						case 'mod':
							$objDbResult = \Database::getInstance()
								->prepare('SELECT * FROM tl_module WHERE id=?')
								->execute($arrMatch[2]);

							// Check if we have a mod element.
							if ($objDbResult->numRows == 0)
							{
								$strContent = str_replace($arrMatch[0], '', $strContent);
								break;
							}

							// Get instance and call generate function.
							$objCE      = new FilterClearAll($objDbResult);
							$strContent = str_replace($arrMatch[0], $objCE->generateReal(), $strContent);
							break;

						default:
							throw new \RuntimeException('Unexpected element determinator encountered: ' . $arrMatch[1]);
					}
				}
			}
		}

		return $strContent;
	}

}
