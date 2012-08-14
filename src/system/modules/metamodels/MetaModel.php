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
 * This is the main MetaModel class.
 * 
 * @see MetaModelFactory::byId()		to instantiate a MetaModel by its ID.
 * @see MetaModelFactory::byTableName()	to instantiate a MetaModel by its table name.
 * 
 * This class handles all attribute definition instantiation and can be queried for a view instance to certain entries.
 * 
 * @package	   MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class MetaModel implements IMetaModel
{
	/**
	 * Information data of this MetaModel instance.
	 * This is the data from tl_metamodel.
	 * 
	 * @var array
	 */
	protected $arrData=array();

	/**
	 * This holds all attribute instances.
	 * Association is $colName => object
	 * 
	 * @var array
	 */
	protected $arrAttributes=array();

	/**
	 * instantiate a MetaModel.
	 * 
	 * @param array $arrData the information array, for information on the available columns, refer to documentation of table tl_metamodel
	 */
	public function __construct($arrData)
	{
		foreach ($arrData as $strKey => $varValue)
		{
			$this->arrData[$strKey] = deserialize($varValue);
		}
	}

	/**
	 * Retrieve the human readble name for this metamodel.
	 * 
	 * @return string the name for the MetaModel.
	 */
	public function getName()
	{
		return $this->arrData['name'];
	}

	/**
	 * Adds an attribute to the internal list of attributes.
	 * 
	 * @param IMetaModelAttribute the attribute instance to add.
	 * 
	 * @return IMetaModel self for chaining
	 */
	protected function addAttribute(IMetaModelAttribute $objAttribute)
	{
		$this->arrAttributes[$objAttribute->getColName()] = $objAttribute;
	}

	/**
	 * Checks if an attribute with the given name has been added to the internal list.
	 * 
	 * @param string $strAttributeName the name of the attribute to search.
	 */
	protected function hasAttribute($strAttributeName)
	{
		return array_key_exists($strAttributeName, $this->arrAttributes);
	}

	/**
	 * Create instances of all attributes that are defined for this MetaModel instance.
	 * This is called internally by the first query of MetaModel::getAttributes().
	 * 
	 * @return void
	 */
	protected function createAttributes()
	{
		$arrAttributes = MetaModelAttributeFactory::getAttributesFor($this);
		foreach ($arrAttributes as $objAttribute)
		{
			if ($this->hasAttribute($objAttribute->getColName()))
			{
				continue;
			}
			$this->addAttribute($objAttribute);
		}
	}

	protected function isComplexAttribute($objAttribute)
	{
		return in_array('IMetaModelAttributeComplex', class_implements($objAttribute));
	}

	/**
	 * This method retrieves all complex attributes from the current MetaModel.
	 * 
	 * @return IMetaModelAttributeComplex[] all complex attributes defined for this instance.
	 */
	protected function getComplexAttributes()
	{
		$arrResult = array();
		foreach($this->getAttributes() as $objAttribute)
		{
			if($this->isComplexAttribute($objAttribute))
			{
				$arrResult[] = $objAttribute;
			}
		}
		return $arrResult;
	}

	/**
	 * Narrow down the list of Ids that match the given filter.
	 * 
	 * @param IMetaModelFilter $objFilter
	 * 
	 * @return array all matching Ids.
	 */
	protected function getMatchingIds($objFilter)
	{
		if ($objFilter)
		{
			$arrFilteredIds = $objFilter->getMatchingIds();
			if ($arrFilteredIds !== NULL)
			{
				return $arrFilteredIds;
			}
		}
		// either no filter object or all ids allowed => return all ids.
		// if no id filter is passed, we assume all ids are provided.
		$objDB = Database::getInstance();
		$objRow = $objDB->execute('SELECT id FROM ' . $this->getTableName());
		return $objRow->fetchEach('id');
	}

	/**
	 * Fetch the "native" database rows with the given ids.
	 * 
	 * @param int[] $arrIds the ids of the items to retrieve the order of ids is used for sorting of the return values.
	 * 
	 * @param array an array containing the database rows with each column "deserialized".
	 */
	protected function fetchRows($arrIds)
	{
		$objDB = Database::getInstance();

		// ensure proper integer ids for SQL injection safety reasons.
		$strIdList = implode(',', array_map('intval', $arrIds));
		$objRow = $objDB->execute('SELECT * FROM ' . $this->getTableName() . ' WHERE id IN (' . $strIdList . ') ORDER BY FIELD(id,' . $strIdList . ')');
		if($objRow->numRows == 0)
		{
			return null;
		}

		$arrResult = array();
		while($objRow->next())
		{
			$arrData = array();

			foreach($objRow->row() as $strKey=>$varValue)
			{
				$arrData[$strKey] = deserialize($varValue);
			}
			$arrResult[$objRow->id] = $arrData;
		}
		return $arrResult;
	}

	/**
	 * This method is called to retrieve the data for certain items from the database.
	 * 
	 * @param int[] $arrIds the ids of the items to retrieve the order of ids is used for sorting of the return values.
	 * 
	 * @return IMetaModelItems a collection of all matched items, sorted by the id list.
	 */
	protected function getItemsWithId($arrIds)
	{
		if (!$arrIds)
		{
			return new MetaModelItems(array());
		}

		$arrResult = $this->fetchRows($arrIds);

		// determine "complex attributes".
		$arrComplexCols = $this->getComplexAttributes();

		// now inject the complex attribute's content into the row.
		foreach($arrComplexCols as $objAttribute)
		{
			$arrAttributeData = $objAttribute->getDataFor($arrIds);
			$strColName = $objAttribute->getColName();
			foreach (array_keys($arrResult) as $intId)
			{
				$arrResult[$intId][$strColName] = $arrAttributeData[$intId];
			}
		}

		// TODO: shall we also implement *item and *items factories?
		$arrItems = array();
		foreach($arrResult as $arrEntry)
		{
			$arrItems[] = new MetaModelItem($this, $arrEntry);
		}

		$objItems = new MetaModelItems($arrItems);

		return $objItems;
	}

	protected function copyFilter($objFilter)
	{
		if ($objFilter)
		{
			$objNewFilter = $objFilter->createCopy();
		} else {
			$objNewFilter = $this->getBaseFilter();
		}
		return $objNewFilter;
	}

	/////////////////////////////////////////////////////////////////
	// interface IMetaModel
	/////////////////////////////////////////////////////////////////

	/**
	 * {@inheritdoc}
	 */
	public function get($strKey)
	{
		// try to retrieve via getter method.
		$strGetter = 'get'.$strKey;
		if(method_exists($this, $strGetter))
		{
			return $this->$strGetter();
		}

		// return via raw array if available.
		if (array_key_exists($strKey, $this->arrData))
		{
			return $this->arrData[$strKey];
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getTableName()
	{
		return $this->arrData['tableName'];
	}

	/**
	 * {@inheritdoc}
	 * 
	 * @link MetaModel::createAttributes() is called internally when the attributes are requested the first time.
	 * 
	 */
	public function getAttributes()
	{
		if (!count($this->arrAttributes))
		{
			// instantiate all attributes now.
			$this->createAttributes();
		}
		return $this->arrAttributes;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getInVariantAttributes()
	{
		$arrAttributes = $this->getAttributes();
		if (!$this->hasVariants())
		{
			return $arrAttributes;
		}
		// remove all attributes that are selected for overriding.
		foreach ($arrAttributes as $strAttributeId => $objAttribute)
		{
			if ($objAttribute->get('isvariant'))
			{
				unset($arrAttributes[$strAttributeId]);
			}
		}
		return $arrAttributes;
	}


	/**
	 * {@inheritdoc}
	 */
	public function isTranslated()
	{
		return $this->arrData['translated'];
	}

	/**
	 * {@inheritdoc}
	 */
	public function hasVariants()
	{
		return $this->arrData['varsupport'];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAvailableLanguages()
	{
		if ($this->isTranslated())
		{
			return array_keys($this->arrData['languages']);
		} else {
			return NULL;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getFallbackLanguage()
	{
		if ($this->isTranslated())
		{
			foreach ($this->arrData['languages'] as $strLangCode=>$arrData)
			{
				if($arrData['isfallback'])
				{
					return $strLangCode;
				}
			}
		}
		return NULL;
	}

	/**
	 * {@inheritdoc}
	 * 
	 * The value is taken from $GLOBALS['TL_LANGUAGE']
	 */
	public function getActiveLanguage()
	{
		return $GLOBALS['TL_LANGUAGE'];
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAttribute($strAttributeName)
	{
		$arrAttributes = $this->getAttributes();
		return array_key_exists($strAttributeName, $arrAttributes)?$arrAttributes[$strAttributeName]:null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAttributeById($intId)
	{
		foreach ($this->getAttributes() as $objAttribute)
		{
			if ($objAttribute->get('id') == $intId)
			{
				return $objAttribute;
			}
		}
		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function findById($intId)
	{
		$objItems = $this->getItemsWithId(array($intId));
		if ($objItems && $objItems->first())
		{
			return $objItems->getItem();
		}
		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function findByFilter($objFilter, $strSortBy = '', $intOffset = 0, $intLimit = 0, $strSortOrder = 'ASC')
	{

		$arrFilteredIds = $this->getMatchingIds($objFilter);
		// if desired, sort the entries.
		if ($arrFilteredIds && $strSortBy != '' && ($objSortAttribute = $this->getAttribute($strSortBy)))
		{   
			$arrFilteredIds = $objSortAttribute->sortIds($arrFilteredIds, $strSortOrder);

		}
		// apply limiting then
		if ($intOffset > 0 || $intLimit > 0)
		{
			$arrFilteredIds = array_slice($arrFilteredIds, $intOffset, $intLimit);
		}
		return $this->getItemsWithId($arrFilteredIds);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getCount($objFilter)
	{
		$objDB = Database::getInstance();
		$arrFilteredIds = $this->getMatchingIds($objFilter);
		if (count($arrFilteredIds) == 0)
		{
			return 0;
		}
		$objRow = $objDB->execute('SELECT COUNT(id) AS count FROM ' . $this->getTableName() . ' WHERE id IN('.implode(',', $arrFilteredIds).')');
		return $objRow->count;
	}

	/**
	 * {@inheritdoc}
	 */
	public function findVariantBase($objFilter)
	{
		$objNewFilter = $this->copyFilter($objFilter);

		$objDB = Database::getInstance();
		$objRow = $objDB->execute('SELECT id FROM ' . $this->getTableName() . ' WHERE varbase=1');
		$objNewFilter->addFilterRule(new MetaModelFilterRuleStaticIdList($objRow->fetchEach('id')));
		return $this->findByFilter($objNewFilter);
	}

	/**
	 * {@inheritdoc}
	 */
	public function findVariants($arrIds, $objFilter)
	{
		if(!$arrIds)
		{
			// return an empty result
			return $this->getItemsWithId(array());
		}
		$objNewFilter = $this->copyFilter($objFilter);

		$objDB = Database::getInstance();
		$objRow = $objDB->execute('SELECT id,vargroup FROM ' . $this->getTableName() . ' WHERE varbase=0 AND vargroup IN ('.implode(',', $arrIds).')');
		$objNewFilter->addFilterRule(new MetaModelFilterRuleStaticIdList($objRow->fetchEach('id')));
		return $this->findByFilter($objNewFilter);
	}
        
        /**
	 * {@inheritdoc}
	 */
	public function findVariantsWithBase($arrIds, $objFilter)
	{
		if(!$arrIds)
		{
			// return an empty result
			return $this->getItemsWithId(array());
		}
		$objNewFilter = $this->copyFilter($objFilter);

		$objDB = Database::getInstance();
		$objRow = $objDB->execute('SELECT id,vargroup FROM ' . $this->getTableName() . ' WHERE vargroup IN (SELECT vargroup FROM ' . $this->getTableName() . ' WHERE id IN ('.implode(',', $arrIds).'))');
		$objNewFilter->addFilterRule(new MetaModelFilterRuleStaticIdList($objRow->fetchEach('id')));
		return $this->findByFilter($objNewFilter);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getAttributeOptions($strAttribute, $objFilter = null)
	{
		$objAttribute = $this->getAttribute($strAttribute);
		if ($objAttribute)
		{
			if ($objFilter)
			{
				$arrFilteredIds = $this->getMatchingIds($objFilter);
				$arrFilteredIds = $objAttribute->sortIds($arrFilteredIds, 'ASC');
				return $objAttribute->getFilterOptions($arrFilteredIds);
			} else {
				return $objAttribute->getFilterOptions();
			}
		}
		return array();
	}

	/**
	 * {@inheritdoc}
	 */
	public function saveItem(&$arrValues)
	{
		$objDB = Database::getInstance();

		$blnDenyInvariantSave = $this->hasVariants() && ($arrValues['varbase'] === '0');

		if (!$arrValues['id'])
		{
			$arrData = array
			(
				'tstamp' => time()
			);

			if ($this->hasVariants())
			{
				$arrData['varbase'] = $arrValues['varbase'];
				$arrData['vargroup'] = $arrValues['vargroup'];
			}

			$arrValues['id'] = $objDB->prepare('INSERT INTO ' . $this->getTableName() . ' %s')
					->set($arrData)
					->execute()
					->insertId;
		}

		if ($this->isTranslated())
		{
			$strActiveLanguage = $this->getActiveLanguage();
		} else {
			$strActiveLanguage = null;
		}

		$arrDataSimple = array();
		foreach ($this->getAttributes() as $strAttributeId => $objAttribute)
		{
			if ($blnDenyInvariantSave && !($objAttribute->get('isvariant')))
			{
				continue;
			}

			$arrInterfaces = class_implements($objAttribute);
			// check for translated fields first, then for complex and save as simple then.
			if ($strActiveLanguage && in_array('IMetaModelAttributeTranslated', $arrInterfaces))
			{
				$objAttribute->setTranslatedDataFor(array($arrValues['id'] => $arrValues[$strAttributeId]), $strActiveLanguage);
			} else if($this->isComplexAttribute($objAttribute))
			{
				// complex saving
				$objAttribute->setDataFor(array($arrValues['id'] => $arrValues[$strAttributeId]));
			} else if(in_array('IMetaModelAttributeSimple', $arrInterfaces)) {
				$arrDataSimple[$strAttributeId] = $arrValues[$strAttributeId];
			} else {
				throw new Exception('Unknown attribute type, can not save. Interfaces implemented: ' . implode(', ', $arrInterfaces));
			}
		}
		// now save the simple columns.
		if ($arrDataSimple)
		{
			$objDB->prepare('UPDATE ' . $this->getTableName() . ' %s WHERE id=?')
			      ->set($arrDataSimple)
			      ->execute($arrValues['id']);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function getEmptyFilter()
	{
		$objFilter = new MetaModelFilter($this);

		return $objFilter;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getBaseFilter()
	{
		$objFilter = $this->getEmptyFilter();

		foreach ($this->getAttributes() as $objAttribute)
		{
			if ($objAttribute)
			{
				$objFilterRule = $objAttribute->parseFilterUrl(array());
				if ($objFilterRule)
				{
					$objFilter->addFilterRule($objFilterRule);
				}
			}
		}
		return $objFilter;
	}

	/**
	 * {@inheritdoc}
	 */
	public function prepareFilter($intFilterSettings, $arrFilterUrl)
	{
		$objFilter = $this->getBaseFilter();
		if ($intFilterSettings)
		{
			$objFilterSettings = MetaModelFilterSettingsFactory::byId($intFilterSettings);
			$objFilterSettings->addRules($objFilter, $arrFilterUrl);
		}
		return $objFilter;
	}
}

?>