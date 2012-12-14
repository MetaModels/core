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
				if($this->get($strMetaName) != $varNewValue)
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
	 * Updates the column in the MetaModel table.
	 */
	public function setDataFor($arrValues)
	{
		$strTable = $this->getMetaModel()->getTableName();
		$strColName = $this->getColName();
		foreach ($arrValues as $intId => $varData)
		{
			if(is_array($varData))
			{
				$varData = serialize($varData);
			}
			Database::getInstance()
				->prepare(sprintf('UPDATE %s SET %s=? WHERE id=%s', $strTable, $strColName, $intId))
				->execute($varData);
		}
	}

	/**
	 * {@inheritdoc}
	 *
	 * Deriving classes SHOULD override this function.
	 *
	 */
	public function getFilterOptions($arrIds = array())
	{
		$strCol = $this->getColName();
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


	/**
	 * {@inheritdoc}
	 */
	public function sortIds($arrIds, $strDirection)
	{
		// base implementation, do a simple sorting on given column.
		$arrIds = Database::getInstance()->prepare(sprintf(
			'SELECT id FROM %s WHERE id IN (%s) ORDER BY %s %s',
			$this->getMetaModel()->getTableName(),
			implode(',', $arrIds),
			$this->getColName(),
			$strDirection))
			->execute()
			->fetchEach('id');
		return $arrIds;
	}

	/**
	 * {@inheritdoc}
	 * Base implementation, perform string matching search.
	 */
	public function searchFor($strPattern)
	{
		// base implementation, do a simple search on given column.
		$objQuery = Database::getInstance()->prepare(sprintf(
			'SELECT id FROM %s WHERE %s LIKE ?',
			$this->getMetaModel()->getTableName(),
			$this->getColName()
			))
			->execute(str_replace(array('*', '?'), array('%', '_'), $strPattern));
		$arrIds = $objQuery->fetchEach('id');
		return $arrIds;
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

