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

/**
 * Data driver class for DC_General
 *
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @package    MetaModels
 * @subpackage Core
 */
class GeneralDataMetaModel implements InterfaceGeneralData, InterfaceGeneralDataMultiLanguage
{
	/**
	 * Name of current table.
	 * @var string
	 */
	protected $strTable = null;

	/**
	 * The MetaModel this DataContainer is working on.
	 *
	 * @var IMetaModel
	 */
	protected $objMetaModel = null;

	/**
	 * The current active language.
	 *
	 * @var string
	 */
	protected $strCurrentLanguage;

	/**
	 * Delete an item.
	 *
	 * The given value may be either integer, string or an instance of InterfaceGeneralModel
	 *
	 * @param mixed $varItem Id or the model itself, to delete.
	 *
	 * @return void
	 *
	 * @throws Exception when an unusable object has been passed.
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
	 * @return void
	 *
	 * @throws Exception As this is currently unimplemented, an Exception is thrown.
	 */
	public function saveVersion(InterfaceGeneralModel $objModel, $strUsername)
	{
		throw new Exception('Versioning not supported in MetaModels so far.');
	}

	/**
	 * Return a model based of the version information.
	 *
	 * @param mixed $mixID      The ID of record.
	 *
	 * @param mixed $mixVersion The ID of the version.
	 *
	 * @return null|InterfaceGeneralModel the model or null if not found.
	 *
	 * @throws Exception As this is currently unimplemented, an Exception is thrown.
	 */
	public function getVersion($mixID, $mixVersion)
	{
		throw new Exception('Versioning not supported in MetaModels so far.');
	}

	/**
	 * Set a version as active.
	 *
	 * @param mixed $mixID      The ID of record.
	 *
	 * @param mixed $mixVersion The ID of the version.
	 *
	 * @return void
	 *
	 * @throws Exception As this is currently unimplemented, an Exception is thrown.
	 */
	public function setVersionActive($mixID, $mixVersion)
	{
		throw new Exception('Versioning not supported in MetaModels so far.');
	}

	/**
	 * Return the active version from a record.
	 *
	 * @param mixed $mixID The ID of the record.
	 *
	 * @return mixed version ID
	 *
	 * @throws Exception As this is currently unimplemented, an Exception is thrown.
	 */
	public function getActiveVersion($mixID)
	{
		throw new Exception('Versioning not supported in MetaModels so far.');
	}

	/**
	 * Fetch a single or first record by id or filter.
	 *
	 * If the model shall be retrieved by id, use $objConfig->setId() to populate the config with an Id.
	 *
	 * If the model shall be retrieved by filter, use $objConfig->setFilter() to populate the config with a filter.
	 *
	 * @param InterfaceGeneralDataConfig $objConfig
	 *
	 * @return InterfaceGeneralModel
	 */
	public function fetch(InterfaceGeneralDataConfig $objConfig)
	{
		// TODO: implement find first item by filter here.

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
	 * Set base config with source and other necessary parameter.
	 *
	 * @param array $arrConfig The configuration to use.
	 *
	 * @return void
	 *
	 * @throws Exception when no source has been defined.
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
	 * Return empty config object.
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
	 * @param array            $arrFilter The filter to be combined into the passed filter object.
	 *
	 * @param IMetaModelFilter $objFilter The filter object where the rules shall get appended to.
	 *
	 * @return void.
	 *
	 * @throws Exception When an improper filter condition is encountered, an exception is thrown.
	 */
	protected function calculateSubfilter($arrFilter, IMetaModelFilter $objFilter)
	{
		if (!is_array($arrFilter))
		{
			throw new Exception('Error Processing subfilter: ' . var_export($arrFilter, true), 1);
		}

		$objAttribute = null;
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

				foreach ($arrFilter['childs'] as $arrChild)
				{
					$objSubFilter = new MetaModelFilter($this->objMetaModel);

					$objFilterRule->addChild($objSubFilter);

					$this->calculateSubfilter($arrChild, $objSubFilter);
				}
				break;

			case '=':
			case '>':
			case '<':
				$objFilterRule = null;
				if ($objAttribute)
				{
					switch ($arrFilter['operation'])
					{
						case '=':
							$objFilterRule = new MetaModelFilterRuleSearchAttribute(
								$objAttribute,
								$arrFilter['value'],
								$this->objMetaModel->getAvailableLanguages()
							);
							break;

						case '>':
							$objFilterRule = new MetaModelFilterRuleFilterAttributeGreaterThan(
								$objAttribute,
								$arrFilter['value']
							);
							break;

						case '<':
							$objFilterRule = new MetaModelFilterRuleFilterAttributeLessThan(
								$objAttribute,
								$arrFilter['value']
							);
							break;
					}
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

			case 'IN':
				// rewrite the IN operation to a rephrased term: "(x=a) OR (x=b) OR ..."
				$arrSubRules = array();
				foreach ($arrFilter['value'] as $varValue)
				{
					$arrSubRules[] = array(
						'property'  => $arrFilter['property'],
						'operation' => '=',
						'value'     => $varValue
					);
				}
				$this->calculateSubfilter(array(
					'operation' => 'OR',
					'childs'    => $arrSubRules
				), $objFilter);
				break;

			case 'LIKE':
				$objFilterRule = null;
				if ($objAttribute)
				{
					$objFilterRule = new MetaModelFilterRuleSearchAttribute(
						$objAttribute,
						$arrFilter['value'],
						$this->objMetaModel->getAvailableLanguages()
					);
				}
				else if(Database::getInstance()->fieldExists($arrFilter['property'], $this->objMetaModel->getTableName()))
				{
					// system column?
					$objFilterRule = new MetaModelFilterRuleSimpleQuery(sprintf(
						'SELECT id FROM %s WHERE %s LIKE ?',
						$this->objMetaModel->getTableName(),
						$arrFilter['property']
					),
					array($arrFilter['value']));
				}
				if (!$objFilterRule)
				{
					throw new Exception('Error processing filter array - unknown property ' . var_export($arrFilter['property'], true), 1);
				}
				$objFilter->addFilterRule($objFilterRule);
				break;

			default:
				throw new Exception('Error processing filter array - unknown operation ' . var_export($arrFilter, true), 1);
		}
	}

	/**
	 * Prepare a filter and return it.
	 *
	 * @param array $arrFilter The values to be applied in attribute name => value style.
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
	 * Fetch all records (optional filtered, sorted and limited).
	 *
	 * @param InterfaceGeneralDataConfig $objConfig The configuration to be applied.
	 *
	 * @return InterfaceGeneralCollection
	 */
	public function fetchAll(InterfaceGeneralDataConfig $objConfig)
	{
		$strBackupLanguage = '';
		if ($this->strCurrentLanguage != '')
		{
			$strBackupLanguage = $GLOBALS['TL_LANGUAGE'];
			$GLOBALS['TL_LANGUAGE'] = $this->strCurrentLanguage;
		}

		$varResult = null;

		$arrSorting = $objConfig->getSorting();

		$strSortBy = '';
		$strSortDir = '';
		if ($arrSorting)
		{
			list($strSortBy, $strSortDir) = each($arrSorting);
		}
		if (!$strSortDir)
		{
			$strSortDir = DCGE::MODEL_SORTING_ASC;
		}

		$objFilter = $this->prepareFilter($objConfig->getFilter());
		if ($objConfig->getIdOnly())
		{
			$varResult = $this->objMetaModel->getIdsFromFilter($objFilter, ($strSortBy?$strSortBy:''), $objConfig->getStart(), $objConfig->getAmount(), ($strSortBy?$strSortDir:''));
		} else {
			$objItems = $this->objMetaModel->findByFilter($objFilter, ($strSortBy?$strSortBy:''), $objConfig->getStart(), $objConfig->getAmount(), ($strSortBy?$strSortDir:''));

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
	 * Retrieve all unique values for the given property.
	 *
	 * The result set will be an array containing all unique values contained in the MetaModel for the defined
	 * attribute in the configuration.
	 *
	 * Note: this only re-ensembles really used values for at least one data set.
	 *
	 * The only information being interpreted from the passed config object is the first property to fetch and the
	 * filter definition.
	 *
	 * @param InterfaceGeneralDataConfig $objConfig   The filter config options.
	 *
	 * @return InterfaceGeneralCollection
	 *
	 * @throws Exception if improper values have been passed (i.e. not exactly one field requested).
	 */
	public function getFilterOptions(InterfaceGeneralDataConfig $objConfig)
	{
		$arrProperties = $objConfig->getFields();
		if (count($arrProperties) <> 1)
		{
			throw new Exception('objConfig must contain exactly one property to be retrieved.');
		}

		$objFilter = $this->prepareFilter($objConfig->getFilter());

		$arrValues = $this->objMetaModel
			->getAttribute($arrProperties[0])
			->getFilterOptions($objFilter->getMatchingIds(), true);

		$objCollection = $this->getEmptyCollection();
		foreach ($arrValues as $strValue)
		{
			$objNewModel = $this->getEmptyModel();
			$objNewModel->setProperty($arrProperties[0], $strValue);
			$objCollection->add($objNewModel);
		}

		return $objCollection;
	}

	/**
	 * Return the amount of total items (filtering may be used in the config).
	 *
	 * @param InterfaceGeneralDataConfig $objConfig
	 *
	 * @return int
	 */
	public function getCount(InterfaceGeneralDataConfig $objConfig)
	{
		$objFilter = $this->prepareFilter($objConfig->getFilter());
		return $this->objMetaModel->getCount($objFilter);
	}

	/**
	 * Return a list with all versions for the model with the given Id.
	 *
	 * @param mixed   $mixID         The ID of the row.
	 *
	 * @param boolean $blnOnlyActive If true, only active versions will get returned, if false all version will get
	 *                               returned.
	 *
	 * @return InterfaceGeneralCollection
	 */
	public function getVersions($mixID, $blnOnlyActive = false)
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

		$objAttribute = $this->objMetaModel->getAttribute($strField);
		if ($objAttribute)
		{
			$this->calculateSubfilter(array(
				'operation' => '=',
				'property' => $objAttribute->getColName(),
				'value' => $varNew
			), $objFilter);
			$arrIds = $objFilter->getMatchingIds();
			return (count($arrIds) == 0) || ($arrIds == array($intId));
		}

		return false;
	}

	/**
	 * Reset the fallback field.
	 *
	 * This clears the given property in all items in the data provider to an empty value.
	 *
	 * @param string $strField The field to reset.
	 *
	 * @return void
	 */
	public function resetFallback($strField)
	{
		// TODO: Unimplemented so far.
	}

	/**
	 * Save an item to the data provider.
	 *
	 * If the item does not have an Id yet, the save operation will add it as a new row to the database and
	 * populate the Id of the model accordingly.
	 *
	 * @param InterfaceGeneralModel $objItem   The model to save back.
	 *
	 * @return InterfaceGeneralModel The passed model.
	 *
	 * @throws Exception When an incompatible item was passed, an Exception is being thrown.
	 */
	public function save(InterfaceGeneralModel $objItem)
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
	 * Save a collection of items to the data provider.
	 *
	 * @param InterfaceGeneralCollection $objItems The collection containing all items to be saved.
	 *
	 * @return void
	 *
	 * @throws Exception when an incompatible item was passed.
	 */
	public function saveEach(InterfaceGeneralCollection $objItems)
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
	 * @return boolean
	 */
	public function fieldExists($strField)
	{
		return !!(in_array($strField, array('id', 'pid', 'tstamp', 'sorting')) || $this->objMetaModel->getAttribute($strField));
	}

	/**
	 * Check if two models have the same values in all properties.
	 *
	 * @param InterfaceGeneralModel $objModel1 The first model to compare.
	 *
	 * @param InterfaceGeneralModel $objModel2 The second model to compare.
	 *
	 * @return boolean True - If both models are same, false if not.
	 */
	public function sameModels($objModel1 , $objModel2)
	{
		/**
		 * These must be:
		 * @var GeneralModelMetaModel $objModel1
		 * @var GeneralModelMetaModel $objModel2
		 */
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
	 * Get all available languages of a certain record.
	 *
	 * @param mixed $mixID The ID of the record to retrieve.
	 *
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
		return null;
	}

	/**
	 * Get the fallback language of a certain record.
	 *
	 * @param mixed $mixID The ID of the record to retrieve.
	 *
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
		return null;
	}

	/**
	 * Set the current working language for the whole data provider.
	 *
	 * @param string $strLanguage The new language, use short tag "2 chars like de, fr etc."
	 *
	 * @return void
	 */
	public function setCurrentLanguage($strLanguage)
	{
		$this->strCurrentLanguage = $strLanguage;
	}

	/**
	 * Get the current working language.
	 *
	 * @return string Short tag for the current working language like de or fr etc.
	 */
	public function getCurrentLanguage()
	{
		return $this->strCurrentLanguage;
	}
}
