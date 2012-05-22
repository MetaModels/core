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
		$objDB = Database::getInstance();
		$objAttributes = $objDB->prepare('SELECT * FROM tl_metamodel_attribute WHERE pid=?')
							->execute($this->get('id'));
		while ($objAttributes->next())
		{
			if ($this->hasAttribute($objAttributes->colName))
			{
				continue;
			}
			$objAttribute = MetaModelAttributeFactory::createFromDB($objAttributes);
			if ($objAttribute)
			{
				$this->addAttribute($objAttribute);
			}
		}
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
			if(in_array('IMetaModelAttributeComplex', class_implements($objAttribute)))
			{
				$arrResult[] = $objAttribute;
			}
		}
		return $arrResult;
	}

	/**
	 * This method is called to retrieve the data for certain items from the database.
	 * 
	 * @param int[] $arrIds the ids of the items to retrieve the order of ids is used for sorting of the return values.
	 * 
	 * @return mixed[] the nature of the resulting array is a mapping from id => "native data" where
	 *                 the definition of "native data" is only of relevance to the given item.
	 */
	protected function getItemsWithId($arrIds)
	{
		$objDB = Database::getInstance();
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

		// determine "complex attributes".
		$arrComplexCols = $this->getComplexAttributes();

		// now inject the complex attribute's content into the row.
		foreach($arrComplexCols as $objAttribute)
		{

			$arrAttributeData = $objAttribute->getDataFor(array($intId));

			foreach($arrAttributeData as $intId => $varValue)
			{
				$arrResult[$intId][$objAttribute->colName] = $varValue;
			}
		}

		return $arrResult;
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
		return $this->getItemsWithId(array($intId));
	}

	/**
	 * {@inheritdoc}
	 */
	public function findByFilter($arrFilter)
	{
		$arrIds = array();
		// simple rule, id search is set.
		if(isset($arrFilter['id']))
		{
			$arrIds['id'] = explode(',', $arrFilter['id']);
		} else {
			$objDB = Database::getInstance();
			// TODO: add ordering here.
			$objRow = $objDB->execute('SELECT id FROM ' . $this->getTableName());
			$arrIds['id'] = $objRow->fetchEach('id');
		}

		foreach($this->getAttributes() as $objAttribute)
		{
			$arrInterfaces = class_implements($objAttribute);
			if(in_array('IMetaModelAttributeComplex', $arrInterfaces))
			{
				$varFilterValue = $objAttribute->parseFilterUrl();

				// if return value is null, ignore this attribute.
				if($varFilterValue === null)
				{
					continue;
				}

				// now intersect the values.
				$arrIds[$objAttribute->getColName()] = $objAttribute->getIdsFromFilter($varFilterValue);
			} else if(in_array('IMetaModelAttributeSimple', $arrInterfaces)) {
				// simple attributes's conditions can be applied in SQL notation.
			}
		}

//		$arrIds[$objAttribute->getColName()] = array_intersect($arrIds, $arrFieldValues);

		return $this->getItemsWithId($arrIds);
	}

	/*
	public function getView($arrAttributes, $arrFilterUrl, $arrOrderBy)
	{

		$arrCols = array();
		$arrFilter = array();
		$arrOrderBy = array();
		foreach ($arrAttributes as $colName)
		{
			$objField = $this->getField($colName);
			if ($objField)
			{
				$arrCols[$colName] = $objField->getSQL();
				$arrFilter[$colName] = $objField->getFilterSQL($arrFilterUrl);
			}
		}

		// TODO: we need support for LIMIT here aswell.
		$strSQL = 'SELECT ' . 
					impode(', ', $arrCols) . 
					' FROM '. $this->tableName . 
					(count($arrFilter)?' WHERE '. implode(' AND ', $arrFilter):'') . 
					(count($arrOrderBy)?' ORDER BY ':'');
	}
	*/
}

?>