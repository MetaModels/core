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
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Cliff Parnitzky <github@cliff-parnitzky.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Helper;

use ContaoCommunityAlliance\DcGeneral\Contao\InputProvider;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use MetaModels\Exceptions\Database\ColumnDoesNotExistException;
use MetaModels\Exceptions\Database\ColumnExistsException;
use MetaModels\Exceptions\Database\InvalidColumnNameException;
use MetaModels\Exceptions\Database\InvalidTableNameException;
use MetaModels\Exceptions\Database\TableDoesNotExistException;
use MetaModels\Exceptions\Database\TableExistsException;

use function in_array;
use function preg_match;
use function sprintf;
use function strtolower;

/**
 * This is the class for table manipulations like creation/renaming/deleting of tables and columns.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class TableManipulator
{
    /**
     * SQL statement template to create a table.
     * First parameter is the table name.
     */
    public const STATEMENT_CREATE_TABLE = '
            CREATE TABLE `%s` (
                `id` int(10) unsigned NOT NULL auto_increment,
                `pid` int(10) unsigned NOT NULL default \'0\',
                `sorting` int(10) unsigned NOT NULL default \'0\',
                `tstamp` int(10) unsigned NOT NULL default \'0\',
                PRIMARY KEY  (`id`)
            ) CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB ROW_FORMAT = DYNAMIC';

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
    public const STATEMENT_RENAME_COLUMN = 'ALTER TABLE `%s` CHANGE COLUMN `%s` `%s` %s';

    /**
     * SQL statement template to add a column to a table.
     * First parameter is the table name.
     * Second parameter is the column name.
     * Third parameter is the type of the new column.
     */
    public const STATEMENT_CREATE_COLUMN = 'ALTER TABLE `%s` ADD `%s` %s';

    /**
     * SQL statement template to delete a column from a table.
     * First parameter is the name of the column.
     */
    public const STATEMENT_DROP_COLUMN = 'ALTER TABLE `%s` DROP COLUMN `%s`';

    /**
     * SQL statement template to add a index to a column of a table.
     * First parameter is table name.
     * second parameter is indextype
     * third parameter is name of the column.
     */
    public const STATEMENT_ADD_INDEX_COLUMN = 'ALTER TABLE `%s` ADD %s(`%s`)';

    /**
     * List of reserved MySQL identifiers.
     *
     * @var string[]
     *
     * @deprecated We work with backticks for table names and columns instead reserved words.
     */
    protected static array $reservedWords = [];

    /**
     * List of reserved column post fix.
     *
     * @var string[]
     */
    protected static array $reservedColumnPostFix = ['__sort'];

    /**
     * Database connection.
     *
     * @var Connection
     */
    private Connection $connection;

    /**
     * All system columns that always are defined in a MetaModel table.
     *
     * When you alter this, ensure to also change @link{TableManipulatior::STATEMENT_CREATE_TABLE} above.
     *
     * @var string[]
     */
    private array $systemColumns;

    /**
     * TableManipulator constructor.
     *
     * @param Connection $connection    Database connection.
     * @param array      $systemColumns System columns that always are defined in a MetaModel table and are not
     *                                  attributes.
     */
    public function __construct(Connection $connection, array $systemColumns)
    {
        $this->connection    = $connection;
        $this->systemColumns = $systemColumns;
    }

    /**
     * Test if the given word is a reserved MySQL word.
     *
     * @param string $word The word to test.
     *
     * @return bool
     *
     * @deprecated We work with backticks for table names and columns instead reserved words.
     */
    public function isReservedWord($word): bool
    {
        return in_array(strtoupper($word), self::$reservedWords);
    }

    /**
     * Test if the given column post fix is a reserved by MetaModels.
     *
     * @param string $strColName The column name to test.
     *
     * @return bool
     */
    public function isReserveColumnPostFix($strColName): bool
    {
        $inputProvider = new InputProvider();

        if (!$inputProvider->hasValue('colname')
            || strtolower($strColName) !== strtolower($inputProvider->getValue('colname'))
        ) {
            return false;
        }

        foreach (self::$reservedColumnPostFix as $postFix) {
            if ($postFix !== strtolower(substr($strColName, -strlen($postFix)))) {
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
    public function isValidMySQLIdentifier(string $strName): bool
    {
        // Match for valid table/column name, according to MySQL, a table name must start
        // with a letter and must be combined of letters, decimals and underscore.
        return (1 == preg_match('/^[a-z_][a-z\d_]*$/i', $strName));
    }

    /**
     * Checks whether the given table name is valid.
     *
     * @param string $strTableName The table name to check.
     *
     * @return bool true if the table name is valid, false otherwise.
     */
    public function isValidTablename(string $strTableName): bool
    {
        return $this->isValidMySQLIdentifier($strTableName) && !$this->isReservedWord($strTableName);
    }

    /**
     * Checks whether the column with the given name is a MetaModel system column.
     *
     * @param string $strColName The name of the column.
     *
     * @return bool true if the column is a system column, false otherwise.
     */
    public function isValidColumnName(string $strColName): bool
    {
        return $this->isValidMySQLIdentifier($strColName)
            && !$this->isReservedWord($strColName)
            && !$this->isReserveColumnPostFix($strColName);
    }

    /**
     * Checks whether the column with the given name is a MetaModel system column.
     *
     * @param string $strColName The name of the column.
     *
     * @return bool true if the column is a system column, false otherwise.
     */
    public function isSystemColumn(string $strColName): bool
    {
        return in_array($strColName, $this->systemColumns);
    }

    /**
     * Checks whether the given table name is valid.
     *
     * @param string $strTableName The table name to check.
     *
     * @return void
     *
     * @throws InvalidTableNameException If an invalid table name has been passed.
     */
    public function checkTablename(string $strTableName): void
    {
        if (!$this->isValidTablename($strTableName)) {
            throw InvalidTableNameException::invalidCharacters($strTableName);
        }
    }

    /**
     * Checks whether the column with the given name is not a MetaModel system column and is a valid column name.
     *
     * If there is any problem, an Exception is raised, stating the nature of the error in the Exception message.
     *
     * @param string $strColName        The name of the column.
     * @param bool   $blnAllowSystemCol If this is set to true, no system column name checking will be applied.
     *
     * @return void
     *
     * @throws InvalidColumnNameException If an invalid column name has been passed.
     *
     * @see{MetaModelTableManipulation::isSystemColumn()} and @see{MetaModelTableManipulation::isValidColumnName()}.
     */
    public function checkColumnName(string $strColName, bool $blnAllowSystemCol = false): void
    {
        if (!$this->isValidColumnName($strColName)) {
            throw InvalidColumnNameException::invalidCharacters($strColName);
        }

        if ((!$blnAllowSystemCol) && $this->isSystemColumn($strColName)) {
            throw InvalidColumnNameException::systemColumn($strColName);
        }
    }

    /**
     * Checks whether the given table exists.
     *
     * @param string $strTableName The table name to check.
     *
     * @return void
     *
     * @throws InvalidTableNameException  If an invalid table name has been passed.
     * @throws TableDoesNotExistException If the table does not exist.
     * @throws Exception
     *
     * @phpcs:ignore Squiz.Commenting.FunctionCommentThrowTag
     */
    public function checkTableExists(string $strTableName): void
    {
        $this->checkTablename($strTableName);
        if (!$this->connection->createSchemaManager()->tablesExist([$strTableName])) {
            throw TableDoesNotExistException::withName($strTableName);
        }
    }

    /**
     * Ensures that the given table does not exist.
     *
     * @param string $strTableName The table name to check.
     *
     * @return void
     *
     * @throws InvalidTableNameException If an invalid table name has been passed.
     * @throws TableExistsException      If a table with the given name exists.
     * @throws Exception
     *
     * @phpcs:ignore Squiz.Commenting.FunctionCommentThrowTag
     */
    public function checkTableDoesNotExist(string $strTableName): void
    {
        $this->checkTablename($strTableName);
        if ($this->connection->createSchemaManager()->tablesExist([$strTableName])) {
            throw TableExistsException::withName($strTableName);
        }
    }

    /**
     * Creates a table with the given name.
     *
     * @param string $strTableName The name of the new table to create.
     *
     * @return void
     *
     * @throws InvalidTableNameException If an invalid table name has been passed.
     * @throws TableExistsException      If a table with the given name exists.
     * @throws Exception                 If query error.
     */
    public function createTable(string $strTableName): void
    {
        $this->checkTableDoesNotExist($strTableName);
        $this->connection->executeQuery(sprintf(self::STATEMENT_CREATE_TABLE, $strTableName));
    }

    /**
     * Renames a table with the given name to the given new name.
     *
     * @param string $strTableName    The name of the table to rename.
     * @param string $strNewTableName The name to which the table shall be renamed to.
     *
     * @return void
     *
     * @throws InvalidTableNameException  If an invalid table name has been passed.
     * @throws TableDoesNotExistException If the source table does not exist.
     * @throws TableExistsException       If a table with the given target name exists.
     * @throws Exception                  If query error.
     */
    public function renameTable(string $strTableName, string $strNewTableName): void
    {
        $this->checkTableExists($strTableName);
        $this->checkTableDoesNotExist($strNewTableName);

        $this->connection->executeQuery(sprintf(self::STATEMENT_RENAME_TABLE, $strTableName, $strNewTableName));
    }

    /**
     * Deletes the table with the given name.
     *
     * @param string $strTableName The name of the new table to delete.
     *
     * @return void
     *
     * @throws InvalidTableNameException  If an invalid table name has been passed.
     * @throws TableDoesNotExistException If the table does not exist.
     * @throws Exception                  If query error.
     */
    public function deleteTable(string $strTableName): void
    {
        $this->checkTableExists($strTableName);

        $this->connection->executeQuery(sprintf(self::STATEMENT_DROP_TABLE, $strTableName));
    }

    /**
     * Add an index to given tablename for specified columnname
     *
     * @param string $strTableName The table name.
     * @param string $strIndexType The index type.
     * @param string $strColName   The column name to add a index.
     *
     * @return void
     *
     * @throws InvalidTableNameException   If an invalid table name has been passed.
     * @throws TableDoesNotExistException  If the table does not exist.
     * @throws InvalidColumnNameException  If an invalid column name has been passed.
     * @throws ColumnDoesNotExistException If the column does not exist.
     * @throws Exception                   If query error.
     */
    public function addIndex(string $strTableName, string $strIndexType, string $strColName): void
    {
        $this->checkColumnExists($strTableName, $strColName);
        $this->connection->executeQuery(
            sprintf(
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
     * @param string $strTableName      The table name to check.
     * @param string $strColName        The column name to check.
     * @param bool   $blnAllowSystemCol If this is set to true, no system column name checking will be applied.
     *
     * @return void
     *
     * @throws InvalidTableNameException   If an invalid table name has been passed.
     * @throws TableDoesNotExistException  If the table does not exist.
     * @throws InvalidColumnNameException  If an invalid column name has been passed.
     * @throws ColumnDoesNotExistException If the column does not exist.
     *
     * @phpcs:ignore Squiz.Commenting.FunctionCommentThrowTag
     */
    public function checkColumnExists(string $strTableName, string $strColName, bool $blnAllowSystemCol = false): void
    {
        $this->checkTableExists($strTableName);
        $this->checkColumnName($strColName, $blnAllowSystemCol);

        if (!$this->fieldExists($strTableName, $strColName)) {
            throw ColumnDoesNotExistException::withName($strColName, $strTableName);
        }
    }

    /**
     * Checks whether the given column does not exist.
     *
     * @param string $strTableName      The table name to check.
     * @param string $strColName        The column name to check.
     * @param bool   $blnAllowSystemCol If this is set to true, no system column name checking will be applied.
     *
     * @return void
     *
     * @throws InvalidTableNameException  If an invalid table name has been passed.
     * @throws TableDoesNotExistException If the table does not exist.
     * @throws InvalidColumnNameException If an invalid column name has been passed.
     * @throws ColumnExistsException      If the column does exist.
     *
     * @phpcs:ignore Squiz.Commenting.FunctionCommentThrowTag
     */
    public function checkColumnDoesNotExist(
        string $strTableName,
        string $strColName,
        bool $blnAllowSystemCol = false
    ): void
    {
        $this->checkTableExists($strTableName);
        $this->checkColumnName($strColName, $blnAllowSystemCol);

        if ($this->fieldExists($strTableName, $strColName)) {
            throw ColumnExistsException::withName($strColName, $strTableName);
        }
    }

    /**
     * Add a column to a table.
     *
     * Throws Exception if the table does not exist, the column name is invalid or the column already exists.
     *
     * @param string $strTableName      The name of the table to add the column to.
     * @param string $strColumnName     The name of the new column.
     * @param string $strType           The SQL type notation of the new column.
     * @param bool   $blnAllowSystemCol If this is set to true, no system column name checking will be applied.
     *
     * @return void
     *
     * @throws Exception If query error.
     */
    public function createColumn(
        string $strTableName,
        string $strColumnName,
        string $strType,
        bool $blnAllowSystemCol = false
    ): void
    {
        $this->checkColumnDoesNotExist($strTableName, $strColumnName, $blnAllowSystemCol);
        $this->connection->executeQuery(
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
     * @param string $strTableName      The name of the table the column is in.
     * @param string $strColumnName     The current name of the column to be renamed.
     * @param string $strNewColumnName  The new name for the column.
     * @param string $strNewType        The new SQL type notation of the column.
     * @param bool   $blnAllowSystemCol If this is set to true, no system column name checking will be applied.
     *
     * @return void
     *
     * @throws Exception
     */
    public function renameColumn(
        string $strTableName,
        string $strColumnName,
        string $strNewColumnName,
        string $strNewType,
        bool $blnAllowSystemCol = false
    ): void
    {
        if ($strColumnName != $strNewColumnName) {
            $this->checkColumnExists($strTableName, $strColumnName, $blnAllowSystemCol);
            $this->checkColumnDoesNotExist($strTableName, $strNewColumnName, $blnAllowSystemCol);
        }
        $this->connection->executeQuery(
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
     *
     * Throws Exception if the table does not exist, the column name is invalid or the column does not exist.
     *
     * @param string $strTableName      The name of the table the column is in.
     * @param string $strColumnName     The name of the column to drop.
     * @param bool   $blnAllowSystemCol If this is set to true, no system column name checking will be applied.
     *
     * @return void
     *
     * @throws Exception
     */
    public function dropColumn(string $strTableName, string $strColumnName, bool $blnAllowSystemCol = false): void
    {
        $this->checkColumnExists($strTableName, $strColumnName, $blnAllowSystemCol);
        $this->connection->executeQuery(
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
     * @param string $strTableName      The table name of the MetaModel.
     * @param bool   $blnVariantSupport Flag if the support shall be turned on or off.
     *
     * @return void
     *
     * @throws Exception
     */
    public function setVariantSupport(string $strTableName, bool $blnVariantSupport): void
    {
        if ($blnVariantSupport) {
            if ($this->connection->createSchemaManager()->tablesExist([$strTableName])
                && (!$this->fieldExists($strTableName, 'varbase'))) {
                $this->createColumn($strTableName, 'varbase', 'char(1) NOT NULL default \'\'', true);
                $this->createColumn($strTableName, 'vargroup', 'int(11) NOT NULL default 0', true);

                // If there is pre-existing data in the table, we need to provide a separate 'vargroup' value to all of
                // them, we can do this safely by setting all vargroups to the id of the base item.
                $this->connection->executeQuery(
                    sprintf('UPDATE `%1$s` SET %1$s.vargroup=id, %1$s.varbase=1', $strTableName)
                );
            }
        } elseif ($this->connection->createSchemaManager()->tablesExist([$strTableName])
                  && $this->fieldExists($strTableName, 'varbase')
        ) {
            $this->dropColumn($strTableName, 'varbase', true);
            $this->dropColumn($strTableName, 'vargroup', true);
        }
    }

    /**
     * Check is a table column exists.
     *
     * @param string $strTableName  Table name.
     * @param string $strColumnName Column name.
     *
     * @return bool
     *
     * @throws Exception
     */
    private function fieldExists(string $strTableName, string $strColumnName): bool
    {
        $columns = $this->connection->createSchemaManager()->listTableColumns($strTableName);

        return isset($columns[$strColumnName]);
    }
}
