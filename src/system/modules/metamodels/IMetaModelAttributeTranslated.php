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
 * This is the interface for translated attributes.
 * To create MetaModelAttribute instances, use the @link{MetaModelAttributeFactory}
 * This interface handles all interfacing needed for translated attributes.
 *
 * @package	   MetaModels
 * @subpackage Interfaces
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
interface IMetaModelAttributeTranslated extends IMetaModelAttribute
{
	/**
	 * Set a value for an item in a certain language.
	 *
	 * @param mixed[int] $arrValues the values to be set in id => value layout.
	 *
	 * @param string $strLangCode the language code for which the data shall be retrieved.
	 *
	 *
	 * @return void
	 */
	public function setTranslatedDataFor($arrValues, $strLangCode);

	/**
	 * Get values for the given items in a certain language.
	 *
	 * @param int[]  $arrIds      the ids for which values shall be retrieved.
	 *
	 * @param string $strLangCode the language code for which the data shall be retrieved.
	 *
	 *
	 * @return mixed[int] the values.
	 */
	public function getTranslatedDataFor($arrIds, $strLangCode);

	/**
	 * Remove values for items in a certain lanugage.
	 *
	 * @param int[]  $arrIds      the ids for which values shall be removed.
	 *
	 * @param string $strLangCode the language code for which the data shall be removed.
	 *
	 *
	 * @return void
	 */
	public function unsetValueFor($arrIds, $strLangCode);
}

?>