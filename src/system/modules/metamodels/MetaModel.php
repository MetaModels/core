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

	/**
	 * Determine if the given attribute is a complex one.
	 *
	 * @param IMetaModelAttribute $objAttribute the attribute to test.
	 *
	 * @return bool true if it is complex, false otherwise.
	 */
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
	 * @return array an array containing the database rows with each column "deserialized".
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
	 * @param int[]    $arrIds      the ids of the items to retrieve the order of ids is used for sorting of the return values.
	 *
	 * @param string[] $arrAttrOnly names of the attributes that shall be contained in the result, defaults to array() which means all attributes. NOTE: simple columns will ever be contained due to the "SELECT *" query.
	 *
	 * @return IMetaModelItems a collection of all matched items, sorted by the id list.
	 */
	protected function getItemsWithId($arrIds, $arrAttrOnly=array())
	{
		if (!$arrIds)
		{
			return new MetaModelItems(array());
		}

		if (!$arrAttrOnly)
		{
			$arrAttrOnly = array_keys($this->getAttributes());
		}

		$arrResult = $this->fetchRows($arrIds);

		// determine "complex attributes".
		$arrComplexCols = $this->getComplexAttributes();

		// now inject the complex attribute's content into the row.
		foreach($arrComplexCols as $objAttribute)
		{
			if (!in_array($objAttribute->getColName(), $arrAttrOnly))
			{
				continue;
			}
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

	/**
	 * clone the given filter or create an empty one if no filter has been passed.
	 *
	 * @param null|IMetaModelFilter the filter to clone.
	 *
	 * @return IMetaModelFilter the cloned filter.
	 */
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
	 * {@link MetaModel::createAttributes()} is called internally when the attributes are requested the first time.
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
			return array_keys((array)$this->arrData['languages']);
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
	public function findById($intId, $arrAttrOnly = array())
	{
		if (!$intId)
		{
			return null;
		}
		$objItems = $this->getItemsWithId(array($intId), $arrAttrOnly);
		if ($objItems && $objItems->first())
		{
			return $objItems->getItem();
		}
		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function findByFilter($objFilter, $strSortBy = '', $intOffset = 0, $intLimit = 0, $strSortOrder = 'ASC', $arrAttrOnly = array())
	{
		return $this->getItemsWithId($this->getIdsFromFilter($objFilter, $strSortBy, $intOffset, $intLimit, $strSortOrder), $arrAttrOnly);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getIdsFromFilter($objFilter, $strSortBy = '', $intOffset = 0, $intLimit = 0, $strSortOrder = 'ASC')
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
		return $arrFilteredIds;
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
	* Find all varints of the given item. This methods makes no difference between the varbase item and other variants.
	*
	* @param type $arrIds
	* @param type $objFilter
	* @return type
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
	 * Update the value of a native column for the given ids with the given data.
	 *
	 * @param string $strColumn the column name to update (i.e. tstamp).
	 *
	 * @param array  $arrIds    the ids of the rows that shall be updated.
	 *
	 * @param mixed  $varData   the data to save. If this is an array, it is automatically serialized.
	 *
	 * @return void
	 */
	protected function saveSimpleColumn($strColumn, $arrIds, $varData)
	{
		if(is_array($varData))
		{
			$varData = serialize($varData);
		}
		Database::getInstance()->prepare(sprintf('UPDATE %s SET %s=? WHERE id IN (%s)', $this->getTableName(), $strColumn, implode(',', $arrIds)))
		      ->execute($varData);
	}

	/**
	 * Update an attribute for the given ids with the given data.
	 *
	 * @param IMetaModelAttribute $objAttribute the attribute to save.
	 *
	 * @param array               $arrIds       the ids of the rows that shall be updated.
	 *
	 * @param mixed               $varData      the data to save in raw data.
	 *
	 * @param string              $strLangCode  the language code to save.
	 */
	protected function saveAttribute($objAttribute, $arrIds, $varData, $strLangCode)
	{
		$arrInterfaces = class_implements($objAttribute);

		$arrData = array();
		foreach ($arrIds as $intId)
		{
			$arrData[$intId] = $varData;
		}
		// check for translated fields first, then for complex and save as simple then.
		if ($strLangCode && in_array('IMetaModelAttributeTranslated', $arrInterfaces))
		{
			$objAttribute->setTranslatedDataFor($arrData, $strLangCode);
		} else if($this->isComplexAttribute($objAttribute))
		{
			// complex saving
			$objAttribute->setDataFor($arrData);
		} else if(in_array('IMetaModelAttributeSimple', $arrInterfaces)) {
			$objAttribute->setDataFor($arrData);
		} else {
			throw new Exception('Unknown attribute type, can not save. Interfaces implemented: ' . implode(', ', $arrInterfaces));
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function saveItem($objItem)
	{
		$objDB = Database::getInstance();

		$objItem->set('tstamp', time());
		if (!$objItem->get('id'))
		{
			$arrData = array
			(
				'tstamp' => $objItem->get('tstamp')
			);

			$blnNewBaseItem = false;
			if ($this->hasVariants())
			{
				/* no vargroup is given, so we have a complete new base item
				 * this should be a workaround for these values should be set by the
				 * GeneralDataMetaModel or whoever is calling this method.
				 */
				if (is_null($objItem->get('vargroup')))
				{
					$objItem->set('varbase', '1');
					$objItem->set('vargroup', '0');
					$blnNewBaseItem = true;
				}
				$arrData['varbase'] = $objItem->get('varbase');
				$arrData['vargroup'] = $objItem->get('vargroup');
			}

			$intItemId = $objDB->prepare('INSERT INTO ' . $this->getTableName() . ' %s')
					->set($arrData)
					->execute()
					->insertId;
			$objItem->set('id', $intItemId);
			//add the vargroup equal to the id
			if ($blnNewBaseItem)
			{
				$this->saveSimpleColumn('vargroup', array($objItem->get('id')), $objItem->get('id'));
			}
		}

		// update system columns.
		if ($objItem->get('pid'))
		{
			$this->saveSimpleColumn('pid', array($objItem->get('id')), $objItem->get('pid'));
		}
		if ($objItem->get('sorting'))
		{
			$this->saveSimpleColumn('sorting', array($objItem->get('id')), $objItem->get('sorting'));
		}
		$this->saveSimpleColumn('tstamp', array($objItem->get('id')), $objItem->get('tstamp'));

		if ($this->isTranslated())
		{
			$strActiveLanguage = $this->getActiveLanguage();
		} else {
			$strActiveLanguage = null;
		}

		$arrAllIds = array();
		if ($objItem->isVariantBase())
		{
			$objVariants = $this->findVariantsWithBase(array($objItem->get('id')), NULL);
			foreach ($objVariants as $objVariant)
			{
				$arrAllIds[] = $objVariant->get('id');
			}
		}

		$blnDenyInvariantSave = $objItem->isVariant();
		$blnOverrideVariants = $objItem->isVariantBase();

		foreach ($this->getAttributes() as $strAttributeId => $objAttribute)
		{
			if ($blnDenyInvariantSave && !($objAttribute->get('isvariant')))
			{
				// base not found, skip attribute.
				continue;
			}

			if ($blnOverrideVariants && !($objAttribute->get('isvariant')))
			{
				// we have to override in variants.
				$arrIds = $arrAllIds;
			} else {
				$arrIds = array($objItem->get('id'));
			}
			$this->saveAttribute($objAttribute, $arrIds, $objItem->get($strAttributeId), $strActiveLanguage);
		}
		// Tell all attributes that the model has been saved. Useful for alias fields, edit counters etc.
		foreach ($this->getAttributes() as $objAttribute)
		{
			$objAttribute->modelSaved($objItem);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete(IMetaModelItem $objItem)
	{
		$arrIds = array($objItem->get('id'));
		// determine if the model is a variant base and if so, fetch the variants additionally.
		if ($objItem->isVariantBase())
		{
			$objVariants = $objItem->getVariants();
			foreach ($objVariants as $objVariant)
			{
				$arrIds[] = $objVariant->get('id');
			}
		}

		// complex attributes shall delete their values first.
		foreach ($this->getAttributes() as $strAttributeId => $objAttribute)
		{
			if($this->isComplexAttribute($objAttribute))
			{
				// complex saving
				$objAttribute->unsetDataFor($arrIds);
			}
		}
		// now make the real row disappear.
		Database::getInstance()->execute(sprintf('DELETE FROM %s WHERE id IN (%s)', $this->getTableName(), implode(',', $arrIds)));
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

	/**
	 * {@inheritdoc}
	 */
	public function getView($intViewId = 0)
	{
		return MetaModelRenderSettingsFactory::byId($this, $intViewId);
	}
}

?>