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
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Types\Types;

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
     *
     * @psalm-suppress InternalProperty
     */
    private function buildChangeSet(DoctrineSchemaInformation $schemaInformation): array
    {
        $platform = $this->connection->getDatabasePlatform();
        $manager  = $this->connection->createSchemaManager();
        $current  = $manager->introspectSchema();
        $diff     = $this->diff($current, $schemaInformation->getSchema(), $manager);

        foreach ($diff->changedTables as $changedTable) {
            foreach ($changedTable->removedColumns as $removedColumn) {
                // @codingStandardsIgnoreStart
                @trigger_error(
                    'Ignoring drop of column "' . $removedColumn->getName(),
                    E_USER_WARNING
                );
                // @codingStandardsIgnoreEnd
            }
            $changedTable->removedColumns = [];
        }
        foreach ($diff->removedTables as $removedTable) {
            // @codingStandardsIgnoreStart
            @trigger_error(
                'Ignoring drop of table "' . $removedTable->getName(),
                E_USER_WARNING
            );
            // @codingStandardsIgnoreEnd
        }
        $diff->removedTables = [];

        return $platform->getAlterSchemaSQL($diff);
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
