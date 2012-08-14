<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package	   MetaModels
 * @subpackage Interfaces
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
 * This is the main MetaModel interface.
 * 
 * This interface handles all filter setting abstraction.
 * dunn
 * @see MetaModelFilterFactory::byId	to instantiate a MetaModel by its ID.
 * @package	   MetaModels
 * @subpackage Interfaces
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
interface IMetaModelFilterSettings
{
	public function getMetaModel();

	public function collectRules();

	/**
	 * Generates all filter rules from the contained filter settings.
	 * 
	 * @param IMetaModelFilter $objFilter the filter object to add rules to.
	 * 
	 * @param array $arrFilterUrl
	 * 
	 * @return void
	 * 
	 */
	public function addRules(IMetaModelFilter $objFilter, $arrFilterUrl);


	public function generateFilterUrlFrom(IMetaModelItem $objItem);
}


?>