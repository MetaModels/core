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
if (!defined('TL_ROOT'))
{
	die('You cannot access this file directly!');
}

/**
 * Implementation of the MetaModel lister module.
 * 
 * @package	   MetaModels
 * @subpackage Frontend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class ModuleMetaModelList extends Module
{
	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_metamodellist';


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

		return parent::generate();
	}

	/**
	 * (non-PHPdoc)
	 * @see Module::compile()
	 */
	protected function compile()
	{
		$objMetaModel = MetaModelFactory::byId($this->metamodel);

		$objFilter = $objMetaModel->prepareFilter($this->metamodel_filtering, $_GET);

		$intTotal = $objMetaModel->getCount($objFilter);

		$intOffset = NULL;
		$intLimit = NULL;
		// if defined, we override the pagination here.
		if ($this->metamodel_use_limit && ($this->metamodel_limit || $this->metamodel_offset))
		{
			if ($this->metamodel_limit)
			{
				$intLimit = $this->metamodel_limit;
			}
			if($this->metamodel_offset)
			{
				$intOffset = $this->metamodel_offset;
			}
		}

		if ($this->perPage > 0)
		{
			// if a total limit has been defined, we need to honor that.
			if (!is_null($intLimit) && ($intTotal>$intLimit))
			{
				$intTotal -= $intLimit;
			}
			$intTotal -= $intOffset;

			// Get the current page
			$intPage = $this->Input->get('page') ? $this->Input->get('page') : 1;

			if ($intPage > ($intTotal/$this->perPage))
			{
				$intPage = ceil($intTotal/$this->perPage);
			}

			// Set limit and offset
			$pageOffset = (max($intPage, 1) - 1) * $this->perPage;
			$intOffset += $pageOffset;
			if (is_null($intLimit))
			{
				$intLimit = $this->perPage;
			} else {
				$intLimit = min($intLimit - $intOffset, $this->perPage);
			}
			// Add pagination menu
			$objPagination = new Pagination($intTotal, $this->perPage);
			$this->Template->pagination = $objPagination->generate("\n  ");
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

		$objItems = $objMetaModel->findByFilter($objFilter, $this->metamodel_sortby, $intOffset, $intLimit);
		$objTemplate = new FrontendTemplate($this->metamodel_template);
		$objTemplate->items = $objItems;

		$this->Template->items = $objTemplate->parse();
	}
}
