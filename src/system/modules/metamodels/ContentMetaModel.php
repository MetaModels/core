<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of ContentMetaModel
 *
 * @author stefan.heimes
 */
class ContentMetaModel extends ContentElement
{

	protected $intLimit = 0;
	protected $intOffset = 0;

	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### METAMODEL LIST ###';

			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

			return $objTemplate->parse();
		}

		// Fallback template
		if (!strlen($this->metamodel_layout))
		{
			$this->metamodel_layout = $this->strTemplate;
		}

		$this->strTemplate = $this->metamodel_layout;

		// if defined, we override the pagination here.
		if ($this->metamodel_use_limit && ($this->metamodel_limit || $this->metamodel_offset))
		{
			if ($this->metamodel_limit)
			{
				$this->intLimit = $this->metamodel_limit;
			}

			if ($this->metamodel_offset)
			{
				$this->intOffset = $this->metamodel_offset;
			}
		}

		return parent::generate();
	}

	/**
	 * Returns the correct render settings for the metamodel.
	 *
	 * @param IMetaModel $objMetaModel      the metamodel for which the view shall be retrieved.
	 *
	 * @param int        $intFilterSettings the filter settings that shall be used for url generation.
	 *
	 * @return IMetaModelRenderSettings the view information.
	 */
	protected function getRenderSettings($objMetaModel, $intFilterSettings)
	{
		$objView = $objMetaModel->getView($this->metamodel_rendersettings);

		if ($objView)
		{
			$objView->set('filter', $intFilterSettings);
		}
		return $objView;
	}

	protected function calculatePagination($intTotal)
	{
		$intOffset = $this->intOffset;
		$intLimit = $this->intLimit;

		if ($this->perPage > 0)
		{
			// if a total limit has been defined, we need to honor that.
			if (!is_null($intLimit) && ($intTotal > $intLimit))
			{
				$intTotal -= $intLimit;
			}
			$intTotal -= $intOffset;

			// Get the current page
			$intPage = $this->Input->get('page') ? $this->Input->get('page') : 1;

			if ($intPage > ($intTotal / $this->perPage))
			{
				$intPage = (int) ceil($intTotal / $this->perPage);
			}

			// Set limit and offset
			$pageOffset = (max($intPage, 1) - 1) * $this->perPage;
			$intOffset += $pageOffset;
			if (is_null($intLimit))
			{
				$intLimit = $this->perPage;
			}
			else
			{
				$intLimit = min($intLimit - $intOffset, $this->perPage);
			}
			// Add pagination menu
			$objPagination = new Pagination($intTotal, $this->perPage);
			$this->Template->pagination = $objPagination->generate("\n  ");
		}
		else
		{
			if (is_null($intLimit))
			{
				$intLimit = 0;
			}
			if (is_null($intOffset))
			{
				$intOffset = 0;
			}
		}

		$this->intLimit = $intLimit;
		$this->intOffset = $intOffset;
	}

	/**
	 * (non-PHPdoc)
	 * @see Module::compile()
	 */
	protected function compile()
	{
		$objMetaModel = MetaModelFactory::byId($this->metamodel);

		$objView = $this->getRenderSettings($objMetaModel, $this->metamodel_filtering);

		if ($objView)
		{
			$objTemplate = new MetaModelTemplate($objView->get('template'));
			$objTemplate->view = $objView;
		}
		else
		{
			// fallback to default.
			$objTemplate = new MetaModelTemplate('metamodel_full');
		}

		$objTemplate->noItemsMsg = $GLOBALS['TL_LANG']['MSC']['noItemsMsg'];

		$arrParameter = $_GET;
		foreach (deserialize($this->metamodel_filterparams, true) as $key => $arrFilterparam)
		{
			// Get flag for using the get param
			$blnUseGet = $arrFilterparam['use_get'];

			// Unset lable/use_get
			unset($arrFilterparam['lable']);
			unset($arrFilterparam['use_get']);

			// Overwrite Get with default values
			foreach ($arrFilterparam as $keyParam => $valueParam)
			{
				if ($blnUseGet && is_null($_GET[$keyParam]))
				{
					$arrParameter[$keyParam] = $valueParam;
				}
				else if (!$blnUseGet)
				{
					$arrParameter[$keyParam] = $valueParam;
				}
			}
		}

		$objFilter = $objMetaModel->prepareFilter($this->metamodel_filtering, $arrParameter);

		$intTotal = $objMetaModel->getCount($objFilter);

		if(!$this->metamodel_nopagination)
		{
			$this->calculatePagination($intTotal);
		}

		$objItems = $objMetaModel->findByFilter($objFilter, $this->metamodel_sortby, $this->intOffset, $this->intOffset, $this->metamodel_sortby_direction, $objView->getSettingNames());

		$objTemplate->items = $objItems;
		$objTemplate->data = array();

		//render items (if any) only if the "do not render option" is not set
		if ($intTotal && !$this->metamodel_noparsing)
		{
			$this->Template->data = $objItems->parseAll($this->Template->getFormat(), $objView);
		}

		$this->Template->items = $objTemplate->parse($this->Template->getFormat());
	}

}

?>
