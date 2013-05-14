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
 * Implementation of the MetaModel content element.
 *
 * @package	   MetaModels
 * @subpackage Frontend
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 */
class ContentMetaModel extends ContentElement
{
	/**
	 * Template
	 * @var string
	 */
	protected $strTemplate = 'ce_metamodel_list';

	public function generate()
	{
		if (TL_MODE == 'BE')
		{
			$objTemplate = new BackendTemplate('be_wildcard');
			$objTemplate->wildcard = '### METAMODEL LIST ###';

			$objTemplate->title = $this->headline;
			$objTemplate->id = $this->id;
			$objTemplate->link = $this->name;
			$objTemplate->href = 'contao/main.php?do=themes&amp;table=tl_content&amp;act=edit&amp;id=' . $this->id;

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
		$objItemRenderer = new MetaModelList();

		$this->Template->searchable = !$this->metamodel_donotindex;

		$this->Template->items = $objItemRenderer
			->setMetaModel($this->metamodel, $this->metamodel_rendersettings)
			->setLimit($this->metamodel_use_limit, $this->metamodel_offset, $this->metamodel_limit)
			->setPageBreak($this->perPage)
			->setSorting($this->metamodel_sortby, $this->metamodel_sortby_direction)
			->setFilterParam($this->metamodel_filtering, deserialize($this->metamodel_filterparams, true), $_GET)
			->render($this->metamodel_noparsing, $this);
		$this->Template->pagination = $objItemRenderer->getPagination();
	}
}

