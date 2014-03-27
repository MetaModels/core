<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package    MetaModels
 * @subpackage Frontend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\FrontendIntegration\Module;

use MetaModels\ItemList;

/**
 * Implementation of the MetaModel content element.
 *
 * @package    MetaModels
 * @subpackage Frontend
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 */
class ModelList extends \Module
{
	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'mod_metamodel_list';

	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new \BackendTemplate('be_wildcard');
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
	 * Retrieve all filter parameters from the input class for the specified filter setting.
	 *
	 * @param \MetaModels\ItemList $objItemRenderer
	 *
	 * @return string[]
	 */
	protected function getFilterParameters($objItemRenderer)
	{
		$arrReturn = array();

		foreach (array_keys($objItemRenderer->getFilterSettings()->getParameterFilterNames()) as $strName)
		{
			$varValue = \Input::getInstance()->get($strName);
			if (is_string($varValue))
			{
				$arrReturn[$strName] = $varValue;
			}
		}
		return $arrReturn;
	}

	/**
	 * (non-PHPdoc)
	 * @see ContentElement::compile()
	 */
	protected function compile()
	{
		$objItemRenderer = new ItemList();

		$this->Template->searchable = !$this->metamodel_donotindex;

		$objItemRenderer
			->setMetaModel($this->metamodel, $this->metamodel_rendersettings)
			->setLimit($this->metamodel_use_limit, $this->metamodel_offset, $this->metamodel_limit)
			->setPageBreak($this->perPage)
			->setSorting($this->metamodel_sortby, $this->metamodel_sortby_direction)
			->setFilterSettings($this->metamodel_filtering)
			->setFilterParameters(deserialize($this->metamodel_filterparams, true), $this->getFilterParameters($objItemRenderer))
			->setMetaTags($this->metamodel_meta_title, $this->metamodel_meta_description);

		$this->Template->items         = $objItemRenderer->render($this->metamodel_noparsing, $this);
		$this->Template->numberOfItems = $objItemRenderer->getItems()->getCount();
		$this->Template->pagination    = $objItemRenderer->getPagination();
	}
}
