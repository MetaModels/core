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
 * Data drive class for DC_General
 * 
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @package    MetaModels
 * @subpackage Core
 */
class DataProviderMetaModel implements InterfaceGeneralData
{
	// Vars --------------------------------------------------------------------

	/**
	 * Name of current table
	 * @var string 
	 */
	protected $strTable = null;

	/**
	 * The MetaModel this DataContainer is working on.
	 * 
	 * @var IMetaModel
	 */
	protected $objMetaModel = NULL;

	// Constructor and co ------------------------------------------------------

	public function __construct(array $arrConfig)
	{
		// Check Vars
		if (!isset($arrConfig["table"]))
		{
			throw new Excpetion("Missing table name.");
		}

		// Init Vars
		$this->strTable = $arrConfig["table"];

		$this->objMetaModel = MetaModelFactory::byTableName($this->strTable);

	}

	// Functions ---------------------------------------------------------------

	public function delete($item)
	{
		// Not impl now
	}

	/**
	 * Save a new Version of a record
	 * 
	 * @param int $intID ID of current record
	 * @param string $strVersion Version number
	 * @return void 
	 */
	public function saveVersion(InterfaceGeneralModel $objModel, $strUsername)
	{
		throw new Exception('Versioning not supported in MetaModels so far.');
	}

	/**
	 * Return a model based of the version information
	 * 
	 * @param mix $mixID The ID of record
	 * @param mix $mixVersion The ID of the Version
	 * 
	 * @return InterfaceGeneralModel 
	 */
	public function getVersion($mixID, $mixVersion)
	{
		throw new Exception('Versioning not supported in MetaModels so far.');
	}


	/**
	 * Set a Version as active. 
	 * 
	 * @param mix $mixID The ID of record
	 * @param mix $mixVersion The ID of the Version
	 */
	public function setVersionActive($mixID, $mixVersion)
	{
		throw new Exception('Versioning not supported in MetaModels so far.');
	}

	/**
	 * Return the active version from a record
	 * 
	 * @param mix $mixID The ID of record
	 * 
	 * @return mix Version ID 
	 */
	public function getActiveVersion($mixID)
	{
		throw new Exception('Versioning not supported in MetaModels so far.');
	}

	public function fetch($intId)
	{
		$objItem = $this->objMetaModel->findById($intId);

		if (!$objItem)
		{
			return null;
		}
		return new DataModelMetaModel($objItem);
	}

	/**
	 * Fetch an empty single record (new item).
	 * 
	 * @return InterfaceGeneralModel
	 */
	public function getEmptyModel()
	{
		$objItem = new MetaModelItem($this->objMetaModel, array());
		return new DataModelMetaModel($objItem);
	}

	public function getEmptyCollection()
	{
		return new GeneralCollectionDefault();
	}

	protected function prepareFilter($arrFilter)
	{
		if ($arrFilter)
		{
			$arrFilterFields = array_keys($arrFilter);
		} else {
			$arrFilterFields = array();
		}
		$objFilter = $this->objMetaModel->prepareFilter($arrFilterFields, $arrFilter);
		return $objFilter;
	}

	public function fetchAll($blnIdOnly = false, $intStart = 0, $intAmount = 0, $arrFilter = array(), $arrSorting = array())
	{
		$objFilter = $this->prepareFilter($arrFilter);
		$objItems = $this->objMetaModel->findByFilter($objFilter, ($arrSorting?$arrSorting[0]:''), $intStart, $intAmount);
		// TODO: optimize by implementing a getIdsFromFilter
		if ($blnIdOnly)
		{
			$arrResult = array();
			foreach ($objItems as $objItem)
			{
				$arrResult[] = $objItem->get('id');
			}
			return $arrResult;
		} else {
			$objResultCollection = $this->getEmptyCollection();
			foreach ($objItems as $objItem)
			{
				$objResultCollection->push(new DataModelMetaModel($objItem));
			}
			return $objResultCollection;
		}
	}

	public function fetchEach($ids)
	{
		// no op
	}

	public function getCount($arrFilter = array())
	{
		$objFilter = $this->prepareFilter($arrFilter);
		return $this->objMetaModel->getCount($objFilter);
	}

	public function getVersions($intID)
	{
		// no version support on MetaModels so far, sorry.
		return null;
	}

	public function isUniqueValue($strField, $varNew)
	{
		// TODO: compile filter for attribute and value here and find all other items with this value, if only the current item has this value, return true.
		return false;
	}

	public function resetFallback($strField)
	{
		// TODO: CS: I do not understand the docs in the base implementation, therefore I do not know how to implement this here. :/
	}

	public function save(InterfaceGeneralModel $objItem, $recursive = false)
	{
		if ($objItem instanceof DataModel_MetaModel)
		{
			$objItem->getItem()->save();
			return;
		}
		throw new Exception('ERROR: incompatible object passed to DataProvider_MetaModel::save()');
	}

	public function saveEach(InterfaceGeneralCollection $objItems, $recursive = false)
	{
		foreach ($objItems as $key => $value)
		{
			$this->save($value);
		}
	}

	public function setVersion($intID, $strVersion)
	{
		// no version support on MetaModels so far, sorry.
	}

	public function fieldExists($strField)
	{
		if ($this->objMetaModel->getAttribute($strField) != null)
		{
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Check if two models have the same properties
	 * 
	 * @param InterfaceGeneralModel $objModel1
	 * @param InterfaceGeneralModel $objModel2
	 * 
	 * return boolean True - If both models are same, false if not
	 */
	public function sameModels($objModel1 , $objModel2)
	{
		$objNative1 = $objModel1->getItem();
		$objNative2 = $objModel2->getItem();
		if ($objNative1->getMetaModel() != $objNative2->getMetaModel())
		{
			return false;
		}
		foreach ($objNative1->getMetaModel()->getAttributes() as $objAttribute)
		{
			if ($objNative1->get($objAttribute->getColName()) != $objNative2->get($objAttribute->getColName()))
			{
				return false;
			}
		}
		return true;
	}
}

?>
