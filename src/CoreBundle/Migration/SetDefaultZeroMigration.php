<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2021 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2021 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\CoreBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Table;

/**
 * This migration changes all 'pid', 'sorting', 'tstamp', 'vargroup', 'varbase' columns
 * to have zero default values.
 *
 * @psalm-type TColumnInformation=array{
 *   default: string,
 *   type: string
 * }
 */
class SetDefaultZeroMigration extends AbstractMigration
{
    private const COLUMN_NAMES = [
        'pid'      => [
            'default' => '0',
            'type'    => 'int(11)',
        ],
        'sorting'  => [
            'default' => '0',
            'type'    => 'int(11)',
        ],
        'tstamp'   => [
            'default' => '0',
            'type'    => 'int(11)',
        ],
        'vargroup' => [
            'default' => '0',
            'type'    => 'int(11)',
        ],
        'varbase'  => [
            'default' => '',
            'type'    => 'char(1)',
        ],
    ];

    /**
     * The database connection.
     *
     * @var Connection
     */
    private Connection $connection;

    /**
     * Create a new instance.
     *
     * @param Connection $connection The database connection.
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Return the name.
     *
     * @return string
     */
    public function getName(): string
    {
        return 'Set default zero values for system columns in MetaModels.';
    }

    /**
     * Must only run if:
     * - the MM tables are present AND
     * - default zero values for system columns values not set.
     *
     * @return bool
     */
    public function shouldRun(): bool
    {
        $nonNullableColumns = $this->fetchNonDefaultZeroColumns();
        if (empty($nonNullableColumns)) {
            return false;
        }

        return true;
    }

    /**
     * Collect the columns to be updated and update them.
     *
     * @return MigrationResult
     */
    public function run(): MigrationResult
    {
        $columnsByTable = $this->fetchNonDefaultZeroColumns();
        $message        = [];
        foreach ($columnsByTable as $tableName => $columns) {
            foreach ($columns as $columnName => $information) {
                $this->fixColumn($tableName, $columnName, $information);
                $message[] = $tableName . '.' . $columnName;
            }
        }

        return new MigrationResult(true, 'Adjusted column(s): ' . implode(', ', $message));
    }

    /**
     * Fetch all columns that are not nullable yet.
     *
     * @return array<string, array<string, TColumnInformation>>
     */
    private function fetchNonDefaultZeroColumns(): array
    {
        $tables = $this->fetchTableNames();
        if (empty($tables)) {
            return [];
        }
        $schemaManager = $this->connection->getSchemaManager();

        $result = [];
        foreach ($tables as $tableName) {
            $columns = $schemaManager->listTableColumns($tableName);
            foreach ($columns as $column) {
                $columnName = $column->getName();
                if (!array_key_exists($columnName, self::COLUMN_NAMES)) {
                    continue;
                }
                $default = self::COLUMN_NAMES[$columnName]['default'];
                if ((false === $column->getNotnull()) || ($default !== $column->getDefault())) {
                    if (!isset($result[$tableName])) {
                        $result[$tableName] = [];
                    }
                    $result[$tableName][$columnName] = self::COLUMN_NAMES[$columnName];
                }
            }
        }

        return $result;
    }

    /**
     * Obtain the names of table columns.
     *
     * @return list<string>
     */
    private function fetchTableNames(): array
    {
        return array_map(
            function (Table $table): string {
                return $table->getName();
            },
            array_filter(
                $this
                    ->connection
                    ->getSchemaManager()
                    ->listTables(),
                function (Table $table): bool {
                    return 'mm_' === substr($table->getName(), 0, 3);
                }
            )
        );
    }

    // @codingStandardsIgnoreStart
    /**
     * Fix a table column.
     *
     * @param string             $tableName   The name of the table.
     * @param string             $columnName  The name of the column.
     * @param TColumnInformation $information The column information.
     *
     * @return void
     */
    private function fixColumn(string $tableName, string $columnName, array $information): void
    {
        $this->connection->executeQuery(
            sprintf(
                'ALTER TABLE `%1$s` CHANGE COLUMN `%2$s` `%2$s` %3$s NOT NULL DEFAULT %4$s',
                $tableName,
                $columnName,
                $information['type'],
                var_export($information['default'], true),
            )
        );
    }
    // @codingStandardsIgnoreEnd
}
