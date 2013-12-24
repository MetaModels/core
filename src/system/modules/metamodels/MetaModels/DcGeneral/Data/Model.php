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
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\DcGeneral\Data;

use DcGeneral\Data\ModelInterface;
use DcGeneral\Data\PropertyValueBagInterface;
use DcGeneral\Exception\DcGeneralInvalidArgumentException;
use MetaModels\IItem;

/**
 * Data model class for DC_General <-> MetaModel adaption
 *
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @package    MetaModels
 * @subpackage Core
 */
class Model implements ModelInterface
{

	/**
	 * the MetaModel item accessible via this instance.
	 *
	 * @var IItem
	 */
	protected $objItem = null;

	/**
	 * The meta information the DC and views need to buffer in this model.
	 *
	 * @var array
	 */
	protected $arrMetaInformation = array();

	/**
	 * Return the names of all properties stored within this model.
	 *
	 * @return string[]
	 */
	protected function getPropertyNames()
	{
		$propertyNames = array('id', 'pid', 'tstamp', 'sorting');

		if ($this->getItem()->getMetaModel()->hasVariants())
		{
			$propertyNames[] = 'varbase';
			$propertyNames[] = 'vargroup';
		}

		return array_merge($propertyNames, array_keys($this->getItem()->getMetaModel()->getAttributes()));
	}

	/**
	 * Returns the native IMetaModelItem instance encapsulated within this abstraction.
	 *
	 * @return IItem
	 */
	public function getItem()
	{
		return $this->objItem;
	}

	/**
	 * Create a new instance of this class.
	 *
	 * @param IItem $objItem the item that shall be encapsulated.
	 *
	 * @return \MetaModels\DcGeneral\Data\Model
	 */
	public function __construct($objItem)
	{
		$this->objItem = $objItem;
	}

	/**
	 * Clone this instance and the encapsulated item.
	 * The result is a fresh copy with no id.
	 *
	 * @return \DcGeneral\Data\ModelInterface
	 */
	public function __clone()
	{
		$this->objItem = $this->getItem()->copy();
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

		foreach ($this->getPropertyNames() as $strKey)
		{
			$arrResult[$strKey] = $this->getProperty($strKey);
		}
		return $arrResult;
	}

	/**
	 * Fetch meta information from model.
	 *
	 * @param string $strMetaName the meta information to retrieve.
	 *
	 * @return mixed|null the set meta information or null if undefined.
	 */
	public function getMeta($strMetaName)
	{
		if (array_key_exists($strMetaName, $this->arrMetaInformation))
		{
			return $this->arrMetaInformation[$strMetaName];
		}
		else
		{
			return null;
		}
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
				$varValue = $objAttribute->widgetToValue($varValue, $this->getItem()->get('id'));
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
	 * Set a meta information in this model.
	 *
	 * @param string $strMetaName the meta information name.
	 *
	 * @param mixed $varValue the meta information to store.
	 *
	 * @return void
	 */
	public function setMeta($strMetaName, $varValue)
	{
		$this->arrMetaInformation[$strMetaName] = $varValue;
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
	 * @return \ArrayIterator
	 */
	public function getIterator()
	{
		return new ModelIterator($this);
	}

	/**
	 * Return the data provider name.
	 *
	 * @return string the name of the corresponding data provider.
	 */
	public function getProviderName()
	{
		return $this->getItem()->getMetaModel()->getTableName();
	}

	/**
	 * Read values from a value bag.
	 */
	public function readFromPropertyValueBag(PropertyValueBagInterface $valueBag)
	{
		foreach ($this->getPropertyNames() as $property)
		{
			if (!$valueBag->hasPropertyValue($property))
			{
				continue;
			}

			if ($valueBag->isPropertyValueInvalid($property))
			{
				throw new DcGeneralInvalidArgumentException('The value for property ' . $property . ' is invalid.');
			}

			$this->setProperty($property, $valueBag->getPropertyValue($property));
		}
	}

	/**
	 * Read values from a value bag.
	 */
	public function writeToPropertyValueBag(PropertyValueBagInterface $valueBag)
	{
		foreach ($this->getPropertyNames() as $property)
		{
			if (!$valueBag->hasPropertyValue($property))
			{
				continue;
			}

			$valueBag->setPropertyValue($property, $this->getProperty($property));
		}
	}
}
