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
 * Reference implementation for IMetaModelAttributeSimple.
 * Simple fields are fields that only consist of one column in the metamodel table and therefore do not need
 * to be handled as complex fields must be.
 * 
 * @package	   MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class MetaModelAttributeSimple extends MetaModelAttribute implements IMetaModelAttributeSimple
{

	/////////////////////////////////////////////////////////////////
	// interface IMetaModelAttribute
	/////////////////////////////////////////////////////////////////

	/**
	 * {@inheritdoc }
	 * in addition, the MetaModelAttributeSimple class will handle colName changes internally and create 
	 * and rename the physical columns accordingly to the given value.
	 */
	public function handleMetaChange($strMetaName, $varNewValue)
	{
		// by default we accept any change of meta information.
		switch($strMetaName)
		{
			case 'colname':
				if($this->get($strKey) != $varNewValue)
				{
					$this->renameColumn($varNewValue);
				}
				return $this;
				break;
		}
		return parent::handleMetaChange($strMetaName, $varNewValue);
	}

	/**
	 * {@inheritdoc }
	 *
	 * Base implementation in MetaModelAttributeSimple performs a simple search on the url param
	 * with the same name as the colName.
	 */
	public function parseFilterUrl($arrUrlParams)
	{
		$objFilterRule = NULL;
		if (key_exists($this->getColName(), $arrUrlParams))
		{
			$objFilterRule = new MetaModelFilterRuleSimpleQuery(
				'SELECT id FROM ' . $this->getMetaModel()->getTableName() . ' WHERE ' . $this->getColName() . '=?',
				array($arrUrlParams[$this->getColName()]),
				'id'
			);
		}
		return $objFilterRule;
	}

	/**
	 * {@inheritdoc}
	 * 
	 * Deriving classes SHOULD override this function.
	 * 
	 */
	public function getFilterOptions($arrIds = array())
	{
		$strCol = $this->colName();
		if ($arrIds)
		{
			// ensure proper integer ids for SQL injection safety reasons.
			$strIdList = implode(',', array_map('intval', $arrIds));
			$objRow = Database::getInstance()->execute('
				SELECT DISTINCT(' . $strCol . ')
				FROM ' . $this->getMetaModel()->getTableName() .
				' WHERE id IN (' . $strIdList . ')
				ORDER BY FIELD(id,' . $strIdList . ')');
		} else {
			$objRow = Database::getInstance()->execute('
				SELECT DISTINCT(' . $strCol . ')
				FROM ' . $this->getMetaModel()->getTableName());
		}
		$arrResult = array();
		while($objRow->next())
		{
			$arrResult[$objRow->$strCol] = $objRow->$strCol;
		}
		return $arrResult;
	}

	/////////////////////////////////////////////////////////////////
	// interface IMetaModelAttributeSimple
	/////////////////////////////////////////////////////////////////

	/**
	 * {@inheritdoc}
	 * 
	 * In this base class a sane value of "blob" allowing NULL is used.
	 * Deriving classes SHOULD override this function.
	 * 
	 * @return string 'blob NULL'
	 */
	public function getSQLDataType()
	{
		return 'blob NULL';
	}

	/**
	 * Create auxiliary data like a column in the MetaModel table or references in another table etc.
	 */
	public function destroyAUX()
	{
		parent::destroyAUX();
		$this->deleteColumn();
	}

	/**
	 * Delete all auxiliary data like a column in the MetaModel table or references in another table etc.
	 */
	public function initializeAUX()
	{
		parent::initializeAUX();
		$this->createColumn();
	}

	/**
	 * Creates the underlying database structure for this field.
	 * You have to override this function in field types, when you want to have multi column structure etc.
	 * 
	 * @return void
	 */
	public function createColumn()
	{
		if($this->getColName())
		{
			MetaModelTableManipulation::createColumn($this->getMetaModel()->getTableName(), $this->getColName(), $this->getSQLDataType());
		}
	}

	/**
	 * Removes the underlying database structure for this field.
	 * 
	 * @return void
	 */
	public function deleteColumn()
	{
		// try to delete the column. be graceful, if it does not exist as we can assume it has been deleted already then
		if($this->getColName() && Database::getInstance()->fieldExists($this->getColName(), $this->getMetaModel()->getTableName(), true))
		{
			MetaModelTableManipulation::dropColumn($this->getMetaModel()->getTableName(), $this->getColName());
		}
	}

	/**
	 * Renames the underlying database structure for this field.
	 * 
	 * @param string $strNewColumnName the new column name.
	 * 
	 * @return void
	 */
	public function renameColumn($strNewColumnName)
	{
		MetaModelTableManipulation::checkColumnName($strNewColumnName);
		if($this->getColName() && Database::getInstance()->fieldExists($this->getColName(), $this->getMetaModel()->getTableName(), true))
		{
			MetaModelTableManipulation::renameColumn($this->getMetaModel()->getTableName(), $this->getColName(), $strNewColumnName, $this->getSQLDataType());
		} else {
			$strBackupColName = $this->getColName();
			$this->set('colname', $strNewColumnName);
			$this->createColumn();
			$this->set('colname', $strBackupColName);
		}
	}
}

?>