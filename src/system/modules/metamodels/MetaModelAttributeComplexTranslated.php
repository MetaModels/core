<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package	   MetaModels
 * @subpackage Core
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
 * Base implementation for translated "complex" MetaModel attributes.
 * 
 * @package	   MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
abstract class MetaModelAttributeComplexTranslated extends MetaModelAttributeComplex implements IMetaModelAttributeTranslated
{
//	abstract public function setTranslatedDataFor($arrValues, $strLangCode)

//	abstract public function getTranslatedDataFor($arrIds, $strLangCode)

//	abstract public function unsetValueFor($arrIds, $strLangCode)
}

?>