<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2026 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2026 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\CoreBundle\Migration;

use Contao\CoreBundle\Migration\AbstractMigration;
use Contao\CoreBundle\Migration\MigrationResult;
use Doctrine\DBAL\ArrayParameterType;
use Doctrine\DBAL\Connection;

use function array_keys;
use function array_unique;
use function count;
use function json_encode;
use function strtolower;

use const JSON_THROW_ON_ERROR;

/**
 * Hands the text columns of MetaModels over to the output encoding migration of Contao 6.
 *
 * Up to Contao 5.7 values were stored entity encoded ("<" as "&#60;"), since Contao 6 they are stored as entered and
 * encoded when they are printed. Contao records the columns to convert in the table "prepare_for_output_encoding"
 * while still on 5.7, but it only knows the static columns of the data containers. The attribute columns of the
 * MetaModels tables and the shared value tables of the attributes are generated at run time and are missing there.
 *
 * This migration adds them so that the migration of Contao converts them as well. It runs before the one of Contao
 * and only as long as Contao still has columns waiting for the conversion - without such columns the data is either
 * new or has been converted already and must not be decoded a second time.
 */
final class OutputEncodingMigration extends AbstractMigration
{
    private const REGISTRY = 'prepare_for_output_encoding';

    /**
     * @param Connection                           $connection     The database connection.
     * @param array<string, string>                $attributeTypes Attribute types with text in the MetaModel table,
     *                                                             mapped to the encoding.
     * @param array<string, array<string, string>> $tables         Value tables of the attributes, mapped to their
     *                                                             columns and the encoding.
     */
    public function __construct(
        private readonly Connection $connection,
        private readonly array $attributeTypes,
        private readonly array $tables,
    ) {
    }

    #[\Override]
    public function getName(): string
    {
        return 'MetaModels: Register the text columns for the output encoding migration of Contao.';
    }

    #[\Override]
    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();
        if (!$schemaManager->tablesExist([self::REGISTRY])) {
            return false;
        }

        $waiting = (int) $this->connection->fetchOne(
            'SELECT COUNT(*) FROM `' . self::REGISTRY . '` WHERE performed_migration = 0'
        );

        return $waiting > 0 && [] !== $this->missing();
    }

    #[\Override]
    public function run(): MigrationResult
    {
        $missing = $this->missing();
        foreach ($missing as [$table, $column, $encoding]) {
            $this->connection->insert(
                self::REGISTRY,
                [
                    'table_name'          => $table,
                    'column_name'         => $column,
                    'virtual_target_name' => null,
                    'encoding_options'    => json_encode([$encoding], JSON_THROW_ON_ERROR),
                    'performed_migration' => 0,
                ]
            );
        }

        return new MigrationResult(
            true,
            'Registered ' . count($missing) . ' MetaModels columns for the output encoding migration.'
        );
    }

    /**
     * Collect the columns that exist but are not registered yet.
     *
     * @return list<array{0: string, 1: string, 2: string}>
     */
    private function missing(): array
    {
        $existing = [];
        $registered = $this->connection->fetchAllNumeric(
            'SELECT table_name, column_name FROM `' . self::REGISTRY . '`'
        );
        foreach ($registered as $row) {
            $existing[strtolower((string) $row[0]) . '.' . strtolower((string) $row[1])] = true;
        }

        $missing = [];
        foreach ($this->candidates() as [$table, $column, $encoding]) {
            if (
                isset($existing[strtolower($table) . '.' . strtolower($column)])
                || !$this->hasColumn($table, $column)
            ) {
                continue;
            }

            $existing[strtolower($table) . '.' . strtolower($column)] = true;
            $missing[] = [$table, $column, $encoding];
        }

        return $missing;
    }

    /**
     * Collect all columns which are in charge, whether they exist or not.
     *
     * @return list<array{0: string, 1: string, 2: string}>
     */
    private function candidates(): array
    {
        $candidates = [];
        foreach ($this->tables as $table => $columns) {
            foreach ($columns as $column => $encoding) {
                $candidates[] = [$table, $column, $encoding];
            }
        }

        if ([] === $this->attributeTypes || !$this->tablesExist(['tl_metamodel', 'tl_metamodel_attribute'])) {
            return $candidates;
        }

        $rows = $this->connection->fetchAllAssociative(
            'SELECT m.tableName AS tableName, a.colname AS colName, a.type AS type
               FROM tl_metamodel_attribute AS a
               JOIN tl_metamodel AS m ON m.id = a.pid
              WHERE a.type IN (?)',
            [array_unique(array_keys($this->attributeTypes))],
            [ArrayParameterType::STRING]
        );

        foreach ($rows as $row) {
            $candidates[] = [
                (string) $row['tableName'],
                (string) $row['colName'],
                $this->attributeTypes[(string) $row['type']],
            ];
        }

        return $candidates;
    }

    private function hasColumn(string $table, string $column): bool
    {
        if ('' === $table) {
            return false;
        }

        $schemaManager = $this->connection->createSchemaManager();

        return $schemaManager->tablesExist([$table])
            && $schemaManager->introspectTableByUnquotedName($table)->hasColumn($column);
    }

    /**
     * @param list<string> $tables
     */
    private function tablesExist(array $tables): bool
    {
        return $this->connection->createSchemaManager()->tablesExist($tables);
    }
}
