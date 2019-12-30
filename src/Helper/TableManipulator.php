<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2019 The MetaModels team.
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
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Helper;

use ContaoCommunityAlliance\DcGeneral\Contao\InputProvider;
use Doctrine\DBAL\Connection;
use MetaModels\Exceptions\Database\ColumnDoesNotExistException;
use MetaModels\Exceptions\Database\ColumnExistsException;
use MetaModels\Exceptions\Database\InvalidColumnNameException;
use MetaModels\Exceptions\Database\InvalidTableNameException;
use MetaModels\Exceptions\Database\TableDoesNotExistException;
use MetaModels\Exceptions\Database\TableExistsException;

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
    const STATEMENT_CREATE_TABLE = '
            CREATE TABLE `%s` (
                `id` int(10) unsigned NOT NULL auto_increment,
                `pid` int(10) unsigned NOT NULL default \'0\',
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
     * Third parameter is the new name of the column.
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
     * SQL statement template to add a index to a column of a table.
     * First parameter is table name.
     * second parameter is indextype
     * third parameter is name of the column.
     */
    const STATEMENT_ADD_INDEX_COLUMN = 'ALTER TABLE %s ADD %s(%s)';

    /**
     * List of reserved MySQL identifiers.
     *
     * @var string[]
     */
    protected static $reservedWords = array(
        // MySQL 5.5 and previous to MySQL 8.0.
        'ACCESSIBLE','ACCOUNT','ACTION','ADD','ADMIN','AFTER','AGAINST','AGGREGATE','ALGORITHM','ALL','ALTER','ALWAYS',
        'ANALYSE','ANALYZE','AND','ANY','AS','ASC','ASCII','ASENSITIVE','AT','AUTHORS','AUTOEXTEND_SIZE',
        'AUTO_INCREMENT','AVG','AVG_ROW_LENGTH','BACKUP','BEFORE','BEGIN','BETWEEN','BIGINT','BINARY','BINLOG','BIT',
        'BLOB','BLOCK','BOOL','BOOLEAN','BOTH','BTREE','BUCKETS','BY','BYTE','CACHE','CALL','CASCADE','CASCADED','CASE',
        'CATALOG_NAME','CHAIN','CHANGE','CHANGED','CHANNEL','CHAR','CHARACTER','CHARSET','CHECK','CHECKSUM','CIPHER',
        'CLASS_ORIGIN','CLIENT','CLONE','CLOSE','COALESCE','CODE','COLLATE','COLLATION','COLUMN','COLUMNS',
        'COLUMN_FORMAT','COLUMN_NAME','COMMENT','COMMIT','COMMITTED','COMPACT','COMPLETION','COMPONENT','COMPRESSED',
        'COMPRESSION','CONCURRENT','CONDITION','CONNECTION','CONSISTENT','CONSTRAINT','CONSTRAINT_CATALOG',
        'CONSTRAINT_NAME','CONSTRAINT_SCHEMA','CONTAINS','CONTEXT','CONTINUE','CONTRIBUTORS','CONVERT','CPU','CREATE',
        'CROSS','CUBE','CUME_DIST','CURRENT','CURRENT_DATE','CURRENT_TIME','CURRENT_TIMESTAMP','CURRENT_USER','CURSOR',
        'CURSOR_NAME','DATA','DATABASE','DATABASES','DATAFILE','DATE','DATETIME','DAY','DAY_HOUR','DAY_MICROSECOND',
        'DAY_MINUTE','DAY_SECOND','DEALLOCATE','DEC','DECIMAL','DECLARE','DEFAULT','DEFAULT_AUTH','DEFINER',
        'DEFINITION','DELAYED','DELAY_KEY_WRITE','DELETE','DENSE_RANK','DESC','DESCRIBE','DESCRIPTION','DES_KEY_FILE',
        'DETERMINISTIC','DIAGNOSTICS','DIRECTORY','DISABLE','DISCARD','DISK','DISTINCT','DISTINCTROW','DIV','DO',
        'DOUBLE','DROP','DUAL','DUMPFILE','DUPLICATE','DYNAMIC','EACH','ELSE','ELSEIF','EMPTY','ENABLE','ENCLOSED',
        'ENCRYPTION','END','ENDS','ENGINE','ENGINES','ENUM','ERROR','ERRORS','ESCAPE','ESCAPED','EVENT','EVENTS',
        'EVERY','EXCEPT','EXCHANGE','EXCLUDE','EXECUTE','EXISTS','EXIT','EXPANSION','EXPIRE','EXPLAIN','EXPORT',
        'EXTENDED','EXTENT_SIZE','FALSE','FAST','FAULTS','FETCH','FIELDS','FILE','FILE_BLOCK_SIZE','FILTER','FIRST',
        'FIRST_VALUE','FIXED','FLOAT','FLOAT4','FLOAT8','FLUSH','FOLLOWING','FOLLOWS','FOR','FORCE','FOREIGN','FORMAT',
        'FOUND','FRAC_SECOND','FROM','FULL','FULLTEXT','FUNCTION','GENERAL','GENERATED','GEOMCOLLECTION','GEOMETRY',
        'GEOMETRYCOLLECTION','GET','GET_FORMAT','GET_MASTER_PUBLIC_KEY','GLOBAL','GRANT','GRANTS','GROUP','GROUPING',
        'GROUPS','GROUP_REPLICATION','HANDLER','HASH','HAVING','HELP','HIGH_PRIORITY','HISTOGRAM','HISTORY','HOST',
        'HOSTS','HOUR','HOUR_MICROSECOND','HOUR_MINUTE','HOUR_SECOND','IDENTIFIED','IF','IGNORE','IGNORE_SERVER_IDS',
        'IMPORT','IN','INDEX','INDEXES','INFILE','INITIAL_SIZE','INNER','INNOBASE','INNODB','INOUT','INSENSITIVE',
        'INSERT','INSERT_METHOD','INSTALL','INSTANCE','INT','INT1','INT2','INT3','INT4','INT8','INTEGER','INTERVAL',
        'INTO','INVISIBLE','INVOKER','IO','IO_AFTER_GTIDS','IO_BEFORE_GTIDS','IO_THREAD','IPC','IS','ISOLATION',
        'ISSUER','ITERATE','JOIN','JSON','JSON_TABLE','KEY','KEYS','KEY_BLOCK_SIZE','KILL','LAG','LANGUAGE','LAST',
        'LAST_VALUE','LEAD','LEADING','LEAVE','LEAVES','LEFT','LESS','LEVEL','LIKE','LIMIT','LINEAR','LINES',
        'LINESTRING','LIST','LOAD','LOCAL','LOCALTIME','LOCALTIMESTAMP','LOCK','LOCKED','LOCKS','LOGFILE','LOGS',
        'LONG','LONGBLOB','LONGTEXT','LOOP','LOW_PRIORITY','MASTER','MASTER_AUTO_POSITION','MASTER_BIND',
        'MASTER_CONNECT_RETRY','MASTER_DELAY','MASTER_HEARTBEAT_PERIOD','MASTER_HOST','MASTER_LOG_FILE',
        'MASTER_LOG_POS','MASTER_PASSWORD','MASTER_PORT','MASTER_PUBLIC_KEY_PATH','MASTER_RETRY_COUNT',
        'MASTER_SERVER_ID','MASTER_SSL','MASTER_SSL_CA','MASTER_SSL_CAPATH','MASTER_SSL_CERT','MASTER_SSL_CIPHER',
        'MASTER_SSL_CRL','MASTER_SSL_CRLPATH','MASTER_SSL_KEY','MASTER_SSL_VERIFY_SERVER_CERT','MASTER_TLS_VERSION',
        'MASTER_USER','MATCH','MAXVALUE','MAX_CONNECTIONS_PER_HOUR','MAX_QUERIES_PER_HOUR','MAX_ROWS','MAX_SIZE',
        'MAX_UPDATES_PER_HOUR','MAX_USER_CONNECTIONS','MEDIUM','MEDIUMBLOB','MEDIUMINT','MEDIUMTEXT','MEMORY','MERGE',
        'MESSAGE_TEXT','MICROSECOND','MIDDLEINT','MIGRATE','MINUTE','MINUTE_MICROSECOND','MINUTE_SECOND','MIN_ROWS',
        'MOD','MODE','MODIFIES','MODIFY','MONTH','MULTILINESTRING','MULTIPOINT','MULTIPOLYGON','MUTEX','MYSQL_ERRNO',
        'NAME','NAMES','NATIONAL','NATURAL','NCHAR','NDB','NDBCLUSTER','NESTED','NEVER','NEW','NEXT','NO','NODEGROUP',
        'NONE','NOT','NOWAIT','NO_WAIT','NO_WRITE_TO_BINLOG','NTH_VALUE','NTILE','NULL','NULLS','NUMBER','NUMERIC',
        'NVARCHAR','OF','OFFSET','OLD_PASSWORD','ON','ONE','ONE_SHOT','ONLY','OPEN','OPTIMIZE','OPTIMIZER_COSTS',
        'OPTION','OPTIONALLY','OPTIONS','OR','ORDER','ORDINALITY','OTHERS','OUT','OUTER','OUTFILE','OVER','OWNER',
        'PACK_KEYS','PAGE','PARSER','PARSE_GCOL_EXPR','PARTIAL','PARTITION','PARTITIONING','PARTITIONS','PASSWORD',
        'PATH','PERCENT_RANK','PERSIST','PERSIST_ONLY','PHASE','PLUGIN','PLUGINS','PLUGIN_DIR','POINT','POLYGON','PORT',
        'PRECEDES','PRECEDING','PRECISION','PREPARE','PRESERVE','PREV','PRIMARY','PRIVILEGES','PROCEDURE','PROCESS',
        'PROCESSLIST','PROFILE','PROFILES','PROXY','PURGE','QUARTER','QUERY','QUICK','RANGE','RANK','READ','READS',
        'READ_ONLY','READ_WRITE','REAL','REBUILD','RECOVER','RECURSIVE','REDOFILE','REDO_BUFFER_SIZE','REDUNDANT',
        'REFERENCE','REFERENCES','REGEXP','RELAY','RELAYLOG','RELAY_LOG_FILE','RELAY_LOG_POS','RELAY_THREAD','RELEASE',
        'RELOAD','REMOTE','REMOVE','RENAME','REORGANIZE','REPAIR','REPEAT','REPEATABLE','REPLACE','REPLICATE_DO_DB',
        'REPLICATE_DO_TABLE','REPLICATE_IGNORE_DB','REPLICATE_IGNORE_TABLE','REPLICATE_REWRITE_DB',
        'REPLICATE_WILD_DO_TABLE','REPLICATE_WILD_IGNORE_TABLE','REPLICATION','REQUIRE','RESET','RESIGNAL','RESOURCE',
        'RESPECT','RESTART','RESTORE','RESTRICT','RESUME','RETURN','RETURNED_SQLSTATE','RETURNS','REUSE','REVERSE',
        'REVOKE','RIGHT','RLIKE','ROLE','ROLLBACK','ROLLUP','ROTATE','ROUTINE','ROW','ROWS','ROW_COUNT','ROW_FORMAT',
        'ROW_NUMBER','RTREE','SAVEPOINT','SCHEDULE','SCHEMA','SCHEMAS','SCHEMA_NAME','SECOND','SECOND_MICROSECOND',
        'SECURITY','SELECT','SENSITIVE','SEPARATOR','SERIAL','SERIALIZABLE','SERVER','SESSION','SET','SHARE','SHOW',
        'SHUTDOWN','SIGNAL','SIGNED','SIMPLE','SKIP','SLAVE','SLOW','SMALLINT','SNAPSHOT','SOCKET','SOME','SONAME',
        'SOUNDS','SOURCE','SPATIAL','SPECIFIC','SQL','SQLEXCEPTION','SQLSTATE','SQLWARNING','SQL_AFTER_GTIDS',
        'SQL_AFTER_MTS_GAPS','SQL_BEFORE_GTIDS','SQL_BIG_RESULT','SQL_BUFFER_RESULT','SQL_CACHE','SQL_CALC_FOUND_ROWS',
        'SQL_NO_CACHE','SQL_SMALL_RESULT','SQL_THREAD','SQL_TSI_DAY','SQL_TSI_FRAC_SECOND','SQL_TSI_HOUR',
        'SQL_TSI_MINUTE','SQL_TSI_MONTH','SQL_TSI_QUARTER','SQL_TSI_SECOND','SQL_TSI_WEEK','SQL_TSI_YEAR','SRID','SSL',
        'STACKED','START','STARTING','STARTS','STATS_AUTO_RECALC','STATS_PERSISTENT','STATS_SAMPLE_PAGES','STATUS',
        'STOP','STORAGE','STORED','STRAIGHT_JOIN','STRING','SUBCLASS_ORIGIN','SUBJECT','SUBPARTITION','SUBPARTITIONS',
        'SUPER','SUSPEND','SWAPS','SWITCHES','SYSTEM','TABLE','TABLES','TABLESPACE','TABLE_CHECKSUM','TABLE_NAME',
        'TEMPORARY','TEMPTABLE','TERMINATED','TEXT','THAN','THEN','THREAD_PRIORITY','TIES','TIME','TIMESTAMP',
        'TIMESTAMPADD','TIMESTAMPDIFF','TINYBLOB','TINYINT','TINYTEXT','TO','TRAILING','TRANSACTION','TRIGGER',
        'TRIGGERS','TRUE','TRUNCATE','TYPE','TYPES','UNBOUNDED','UNCOMMITTED','UNDEFINED','UNDO','UNDOFILE',
        'UNDO_BUFFER_SIZE','UNICODE','UNINSTALL','UNION','UNIQUE','UNKNOWN','UNLOCK','UNSIGNED','UNTIL','UPDATE',
        'UPGRADE','USAGE','USE','USER','USER_RESOURCES','USE_FRM','USING','UTC_DATE','UTC_TIME','VALIDATION',
        'VALUES','VARBINARY','VARCHAR','VARCHARACTER','VARIABLES','VARYING','VCPU','VIEW','VIRTUAL','VISIBLE',
        'WAIT','WARNINGS','WEEK','WEIGHT_STRING','WHEN','WHERE','WHILE','WINDOW','WITH','WITHOUT','WORK','WRAPPER',
        'WRITE','X509','XA','XID','XML','XOR','YEAR','YEAR_MONTH','ZEROFILL'
    );

    /**
     * List of reserved column post fix.
     *
     * @var string[]
     */
    protected static $reservedColumnPostFix = array('__sort');

    /**
     * Database connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * All system columns that always are defined in a MetaModel table.
     *
     * When you alter this, ensure to also change @link{TableManipulatior::STATEMENT_CREATE_TABLE} above.
     *
     * @var string[]
     */
    private $systemColumns;

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
     */
    public function isReservedWord($word)
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
    public function isReserveColumnPostFix($strColName)
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
    public function isValidMySQLIdentifier($strName)
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
    public function isValidTablename($strTableName)
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
    public function isValidColumnName($strColName)
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
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function isSystemColumn($strColName)
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
     * @throws \Exception If an invalid table name has been passed.
     *
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function checkTablename($strTableName)
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
     * @param string  $strColName        The name of the column.
     *
     * @param boolean $blnAllowSystemCol If this is set to true, no system column name checking will be applied.
     *
     * @return void
     *
     * @throws \Exception If an invalid column name has been passed.
     *
     * @see{MetaModelTableManipulation::isSystemColumn()} and @see{MetaModelTableManipulation::isValidColumnName()}.
     *
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function checkColumnName($strColName, $blnAllowSystemCol = false)
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
     * @throws \Exception If an invalid table name has been passed or the table does not exist.
     *
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function checkTableExists($strTableName)
    {
        $this->checkTablename($strTableName);
        if (!$this->connection->getSchemaManager()->tablesExist([$strTableName])) {
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
     * @throws \Exception If an invalid table name has been passed or a table with the given name exists.
     *
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function checkTableDoesNotExist($strTableName)
    {
        $this->checkTablename($strTableName);
        if ($this->connection->getSchemaManager()->tablesExist([$strTableName])) {
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
     * @throws \Exception If an invalid table name has been passed or a table with the given name exists.
     */
    public function createTable($strTableName)
    {
        $this->checkTableDoesNotExist($strTableName);
        $this->connection->query(sprintf(self::STATEMENT_CREATE_TABLE, $strTableName));
    }

    /**
     * Renames a table with the given name to the given new name.
     *
     * @param string $strTableName    The name of the table to rename.
     *
     * @param string $strNewTableName The name to which the table shall be renamed to.
     *
     * @return void
     *
     * @throws \Exception If an invalid table name has been passed.
     */
    public function renameTable($strTableName, $strNewTableName)
    {
        $this->checkTableExists($strTableName);
        $this->checkTableDoesNotExist($strNewTableName);

        $this->connection->query(sprintf(self::STATEMENT_RENAME_TABLE, $strTableName, $strNewTableName));
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
    public function deleteTable($strTableName)
    {
        $this->checkTableExists($strTableName);

        $this->connection->query(sprintf(self::STATEMENT_DROP_TABLE, $strTableName));
    }

    /**
     * Add a index to given tablename for specified columnname
     *
     * @param string $strTableName The table name.
     *
     * @param string $strIndexType The index type.
     *
     * @param string $strColName   The column name to add a index.
     *
     * @return void
     *
     * @throws \Exception If an invalid table name has been passed or the table does not exist, the column name is
     *                    invalid or the column does not exist.
     */
    public function addIndex($strTableName, $strIndexType, $strColName)
    {
        $this->checkColumnExists($strTableName, $strColName);
        $this->connection->query(
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
     * @param string  $strTableName      The table name to check.
     *
     * @param string  $strColName        The column name to check.
     *
     * @param boolean $blnAllowSystemCol If this is set to true, no system column name checking will be applied.
     *
     * @return void
     *
     * @throws \Exception If an invalid table name has been passed or the table does not exist, the column name is
     *                    invalid or the column does not exist.
     *
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function checkColumnExists($strTableName, $strColName, $blnAllowSystemCol = false)
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
     * @param string  $strTableName      The table name to check.
     *
     * @param string  $strColName        The column name to check.
     *
     * @param boolean $blnAllowSystemCol If this is set to true, no system column name checking will be applied.
     *
     * @return void
     *
     * @throws \Exception If an invalid table name has been passed or the table does not exist, the column name is
     *                    invalid or the column does not exist.
     *
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function checkColumnDoesNotExist($strTableName, $strColName, $blnAllowSystemCol = false)
    {
        $this->checkTableExists($strTableName);
        $this->checkColumnName($strColName, $blnAllowSystemCol);

        if ($this->fieldExists($strColName, $strTableName)) {
            throw ColumnExistsException::withName($strColName, $strTableName);
        }
    }

    /**
     * Add a column to a table.
     *
     * Throws Exception if the table does not exist, the column name is invalid or the column already exists.
     *
     * @param string  $strTableName      The name of the table to add the column to.
     *
     * @param string  $strColumnName     The name of the new column.
     *
     * @param string  $strType           The SQL type notation of the new column.
     *
     * @param boolean $blnAllowSystemCol If this is set to true, no system column name checking will be applied.
     *
     * @return void
     */
    public function createColumn($strTableName, $strColumnName, $strType, $blnAllowSystemCol = false)
    {
        $this->checkColumnDoesNotExist($strTableName, $strColumnName, $blnAllowSystemCol);
        $this->connection->query(
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
     * @param string  $strTableName      The name of the table the column is in.
     *
     * @param string  $strColumnName     The current name of the column to be renamed.
     *
     * @param string  $strNewColumnName  The new name for the column.
     *
     * @param string  $strNewType        The new SQL type notation of the column.
     *
     * @param boolean $blnAllowSystemCol If this is set to true, no system column name checking will be applied.
     *
     * @return void
     */
    public function renameColumn(
        $strTableName,
        $strColumnName,
        $strNewColumnName,
        $strNewType,
        $blnAllowSystemCol = false
    ) {
        if ($strColumnName != $strNewColumnName) {
            $this->checkColumnExists($strTableName, $strColumnName, $blnAllowSystemCol);
            $this->checkColumnDoesNotExist($strTableName, $strNewColumnName, $blnAllowSystemCol);
        }
        $this->connection->query(
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
     * @param string  $strTableName      The name of the table the column is in.
     *
     * @param string  $strColumnName     The name of the column to drop.
     *
     * @param boolean $blnAllowSystemCol If this is set to true, no system column name checking will be applied.
     *
     * @return void
     */
    public function dropColumn($strTableName, $strColumnName, $blnAllowSystemCol = false)
    {
        $this->checkColumnExists($strTableName, $strColumnName, $blnAllowSystemCol);
        $this->connection->query(
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
     *
     * @param bool   $blnVariantSupport Flag if the support shall be turned on or off.
     *
     * @return void
     */
    public function setVariantSupport($strTableName, $blnVariantSupport)
    {
        if ($blnVariantSupport) {
            if ($this->connection->getSchemaManager()->tablesExist([$strTableName])
                && (!$this->fieldExists($strTableName, 'varbase'))) {
                $this->createColumn($strTableName, 'varbase', 'char(1) NOT NULL default \'\'', true);
                $this->createColumn($strTableName, 'vargroup', 'int(11) NOT NULL default 0', true);

                // If there is pre-existing data in the table, we need to provide a separate 'vargroup' value to all of
                // them, we can do this safely by setting all vargroups to the id of the base item.
                $this->connection->query(sprintf('UPDATE %s SET vargroup=id, varbase=1', $strTableName));
            }
        } else {
            if ($this->connection->getSchemaManager()->tablesExist([$strTableName])
                && $this->fieldExists($strTableName, 'varbase')
            ) {
                $this->dropColumn($strTableName, 'varbase', true);
                $this->dropColumn($strTableName, 'vargroup', true);
            }
        }
    }

    /**
     * Check is a table column exists.
     *
     * @param string $strTableName  Table name.
     * @param string $strColumnName Column name.
     *
     * @return bool
     */
    private function fieldExists($strTableName, $strColumnName)
    {
        $columns = $this->connection->getSchemaManager()->listTableColumns($strTableName);

        return isset($columns[$strColumnName]);
    }
}
