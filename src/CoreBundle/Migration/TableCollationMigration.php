<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2020 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2020 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\CoreBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;

/**
 * This migration changes collation of all mm_* databases to the collation from the default table options
 * and/or DB engine to the engine from the default table options.
 */
class TableCollationMigration extends AbstractMigration
{
    /**
     * The database connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * The default table options.
     *
     * @var array
     */
    private $defaultTableOptions;

    /**
     * Create a new instance.
     *
     * @param Connection $connection          The database connection.
     * @param array      $defaultTableOptions The default table options.
     */
    public function __construct(Connection $connection, array $defaultTableOptions)
    {
        $this->connection          = $connection;
        $this->defaultTableOptions = $defaultTableOptions;
    }

    /**
     * Return the name.
     *
     * @return string
     */
    public function getName(): string
    {
        return \sprintf(
            'Change collation to %1$s and/or DB engine to %2$s of all mm_* tables.',
            $this->defaultTableOptions['collate'],
            $this->defaultTableOptions['engine']
        );
    }

    /**
     * Must only run if:
     * - the mm_* tables are present AND
     * - there collation is not utf8mb4_unicode_ci OR
     * - these engine is not InnoDB.
     *
     * @return bool
     */
    public function shouldRun(): bool
    {
        $tables = $this->fetchPendingTables();
        if (empty($tables)) {
            return false;
        }

        return true;
    }

    /**
     * Collect the tables to be updated and update them.
     *
     * @return MigrationResult
     */
    public function run(): MigrationResult
    {
        $tables  = $this->fetchPendingTables();
        $message = [];
        foreach ($tables as $table) {
            $this->fixTable($table);
            $message[] = $table;
        }

        return new MigrationResult(true, 'Adjusted table(s): ' . implode(', ', $message));
    }

    /**
     * Fetch all tables that are not right collection or DB engine yet.
     *
     * @return array
     */
    private function fetchPendingTables(): array
    {
        $schemaManager = $this->connection->getSchemaManager();
        $tableNames    = $schemaManager->listTableNames();

        $results = [];
        foreach ($tableNames as $tableName) {
            // Only MM model tables.
            if ('mm_' !== substr($tableName, 0, 3)) {
                continue;
            }

            // Retrieve table data.
            $result = $this->connection
                ->executeQuery(sprintf('SHOW TABLE STATUS LIKE \'%1$s\'', $tableName))
                ->fetch();

            // Check collation and DB engine and collect tables with false data.
            if (($this->defaultTableOptions['collate'] !== $result['Collation'])
                || ($this->defaultTableOptions['engine'] !== $result['Engine'])
            ) {
                $results[] = $tableName;
            }
        }

        return $results;
    }

    /**
     * Fix a table collation and DB engine.
     *
     * @param string $tableName The name of the table.
     *
     * @return void
     */
    private function fixTable(string $tableName): void
    {
        $this->connection->query(
            sprintf(
                'ALTER TABLE %1$s
                ENGINE=%2$s
                DEFAULT CHARSET=%3$s COLLATE %4$s
                ROW_FORMAT=DYNAMIC',
                $tableName,
                $this->defaultTableOptions['engine'],
                $this->defaultTableOptions['charset'],
                $this->defaultTableOptions['collate']
            )
        );
    }
}
