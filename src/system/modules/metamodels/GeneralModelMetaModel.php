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
 * Data model class for DC_General <-> MetaModel adaption
 *
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @package    MetaModels
 * @subpackage Core
 */
class GeneralModelMetaModel implements InterfaceGeneralModel
{

	/**
	 * A list with all Properties.
	 *
	 * @var IMetaModelItem
	 */
	protected $objItem = null;

	public function getItem()
	{
		return $this->objItem;
	}

	public function __construct($objItem)
	{
		$this->objItem = $objItem;
	}

	public function __clone()
	{
		return new GeneralModelMetaModel($this->getItem()->copy());
	}

	public function getID()
	{
		return $this->getItem()->get('id');
	}

	/**
	 * @see InterfaceGeneralModel::getProperty()
	 *
	 * @param String $strPropertyName
	 * @return null
	 */
	public function getProperty($strPropertyName)
	{
		if ($this->getItem())
		{
			$varValue = $this->getItem()->get($strPropertyName);
			// test if it is an attribute, if so, let it transform the data
			// for the widget.
			$objAttribute = $this->getItem()->getAttribute($strPropertyName);

			if ($objAttribute)
			{
				$varValue = $objAttribute->valueToWidget($varValue);
			}
			return $varValue;
		}
		else
		{
			return null;
		}
	}

	public function getPropertiesAsArray()
	{
		$arrResult = array();
		foreach (array_keys($this->getItem()->getMetaModel()->getAttributes()) as $strKey)
		{
			$arrResult[$strKey] = $this->getProperty($strKey);
		}
		return $arrResult;
	}

	public function setID($mixID)
	{
		$this->getItem()->set('id', $mixID);
	}

	/**
	 * @see InterfaceGeneralModel::setProperty()
	 *
	 * @param String $strPropertyName
	 * @param mixed $varValue
	 */
	public function setProperty($strPropertyName, $varValue)
	{
		if ($this->getItem())
		{
			// test if it is an attribute, if so, let it transform the data
			// for the widget.
			$objAttribute = $this->getItem()->getAttribute($strPropertyName);
			if ($objAttribute)
			{
				$varValue = $objAttribute->widgetToValue($varValue);
			}
			$this->getItem()->set($strPropertyName, $varValue);
		}
	}

	public function setPropertiesAsArray($arrProperties)
	{
		foreach ($arrProperties as $strKey => $varValue)
		{
			$this->setProperty($strKey, $varValue);
		}
	}

	/**
	 * @see InterfaceGeneralModel::hasProperties()
	 *
	 * @return boolean
	 */
	public function hasProperties()
	{
		return ($this->getItem())?true:false;
	}

	/**
	 * Get a iterator for this model
	 *
	 * @return ArrayIterator
	 */
	public function getIterator()
	{
		return new GeneralModelMetaModelIterator($this);
	}
}

?>
