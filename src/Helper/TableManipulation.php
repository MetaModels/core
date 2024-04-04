<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2023 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Henry Lamorski <henry.lamorski@mailbox.org>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Cliff Parnitzky <github@cliff-parnitzky.de>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Helper;

use Contao\System;
use ContaoCommunityAlliance\DcGeneral\Contao\InputProvider;
use MetaModels\IMetaModelsServiceContainer;
use MetaModels\MetaModelsServiceContainer;

/**
 * This is the class for table manipulations like creation/renaming/deleting of tables and columns.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class TableManipulation
{
    /**
     * SQL statement template to create a table.
     * First parameter is the table name.
     */
    public const STATEMENT_CREATE_TABLE = '
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
    public const STATEMENT_RENAME_TABLE = 'ALTER TABLE `%s` RENAME TO `%s`';

    /**
     * SQL statement template to drop a table.
     * First parameter is the table name of the table to drop.
     */
    public const STATEMENT_DROP_TABLE = 'DROP TABLE `%s`';

    /**
     * SQL statement template to rename a column of a table.
     * First parameter is table name.
     * Second parameter is the old name of the column.
     * Third parameter is the new name of the column.
     * Fourth parameter is the new type of the column.
     */
    public const STATEMENT_RENAME_COLUMN = 'ALTER TABLE `%s` CHANGE COLUMN %s %s %s';

    /**
     * SQL statement template to add a column to a table.
     * First parameter is the table name.
     * Second parameter is the column name.
     * Third parameter is the type of the new column.
     */
    public const STATEMENT_CREATE_COLUMN = 'ALTER TABLE `%s` ADD %s %s';

    /**
     * SQL statement template to delete a column from a table.
     * First parameter is the name of the column.
     */
    public const STATEMENT_DROP_COLUMN = 'ALTER TABLE `%s` DROP COLUMN %s';

    /**
     * SQL statement template to add a index to a column of a table.
     * First parameter is table name.
     * second parameter is indextype
     * third parameter is name of the column.
     */
    public const STATEMENT_ADD_INDEX_COLUMN = 'ALTER TABLE `%s` ADD %s(%s)';

    /**
     * List of reserved column post fix.
     *
     * @var string[]
     */
    protected static $reservedColumnPostFix = ['__sort'];

    /**
     * All system columns that always are defined in a MetaModel table.
     *
     * When you alter this, ensure to also change @link{TableManipulation::STATEMENT_CREATE_TABLE} above.
     *
     * @var string[]
     */
    protected static $systemColumns = array('id', 'pid', 'sorting', 'tstamp');

    /**
     * Returns the Contao database instance to use.
     *
     * @return \Database the database instance.
     *
     * @deprecated
     */
    protected static function getDB()
    {
        /** @psalm-suppress DeprecatedClass */
        $serviceContainer = System::getContainer()->get(MetaModelsServiceContainer::class);
        assert($serviceContainer instanceof IMetaModelsServiceContainer);
        /** @psalm-suppress DeprecatedMethod */
        return $serviceContainer->getDatabase();
    }

    /**
     * Test if the given column post fix is a reserved by MetaModels.
     *
     * @param string $strColName The column name to test.
     *
     * @return bool
     */
    public static function isReserveColumnPostFix($strColName)
    {
        $inputProvider = new InputProvider();

        if (
            !$inputProvider->hasValue('colname')
            || \strtolower($strColName) !== \strtolower($inputProvider->getValue('colname'))
        ) {
            return false;
        }

        foreach (self::$reservedColumnPostFix as $postFix) {
            if ($postFix !== \strtolower(\substr($strColName, -\strlen($postFix)))) {
                continue;
            }

            return true;
        }

        return false;
    }

    /**
     * Ensure that an identifier is valid in MySQL context.
     *
     * @param string $strName The identifier to check.
     *
     * @return bool
     */
    public static function isValidMySQLIdentifier($strName)
    {
        // Match for valid table/column name, according to MySQL, a table name must start
        // with a letter and must be combined of letters, decimals and underscore.
        return (1 === \preg_match('/^[a-z_][a-z\d_]*$/i', $strName));
    }

    /**
     * Checks whether the given table name is valid.
     *
     * @param string $strTableName The table name to check.
     *
     * @return bool true if the table name is valid, false otherwise.
     */
    public static function isValidTablename($strTableName)
    {
        return self::isValidMySQLIdentifier($strTableName);
    }

    /**
     * Checks whether the column with the given name is a MetaModel system column.
     *
     * @param string $strColName The name of the column.
     *
     * @return bool true if the column is a system column, false otherwise.
     */
    public static function isValidColumnName($strColName)
    {
        return self::isValidMySQLIdentifier($strColName) && !self::isReserveColumnPostFix($strColName);
    }

    /**
     * Checks whether the column with the given name is a MetaModel system column.
     *
     * @param string $strColName The name of the column.
     *
     * @return bool true if the column is a system column, false otherwise.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public static function isSystemColumn($strColName)
    {
        return \in_array($strColName, $GLOBALS['METAMODELS_SYSTEM_COLUMNS']);
    }

    /**
     * Checks whether the given table name is valid.
     *
     * @param string $strTableName The table name to check.
     *
     * @return void
     *
     * @throws \Exception If an invalid table name has been passed.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public static function checkTablename($strTableName)
    {
        if (!self::isValidTablename($strTableName)) {
            throw new \Exception(\sprintf($GLOBALS['TL_LANG']['ERR']['invalidTableName'], $strTableName));
        }
    }

    /**
     * Checks whether the column with the given name is not a MetaModel system column and is a valid column name.
     *
     * If there is any problem, an Exception is raised, stating the nature of the error in the Exception message.
     *
     * @param string  $strColName        The name of the column.
     * @param boolean $blnAllowSystemCol If this is set to true, no system column name checking will be applied.
     *
     * @return void
     *
     * @throws \Exception If an invalid column name has been passed.
     *
     * @see{MetaModelTableManipulation::isSystemColumn()} and @see{MetaModelTableManipulation::isValidColumnName()}.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public static function checkColumnName($strColName, $blnAllowSystemCol = false)
    {
        if (!self::isValidColumnName($strColName)) {
            throw new \Exception(\sprintf($GLOBALS['TL_LANG']['ERR']['invalidColumnName'], $strColName));
        }

        if ((!$blnAllowSystemCol) && self::isSystemColumn($strColName)) {
            throw new \Exception(\sprintf($GLOBALS['TL_LANG']['ERR']['systemColumn'], $strColName));
        }
    }

    /**
     * Checks whether the given table exists.
     *
     * @param string $strTableName The table name to check.
     *
     * @return void
     *
     * @throws \Exception If an invalid table name has been passed or the table does not exist.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public static function checkTableExists($strTableName)
    {
        self::checkTablename($strTableName);
        /** @psalm-suppress DeprecatedMethod */
        if (!self::getDB()->tableExists($strTableName, null, true)) {
            throw new \Exception(\sprintf($GLOBALS['TL_LANG']['ERR']['tableDoesNotExist'], $strTableName));
        }
    }

    /**
     * Ensures that the given table does not exist.
     *
     * @param string $strTableName The table name to check.
     *
     * @return void
     *
     * @throws \Exception If an invalid table name has been passed or a table with the given name exists.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public static function checkTableDoesNotExist($strTableName)
    {
        self::checkTablename($strTableName);
        /** @psalm-suppress DeprecatedMethod */
        if (self::getDB()->tableExists($strTableName, null, true)) {
            throw new \Exception(\sprintf($GLOBALS['TL_LANG']['ERR']['tableExists'], $strTableName));
        }
    }

    /**
     * Creates a table with the given name.
     *
     * @param string $strTableName The name of the new table to create.
     *
     * @return void
     *
     * @throws \Exception If an invalid table name has been passed or a table with the given name exists.
     */
    public static function createTable($strTableName)
    {
        self::checkTableDoesNotExist($strTableName);
        /** @psalm-suppress DeprecatedMethod */
        self::getDB()->execute(\sprintf(self::STATEMENT_CREATE_TABLE, $strTableName));
    }

    /**
     * Renames a table with the given name to the given new name.
     *
     * @param string $strTableName    The name of the table to rename.
     * @param string $strNewTableName The name to which the table shall be renamed to.
     *
     * @return void
     *
     * @throws \Exception If an invalid table name has been passed.
     */
    public static function renameTable($strTableName, $strNewTableName)
    {
        self::checkTableExists($strTableName);
        self::checkTableDoesNotExist($strNewTableName);
        /** @psalm-suppress DeprecatedMethod */
        self::getDB()->execute(\sprintf(self::STATEMENT_RENAME_TABLE, $strTableName, $strNewTableName));
    }

    /**
     * Deletes the table with the given name.
     *
     * @param string $strTableName The name of the new table to delete.
     *
     * @return void
     *
     * @throws \Exception If an invalid table name has been passed or the table does not exist.
     */
    public static function deleteTable($strTableName)
    {
        self::checkTableExists($strTableName);
        /** @psalm-suppress DeprecatedMethod */
        self::getDB()->execute(\sprintf(self::STATEMENT_DROP_TABLE, $strTableName));
    }

    /**
     * Add a index to given tablename for specified columnname
     *
     * @param string $strTableName The table name.
     * @param string $strIndexType The index type.
     * @param string $strColName   The column name to add a index.
     *
     * @return void
     *
     * @throws \Exception If an invalid table name has been passed or the table does not exist, the column name is
     *                    invalid or the column does not exist.
     */
    public static function addIndex($strTableName, $strIndexType, $strColName)
    {
        self::checkColumnExists($strTableName, $strColName);
        /** @psalm-suppress DeprecatedMethod */
        self::getDB()->execute(
            \sprintf(
                self::STATEMENT_ADD_INDEX_COLUMN,
                $strTableName,
                $strIndexType,
                $strColName
            )
        );
    }

    /**
     * Checks whether the given table exists.
     *
     * @param string  $strTableName      The table name to check.
     * @param string  $strColName        The column name to check.
     * @param boolean $blnAllowSystemCol If this is set to true, no system column name checking will be applied.
     *
     * @return void
     *
     * @throws \Exception If an invalid table name has been passed or the table does not exist, the column name is
     *                    invalid or the column does not exist.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public static function checkColumnExists($strTableName, $strColName, $blnAllowSystemCol = false)
    {
        self::checkTableExists($strTableName);
        self::checkColumnName($strColName, $blnAllowSystemCol);
        /** @psalm-suppress DeprecatedMethod */
        if (!self::getDB()->fieldExists($strColName, $strTableName, true)) {
            throw new \Exception(
                \sprintf($GLOBALS['TL_LANG']['ERR']['columnDoesNotExist'], $strColName, $strTableName)
            );
        }
    }

    /**
     * Checks whether the given column does not exist.
     *
     * @param string  $strTableName      The table name to check.
     * @param string  $strColName        The column name to check.
     * @param boolean $blnAllowSystemCol If this is set to true, no system column name checking will be applied.
     *
     * @return void
     *
     * @throws \Exception If an invalid table name has been passed or the table does not exist, the column name is
     *                    invalid or the column does not exist.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public static function checkColumnDoesNotExist($strTableName, $strColName, $blnAllowSystemCol = false)
    {
        self::checkTableExists($strTableName);
        self::checkColumnName($strColName, $blnAllowSystemCol);
        /** @psalm-suppress DeprecatedMethod */
        if (self::getDB()->fieldExists($strColName, $strTableName, true)) {
            throw new \Exception(\sprintf($GLOBALS['TL_LANG']['ERR']['columnExists'], $strColName, $strTableName));
        }
    }

    /**
     * Add a column to a table.
     *
     * Throws Exception if the table does not exist, the column name is invalid or the column already exists.
     *
     * @param string  $strTableName      The name of the table to add the column to.
     * @param string  $strColumnName     The name of the new column.
     * @param string  $strType           The SQL type notation of the new column.
     *
     * @param boolean $blnAllowSystemCol If this is set to true, no system column name checking will be applied.
     *
     * @return void
     */
    public static function createColumn($strTableName, $strColumnName, $strType, $blnAllowSystemCol = false)
    {
        self::checkColumnDoesNotExist($strTableName, $strColumnName, $blnAllowSystemCol);
        /** @psalm-suppress DeprecatedMethod */
        self::getDB()->execute(
            \sprintf(
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
     * @param string  $strTableName      The name of the table the column is in.
     * @param string  $strColumnName     The current name of the column to be renamed.
     * @param string  $strNewColumnName  The new name for the column.
     * @param string  $strNewType        The new SQL type notation of the column.
     * @param boolean $blnAllowSystemCol If this is set to true, no system column name checking will be applied.
     *
     * @return void
     */
    public static function renameColumn(
        $strTableName,
        $strColumnName,
        $strNewColumnName,
        $strNewType,
        $blnAllowSystemCol = false
    ) {
        if ($strColumnName != $strNewColumnName) {
            self::checkColumnExists($strTableName, $strColumnName, $blnAllowSystemCol);
            self::checkColumnDoesNotExist($strTableName, $strNewColumnName, $blnAllowSystemCol);
        }
        /** @psalm-suppress DeprecatedMethod */
        self::getDB()->execute(
            \sprintf(
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
     *
     * Throws Exception if the table does not exist, the column name is invalid or the column does not exist.
     *
     * @param string  $strTableName      The name of the table the column is in.
     * @param string  $strColumnName     The name of the column to drop.
     * @param boolean $blnAllowSystemCol If this is set to true, no system column name checking will be applied.
     *
     * @return void
     */
    public static function dropColumn($strTableName, $strColumnName, $blnAllowSystemCol = false)
    {
        self::checkColumnExists($strTableName, $strColumnName, $blnAllowSystemCol);
        /** @psalm-suppress DeprecatedMethod */
        self::getDB()->execute(
            \sprintf(
                self::STATEMENT_DROP_COLUMN,
                $strTableName,
                $strColumnName
            )
        );
    }

    /**
     * Enables or disables Variant support on a certain MetaModel table.
     *
     * @param string $strTableName      The table name of the MetaModel.
     * @param bool   $blnVariantSupport Flag if the support shall be turned on or off.
     *
     * @return void
     */
    public static function setVariantSupport($strTableName, $blnVariantSupport)
    {
        if ($blnVariantSupport) {
            /** @psalm-suppress DeprecatedMethod */
            if (
                self::getDB()->tableExists($strTableName, null, true)
                && (!self::getDB()->fieldExists('varbase', $strTableName, true))
            ) {
                self::createColumn($strTableName, 'varbase', 'char(1) NOT NULL default \'\'', true);
                self::createColumn($strTableName, 'vargroup', 'int(11) NOT NULL default 0', true);

                // If there is pre-existing data in the table, we need to provide a separate 'vargroup' value to all of
                // them, we can do this safely by setting all vargroups to the id of the base item.
                /** @psalm-suppress DeprecatedMethod */
                self::getDB()->execute(\sprintf('UPDATE `%s` t SET t.vargroup=id, t.varbase=1', $strTableName));
            }
        } else {
            /** @psalm-suppress DeprecatedMethod */
            if (
                self::getDB()->tableExists($strTableName, null, true)
                && self::getDB()->fieldExists('varbase', $strTableName, true)
            ) {
                self::dropColumn($strTableName, 'varbase', true);
                self::dropColumn($strTableName, 'vargroup', true);
            }
        }
    }
}
