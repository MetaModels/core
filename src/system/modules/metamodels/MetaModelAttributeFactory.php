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
 * This is the implementation of the Field factory to query instances of fields.
 * Usually this is only used internally by {@link MetaModel}
 * 
 * @package	   MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class MetaModelAttributeFactory implements IMetaModelAttributeFactory
{

	/**
	 * All attribute instances for all MetaModels that are created via this factory.
	 */
	protected static $arrAttributes = array();

	/**
	 * Determines the correct class from a field type name.
	 * 
	 * @param string $strFieldType the field type of which the class shall be fetched from.
	 * 
	 * @return string the class name which handles the field type or NULL if no class could be found.
	 */
	protected static function getAttributeTypeClass($strFieldType)
	{
		return $GLOBALS['METAMODELS']['attributes'][$strFieldType]['class'];
	}

	/////////////////////////////////////////////////////////////////
	// interface IMetaModelAttributeFactory
	/////////////////////////////////////////////////////////////////

	/**
	 * {@inheritdoc}
	 */
	public static function createFromArray($arrData)
	{
		$strClassName = self::getAttributeTypeClass($arrData['type']);
		return $strClassName ? new $strClassName(MetaModelFactory::byId($arrData['pid']), $arrData) : NULL;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function createFromDB($objRow)
	{
		$strClassName = self::getAttributeTypeClass($objRow->type);
		return $strClassName ? new $strClassName(MetaModelFactory::byId($objRow->pid), $objRow->row()) : NULL;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function getAttributesFor($objMetaModel)
	{
		$objDB = Database::getInstance();
		$objAttributes = $objDB->prepare('SELECT * FROM tl_metamodel_attribute WHERE pid=?')
							->execute($objMetaModel->get('id'));

		$arrAttributes = array();
		while ($objAttributes->next())
		{
			if(isset(self::$arrAttributes[$objAttributes->id]))
			{
				$arrAttributes[] = self::$arrAttributes[$objAttributes->id];
			} else {
				$objAttribute = self::createFromDB($objAttributes);
				$arrAttributes[] = $objAttribute;
				self::$arrAttributes[$objAttributes->id] = $objAttribute;
			}
		}
		return $arrAttributes;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function getAttributeTypes($blnSupportTranslated = false, $blnSupportVariants = false)
	{
		if($blnSupportTranslated)
		{
			return array_keys($GLOBALS['METAMODELS']['attributes']);
		}
		$arrRet = array();
		foreach ($GLOBALS['METAMODELS']['attributes'] as $strKey => $arrInformation)
		{
			$arrInterfaces = class_implements($arrInformation['class'], true);
			// skip translated fieldtypes if translation is not supported.
			if ((!$blnSupportTranslated && in_array('IMetaModelAttributeTranslated', $arrInterfaces)))
			{
				continue;
			}

			// TODO: will we really ever have some interface like this?
			// skip variant fields if variants are not supported.
			if ((!$blnSupportVariants && in_array('IMetaModelAttributeVariants', $arrInterfaces)))
			{
				continue;
			}
			$arrRet[] = $strKey;
		}
		return $arrRet;
	}

	/**
	 * {@inheritdoc}
	 */
	public static function isValidAttributeType($strFieldType)
	{
		return array_key_exists($strFieldType, $GLOBALS['METAMODELS']['attributes']);
	}
}

?>