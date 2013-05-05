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
 * Implementation of the MetaModel Backend Module that displays nice and helpfull stuff..
 *
 * @package	   MetaModels
 * @subpackage Backend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class MetaModelsBackendSupport extends BackendModule
{
	/**
	 * The template to use
	 * @var string
	 */
	protected $strTemplate = 'be_supportscreen';

	/**
	 * Compile the current element
	 */
	protected function compile()
	{
		$GLOBALS['TL_CSS'][] = 'system/modules/metamodels/html/style.css';
		// TODO: if we need some information in the Template, add it here.
		// $this->Template->some_information = 'pretty important data';
	}
}