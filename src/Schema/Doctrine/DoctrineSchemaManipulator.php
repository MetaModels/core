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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\Schema\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use Doctrine\DBAL\Schema\Column;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Schema\SchemaDiff;
use Doctrine\DBAL\Schema\Table;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;
use ReflectionMethod;

use function in_array;

/**
 * This updates the database to be in sync with the passed schema.
 */
class DoctrineSchemaManipulator
{
    /**
     * The doctrine connection.
     *
     * @var Connection
     */
    private Connection $connection;

    /**
     * Create a new instance.
     *
     * @param Connection $connection The database connection to use.
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Update the database to be in sync with the passed doctrine information.
     *
     * @param DoctrineSchemaInformation $schemaInformation The schema information.
     *
     * @return void
     */
    public function updateDatabase(DoctrineSchemaInformation $schemaInformation): void
    {
        foreach ($this->buildChangeSet($schemaInformation) as $query) {
            $this->connection->executeStatement($query);
        }
    }

    /**
     * Obtain the list of tasks being performed.
     *
     * @param DoctrineSchemaInformation $schemaInformation The schema information.
     *
     * @return string[]
     */
    public function getScript(DoctrineSchemaInformation $schemaInformation): array
    {
        return $this->buildChangeSet($schemaInformation);
    }

    /**
     * Build the change set.
     *
     * @param DoctrineSchemaInformation $schemaInformation The schema information.
     *
     * @return list<string>
     * @throws Exception
     */
    private function buildChangeSet(DoctrineSchemaInformation $schemaInformation): array
    {
        $platform = $this->connection->getDatabasePlatform();
        $manager  = $this->connection->createSchemaManager();
        $current  = $manager->introspectSchema();
        $desired  = $schemaInformation->getSchema();

        // MetaModels never drops tables/columns on its own - carry anything not present in the
        // desired schema over from $current into $desired *before* diffing, unchanged, warning
        // about each. That way the comparator never sees them as missing and never proposes a
        // drop, on any platform - including one that has to rebuild the whole table for an
        // unrelated change (e.g. SQLite), where a drop suppressed only in the computed diff would
        // still silently lose the column's data during the rebuild's copy-back step.
        $this->ignoreUnwantedRemovals($current, $desired);

        $diff = $this->diff($current, $desired, $manager);

        return $platform->getAlterSchemaSQL($diff);
    }

    /**
     * Carry tables and columns present in $current but missing from $desired over into $desired
     * unchanged, warning about each.
     *
     * @param Schema $current The introspected (actual) schema.
     * @param Schema $desired The desired schema, mutated in place.
     *
     * @return void
     */
    private function ignoreUnwantedRemovals(Schema $current, Schema $desired): void
    {
        foreach ($current->getTables() as $table) {
            if (!$desired->hasTable($table->getName())) {
                // @codingStandardsIgnoreStart
                @trigger_error(
                    'Ignoring drop of table "' . $table->getName(),
                    E_USER_WARNING
                );
                // @codingStandardsIgnoreEnd
                $this->copyTableInto($desired, $table);
                continue;
            }

            $desiredTable = $desired->getTable($table->getName());
            foreach ($table->getColumns() as $column) {
                if ($desiredTable->hasColumn($column->getName())) {
                    continue;
                }
                // @codingStandardsIgnoreStart
                @trigger_error(
                    'Ignoring drop of column "' . $column->getName(),
                    E_USER_WARNING
                );
                // @codingStandardsIgnoreEnd
                $this->copyColumnInto($desiredTable, $column);
            }
        }
    }

    /**
     * Add a clone of $table to $desired.
     *
     * Schema has no public API for adding an already-built Table instance (only createTable(),
     * which builds an empty one) - Schema::_addTable() is @internal, but exactly what is needed
     * here, so this goes through reflection instead of rebuilding the table column by column.
     *
     * @param Schema $desired The schema to add the table to.
     * @param Table  $table   The table to add, taken as-is from the current (actual) schema.
     *
     * @return void
     *
     * @psalm-suppress InternalMethod
     */
    private function copyTableInto(Schema $desired, Table $table): void
    {
        (new ReflectionMethod($desired, '_addTable'))->invoke($desired, clone $table);
    }

    /**
     * Add a clone of $column to $table.
     *
     * Table has no public API for adding an already-built Column instance - Table::_addColumn()
     * is @internal, but exactly what is needed here, see copyTableInto().
     *
     * @param Table  $table  The table to add the column to.
     * @param Column $column The column to add, taken as-is from the current (actual) schema.
     *
     * @return void
     *
     * @psalm-suppress InternalMethod
     */
    private function copyColumnInto(Table $table, Column $column): void
    {
        (new ReflectionMethod($table, '_addColumn'))->invoke($table, clone $column);
    }

    private function diff(Schema $current, Schema $desired, AbstractSchemaManager $manager): SchemaDiff
    {
        // We have to "inherit" collation and charset for certain types as doctrine will report them in the current
        // columns and always mark them as changed when no charset/collation has been explicitly specified in the
        // desired column - despite being already in the correct condition "on disk".
        $checkTypes = [
            Types::ASCII_STRING,
            Types::STRING,
            Types::TEXT,
        ];

        $registry = Type::getTypeRegistry();
        foreach ($desired->getTables() as $table) {
            if (!$current->hasTable($table->getName())) {
                continue;
            }
            $existingTable = $current->getTable($table->getName());
            foreach ($table->getColumns() as $column) {
                if (!$existingTable->hasColumn($column->getName())) {
                    continue;
                }
                $existingColumn = $existingTable->getColumn($column->getName());
                if (!in_array($registry->lookupName($column->getType()), $checkTypes, true)) {
                     continue;
                }
                $this->inheritPlatformOptionIfNotSet('collation', $column, $existingColumn);
                $this->inheritPlatformOptionIfNotSet('charset', $column, $existingColumn);
            }
        }

        return $manager->createComparator()->compareSchemas($current, $desired);
    }

    private function inheritPlatformOptionIfNotSet(string $optionName, Column $column, Column $existingColumn): void
    {
        if (!$column->hasPlatformOption($optionName)) {
            if (!$existingColumn->hasPlatformOption($optionName)) {
                return;
            }
            $column->setPlatformOption($optionName, $existingColumn->getPlatformOption($optionName));
        }
    }
}
