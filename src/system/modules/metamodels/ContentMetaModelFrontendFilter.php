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
 * Content element for FE-filtering
 *
 * @package	   MetaModels
 * @subpackage FrontendFilter
 * @author     Christian de la Haye <service@delahaye.de>
 */
class ContentMetaModelFrontendFilter extends ContentElement
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

			return $objTemplate->parse();
		}

		return parent::generate();
	}


	/**
	 * Generate the content element
	 */
	protected function compile()
	{
		// get filter data
		$objFilter = new MetaModelFrontendFilter();
		$arrFilter = $objFilter->getMetaModelFrontendFilter($this);

		// get template
		if ($this->metamodel_fef_template)
		{
			$this->Template = new FrontendTemplate($this->metamodel_fef_template);
		}
		$this->Template->setData($arrFilter);
		$this->Template->submit = $arrFilter['submit'];
	}
}

