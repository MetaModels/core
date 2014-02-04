<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package    MetaModels
 * @subpackage Interfaces
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Filter\Setting;

use MetaModels\FrontendIntegration\FrontendFilterOptions;
use MetaModels\IItem;
use MetaModels\Filter\IFilter;
use MetaModels\Render\Setting\ICollection as IRenderSettings;

/**
 * This interface handles all filter setting abstraction.
 *
 * @package    MetaModels
 * @subpackage Interfaces
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
interface ICollection
{
	/**
	 * @return \MetaModels\IMetaModel
	 */
	public function getMetaModel();

	public function collectRules();

	/**
	 * Generates all filter rules from the contained filter settings.
	 *
	 * @param \MetaModels\Filter\IFilter $objFilter the filter object to add rules to.
	 *
	 * @param array            $arrFilterUrl
	 *
	 * @param array            $arrIgnoredFilter An optional list with filter ids that should be ignored. Defaults to empty array.
	 *
	 * @return void
	 */
	public function addRules(IFilter $objFilter, $arrFilterUrl, $arrIgnoredFilter = array());

	/**
	 * Generate an filter url (aka jump to url) according to the contained filter rules.
	 * @todo this way of generating jump to urls is not as elegant as it could be and therefore we might want to refactor it.
	 *
	 *
	 */
	public function generateFilterUrlFrom(IItem $objItem, IRenderSettings $objRenderSetting);

	/**
	 * Retrieve a list of all registered parameters from the setting.
	 *
	 * @return array
	 */
	public function getParameters();

	/**
	 * Retrieve the names of all parameters for listing in frontend filter configuration.
	 *
	 * @return string[string] the parameters as array. parametername => label
	 */
	public function getParameterFilterNames();

	/**
	 * Retrieve a list of filter widgets for all registered parameters as form field arrays.
	 *
	 * @param array                           $arrFilterUrl       the current filter url.
	 *
	 * @param array                           $arrJumpTo          the selected jump to page to use for link generating.
	 *
	 * @param \MetaModels\FrontendIntegration\FrontendFilterOptions $objFrontendFilterOptions
	 *
	 * @return array
	 */
	public function getParameterFilterWidgets($arrFilterUrl, $arrJumpTo = array(), FrontendFilterOptions $objFrontendFilterOptions);

	/**
	 * Retrieve a list of all registered parameters from the setting as DCA compatible arrays.
	 *
	 * @return array
	 */
	public function getParameterDCA();

	/**
	 * Retrieve a list of all referenced attributes within the filter setting.
	 *
	 * @return array
	 */
	public function getReferencedAttributes();
}
