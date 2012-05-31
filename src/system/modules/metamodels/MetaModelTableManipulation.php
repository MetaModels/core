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
 * This is the class for table manipulations like creation/renaming/deleting of tables and columns.
 * 
 * @package	   MetaModel
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
	const STATEMENT_RENAME_COLUMN = 'ALTER TABLE %s CHANGE COLUMN %s %s %s';

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
	protected static $systemColumns = array('id', 'pid', 'sorting', 'tstamp');

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
	 * 
	 * @return bool true if the table name is valid, false otherwise.
	 */
	public static function isValidTablename($strTableName)
	{
		// match for valid table name, according to MySQL, a table name must start
		// with a letter and must be combined of letters, decimals and underscore.
		return (bool)preg_match('/^[a-z_][a-z\d_]*$/iu', $strTableName);
	}

	/**
	 * Checks whether the column with the given name is a MetaModel system column.
	 * 
	 * @param string $strColName the name of the column
	 * 
	 * @return bool true if the column is a system column, false otherwise.
	 */
	public static function isValidColumnName($strColName)
	{
		return (bool)preg_match('/^[a-z_][a-z\d_]*$/i', $strColName);
	}

	/**
	 * Checks whether the column with the given name is a MetaModel system column.
	 * 
	 * @param string $strColName the name of the column
	 * 
	 * @return bool true if the column is a system column, false otherwise.
	 */
	public static function isSystemColumn($strColName)
	{
		return in_array($strColName, self::$systemColumns);
	}

	/**
	 * Checks whether the given table name is valid.
	 * Throws an Exception if the table name is invalid.
	 * 
	 * @param string $strTableName the table name to check
	 * 
	 * @return void
	 */
	public static function checkTablename($strTableName)
	{
		if (!self::isValidTablename($strTableName))
		{
			throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['invalidTableName'], $strTableName));
		}
	}

	/**
	 * Checks whether the column with the given name is not a MetaModel system column and is a valid Database 
	 * column name, @see{MetaModelTableManipulation::isSystemColumn()} and @see{MetaModelTableManipulation::isValidColumnName()}.
	 * If there is any problem, an Exception is raised, stating the nature of the error in the Exception message.
	 * 
	 * @param string $strColName the name of the column
	 * 
	 * @return void
	 */
	public static function checkColumnName($strColName)
	{
		if (!self::isValidColumnName($strColName))
		{
			throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['invalidColumnName'], $strColName));
		}

		if (self::isSystemColumn($strColName))
		{
			throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['systemColumn'], $strColName));
		}
	}

	/**
	 * Checks whether the given table exists.
	 * Throws an Exception if the table name is invalid or does not exist.
	 * 
	 * @param string $strTableName the table name to check
	 * 
	 * @return void
	 */
	public static function checkTableExists($strTableName)
	{
		self::checkTablename($strTableName);
		if (!self::getDB()->tableExists($strTableName, null, true))
		{
			throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['tableDoesNotExist'], $strTableName)); 
		}
	}

	/**
	 * ensures that the given table does not exist.
	 * Throws an Exception if the table name is invalid or does exist.
	 * 
	 * @param string $strTableName the table name to check
	 * 
	 * @return void
	 */
	public static function checkTableDoesNotExist($strTableName)
	{
		self::checkTablename($strTableName);
		if (self::getDB()->tableExists($strTableName, null, true))
		{
			throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['tableExists'], $strTableName)); 
		}
	}

	/**
	 * Creates a table with the given name.
	 * Throws Exception if the table name is invalid or already exists.
	 * 
	 * @param string $strTableName the name of the new table to create.
	 * 
	 * @return void
	 */
	public static function createTable($strTableName)
	{
		self::checkTableDoesNotExist($strTableName);
		self::getDB()->execute(sprintf(self::STATEMENT_CREATE_TABLE, $strTableName));
	}

	/**
	 * Renames a table with the given name to the given new name.
	 * Throws Exception if the new table name is invalid.
	 * 
	 * @param string $strTableName    the name of the table to rename.
	 * 
	 * @param string $strNewTableName the name to which the table shall be renamed to.
	 * 
	 * @return void
	 * 	 */
	public static function renameTable($strTableName, $strNewTableName)
	{
		self::checkTableExists($strTableName);
		self::checkTableDoesNotExist($strNewTableName);

		self::getDB()->execute(sprintf(self::STATEMENT_RENAME_TABLE, $strTableName, $strNewTableName));
	}

	/**
	 * Deletes the table with the given name.
	 * Throws Exception if the table name is invalid or the table does not exist.
	 * 
	 * @param string $strTableName the name of the new table to delete.
	 * 
	 * @return void
	 */
	public static function deleteTable($strTableName)
	{
		self::checkTableExists($strTableName);

		self::getDB()->execute(sprintf(self::STATEMENT_DROP_TABLE, $strTableName));
	}

	/**
	 * Checks whether the given table exists.
	 * Throws an Exception if the table name is invalid or does not exist.
	 * 
	 * @param string $strTableName the table name to check
	 * 
	 * @param string $strColName   the column name to check
	 * 
	 * 
	 * @return void
	 */
	public static function checkColumnExists($strTableName, $strColName)
	{
		self::checkTableExists($strTableName);
		self::checkColumnName($strTableName);
		if (!self::getDB()->fieldExists($strColName, $strTableName, true))
		{
			throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['columnDoesNotExist'], $strColName, $strTableName)); 
		}
	}

	/**
	 * Checks whether the given table exists.
	 * Throws an Exception if the table name is invalid or does not exist.
	 * 
	 * @param string $strTableName the table name to check
	 * 
	 * @param string $strColName   the column name to check
	 * 
	 * 
	 * @return void
	 */
	public static function checkColumnDoesNotExist($strTableName, $strColName)
	{
		self::checkTableExists($strTableName);
		self::checkColumnName($strTableName);
		if (self::getDB()->fieldExists($strColName, $strTableName, true))
		{
			throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['columnExists'], $strColName)); 
		}
	}



	/**
	 * Add a column to a table.
	 * 
	 * Throws Exception if the table does not exist, the column name is invalid or the column already exists.
	 * 
	 * @param string $strTableName  the name of the table to add the column to.
	 * 
	 * @param string $strColumnName the name of the new column.
	 * 
	 * @param string $strType       the SQL type notation of the new column.
	 * 
	 * 
	 * @return void
	 */
	public static function createColumn($strTableName, $strColumnName, $strType)
	{
		self::checkColumnDoesNotExist($strTableName, $strColumnName);
		// TODO: throw exceptions
		self::getDB()->execute(
						sprintf(
							self::STATEMENT_CREATE_COLUMN,
							$strTableName,
							$strColumnName,
							$strType
						)
		);
	}

	/**
	 * Rename a column and/or change it's type in a table.
	 * 
	 * Throws Exception if the table does not exist, the column name is invalid or the column already exists.
	 * 
	 * @param string $strTableName     the name of the table the column is in.
	 * 
	 * @param string $strColumnName    the current name of the column to be renamed.
	 * 
	 * @param string $strNewColumnName the new name for the column.
	 * 
	 * @param string $strNewType       the new SQL type notation of the column.
	 * 
	 * 
	 * @return void
	 */
	public static function renameColumn($strTableName, $strColumnName, $strNewColumnName, $strNewType)
	{
		if ($strColumnName != $strNewColumnName)
		{
			self::checkColumnExists($strTableName, $strColumnName);
			self::checkColumnDoesNotExist($strTableName, $strNewColumnName);
		}
		// TODO: throw exceptions
		self::getDB()->execute(
						sprintf(
							self::STATEMENT_RENAME_COLUMN,
							$strTableName,
							$strColumnName,
							$strNewColumnName,
							$strNewType
						)
		);
	}

	/**
	 * Delete a column from a table.
	 * Throws Exception if the table does not exist, the column name is invalid or the column does not exist.
	 * 
	 * @param string $strTableName  the name of the table the column is in.
	 * 
	 * @param string $strColumnName the name of the column to drop.
	 * 
	 * @return void
	 */
	public static function dropColumn($strTableName, $strColumnName)
	{
		self::checkColumnExists($strTableName, $strColumnName);
		// TODO: throw exceptions
		self::getDB()->execute(
						sprintf(
							self::STATEMENT_DROP_COLUMN, 
							$strTableName, 
							$strColumnName
						)
		);
	}

	/**
	 * Enables or disables Variant support on a certain MetaModel table.
	 * 
	 * @param string $strTableName      the table name of the MetaModel.
	 * 
	 * @param bool   $blnVariantSupport flag if the support shall be turned on or off.
	 * 
	 * @return void
	 */
	public static function setVariantSupport($strTableName, $blnVariantSupport)
	{
		if ($blnVariantSupport)
		{
			if (!self::getDB()->fieldExists('varbase', $strTableName, true))
			{
				self::createColumn($strTableName, 'varbase', 'char(1) NOT NULL default \'\'');
				self::createColumn($strTableName, 'vargroup', 'int(11) NOT NULL default 0');
				// TODO: we should also apply an index on vargroup here.

				// if there is pre-existing data in the table, we need to provide a separate 'vargroup' value to all of them,
				// we can do this safely by setting all vargroups to the id of the base item.
				self::getDB()->execute(sprintf('UPDATE %s SET vargroup=id, varbase=1', $strTableName));
			}
		} else {
			if (self::getDB()->fieldExists('varbase', $strTableName, true))
			{
				self::dropColumn($strTableName, 'varbase');
				self::dropColumn($strTableName, 'vargroup');
			}
		}
	}
}

?>