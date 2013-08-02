<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package	   MetaModels
 * @subpackage Frontend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

/**
 * Implementation of a general purpose MetaModel listing.
 *
 * @package	   MetaModels
 * @subpackage Frontend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class MetaModelList extends Controller
{
	/**
	 * Use limit.
	 *
	 * @var bool
	 */
	protected $blnUseLimit = false;

	/**
	 * Offset.
	 *
	 * @var int
	 */
	protected $intOffset = 0;

	/**
	 * Limit.
	 *
	 * @var int
	 */
	protected $intLimit = 0;

	/**
	 * Pagination page break.
	 *
	 * @var int
	 */
	protected $intPerPage = 0;

	/**
	 * Sort by attribute
	 *
	 * @var string
	 */
	protected $strSortBy = '';

	/**
	 * Sort by attribute
	 *
	 * @var string
	 */
	protected $strSortDirection = 'ASC';

	/**
	 * The view to use.
	 *
	 * @var int
	 */
	protected $intView = 0;

	/**
	 * Sort by attribute
	 *
	 * @var string
	 */
	protected $strOutputFormat;

	/**
	 * The metamodel to use.
	 *
	 * @var int
	 */
	protected $intMetaModel = 0;

	/**
	 * The filter to use.
	 *
	 * @var int
	 */
	protected $intFilter = 0;

	/**
	 *  the parameters for the filter.
	 *
	 * @var string[]
	 */
	protected $arrParam = array();

	public function __construct() {
		parent::__construct();
	}

	/**
	 * Set the limit
	 *
	 * @param bool $blnUse    if true, use limit, if false no limit is applied.
	 *
	 * @param int  $intOffset like in SQL, first element to be returned (0 based).
	 *
	 * @param int  $intLimit  like in SQL, amount of elements to retrieve.
	 *
	 * @return MetaModelList
	 */
	public function setLimit($blnUse, $intOffset, $intLimit)
	{
		//
		$this->blnUseLimit = $blnUse;
		$this->intOffset   = $intOffset;
		$this->intLimit    = $intLimit;

		return $this;
	}

	/**
	 * Set page breaking to the given amount of items. A value of 0 disables pagination at all.
	 *
	 * @param int $intLimit the amount of items per page. A value of 0 disables pagination.
	 *
	 * @return MetaModelList
	 */
	public function setPageBreak($intLimit)
	{
		$this->intPerPage   = $intLimit;

		return $this;
	}

	/**
	 * Set sorting to an attribute or system column optionally in the given direction.
	 *
	 * @param string $strSortBy    The name of the attribute or system column to be used for sorting.
	 *
	 * @param string $strDirection The direction, either ASC or DESC (optional).
	 *
	 * @return MetaModelList
	 */
	public function setSorting($strSortBy, $strDirection = 'ASC')
	{
		$this->strSortBy          = $strSortBy;
		$this->strSortDirection   = ($strDirection == 'DESC') ? 'DESC' : 'ASC';

		return $this;
	}

	/**
	 * Set output format.
	 *
	 * @param string $strFormat The name of the template output format to use.
	 *
	 * @return MetaModelList
	 * @deprecated Use overrideOutputFormat instead
	 */
	public function setTemplateFormat($strFormat)
	{
		$this->overrideOutputFormat($strFormat);
		return $this;
	}

	/**
	 * Override the output format of the used view
	 *
	 * @param string|null $strOutputFormat
	 * @return MetaModelList
	 */
	public function overrideOutputFormat($strOutputFormat = null)
	{
		$strOutputFormat = strval($strOutputFormat);
		if(strlen($strOutputFormat))
		{
			$this->strOutputFormat = $strOutputFormat;
		} else {
			unset($this->strOutputFormat);
		}
		return $this;
	}

	/**
	 * Set metamodel and render settings.
	 *
	 * @param int $intMetaModel the metamodel to use.
	 *
	 * @param int $intView      the render settings to use (if 0, the default will be used).
	 *
	 * @return MetaModelList
	 */
	public function setMetaModel($intMetaModel, $intView)
	{
		$this->intMetaModel = $intMetaModel;
		$this->intView      = $intView;

		// initialize the metamodel
		$this->prepareMetaModel();

		// initialize the view
		$this->prepareView();

		return $this;
	}

	/**
	 * Set filter and parameter.
	 *
	 * @param int      $intFilter  the filter settings to use (if 0, the default will be used).
	 *
	 * @param string[] $arrPresets the parameter preset values to use.
	 *
	 * @param string[] $arrValues the dynamic parameter values that may be used.
	 *
	 * @param string[] $arrPresets the parameters for the filter.
	 *
	 * @return MetaModelList
	 */
	public function setFilterParam($intFilter, $arrPresets, $arrValues)
	{
		$this->intFilter    = $intFilter;
		
		$this->objFilterSettings = MetaModelFilterSettingsFactory::byId($this->intFilter);
		
		if (!$this->objFilterSettings)
		{
			throw new Exception('Error: no filter object defined.');
		}

		$arrPresetNames = $this->objFilterSettings->getParameters();
		$arrFEFilterParams = array_keys($this->objFilterSettings->getParameterFilterNames());
		
		$arrProcessed = array();

		// We have to use all the preset values we want first.
		foreach ($arrPresets as $strPresetName => $arrPreset)
		{
			if (in_array($strPresetName, $arrPresetNames))
			{
				$arrProcessed[$strPresetName] = $arrPreset['value'];
			}
		}
		
		// now we have to use all FE filter params, that are either:
		// * not contained within the presets
		// * or are overridable.
		foreach ($arrFEFilterParams as $strParameter)
		{
			// unknown parameter? - next please
			if (!array_key_exists($strParameter, $arrValues))
			{
				continue;
			}

			// not a preset or allowed to override? - use value
			if ((!array_key_exists($strParameter, $arrPresets)) || $arrPresets[$strParameter]['use_get'])
			{
				$arrProcessed[$strParameter] = $arrValues[$strParameter];
			}
		}

		$this->arrParam = $arrProcessed;

		return $this;
	}


	/**
	 * The Metamodel to use
	 *
	 * @var IMetaModel
	 */
	protected $objMetaModel;

	/**
	 * The render settings to use.
	 *
	 * @var IMetaModelRenderSettings
	 */
	protected $objView;

	/**
	 * The render template to use.
	 *
	 * @var IMetaModelRenderSettings
	 */
	protected $objTemplate;

	/**
	 * The filter settings to use.
	 *
	 * @var IMetaModelFilterSettings
	 */
	protected $objFilterSettings;

	/**
	 * The filter to use.
	 *
	 * @var IMetaModelFilter
	 */
	protected $objFilter;

	/**
	 * Prepare the metamodel.
	 *
	 * @return void
	 */
	protected function prepareMetaModel()
	{
		$this->objMetaModel = MetaModelFactory::byId($this->intMetaModel);
	}

	/**
	 * Prepare the view.
	 * NOTE: must be called after prepareMetaModel().
	 *
	 * @return void
	 */
	protected function prepareView()
	{
		$this->objView = $this->objMetaModel->getView($this->intView);

		if ($this->objView)
		{
			$this->objTemplate = new MetaModelTemplate($this->objView->get('template'));
			$this->objTemplate->view = $this->objView;
		} else {
			// fallback to default.
			$this->objTemplate = new MetaModelTemplate('metamodel_full');
		}
	}

		
	/**
	 * the calculated pagination, if any.
	 */
	protected $strPagination = '';

	/**
	 * Calculate the pagination based upon the offset, limit and total amount of items.
	 */
	protected function calculatePagination($intTotal)
	{
		$intOffset = NULL;
		$intLimit = NULL;
		// if defined, we override the pagination here.
		if ($this->blnUseLimit && ($this->intLimit || $this->intOffset))
		{
			if ($this->intLimit)
			{
				$intLimit = $this->intLimit;
			}
			if ($this->intOffset)
			{
				$intOffset = $this->intOffset;
			}
		}

		if ($this->intPerPage > 0)
		{
			// if a total limit has been defined, we need to honor that.
			if (!is_null($intLimit) && ($intTotal>$intLimit))
			{
				$intTotal -= $intLimit;
			}
			$intTotal -= $intOffset;

			// Get the current page
			$intPage = $this->Input->get('page') ? $this->Input->get('page') : 1;

			if ($intPage > ($intTotal/$this->intPerPage))
			{
				$intPage = (int)ceil($intTotal/$this->intPerPage);
			}

			// Set limit and offset
			$pageOffset = (max($intPage, 1) - 1) * $this->intPerPage;
			$intOffset += $pageOffset;
			if (is_null($intLimit))
			{
				$intLimit = $this->intPerPage;
			} else {
				$intLimit = min($intLimit - $intOffset, $this->intPerPage);
			}
			// Add pagination menu
			$objPagination = new Pagination($intTotal, $this->intPerPage);

			$this->strPagination = $objPagination->generate("\n  ");
		} else {
			if (is_null($intLimit))
			{
				$intLimit = 0;
			}
			if (is_null($intOffset))
			{
				$intOffset = 0;
			}
		}
		$this->intLimit  = $intLimit;
		$this->intOffset = $intOffset;
	}

	/**
	 * The items in the list view.
	 * @var IMetaModelItems
	 */
	protected $objItems = NULL;


	/**
	 * Add additional filter rules to the list.
	 * Can be overridden by subclasses to add additional filter rules to the filter before it will get evaluated.
	 *
	 * @return MetaModelList
	 */
	protected function modifyFilter()
	{
		return $this;
	}
	
	
	/**
	 * Add additional filter rules to the list on the fly.
	 *
	 * @return MetaModelList
	 */
	public function addFilterRule($objFilterRule)
	{
		if(!$this->objFilter)
		{
			$this->objFilter = $this->objMetaModel->getEmptyFilter();
		}
		
		$this->objFilter->addFilterRule($objFilterRule);
		
		return $this;
	}
	

	/**
	 * Return all attributes that shall be fetched from the MetaModel.
	 * In this base implementation, this only includes the attributes mentioned in the render setting.
	 *
	 * @return string[] the names of the attributes to be fetched.
	 */
	protected function getAttributeNames()
	{
		$arrAttributes = $this->objView->getSettingNames();

		// Get the right jumpto.
		$strDesiredLanguage  = $this->getMetaModel()->getActiveLanguage();
		$strFallbackLanguage = $this->getMetaModel()->getFallbackLanguage();

		$intFilterSettings = 0;

		foreach ((array)$this->getView()->get('jumpTo') as $arrJumpTo)
		{
			// If either desired language or fallback, keep the result.
			if (!$this->getMetaModel()->isTranslated()
				|| $arrJumpTo['langcode'] == $strDesiredLanguage
				|| $arrJumpTo['langcode'] == $strFallbackLanguage)
			{
				$intFilterSettings = $arrJumpTo['filter'];
				// If the desired language, break. Otherwise try to get the desired one until all have been evaluated.
				if ($strDesiredLanguage == $arrJumpTo['langcode'])
				{
					break;
				}
			}
		}

		if ($intFilterSettings)
		{
			$objFilterSettings = MetaModelFilterSettingsFactory::byId($intFilterSettings);
			$arrAttributes = array_merge($objFilterSettings->getReferencedAttributes(), $arrAttributes);
		}

		return $arrAttributes;
	}

	/**
	 * Prepare the rendering
	 */
	public function prepare()
	{
		if ($this->objItems)
		{
			return $this;
		}

		// create an empty filter object if not done before
		if(!$this->objFilter)
		{
			$this->objFilter = $this->objMetaModel->getEmptyFilter();
		}
		
		if($this->objFilterSettings)
		{
			$this->objFilterSettings->addRules($this->objFilter, $this->arrParam);
		}
		
		$this->modifyFilter();

		$intTotal = $this->objMetaModel->getCount($this->objFilter);

		$this->calculatePagination($intTotal);

		$this->objItems = $this->objMetaModel->findByFilter($this->objFilter, $this->strSortBy, $this->intOffset, $this->intLimit, $this->strSortDirection, $this->getAttributeNames());

		return $this;
	}
	
	/**
	 * Return the filter.
	 *
	 * @return MetaModelFilter
	 */
	public function getFilter()
	{
		return $this->objFilter;
	}
	
	/**
	 * Return the filter settings.
	 *
	 * @return MetaModelFilterSettings
	 */
	public function getFilterSettings()
	{
		return $this->objFilterSettings;
	}

	/**
	 * Returns the pagination string.
	 * Remember to call prepare() first.
	 */
	public function getPagination()
	{
		return $this->strPagination;
	}

	/**
	 * Returns the item list in the view.
	 */
	public function getItems()
	{
		return $this->objItems;
	}

	/**
	 * Returns the item list in the view.
	 */
	public function getView()
	{
		return $this->objView;
	}

	/**
	 * Returns the item list in the view.
	 */
	public function getMetaModel()
	{
		return $this->objMetaModel;
	}

	public function getOutputFormat()
	{
		if (isset($this->strOutputFormat))
		{
			return $this->strOutputFormat;
		}
		if (isset($this->objView) && $this->objView->get('format'))
		{
			return $this->objView->get('format');
		}
		if (TL_MODE == 'FE' && is_object($GLOBALS['objPage']) && $GLOBALS['objPage']->outputFormat)
		{
			return $GLOBALS['objPage']->outputFormat;
		}
		return 'text';
	}

	/**
	 * Render the list view.
	 *
	 * @param bool $blnNoNativeParsing flag determining if the parsing shall be done internal or if the template will handle the parsing on it's own.
	 *
	 * @param object $objCaller        the object calling us, might be a Module or ContentElement or anything else.
	 *
	 * @return string
	 */
	public function render($blnNoNativeParsing, $objCaller)
	{
		$this->objTemplate->noItemsMsg = $GLOBALS['TL_LANG']['MSC']['noItemsMsg'];
		$this->objTemplate->details    = $GLOBALS['TL_LANG']['MSC']['details'];

		$this->prepare();
		$strOutputFormat = $this->getOutputFormat();

		if($this->objItems->getCount() && !$blnNoNativeParsing)
		{
			$this->objTemplate->data = $this->objItems->parseAll($strOutputFormat, $this->objView);
		} else {
			$this->objTemplate->data = array();
		}

		$this->objTemplate->caller       = $objCaller;
		$this->objTemplate->items        = $this->objItems;
		$this->objTemplate->filterParams = $this->arrParam;

		return $this->objTemplate->parse($strOutputFormat);
	}

}
