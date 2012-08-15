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

	/**
	 * Returns the native IMetaModelItem instance encapsulated within this abstraction.
	 *
	 * @return IMetaModelItem
	 */
	public function getItem()
	{
		return $this->objItem;
	}

	/**
	 * Create a new instance of this class.
	 *
	 * @param IMetaModelItem $objItem the item that shall be encapsulated.
	 *
	 * @return GeneralModelMetaModel
	 */
	public function __construct($objItem)
	{
		$this->objItem = $objItem;
	}

	/**
	 * Clone this instance and the encapsulated item.
	 * The result is a fresh copy with no id.
	 *
	 * @return GeneralModelMetaModel
	 */
	public function __clone()
	{
		return new GeneralModelMetaModel($this->getItem()->copy());
	}

	/**
	 * Returns the id of the item.
	 *
	 * @return int the id.
	 */
	public function getID()
	{
		return $this->getItem()->get('id');
	}

	/**
	 * Retrieve a property from the model (in MetaModel context: an attribute value).
	 *
	 * @see InterfaceGeneralModel::getProperty()
	 *
	 * @param  string     $strPropertyName the property name.
	 *
	 * @return null|mixed the property value or null if not contained.
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

	/**
	 * Fetches all properties as an array.
	 *
	 * @return mixed[string]
	 */
	public function getPropertiesAsArray()
	{
		$arrResult = array();
		foreach (array_keys($this->getItem()->getMetaModel()->getAttributes()) as $strKey)
		{
			$arrResult[$strKey] = $this->getProperty($strKey);
		}
		return $arrResult;
	}

	/**
	 * Sets the id
	 *
	 * @param mixed $mixID the id that shall be set
	 *
	 * @return void
	 */
	public function setID($mixID)
	{
		$this->getItem()->set('id', $mixID);
	}

	/**
	 * Set a property value.
	 * @see InterfaceGeneralModel::setProperty()
	 *
	 * @param String $strPropertyName name of the property to be set.
	 *
	 * @param mixed  $varValue        value to be set.
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

	/**
	 * Set a bunch of properties.
	 *
	 * @param mixed[] $arrProperties the properties to be set.
	 *
	 * @return void
	 */
	public function setPropertiesAsArray($arrProperties)
	{
		foreach ($arrProperties as $strKey => $varValue)
		{
			$this->setProperty($strKey, $varValue);
		}
	}

	/**
	 * determine if there are properties contained within this instance.
	 *
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
