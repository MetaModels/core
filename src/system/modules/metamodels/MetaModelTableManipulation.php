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
 * This is the class for table manipulations like creation/renaming/deleting of tables and columns.
 *
 * @package	   MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class MetaModelTableManipulation
{

	/**
	 * SQL statement template to create a table.
	 * First parameter is the table name.
	 */
	const STATEMENT_CREATE_TABLE = '
			CREATE TABLE `%s` (
				`id` int(10) unsigned NOT NULL auto_increment,
				`pid` int(10) unsigned NOT NULL,
				`sorting` int(10) unsigned NOT NULL default \'0\',
				`tstamp` int(10) unsigned NOT NULL default \'0\',
				PRIMARY KEY  (`id`)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8';

	/**
	 * SQL statement template to rename a table.
	 * First parameter is the old name, second parameter is the new name.
	 */
	const STATEMENT_RENAME_TABLE = 'ALTER TABLE `%s` RENAME TO `%s`';

	/**
	 * SQL statement template to drop a table.
	 * First parameter is the table name of the table to drop.
	 */
	const STATEMENT_DROP_TABLE = 'DROP TABLE `%s`';

	/**
	 * SQL statement template to rename a column of a table.
	 * First parameter is table name.
	 * Second parameter is the old name of the column.
	 * Third paramter is the new name of the column.
	 * Fourth parameter is the new type of the column.
	 */
	const STATEMENT_CHANGE_COLUMN = 'ALTER TABLE %s CHANGE COLUMN %s %s %s';

	/**
	 * SQL statement template to add a column to a table.
	 * First parameter is the table name.
	 * Second parameter is the column name.
	 * Third parameter is the type of the new column.
	 */
	const STATEMENT_CREATE_COLUMN = 'ALTER TABLE %s ADD %s %s';

	/**
	 * SQL statement template to delete a column from a table.
	 * First parameter is the name of the column.
	 */
	const STATEMENT_DROP_COLUMN = 'ALTER TABLE %s DROP COLUMN %s';

	/**
	 * All system columns that always are defined in a MetaModel table.
	 * When you alter this, ensure to also change @link{MetaModelTableManipulation::STATEMENT_CREATE_TABLE} above
	 */
	protected static $arrSystemColumns = array('id', 'pid', 'sorting', 'tstamp');

	/**
	 * Returns the Contao database instance to use.
	 *
	 * @return Database the database instance.
	 */
	protected static function getDB()
	{
		return Database::getInstance();
	}

	/**
	 * Checks wheter the given table name is valid.
	 *
	 * @param string $strTableName the table name to check
	 * @return bool true if the table name is valid, false otherwise.
	 */
	public static function isValidTableName($strTableName)
	{
		return self::isValidMySQLIdentifier($strTableName);
	}

	/**
	 * Checks whether the column with the given name is a MetaModel system column.
	 *
	 * @param string $strColumnName the name of the column
	 * @return bool true if the column is a system column, false otherwise.
	 */
	public static function isValidColumnName($strColumnName)
	{
		return self::isValidMySQLIdentifier($strColumnName);
	}

	public static function isValidMySQLIdentifier($strName)
	{
		// match for valid table name, according to MySQL, a table name must start
		// with a letter and must be combined of letters, decimals and underscore.
		return 1 == preg_match('/^[a-z_][a-z\d_]*$/i', $strName);
	}

	public static function isReservedIdentifier($strName)
	{
		return false !== strpos($strName, '__');
	}

	/**
	 * Checks whether the column with the given name is a MetaModel system column.
	 *
	 * @param string $strColumnName the name of the column
	 * @return bool true if the column is a system column, false otherwise.
	 */
	public static function isSystemColumn($strColName)
	{
		return in_array($strColName, self::$arrSystemColumns);
	}

	public static function getCreateTableStatement($strTableName)
	{
		return sprintf(self::STATEMENT_CREATE_TABLE, $strTableName);
	}

	public static function getRenameTableStatement($strTableName, $strNewTableName)
	{
		return sprintf(self::STATEMENT_RENAME_TABLE, $strTableName, $strNewTableName);
	}

	public static function getDropTableStatement($strTableName)
	{
		return sprintf(self::STATEMENT_DROP_TABLE, $strTableName);
	}

	public static function getCreateColumnStatement($strTableName, $strColumnName, $strType)
	{
		return sprintf(self::STATEMENT_CREATE_COLUMN, $strTableName, $strColumnName, $strType);
	}

	public static function getChangeColumnStatement($strTableName, $strColumnName, $strNewColumnName, $strNewType)
	{
		return sprintf(self::STATEMENT_CHANGE_COLUMN, $strTableName, $strColumnName, $strNewColumnName, $strNewType);
	}

	public static function getDropColumnStatement($strTableName, $strColumnName)
	{
		return sprintf(self::STATEMENT_DROP_COLUMN, $strTableName, $strColumnName);
	}

	/**
	 * Checks whether the given table name is valid.
	 * Throws an Exception if the table name is invalid.
	 *
	 * @param string $strTableName the table name to check
	 * @return void
	 */
	public static function checkTableName($strTableName, $blnAllowReserved = false)
	{
		if (!self::isValidTableName($strTableName)) {
			$strMessage = sprintf(
				$GLOBALS['TL_LANG']['ERR']['invalidTableName'],
				specialchars($strTableName)
			);
			throw new Exception($strMessage, 1);
		}
		if (!$blnAllowReserved && self::isReservedIdentifier($strTableName)) {
			$strMessage = sprintf(
				$GLOBALS['TL_LANG']['ERR']['reservedTableName'],
				$strTableName
			);
			throw new Exception($strMessage, 1);
		}
	}

	/**
	 * Checks whether the given table exists.
	 * Throws an Exception if the table name is invalid or does not exist.
	 *
	 * @param string $strTableName the table name to check
	 * @return void
	 */
	public static function checkTableExists($strTableName)
	{
		if (!self::getDB()->tableExists($strTableName, null, true)) {
			$strMessage = sprintf(
				$GLOBALS['TL_LANG']['ERR']['tableDoesNotExist'],
				$strTableName
			);
			throw new Exception($strMessage, 1);
		}
	}

	/**
	 * ensures that the given table does not exist.
	 * Throws an Exception if the table name is invalid or does exist.
	 *
	 * @param string $strTableName the table name to check
	 * @return void
	 */
	public static function checkTableDoesNotExist($strTableName)
	{
		if (self::getDB()->tableExists($strTableName, null, true)) {
			$strMessage = sprintf(
				$GLOBALS['TL_LANG']['ERR']['tableExists'],
				$strTableName
			);
			throw new Exception($strMessage, 1);
		}
	}

	/**
	 * Checks whether the column with the given name is not a MetaModel system column and is a valid Database
	 * column name, @see{MetaModelTableManipulation::isSystemColumn()} and @see{MetaModelTableManipulation::isValidColumnName()}.
	 * If there is any problem, an Exception is raised, stating the nature of the error in the Exception message.
	 *
	 * @param string $strColumnName the name of the column
	 * @return void
	 */
	public static function checkColumnName($strColumnName, $blnAllowReserved = false)
	{
		if (!self::isValidColumnName($strColumnName)) {
			$strMessage = sprintf(
				$GLOBALS['TL_LANG']['ERR']['invalidColumnName'],
				specialchars($strColumnName)
			);
			throw new Exception($strMessage, 1);
		}
		if (
			self::isSystemColumn($strColumnName)
			|| (!$blnAllowReserved && self::isReservedIdentifier($strColumnName))
		) {
			$strMessage = sprintf(
				$GLOBALS['TL_LANG']['ERR']['reservedColumnName'],
				$strColumnName
			);
			throw new Exception($strMessage, 1);
		}
	}

	/**
	 * Checks whether the given table exists.
	 * Throws an Exception if the table name is invalid or does not exist.
	 *
	 * @param string $strTableName the table name to check
	 * @param string $strColumnName the column name to check
	 * @return void
	 */
	public static function checkColumnExists($strTableName, $strColumnName)
	{
		self::checkTableName($strTableName, true); // this cant be handled by Contao Database class
		if (!self::getDB()->fieldExists($strColumnName, $strTableName, true)) {
			$strMessage = sprintf(
				$GLOBALS['TL_LANG']['ERR']['columnDoesNotExist'],
				$strColumnName,
				$strTableName
			);
			throw new Exception($strMessage, 1);
		}
	}

	/**
	 * Checks whether the given table exists.
	 * Throws an Exception if the table name is invalid or does not exist.
	 *
	 * @param string $strTableName the table name to check
	 * @param string $strColumnName the column name to check
	 * @return void
	 */
	public static function checkColumnDoesNotExist($strTableName, $strColumnName)
	{
		self::checkTableName($strTableName, true); // this cant be handled by Contao Database class
		if (self::getDB()->fieldExists($strColumnName, $strTableName, true)) {
			$strMessage = sprintf(
				$GLOBALS['TL_LANG']['ERR']['columnExists'],
				$strColumnName,
				$strTableName
			);
			throw new Exception($strMessage, 1);
		}
	}

	public static function executeQuery($strQuery)
	{
		try {
			self::getDB()->query($strQuery);
		} catch (Exception $e) {
			$strMessage = sprintf(
				$GLOBALS['TL_LANG']['ERR']['queryError'],
				$strQuery,
				$e->getMessage()
			);
			throw new Exception($strMessage, 1, $e);
		}
	}

	/**
	 * Creates a table with the given name.
	 * Throws Exception if the table name is invalid or already exists.
	 *
	 * @param string $strTableName the name of the new table to create.
	 * @return void
	 */
	public static function createTable($strTableName, $blnAllowReserved = false)
	{
		try {
			self::checkTableName($strTableName, $blnAllowReserved);
			self::checkTableDoesNotExist($strTableName);
			$strQuery = self::getCreateTableStatement($strTableName);
			self::executeQuery($strQuery);
		} catch (Exception $e) {
			$strMessage = sprintf(
				$GLOBALS['TL_LANG']['ERR']['createTableError'],
				$strTableName,
				$e->getMessage()
			);
			throw new Exception($strMessage, 1, $e);
		}
	}

	/**
	 * Renames a table with the given name to the given new name.
	 * Throws Exception if the new table name is invalid.
	 *
	 * @param string $strTableName the name of the table to rename.
	 * @param string $strNewTableName the name to which the table shall be renamed to.
	 * @return void
	 */
	public static function renameTable($strTableName, $strNewTableName, $blnAllowReserved = false)
	{
		if ($strTableName == $strNewTableName) {
			return;
		}
		try {
			self::checkTableName($strTableName, $blnAllowReserved);
			self::checkTableName($strNewTableName, $blnAllowReserved);
			self::checkTableExists($strTableName);
			self::checkTableDoesNotExist($strNewTableName);
			$strQuery = self::getRenameTableStatement($strTableName, $strNewTableName);
			self::executeQuery($strQuery);
		} catch (Exception $e) {
			$strMessage = sprintf(
				$GLOBALS['TL_LANG']['ERR']['renameTableError'],
				$strTableName,
				$strNewTableName,
				$e->getMessage()
			);
			throw new Exception($strMessage, 1, $e);
		}
	}

	/**
	 * Deletes the table with the given name.
	 * Throws Exception if the table name is invalid or the table does not exist.
	 *
	 * @param string $strTableName the name of the new table to delete.
	 * @return void
	 */
	public static function dropTable($strTableName, $blnAllowReserved = false)
	{
		try {
			self::checkTableName($strTableName, $blnAllowReserved);
			self::checkTableExists($strTableName);
			$strQuery = self::getDropTableStatement($strTableName);
			self::executeQuery($strQuery);
		} catch (Exception $e) {
			$strMessage = sprintf(
				$GLOBALS['TL_LANG']['ERR']['dropTableError'],
				$strTableName,
				$e->getMessage()
			);
			throw new Exception($strMessage, 1, $e);
		}
	}

	/**
	 * Add a column to a table.
	 *
	 * Throws Exception if the table does not exist, the column name is invalid or the column already exists.
	 *
	 * @param string $strTableName the name of the table to add the column to.
	 * @param string $strColumnName the name of the new column.
	 * @param string $strType the SQL type notation of the new column.
	 * @return void
	 */
	public static function createColumn($strTableName, $strColumnName, $strType, $blnAllowReserved = false)
	{
		try {
			self::checkTableName($strTableName, $blnAllowReserved);
			self::checkColumnName($strColumnName, $blnAllowReserved);
			self::checkColumnDoesNotExist($strTableName, $strColumnName);
			$strQuery = self::getCreateColumnStatement($strTableName, $strColumnName, $strType);
			self::executeQuery($strQuery);
		} catch (Exception $e) {
			$strMessage = sprintf(
				$GLOBALS['TL_LANG']['ERR']['createColumnError'],
				$strColumnName,
				$strTableName,
				$e->getMessage()
			);
			throw new Exception($strMessage, 1, $e);
		}
	}

	/**
	 * Rename a column and/or change it's type in a table.
	 *
	 * Throws Exception if the table does not exist, the column name is invalid or the column already exists.
	 *
	 * @param string $strTableName the name of the table the column is in.
	 * @param string $strColumnName the current name of the column to be renamed.
	 * @param string $strNewColumnName the new name for the column.
	 * @param string $strNewType the new SQL type notation of the column.
	 * @return void
	 */
	public static function renameColumn($strTableName, $strColumnName, $strNewColumnName, $strNewType, $blnAllowReserved = false)
	{
		if ($strColumnName == $strNewColumnName) {
			return;
		}
		self::changeColumn($strTableName, $strColumnName, $strNewColumnName, $strNewType, $blnAllowReserved);
	}

	public static function changeColumn($strTableName, $strColumnName, $strNewColumnName, $strNewType, $blnAllowReserved = false)
	{
		try {
			self::checkTableName($strTableName, $blnAllowReserved);
			self::checkColumnName($strColumnName, $blnAllowReserved);
			self::checkColumnName($strNewColumnName, $blnAllowReserved);
			self::checkTableExists($strTableName);
			self::checkColumnExists($strTableName, $strColumnName);
			if ($strColumnName != $strNewColumnName) {
				self::checkColumnDoesNotExist($strTableName, $strNewColumnName);
			}
			$strQuery = self::getChangeColumnStatement($strTableName, $strColumnName, $strNewColumnName, $strNewType);
			self::executeQuery($strQuery);
		} catch (Exception $e) {
			$strMessage = sprintf(
				$GLOBALS['TL_LANG']['ERR']['changeColumnError'],
				$strColumnName,
				$strNewColumnName,
				$strTableName,
				$e->getMessage()
			);
			throw new Exception($strMessage, 1, $e);
		}
	}

	/**
	 * Delete a column from a table.
	 * Throws Exception if the table does not exist, the column name is invalid or the column does not exist.
	 *
	 * @param string $strTableName the name of the table the column is in.
	 * @param string $strColumnName the name of the column to drop.
	 * @return void
	 */
	public static function dropColumn($strTableName, $strColumnName, $blnAllowReserved = false)
	{
		try {
			self::checkTableName($strTableName, $blnAllowReserved);
			self::checkColumnName($strColumnName, $blnAllowReserved);
			self::checkTableExists($strTableName);
			self::checkColumnExists($strTableName, $strColumnName);
			$strQuery = self::getDropColumnStatement($strTableName, $strColumnName);
			self::executeQuery($strQuery);
		} catch (Exception $e) {
			$strMessage = sprintf(
				$GLOBALS['TL_LANG']['ERR']['dropColumnError'],
				$strColumnName,
				$strTableName,
				$e->getMessage()
			);
			throw new Exception($strMessage, 1, $e);
		}
	}

	/**
	 * Enables or disables Variant support on a certain MetaModel table.
	 *
	 * @param string $strTableName the table name of the MetaModel.
	 * @param bool $blnVariantSupport flag if the support shall be turned on or off.
	 * @return void
	 */
	public static function setVariantSupport($strTableName, $blnVariantSupport)
	{
		if ($blnVariantSupport)
		{
			if (self::getDB()->tableExists($strTableName, null, true) && (!self::getDB()->fieldExists('varbase', $strTableName, true)))
			{
				self::createColumn($strTableName, 'varbase', 'char(1) NOT NULL default \'\'');
				self::createColumn($strTableName, 'vargroup', 'int(11) NOT NULL default 0');
				// TODO: we should also apply an index on vargroup here.

				// if there is pre-existing data in the table, we need to provide a separate 'vargroup' value to all of them,
				// we can do this safely by setting all vargroups to the id of the base item.
				self::getDB()->execute(sprintf('UPDATE %s SET vargroup=id, varbase=1', $strTableName));
			}
		} else {
			if (self::getDB()->tableExists($strTableName, null, true) && self::getDB()->fieldExists('varbase', $strTableName, true))
			{
				self::dropColumn($strTableName, 'varbase');
				self::dropColumn($strTableName, 'vargroup');
			}
		}
	}

	/** @deprecated */
	const STATEMENT_RENAME_COLUMN = 'ALTER TABLE %s CHANGE COLUMN %s %s %s';

	/** @deprecated use dropTable */
	public static function deleteTable($strTableName)
	{
		return self::dropTable($strTableName);
	}

}

