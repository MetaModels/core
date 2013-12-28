<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\DcGeneral\Data;

use DcGeneral\Data\DCGE;
use DcGeneral\Data\MultiLanguageDriverInterface;
use DcGeneral\Data\ModelInterface;
use DcGeneral\Data\ConfigInterface;
use DcGeneral\Data\CollectionInterface;
use DcGeneral\Data\DefaultConfig;
use DcGeneral\Data\DefaultCollection;
use DcGeneral\Data\DefaultModel;

use MetaModels\Factory as ModelFactory;
use MetaModels\IMetaModel;
use MetaModels\Item;
use MetaModels\Filter\IFilter;
use MetaModels\Filter\Rules\Condition\ConditionAnd;
use MetaModels\Filter\Rules\Condition\ConditionOr;
use MetaModels\Filter\Rules\Comparing\GreaterThan;
use MetaModels\Filter\Rules\Comparing\LessThan;
use MetaModels\Filter\Rules\SearchAttribute;
use MetaModels\Filter\Filter;
use MetaModels\Filter\Rules\SimpleQuery;

/**
 * Data driver class for DC_General
 *
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @package    MetaModels
 * @subpackage Core
 */
class Driver implements MultiLanguageDriverInterface
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
	 * The given value may be either integer, string or an instance of Model
	 *
	 * @param mixed $varItem Id or the model itself, to delete.
	 *
	 * @return void
	 *
	 * @throws \RuntimeException when an unusable object has been passed.
	 */
	public function delete($varItem)
	{
		$objModelItem = null;
		// determine the id
		if (is_object($varItem) && ($varItem instanceof Model))
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
	 * @param \DcGeneral\Data\ModelInterface $objModel    the model to be saved.
	 *
	 * @param string                $strUsername the username that creates the new version.
	 *
	 * @return void
	 *
	 * @throws \RuntimeException As this is currently unimplemented, an Exception is thrown.
	 */
	public function saveVersion(ModelInterface $objModel, $strUsername)
	{
		throw new \RuntimeException('Versioning not supported in MetaModels so far.');
	}

	/**
	 * Return a model based of the version information.
	 *
	 * @param mixed $mixID      The ID of record.
	 *
	 * @param mixed $mixVersion The ID of the version.
	 *
	 * @return null|\DcGeneral\Data\ModelInterface the model or null if not found.
	 *
	 * @throws \RuntimeException As this is currently unimplemented, an Exception is thrown.
	 */
	public function getVersion($mixID, $mixVersion)
	{
		throw new \RuntimeException('Versioning not supported in MetaModels so far.');
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
	 * @throws \RuntimeException As this is currently unimplemented, an Exception is thrown.
	 */
	public function setVersionActive($mixID, $mixVersion)
	{
		throw new \RuntimeException('Versioning not supported in MetaModels so far.');
	}

	/**
	 * Return the active version from a record.
	 *
	 * @param mixed $mixID The ID of the record.
	 *
	 * @return mixed version ID
	 *
	 * @throws \RuntimeException As this is currently unimplemented, an Exception is thrown.
	 */
	public function getActiveVersion($mixID)
	{
		throw new \RuntimeException('Versioning not supported in MetaModels so far.');
	}

	/**
	 * Fetch a single or first record by id or filter.
	 *
	 * If the model shall be retrieved by id, use $objConfig->setId() to populate the config with an Id.
	 *
	 * If the model shall be retrieved by filter, use $objConfig->setFilter() to populate the config with a filter.
	 *
	 * @param \DcGeneral\Data\ConfigInterface $objConfig
	 *
	 * @return \DcGeneral\Data\ModelInterface
	 */
	public function fetch(ConfigInterface $objConfig)
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
		return new Model($objItem);
	}

	/**
	 * Set base config with source and other necessary parameter.
	 *
	 * @param array $arrConfig The configuration to use.
	 *
	 * @return void
	 *
	 * @throws \RuntimeException when no source has been defined.
	 */
	public function setBaseConfig(array $arrConfig)
	{
		// Check Vars
		if (!$arrConfig["source"])
		{
			throw new \RuntimeException("Missing table name.");
		}

		// Init Vars
		$this->strTable = $arrConfig["source"];

		$this->objMetaModel = ModelFactory::byTableName($this->strTable);
	}

	/**
	 * Return empty config object.
	 *
	 * @return \DcGeneral\Data\ConfigInterface
	 */
	public function getEmptyConfig()
	{
		return DefaultConfig::init();
	}

	/**
	 * Fetch an empty single record (new item).
	 *
	 * @return \DcGeneral\Data\ModelInterface
	 */
	public function getEmptyModel()
	{
		$objItem = new Item($this->objMetaModel, array());
		return new Model($objItem);
	}

	/**
	 * Fetch an empty collection.
	 *
	 * @return \DcGeneral\Data\CollectionInterface
	 */
	public function getEmptyCollection()
	{
		return new DefaultCollection();
	}

	/**
	 * Combine a filter in standard filter array notation.
	 * Supported operations are:
	 * operation      needed arguments     argument type.
	 * AND
	 *                'children'           array
	 * OR
	 *                'children'           array
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
	 * @param \MetaModels\Filter\IFilter $objFilter The filter object where the rules shall get appended to.
	 *
	 * @return void.
	 *
	 * @throws \RuntimeException When an improper filter condition is encountered, an exception is thrown.
	 */
	protected function calculateSubfilter($arrFilter, IFilter $objFilter)
	{
		if (!is_array($arrFilter))
		{
			throw new \RuntimeException('Error Processing subfilter: ' . var_export($arrFilter, true), 1);
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
				// FIXME: backwards compat - remove when done
				if (is_array($arrFilter['childs']))
				{
					trigger_error('Filter array uses deprecated entry "childs", please use "children" instead.', E_USER_DEPRECATED);
					$arrFilter['children'] = $arrFilter['childs'];
				}

				if (!$arrFilter['children'])
				{
					return;
				}

				if ($arrFilter['operation'] == 'AND')
				{
					$objFilterRule = new ConditionAnd();
				} else {
					$objFilterRule = new ConditionOr();
				}
				$objFilter->addFilterRule($objFilterRule);

				$objSubFilter = new Filter($this->objMetaModel);

				$objFilterRule->addChild($objSubFilter);

				foreach ($arrFilter['children'] as $arrChild)
				{
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
							$objFilterRule = new SearchAttribute(
								$objAttribute,
								$arrFilter['value'],
								$this->objMetaModel->getAvailableLanguages()
							);
							break;

						case '>':
							$objFilterRule = new GreaterThan(
								$objAttribute,
								$arrFilter['value']
							);
							break;

						case '<':
							$objFilterRule = new LessThan(
								$objAttribute,
								$arrFilter['value']
							);
							break;
					}
				}
				elseif(\Database::getInstance()->fieldExists($arrFilter['property'], $this->objMetaModel->getTableName()))
				{
					// system column?
					$objFilterRule = new SimpleQuery(sprintf(
						'SELECT id FROM %s WHERE %s %s %s',
						$this->objMetaModel->getTableName(),
						$arrFilter['property'],
						$arrFilter['operation'],
						$arrFilter['value']
					));
				}

				if (!$objFilterRule)
				{
					throw new \RuntimeException('Error processing filter array - unknown property ' . var_export($arrFilter['property'], true), 1);
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
					'children'    => $arrSubRules
				), $objFilter);
				break;

			case 'LIKE':
				$objFilterRule = null;
				if ($objAttribute)
				{
					$objFilterRule = new SearchAttribute(
						$objAttribute,
						$arrFilter['value'],
						$this->objMetaModel->getAvailableLanguages()
					);
				}
				else if(\Database::getInstance()->fieldExists($arrFilter['property'], $this->objMetaModel->getTableName()))
				{
					// system column?
					$objFilterRule = new SimpleQuery(sprintf(
							'SELECT id FROM %s WHERE %s LIKE ?',
							$this->objMetaModel->getTableName(),
							$arrFilter['property']
						),
						array($arrFilter['value']));
				}

				if (!$objFilterRule)
				{
					throw new \RuntimeException('Error processing filter array - unknown property ' . var_export($arrFilter['property'], true), 1);
				}
				$objFilter->addFilterRule($objFilterRule);
				break;

			default:
				throw new \RuntimeException('Error processing filter array - unknown operation ' . var_export($arrFilter, true), 1);
		}
	}

	/**
	 * Prepare a filter and return it.
	 *
	 * @param array $arrFilter The values to be applied in attribute name => value style.
	 *
	 * @return IFilter
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
					'children' => $arrFilter
				),
				$objFilter
			);
		}
		return $objFilter;
	}

	/**
	 * Fetch all records (optional filtered, sorted and limited).
	 *
	 * @param \DcGeneral\Data\ConfigInterface $objConfig The configuration to be applied.
	 *
	 * @return \DcGeneral\Data\CollectionInterface
	 */
	public function fetchAll(ConfigInterface $objConfig)
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
				$objResultCollection->push(new Model($objItem));
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
	 * @param \DcGeneral\Data\ConfigInterface $objConfig   The filter config options.
	 *
	 * @return \DcGeneral\Data\CollectionInterface
	 *
	 * @throws \RuntimeException if improper values have been passed (i.e. not exactly one field requested).
	 */
	public function getFilterOptions(ConfigInterface $objConfig)
	{
		$arrProperties = $objConfig->getFields();
		if (count($arrProperties) <> 1)
		{
			throw new \RuntimeException('objConfig must contain exactly one property to be retrieved.');
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
	 * @param \DcGeneral\Data\ConfigInterface $objConfig
	 *
	 * @return int
	 */
	public function getCount(ConfigInterface $objConfig)
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
	 * @return \DcGeneral\Data\CollectionInterface
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
	 * @param \DcGeneral\Data\ModelInterface $objItem   The model to save back.
	 *
	 * @return \DcGeneral\Data\ModelInterface The passed model.
	 *
	 * @throws \RuntimeException When an incompatible item was passed, an Exception is being thrown.
	 */
	public function save(ModelInterface $objItem)
	{
		if ($objItem instanceof Model)
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
		throw new \RuntimeException('ERROR: incompatible object passed to GeneralDataMetaModel::save()');
	}

	/**
	 * Save a collection of items to the data provider.
	 *
	 * @param \DcGeneral\Data\CollectionInterface $objItems The collection containing all items to be saved.
	 *
	 * @return void
	 *
	 * @throws \RuntimeException when an incompatible item was passed.
	 */
	public function saveEach(CollectionInterface $objItems)
	{
		foreach ($objItems as $objItem)
		{
			$this->save($objItem);
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
	 * @param \DcGeneral\Data\ModelInterface $objModel1 The first model to compare.
	 *
	 * @param \DcGeneral\Data\ModelInterface $objModel2 The second model to compare.
	 *
	 * @return boolean True - If both models are same, false if not.
	 */
	public function sameModels($objModel1 , $objModel2)
	{
		/**
		 * These must be:
		 * @var Model $objModel1
		 * @var Model $objModel2
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
	 * @param \DcGeneral\Data\ConfigInterface $objConfig
	 *
	 * @return \DcGeneral\Data\ModelInterface
	 */
	public function createVariant(ConfigInterface $objConfig)
	{
		$objItem = $this->objMetaModel->findById($objConfig->getId())->varCopy();

		if (!$objItem)
		{
			return null;
		}
		return new Model($objItem);
	}

	/**
	 * Get all available languages of a certain record.
	 *
	 * @param mixed $mixID The ID of the record to retrieve.
	 *
	 * @return \DcGeneral\Data\CollectionInterface
	 */
	public function getLanguages($mixID)
	{
		$objCollection = $this->getEmptyCollection();

		if ($this->objMetaModel->isTranslated())
		{
			foreach ($this->objMetaModel->getAvailableLanguages() as $strLangCode)
			{
				$objModel = new DefaultModel();
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
	 * @return \DcGeneral\Data\ModelInterface|null
	 */
	public function getFallbackLanguage($mixID)
	{
		if ($this->objMetaModel->isTranslated())
		{
			$objModel = new DefaultModel();
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
