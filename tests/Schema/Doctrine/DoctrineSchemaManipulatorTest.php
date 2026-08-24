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

namespace MetaModels\Test\Schema\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Types\Types;
use MetaModels\Schema\Doctrine\DoctrineSchemaInformation;
use MetaModels\Schema\Doctrine\DoctrineSchemaManipulator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;

/**
 * MetaModels never drops tables/columns on its own behalf - it only warns about schema elements
 * it no longer manages. TableDiff/SchemaDiff became immutable value objects in DBAL 4, so a table
 * or column that should survive gets carried over into the *desired* schema instead (unchanged),
 * see DoctrineSchemaManipulator::ignoreUnwantedRemovals(). Erasing it from the current schema
 * instead (the first attempt at this fix) looked equivalent for a table that needs no other
 * change, but silently lost the data of an orphaned column whenever the table needed a real
 * rebuild for an unrelated reason (SQLite has to rebuild for every column-level change) - the
 * rebuild's copy-back step only carries over columns the diff still knows about.
 */
#[CoversClass(DoctrineSchemaManipulator::class)]
final class DoctrineSchemaManipulatorTest extends TestCase
{
    public function testIgnoreUnwantedRemovalsCarriesATableMissingFromDesiredOver(): void
    {
        $current = new Schema();
        $current->createTable('mm_kept');
        $current->createTable('mm_orphan');
        $desired = new Schema();
        $desired->createTable('mm_kept');

        $this->invokeIgnoreUnwantedRemovals($current, $desired);

        self::assertTrue($desired->hasTable('mm_orphan'));
        // The carried-over table must be a clone, not the same instance shared with $current.
        self::assertNotSame($current->getTable('mm_orphan'), $desired->getTable('mm_orphan'));
    }

    public function testIgnoreUnwantedRemovalsCarriesAColumnMissingFromDesiredOver(): void
    {
        $current = new Schema();
        $table   = $current->createTable('mm_employees');
        $table->addColumn('id', Types::INTEGER);
        $table->addColumn('orphan_column', Types::STRING);

        $desired      = new Schema();
        $desiredTable = $desired->createTable('mm_employees');
        $desiredTable->addColumn('id', Types::INTEGER);

        $this->invokeIgnoreUnwantedRemovals($current, $desired);

        self::assertTrue($desiredTable->hasColumn('orphan_column'));
        self::assertNotSame(
            $table->getColumn('orphan_column'),
            $desiredTable->getColumn('orphan_column')
        );
    }

    public function testIgnoreUnwantedRemovalsDoesNotTouchColumnsAlreadyInDesired(): void
    {
        $current = new Schema();
        $table   = $current->createTable('mm_employees');
        $table->addColumn('id', Types::INTEGER);
        $table->addColumn('name', Types::STRING);

        $desired      = new Schema();
        $desiredTable = $desired->createTable('mm_employees');
        $desiredTable->addColumn('id', Types::INTEGER);
        $desiredTable->addColumn('name', Types::STRING);
        $desiredTable->addColumn('email', Types::STRING);

        $this->invokeIgnoreUnwantedRemovals($current, $desired);

        // 'email' is genuinely new (in $desired only) and must be left as the caller built it.
        self::assertTrue($desiredTable->hasColumn('email'));
        self::assertCount(3, $desiredTable->getColumns());
    }

    public function testGetScriptNeverTouchesATableOrphanedFromTheDesiredSchema(): void
    {
        $connection = $this->createInMemoryConnection();
        $connection->executeStatement('CREATE TABLE mm_kept (id INTEGER PRIMARY KEY)');
        $connection->executeStatement('CREATE TABLE mm_orphan (id INTEGER PRIMARY KEY)');

        $desired      = new Schema();
        $desiredTable = $desired->createTable('mm_kept');
        $desiredTable->addColumn('id', Types::INTEGER);
        $desiredTable->setPrimaryKey(['id']);

        $manipulator = new DoctrineSchemaManipulator($connection);
        $script      = $manipulator->getScript(new DoctrineSchemaInformation($desired));

        foreach ($script as $statement) {
            self::assertStringNotContainsStringIgnoringCase('mm_orphan', $statement);
        }
    }

    public function testUpdateDatabaseNeverDropsAColumnOrphanedFromTheDesiredSchema(): void
    {
        $connection = $this->createInMemoryConnection();
        $connection->executeStatement('CREATE TABLE mm_kept (id INTEGER PRIMARY KEY)');
        $connection->executeStatement('ALTER TABLE mm_kept ADD COLUMN orphan_column VARCHAR(255)');

        $desired      = new Schema();
        $desiredTable = $desired->createTable('mm_kept');
        $desiredTable->addColumn('id', Types::INTEGER);
        $desiredTable->setPrimaryKey(['id']);

        $manipulator = new DoctrineSchemaManipulator($connection);
        $manipulator->updateDatabase(new DoctrineSchemaInformation($desired));

        // SQLite has to rebuild the table to add/drop a column at all, but the orphaned column's
        // data must survive that rebuild untouched - MetaModels only ever warns, never deletes.
        $columns = $connection->createSchemaManager()->listTableColumns('mm_kept');
        self::assertArrayHasKey('orphan_column', $columns);
    }

    public function testUpdateDatabaseStillAddsAGenuinelyMissingColumn(): void
    {
        $connection = $this->createInMemoryConnection();
        $connection->executeStatement('CREATE TABLE mm_kept (id INTEGER PRIMARY KEY)');

        $desired      = new Schema();
        $desiredTable = $desired->createTable('mm_kept');
        $desiredTable->addColumn('id', Types::INTEGER);
        $desiredTable->addColumn('new_column', Types::STRING)->setNotnull(false);
        $desiredTable->setPrimaryKey(['id']);

        $manipulator = new DoctrineSchemaManipulator($connection);
        $manipulator->updateDatabase(new DoctrineSchemaInformation($desired));

        $columns = $connection->createSchemaManager()->listTableColumns('mm_kept');
        self::assertArrayHasKey('new_column', $columns);
    }

    private function invokeIgnoreUnwantedRemovals(Schema $current, Schema $desired): void
    {
        $connection  = $this->createInMemoryConnection();
        $manipulator = new DoctrineSchemaManipulator($connection);

        $method = new ReflectionMethod($manipulator, 'ignoreUnwantedRemovals');
        $method->invoke($manipulator, $current, $desired);
    }

    private function createInMemoryConnection(): Connection
    {
        return DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
    }
}
