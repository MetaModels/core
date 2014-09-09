<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Filter\Setting;

use MetaModels\Filter\IFilter;
use MetaModels\FrontendIntegration\FrontendFilterOptions;
use MetaModels\IItem;
use MetaModels\IMetaModel;
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
	 * Retrieve the MetaModel this filter belongs to.
	 *
	 * @return IMetaModel
	 *
	 * @throws \RuntimeException When the MetaModel can not be determined.
	 */
	public function getMetaModel();

	/**
	 * Retrieve all settings that shall be contained within this collection from the database.
	 *
	 * @todo deprecate this and inject the settings in favor of pulling them.
	 *
	 * @return void
	 */
	public function collectRules();

	/**
	 * Generates all filter rules from the contained filter settings.
	 *
	 * @param IFilter $objFilter        The filter object to add rules to.
	 *
	 * @param array   $arrFilterUrl     The filter url to be applied.
	 *
	 * @param array   $arrIgnoredFilter An optional list with filter ids that should be ignored. Defaults to empty array.
	 *
	 * @return void
	 */
	public function addRules(IFilter $objFilter, $arrFilterUrl, $arrIgnoredFilter = array());

	/**
	 * Generate an filter url (aka jump to url) according to the contained filter rules.
	 *
	 * @param IItem           $objItem          The item from which the values shall be retrieved from.
	 *
	 * @param IRenderSettings $objRenderSetting The render settings that hold the destination filter settings and
	 *                                          jumpTo page.
	 *
	 * @return array The filter url parameters.
	 *
	 * @todo     this way of generating jump to urls is not as elegant as it could be and therefore we might want to refactor it.
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
	 * @param array                 $arrFilterUrl             The current filter url.
	 *
	 * @param array                 $arrJumpTo                The selected jump to page to use for link generating.
	 *
	 * @param FrontendFilterOptions $objFrontendFilterOptions The frontend filter options to be passed to the widget.
	 *
	 * @return array
	 */
	public function getParameterFilterWidgets(
		$arrFilterUrl,
		$arrJumpTo = array(),
		FrontendFilterOptions $objFrontendFilterOptions
	);

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
