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
 * @copyright  CyberSpectrum
 * @license    private
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
	protected $strOutputFormat = '';

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
	 */
	public function setTemplateFormat($strFormat)
	{
		$this->strOutputFormat = $strFormat;

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
		$this->getMetaModel();

		// initialize the view
		$this->getView();

		return $this;
	}

	/**
	 * Set filter and parameter.
	 *
	 * @param int $intFilter    the filter settings to use (if 0, the default will be used).
	 *
	 * @param string[] $arrParam the parameters for the filter.
	 *
	 * @return MetaModelList
	 */
	public function setFilterParam($intFilter, $arrParams, $arrParamGet)
	{
		$this->intFilter    = $intFilter;

		$this->getFilter($this->arrParam);

		if (!$this->objFilterSettings)
		{
			throw new Exception('Error: no filter object defined.');
		}

		$arrLegalParams = $this->objFilterSettings->getParameters();

		$arrProcessed = array();

		foreach ($arrLegalParams as $strParameter)
		{
			if (array_key_exists($strParameter, $arrParams))
			{
				// is in presets, check if _GET may override.
				$arrProcessed[$strParameter] = ($arrParams[$strParameter]['use_get'] && $arrParamGet[$strParameter]) ? $arrParamGet[$strParameter] : $arrParams[$strParameter]['value'];
			} else {
				$arrProcessed[$strParameter] = $arrParamGet[$strParameter];
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
	protected function getMetaModel()
	{
		$this->objMetaModel = MetaModelFactory::byId($this->intMetaModel);
	}

	/**
	 * Prepare the view.
	 * NOTE: must be called after getMetaModel().
	 *
	 * @return void
	 */
	protected function getView()
	{
		$this->objView = $this->objMetaModel->getView($this->intView);

		if ($this->objView)
		{
			$this->objView->set('filter', $intFilterSettings);
		}

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
	 * Prepare the filter.
	 *
	 * @param array filter params to use.
	 *
	 * @return void
	 */
	protected function getFilter()
	{
		$this->objFilterSettings = MetaModelFilterSettingsFactory::byId($this->intFilter);

		if ($this->objView)
		{
			// TODO: we should use different filter settings for jumpTo generating but for now we use the input filter also for output.
			$this->objView->set('filter', $this->intFilter);
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
			if($this->intOffset)
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

	public function getPagination()
	{
		return $this->strPagination;
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

		$this->objFilter = $this->objMetaModel->getEmptyFilter();
		$this->objFilterSettings->addRules($this->objFilter, $this->arrParam);

		$intTotal = $this->objMetaModel->getCount($this->objFilter);

		$arrLimits = $this->calculatePagination($intTotal);

		$objItems = $this->objMetaModel->findByFilter($this->objFilter, $this->strSortBy, $this->intOffset, $this->intLimit, $this->strSortDirection, $this->objView->getSettingNames());

		$this->objTemplate->items = $objItems;

		$this->objTemplate->data = ($intTotal && !$blnNoNativeParsing) ? $objItems->parseAll($this->strOutputFormat, $this->objView) : array();

		return $this->objTemplate->parse($this->strOutputFormat);
	}

}
