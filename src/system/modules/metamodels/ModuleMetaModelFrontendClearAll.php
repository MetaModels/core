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

/**
 * FE-module for FE-filtering
 *
 * @package	   MetaModels
 * @subpackage FrontendClearAll
 * @author     Stefan Heimes <cms@men-at-work.de>
 */
class ModuleMetaModelFrontendClearAll extends Module
{

	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mm_filter_default';


	/**
	 * Display a wildcard in the back end
	 * @return string
	 */
	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');

			$objTemplate->wildcard = '### METAMODELS FE-FILTERBLOCK ###';
			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->title;
			$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		// get template
		if ($this->metamodel_fef_template)
		{
			$this->strTemplate = $this->metamodel_fef_template;
		}

		return sprintf('[[[metamodelfrontendfilterclearall::mod::%s]]]', $this->id);
	}


	/**
	 * Generate module
	 */
	protected function compile()
	{
		$blnActiveParam   = false;
		$arrPage          = $GLOBALS['objPage']->fetchAllAssoc();
		$arrGetParameters = array();

		// Skip filter params.
		foreach (array_keys($_GET) as $mixGetKey)
		{
			if (in_array($mixGetKey, $GLOBALS['MM_FILTER_PARAMS']))
			{
				$blnActiveParam = true;
				continue;
			}

			$arrGetParameters[$mixGetKey] = $this->Input->get($mixGetKey);
		}

		// Check if we have filter and if we have active params
		$this->Template->active		 = (!is_array($GLOBALS['MM_FILTER_PARAMS']) || count($GLOBALS['MM_FILTER_PARAMS']) == 0) ? false : true;
		$this->Template->activeParam = $blnActiveParam;

		// Build FE url.
		$this->Template->href = $this->generateFrontendUrl($arrPage[0], $this->getJumpToUrl($arrGetParameters));
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

