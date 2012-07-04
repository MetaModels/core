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
 * Iterator class for allowing usage of GeneralModelMetaModel
 * in foreach constructs.
 * 
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @package    MetaModels
 * @subpackage Core
 */
class GeneralModelMetaModelIterator implements Iterator
{
	protected $objModel = null;

	private $intPosition = 0;

	protected $arrKeys = array();

	protected function getKeys()
	{
		return $this->arrKeys;
	}

	public function __construct(GeneralModelMetaModel $objModel)
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

	public function rewind()
	{
		$this->position = 0;
	}

	public function current()
	{
		return $this->objModel->getProperty($this->key());
	}

	public function key()
	{
		$arrKeys = $this->getKeys();
		return $arrKeys[$this->position];
	}

	public function next()
	{
		++$this->position;
	}

	public function valid()
	{
		return strlen($this->key()) > 0;
	}
}

?>