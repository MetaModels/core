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
 * This is the factory interface to query instances of attributes.
 * Usually this is only used internally from within the MetaModel class.
 * 
 * @package	   MetaModels
 * @subpackage Interfaces
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
interface IMetaModelAttributeFactory
{
	/**
	 * instantiate a attribute from an array.
	 * 
	 * @param array the attribute information data.
	 * 
	 * @return IMetaModelAttribute|null the instance of the attribute or NULL if the class could not be determined
	 */
	public static function createFromArray($arrData);

	/**
	 * instantiate a attribute from an array.
	 * 
	 * @param Database_Result $objRow the attribute information data.
	 * 
	 * @return IMetaModelAttribute|null the instance of the attribute or NULL if the class could not be determined
	 */
	public static function createFromDB($objRow);

	/**
	 * instantiate all attributes for the given MetaModel instance.
	 * 
	 * @param IMetaModel $objMetaModel the MetaModel instance for which all attributes shall be returned.
	 * 
	 * @return IMetaModelAttribute[] the instances of the attributes
	 */
	public static function getAttributesFor($objMetaModel);

	/**
	 * Returns an array of all registered attribute types.
	 * 
	 * @return string[] all attribute types
	 */
	public static function getAttributeTypes();

	/**
	 * Checks whether the given attribute type name is registered in the system
	 * 
	 * @param string $strFieldType the attribute type name to check.
	 * 
	 * @return bool true if the attribute type is valid, false otherwise.
	 */
	public static function isValidAttributeType($strFieldType);
}

?>