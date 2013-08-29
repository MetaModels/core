<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package    MetaModels
 * @subpackage FrontendClearAll
 * @author     Stefan Heimes <cms@men-at-work.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\FrontendIntegration\Content;

/**
 * Content element clearing the FE-filter
 *
 * @package    MetaModels
 * @subpackage FrontendClearAll
 * @author     Stefan Heimes <cms@men-at-work.de>
 */
class FilterClearAll extends \ContentElement
{
	/**
	 * Template.
	 *
	 * @var string
	 */
	protected $strTemplate = 'mm_filter_clearall';

	/**
	 * Display a wildcard in the back end.
	 *
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new \BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### METAMODELS FE-CLEAR ALL ###';
			$objTemplate->title    = $this->headline;

			return $objTemplate->parse();
		}

		// Get template if configured.
		if ($this->metamodel_fef_template)
		{
			$this->strTemplate = $this->metamodel_fef_template;
		}

		return sprintf('[[[metamodelfrontendfilterclearall::ce::%s]]]', $this->id);
	}

	/**
	 * Generate the content element.
	 *
	 * @return void
	 */
	protected function compile()
	{
		$blnActiveParam   = false;
		$arrPage          = $GLOBALS['objPage']->row();
		$arrGetParameters = array();

		// Skip filter params.
		foreach (array_keys($_GET) as $mixGetKey)
		{
			if (in_array($mixGetKey, $GLOBALS['MM_FILTER_PARAMS']))
			{
				$blnActiveParam = true;
				continue;
			}

			$arrGetParameters[$mixGetKey] = \Input::getInstance()->get($mixGetKey);
		}

		// Check if we have filter and if we have active params
		$this->Template->active      = (is_array($GLOBALS['MM_FILTER_PARAMS']) && count($GLOBALS['MM_FILTER_PARAMS']) != 0) ? true : false;
		$this->Template->activeParam = $blnActiveParam;

		// Build FE url.
		$this->Template->href = $this->generateFrontendUrl($arrPage, $this->getJumpToUrl($arrGetParameters));
	}

	public function generateReal()
	{
		return parent::generate();
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

}
