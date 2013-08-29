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


/**
 * Iterator class for allowing usage of MetaModels\DcGeneral\Data\Model
 * in foreach constructs.
 *
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @package    MetaModels
 * @subpackage Core
 */
class ModelIterator implements \Iterator
{
	/**
	 * the model to iterate over
	 *
	 * @var Model
	 */
	protected $objModel = null;

	/**
	 * the current position of the index.
	 *
	 * @var int
	 */
	private $intPosition = 0;

	/**
	 * all property names.
	 *
	 * @var string[]
	 */
	protected $arrKeys = array();

	/**
	 * Returns an array containing all property names.
	 *
	 * @return string[] all property names.
	 */
	protected function getKeys()
	{
		return $this->arrKeys;
	}

	/**
	 * Create a new instance for the given model.
	 *
	 * @param Model $objModel the model to iterate over.
	 *
	 * @return ModelIterator the new instance.
	 */
	public function __construct(Model $objModel)
	{
		$this->intPosition = 0;
		$this->objModel = $objModel;

		$objMetaModel = $this->objModel->getItem()->getMetaModel();

		$arrKeys = array();
		if ($objMetaModel->hasVariants())
		{
			$arrKeys[] = 'varbase';
			$arrKeys[] = 'vargroup';
		}
		$this->arrKeys = array_merge($arrKeys, array_keys($objMetaModel->getAttributes()));
	}

	/**
	 * Reset the iterator to the beginning.
	 *
	 * @return void
	 */
	public function rewind()
	{
		$this->intPosition = 0;
	}

	/**
	 * return the current value.
	 *
	 * @return mixed
	 */
	public function current()
	{
		return $this->objModel->getProperty($this->key());
	}

	/**
	 * return the current property name.
	 *
	 * @return string
	 */
	public function key()
	{
		$arrKeys = $this->getKeys();
		return $arrKeys[$this->intPosition];
	}

	/**
	 * Move the iterator one step forward.
	 *
	 * @return void
	 */
	public function next()
	{
		++$this->intPosition;
	}

	/**
	 * determine if the current index is still valid.
	 *
	 * @return bool
	 */
	public function valid()
	{
		return strlen($this->key()) > 0;
	}
}
