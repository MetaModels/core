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
 * Data driver class for DC_General
 *
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @package    MetaModels
 * @subpackage Core
 */
class GeneralDataMetaModel implements InterfaceGeneralData, InterfaceGeneralDataML
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

	// Functions ---------------------------------------------------------------

	/**
	 * Delete an item.
	 *
	 * @param int|string|InterfaceGeneralModel $item Id or the object itself, to delete
	 *
	 *
	 * @return void
	 */
	public function delete($varItem)
	{
		$objModelItem = null;
		// determine the id
		if (is_object($varItem) && ($varItem instanceof GeneralModelMetaModel))
		{
			$objModelItem = $varItem->getItem();
		} else {
			$objModelItem = $this->objMetaModel->findById($varItem);
		}
		if ($objModelItem)
		{
			$this->objMetaModel->delete($objModelItem);
		}
	}

	/**
	 * Save a new Version of a record
	 *
	 * @param InterfaceGeneralModel $objModel    the model to be saved.
	 *
	 * @param string                $strUsername the username that creates the new version.
	 *
	 *
	 * @return void
	 */
	public function saveVersion(InterfaceGeneralModel $objModel, $strUsername)
	{
		throw new Exception('Versioning not supported in MetaModels so far.');
	}

	/**
	 * Return a model based of the version information
	 *
	 * @param mixed $mixID      The ID of record
	 *
	 * @param mixed $mixVersion The ID of the version
	 *
	 *
	 * @return null|InterfaceGeneralModel the model or null if not found.
	 */
	public function getVersion($mixID, $mixVersion)
	{
		throw new Exception('Versioning not supported in MetaModels so far.');
	}

	/**
	 * Set a version as active.
	 *
	 * @param mix $mixID      The ID of record
	 *
	 * @param mix $mixVersion The ID of the version
	 *
	 *
	 * @return void
	 */
	public function setVersionActive($mixID, $mixVersion)
	{
		throw new Exception('Versioning not supported in MetaModels so far.');
	}

	/**
	 * Return the active version from a record
	 *
	 * @param mixed $mixID The ID of the record
	 *
	 *
	 * @return mixed version ID
	 */
	public function getActiveVersion($mixID)
	{
		throw new Exception('Versioning not supported in MetaModels so far.');
	}

	/**
	 * Fetch a single record by id.
	 *
	 * @param GeneralDataConfigDefault $objConfig
	 *
	 * @return InterfaceGeneralModel
	 */
	public function fetch(GeneralDataConfigDefault $objConfig)
	{
		$strBackupLanguage = '';
		if ($this->strCurrentLanguage != '')
		{
			$strBackupLanguage = $GLOBALS['TL_LANGUAGE'];
			$GLOBALS['TL_LANGUAGE'] = $this->strCurrentLanguage;
		}

		$objItem = $this->objMetaModel->findById($objConfig->getId());

		if ($strBackupLanguage != '')
		{
			$GLOBALS['TL_LANGUAGE'] = $strBackupLanguage;
		}

		if (!$objItem)
		{
			return null;
		}
		return new GeneralModelMetaModel($objItem);
	}

	/**
	 * Set base config with source and other neccesary prameter
	 *
	 * @param array $arrConfig the configuration array.
	 *
	 *
	 * @return void
	 *
	 * @throws Exception
	 */
	public function setBaseConfig(array $arrConfig)
	{
		// Check Vars
		if (!$arrConfig["source"])
		{
			throw new Exception("Missing table name.");
		}

		// Init Vars
		$this->strTable = $arrConfig["source"];

		$this->objMetaModel = MetaModelFactory::byTableName($this->strTable);
	}

	/**
	 * Return empty config object
	 *
	 * @return InterfaceGeneralDataConfig
	 */
	public function getEmptyConfig()
	{
		return GeneralDataConfigDefault::init();
	}

	/**
	 * Fetch an empty single record (new item).
	 *
	 * @return InterfaceGeneralModel
	 */
	public function getEmptyModel()
	{
		$objItem = new MetaModelItem($this->objMetaModel, array());
		return new GeneralModelMetaModel($objItem);
	}

	/**
	 * Fetch an empty collection.
	 *
	 * @return InterfaceGeneralCollection
	 */
	public function getEmptyCollection()
	{
		return new GeneralCollectionDefault();
	}


	/**
	 * Combine a filter in standard filter array notation.
	 * Supported operations are:
	 * operation      needed arguments     argument type.
	 * AND
	 *                'childs'             array
	 * OR
	 *                'childs'             array
	 * =
	 *                'property'           string (the name of a property)
	 *                'value'              literal
	 * >
	 *                'property'           string (the name of a property)
	 *                'value'              literal
	 * <
	 *                'property'           string (the name of a property)
	 *                'value'              literal
	 * IN
	 *                'property'           string (the name of a property)
	 *                'values'             array of literal
	 *
	 * @param array $arrFilters the filter to be combined to a valid SQL filter query.
	 *
	 * @return string the combined WHERE clause.
	 */
	protected function calculateSubfilter($arrFilter, IMetaModelFilter $objFilter)
	{
		if (!is_array($arrFilter))
		{
			throw new Exception('Error Processing subfilter: ' . var_export($arrFilter, true), 1);
		}

		$objAttribute = NULL;
		if ($arrFilter['property'])
		{
			$objAttribute = $this->objMetaModel->getAttribute($arrFilter['property']);
		}

		switch ($arrFilter['operation'])
		{
			case 'AND':
			case 'OR':
				if ($arrFilter['operation'] == 'AND')
				{
					$objFilterRule = new MetaModelFilterRuleAND();
				} else {
					$objFilterRule = new MetaModelFilterRuleOR();
				}
				$objFilter->addFilterRule($objFilterRule);

				$objSubFilter = new MetaModelFilter($this->objMetaModel);

				$objFilterRule->addChild($objSubFilter);

				foreach ($arrFilter['childs'] as $arrChild)
				{
					$this->calculateSubfilter($arrChild, $objSubFilter);
				}
				break;

			case '=':
				$objFilterRule = NULL;
				if ($objAttribute)
				{
					$objFilterRule = $objAttribute->parseFilterUrl(array($objAttribute->getColName() => $arrFilter['value']));
				} else if(Database::getInstance()->fieldExists($arrFilter['property'], $this->objMetaModel->getTableName())) {
					// system column?
					$objFilterRule = new MetaModelFilterRuleSimpleQuery(sprintf(
						'SELECT id FROM %s WHERE %s %s %s',
						$this->objMetaModel->getTableName(),
						$arrFilter['property'],
						$arrFilter['operation'],
						$arrFilter['value']
					));
				}
				if (!$objFilterRule)
				{
					throw new Exception('Error processing filter array - unknown property ' . var_export($arrFilter['property'], true), 1);
				}
				$objFilter->addFilterRule($objFilterRule);
				break;
			case '>':
			case '<':
				break;

			case 'IN':
				break;

			default:
				throw new Exception('Error processing filter array - unknown operation ' . var_export($arrFilter, true), 1);
		}
	}

	/**
	 * Prepare a filter and return it.
	 *
	 * @param array $arrFilter the values to be applied in attribute name => value style.
	 *
	 *
	 * @return IMetaModelFilter
	 */
	protected function prepareFilter($arrFilter = array())
	{
		$objFilter = $this->objMetaModel->getEmptyFilter();

		if ($arrFilter)
		{
			$this->calculateSubfilter(
				array
				(
					'operation' => 'AND',
					'childs' => $arrFilter
				),
				$objFilter
			);
		}
		return $objFilter;
	}

	/**
	 * Fetch all records (optional limited).
	 *
	 * @param GeneralDataConfigDefault $objConfig the configuration object to use.
	 *
	 *
	 * @return InterfaceGeneralCollection collection containing all matching items.
	 */
	public function fetchAll(GeneralDataConfigDefault $objConfig)
	{
		$strBackupLanguage = '';
		if ($this->strCurrentLanguage != '')
		{
			$strBackupLanguage = $GLOBALS['TL_LANGUAGE'];
			$GLOBALS['TL_LANGUAGE'] = $this->strCurrentLanguage;
		}

		$varResult = NULL;

		$arrSorting = $objConfig->getSorting();
		$objFilter = $this->prepareFilter($objConfig->getFilter());
		if ($objConfig->getIdOnly())
		{
			$varResult = $this->objMetaModel->getIdsFromFilter($objFilter, ($arrSorting?$arrSorting[0]:''), $objConfig->getStart(), $objConfig->getAmount());
		} else {
			$objItems = $this->objMetaModel->findByFilter($objFilter, ($arrSorting?$arrSorting[0]:''), $objConfig->getStart(), $objConfig->getAmount());

			$objResultCollection = $this->getEmptyCollection();
			foreach ($objItems as $objItem)
			{
				$objResultCollection->push(new GeneralModelMetaModel($objItem));
			}
			$varResult = $objResultCollection;
		}

		if ($strBackupLanguage != '')
		{
			$GLOBALS['TL_LANGUAGE'] = $strBackupLanguage;
		}
		return $varResult;
	}

	/**
	 * Fetch multiple records by ids.
	 *
	 * @param GeneralDataConfigDefault $objConfig the configuration object to use.
	 *
	 *
	 * @return InterfaceGeneralCollection collection containing all matching items.
	 */
	public function fetchEach(GeneralDataConfigDefault $objConfig)
	{
		$strBackupLanguage = '';
		if ($this->strCurrentLanguage != '')
		{
			$strBackupLanguage = $GLOBALS['TL_LANGUAGE'];
			$GLOBALS['TL_LANGUAGE'] = $this->strCurrentLanguage;
		}

		$objFilter = $this->prepareFilter();
		// filter for the desired items only.
		$objFilter->addFilterRule(new MetaModelFilterRuleStaticIdList($objConfig->getIds()));
		$objItems = $this->objMetaModel->findByFilter($objFilter);
		$objResultCollection = $this->getEmptyCollection();
		foreach ($objItems as $objItem)
		{
			$objResultCollection->push(new GeneralModelMetaModel($objItem));
		}

		if ($strBackupLanguage != '')
		{
			$GLOBALS['TL_LANGUAGE'] = $strBackupLanguage;
		}

		return $objResultCollection;
	}

	/**
	 * Return the amount of total items.
	 *
	 * @param GeneralDataConfigDefault $objConfig the configuration object to use.
	 *
	 * @return int the amount.
	 */
	public function getCount(GeneralDataConfigDefault $objConfig)
	{
		$objFilter = $this->prepareFilter($objConfig->getFilter());
		return $this->objMetaModel->getCount($objFilter);
	}

	/**
	 * Return a list with all versions for this row
	 *
	 * @param mixed $mixID The ID of record
	 *
	 *
	 * @return InterfaceGeneralCollection all versions of the given item.
	 */
	public function getVersions($mixID)
	{
		// no version support on MetaModels so far, sorry.
		return null;
	}

	/**
	 * Determine if a given value is unique within the metamodel.
	 *
	 * @param string $strField the attribute name.
	 *
	 * @param mixed  $varNew    the value that shall be checked.
	 *
	 * @param int    $intId    the (optional) id of the item currently in scope - pass null for new items.
	 *
	 * @return bool true if the values is not yet contained within the table, false otherwise.
	 */
	public function isUniqueValue($strField, $varNew, $intId = null)
	{
		$objFilter = $this->objMetaModel->getEmptyFilter();

		$objAttribute = $this->getMetaModel()->getAttribute($strField);
		if ($objAttribute)
		{
			$objFilterRule = $objAttribute->parseFilterUrl(array($objAttribute->getColName() => $varNew));
			if ($objFilterRule)
			{
				$objFilter->addFilterRule($objFilterRule);
				$arrIds = $objFilter->getMatchingIds();
				return (count($arrIds) == 0) || ($arrIds == array($intId));
			}
		}

		return false;
	}

	/**
	 * Vague definition, therefore currently unimplemented.
	 */
	public function resetFallback($strField)
	{
		// TODO: CS: I do not understand the docs in the base implementation, therefore I do not know how to implement this here. :/
	}

	/**
	 * save the given item to the database.
	 *
	 * @param InterfaceGeneralModel $objItem   the item to save.
	 *
	 * @param bool                  $recursive if the model contains submodels, define if those shall be saved as well (defaults to false).
	 *
	 * @return void
	 *
	 * @throws Exception when an incompatible item was passed.
	 */
	public function save(InterfaceGeneralModel $objItem, $recursive = false)
	{
		if ($objItem instanceof GeneralModelMetaModel)
		{
			$strBackupLanguage = '';
			if ($this->strCurrentLanguage != '')
			{
				$strBackupLanguage = $GLOBALS['TL_LANGUAGE'];
				$GLOBALS['TL_LANGUAGE'] = $this->strCurrentLanguage;
			}

			$objItem->getItem()->save();

			if ($strBackupLanguage != '')
			{
				$GLOBALS['TL_LANGUAGE'] = $strBackupLanguage;
			}

			return;
		}
		throw new Exception('ERROR: incompatible object passed to GeneralDataMetaModel::save()');
	}

	/**
	 * save the given items to the database.
	 *
	 * @param InterfaceGeneralCollection $objItems  the item to save.
	 *
	 * @param bool                       $recursive if the model contains submodels, define if those shall be saved as well (defaults to false).
	 *
	 *
	 * @return void
	 *
	 * @throws Exception when an incompatible item was passed.
	 */
	public function saveEach(InterfaceGeneralCollection $objItems, $recursive = false)
	{
		foreach ($objItems as $key => $value)
		{
			$this->save($value);
		}
	}

	/**
	 * Check if the attribute exists in the table and holds a value.
	 *
	 * @param string $strField the name of the attribute that shall be tested.
	 *
	 *
	 * @return boolean
	 */
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
	 *
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

	/**
	 * Fetch a variant of a single record by id.
	 *
	 * @param GeneralDataConfigDefault $objConfig
	 *
	 * @return InterfaceGeneralModel
	 */
	public function createVariant(GeneralDataConfigDefault $objConfig)
	{
		$objItem = $this->objMetaModel->findById($objConfig->getId())->varCopy();

		if (!$objItem)
		{
			return null;
		}
		return new GeneralModelMetaModel($objItem);
	}


	/**
	 * the currently active language.
	 *
	 * @var string
	 */
	protected $strCurrentLanguage;

	/**
	 * Get all avaidable languages for a special record.
	 *
	 * @param mixed $mixID The ID of record
	 * @return InterfaceGeneralCollection
	 */
	public function getLanguages($mixID)
	{
		$objCollection = $this->getEmptyCollection();

		if ($this->objMetaModel->isTranslated())
		{
			foreach ($this->objMetaModel->getAvailableLanguages() as $strLangCode)
			{
				$objModel = new GeneralModelDefault();
				$objModel->setID($strLangCode);
				$objModel->setProperty("name", $GLOBALS['TL_LANG']['LNG'][$strLangCode]);
				$objModel->setProperty("active", ($this->getCurrentLanguage() == $strLangCode));
				$objCollection->add($objModel);
			}
			if ($objCollection->length() > 0)
			{
				return $objCollection;
			}
		}
		return NULL;
	}

	/**
	 * Get the fallback language
	 *
	 * @param mixed $mixID The ID of record
	 * @return InterfaceGeneralModel
	 */
	public function getFallbackLanguage($mixID)
	{
		if ($this->objMetaModel->isTranslated())
		{
			$objModel = new GeneralModelDefault();
			$strLangCode = $this->objMetaModel->getFallbackLanguage();
			$objModel->setID($strLangCode);
			$objModel->setProperty("name", $GLOBALS['TL_LANG']['LNG'][$strLangCode]);
			$objModel->setProperty("active", ($this->getCurrentLanguage() == $strLangCode));
			return $objModel;
		}
		return NULL;
	}

	/**
	 * Set the working language for the whole dataprovider.
	 *
	 * @param $strLanguage The new language, use hort tag "2 chars like de, fr etc."
	 * @return void
	 */
	public function setCurrentLanguage($strLanguage)
	{
		$this->strCurrentLanguage = $strLanguage;
	}

	/**
	 * Get the working language
	 *
	 * return String Short tag for the current working language like de or fr etc.
	 */
	public function getCurrentLanguage()
	{
		return $this->strCurrentLanguage;
	}
}

?>
