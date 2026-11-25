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

namespace MetaModels\Test\CoreBundle\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use MetaModels\CoreBundle\Migration\OutputEncodingMigration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function array_map;
use function serialize;
use function unserialize;

/**
 * Tests the registration of the MetaModels columns for the output encoding migration of Contao.
 */
#[CoversClass(OutputEncodingMigration::class)]
final class OutputEncodingMigrationTest extends TestCase
{
    private Connection $connection;

    #[\Override]
    protected function setUp(): void
    {
        $this->connection = DriverManager::getConnection(['driver' => 'pdo_sqlite', 'memory' => true]);
        $this->connection->executeStatement('CREATE TABLE tl_metamodel (id INTEGER PRIMARY KEY, tableName TEXT)');
        $this->connection->executeStatement(
            'CREATE TABLE tl_metamodel_attribute (id INTEGER PRIMARY KEY, pid INTEGER, colname TEXT, type TEXT)'
        );
        $this->connection->executeStatement('CREATE TABLE mm_people (id INTEGER PRIMARY KEY, name TEXT, age TEXT)');
        $this->connection->executeStatement(
            'CREATE TABLE tl_metamodel_translatedtext (id INTEGER PRIMARY KEY, value TEXT)'
        );
        $this->connection->insert('tl_metamodel', ['id' => 1, 'tableName' => 'mm_people']);
        foreach ([[1, 'name', 'text'], [2, 'age', 'numeric'], [3, 'gone', 'text']] as [$id, $column, $type]) {
            $this->connection->insert(
                'tl_metamodel_attribute',
                ['id' => $id, 'pid' => 1, 'colname' => $column, 'type' => $type]
            );
        }
    }

    private function createRegistry(int $performed): void
    {
        $this->connection->executeStatement(
            'CREATE TABLE prepare_for_output_encoding (table_name TEXT, column_name TEXT, virtual_target_name TEXT, '
            . 'encoding_options TEXT, performed_migration INTEGER, PRIMARY KEY (table_name, column_name))'
        );
        $this->connection->insert(
            'prepare_for_output_encoding',
            [
                'table_name'          => 'tl_page',
                'column_name'         => 'title',
                'virtual_target_name' => null,
                'encoding_options'    => '["fullyEncoded"]',
                'performed_migration' => $performed,
            ]
        );
    }

    /**
     * @param array<string, array<string, string>>|null $tables
     */
    private function migration(?array $tables = null): OutputEncodingMigration
    {
        return new OutputEncodingMigration(
            $this->connection,
            ['text' => 'fullyEncoded', 'longtext' => 'decodeEntities'],
            $tables ?? [
                'tl_metamodel_translatedtext' => ['value' => 'fullyEncoded'],
                'tl_metamodel_gone'           => ['value' => 'fullyEncoded'],
            ]
        );
    }

    /**
     * @return list<array{0: string, 1: string, 2: string}>
     */
    private function registered(): array
    {
        return $this->connection->fetchAllNumeric(
            "SELECT table_name, column_name, encoding_options FROM prepare_for_output_encoding
              WHERE table_name <> 'tl_page' ORDER BY table_name, column_name"
        );
    }

    public function testDoesNotRunWithoutTheRegistryOfContao(): void
    {
        self::assertFalse($this->migration()->shouldRun());
    }

    public function testDoesNotRunWhenNothingWaitsForTheConversion(): void
    {
        $this->createRegistry(1);

        self::assertFalse($this->migration()->shouldRun());
    }

    public function testRegistersTheExistingColumnsOfTheMetaModelsWhileContaoWaits(): void
    {
        $this->createRegistry(0);
        $migration = $this->migration();

        self::assertTrue($migration->shouldRun());

        $result = $migration->run();

        self::assertTrue($result->isSuccessful());
        self::assertSame(
            [
                ['mm_people', 'name', '["fullyEncoded"]'],
                ['tl_metamodel_translatedtext', 'value', '["fullyEncoded"]'],
            ],
            $this->registered()
        );
        self::assertSame(
            [0, 0],
            array_map('intval', $this->connection->fetchFirstColumn(
                "SELECT performed_migration FROM prepare_for_output_encoding WHERE table_name <> 'tl_page'"
            ))
        );
    }

    public function testRunsOnlyOnce(): void
    {
        $this->createRegistry(0);
        $migration = $this->migration();
        $migration->run();

        self::assertFalse($migration->shouldRun());
    }

    public function testLeavesAnExistingRegistryEntryAlone(): void
    {
        $this->createRegistry(0);
        $this->connection->insert(
            'prepare_for_output_encoding',
            [
                'table_name'          => 'mm_people',
                'column_name'         => 'name',
                'virtual_target_name' => null,
                'encoding_options'    => '["decodeEntities"]',
                'performed_migration' => 1,
            ]
        );

        $this->migration()->run();

        self::assertSame(
            [
                ['mm_people', 'name', '["decodeEntities"]'],
                ['tl_metamodel_translatedtext', 'value', '["fullyEncoded"]'],
            ],
            $this->registered()
        );
    }

    private function addKeywordAttribute(): void
    {
        $this->connection->insert(
            'tl_metamodel_attribute',
            ['id' => 4, 'pid' => 1, 'colname' => 'select', 'type' => 'text']
        );
    }

    private function insertPerson(int $id, string $keywordValue): void
    {
        $this->connection->executeStatement(
            'INSERT INTO mm_people (id, name, "select") VALUES (?, ?, ?)',
            [$id, 'person', $keywordValue]
        );
    }

    public function testConvertsColumnsNamedLikeKeywordsItself(): void
    {
        $this->createRegistry(0);
        $this->connection->executeStatement('ALTER TABLE mm_people ADD COLUMN "select" TEXT');
        $this->addKeywordAttribute();
        $this->insertPerson(1, 'A &#38; B &#60;b&#62;');
        $this->insertPerson(2, '');

        $this->migration([])->run();

        self::assertSame('A & B <b>', $this->connection->fetchOne('SELECT "select" FROM mm_people WHERE id = 1'));
        self::assertSame('', $this->connection->fetchOne('SELECT "select" FROM mm_people WHERE id = 2'));
        self::assertSame(
            '1',
            (string) $this->connection->fetchOne(
                "SELECT performed_migration FROM prepare_for_output_encoding WHERE column_name = 'select'"
            )
        );
        // The columns Contao can handle stay registered for its own migration.
        self::assertSame(
            '0',
            (string) $this->connection->fetchOne(
                "SELECT performed_migration FROM prepare_for_output_encoding WHERE column_name = 'name'"
            )
        );
    }

    public function testConvertsTablesWithoutAnIdItself(): void
    {
        $this->createRegistry(0);
        $this->connection->executeStatement(
            'CREATE TABLE tl_metamodel_translatedurl '
            . '(att_id INTEGER, item_id INTEGER, langcode TEXT, href TEXT, title TEXT)'
        );
        $this->connection->insert(
            'tl_metamodel_translatedurl',
            [
                'att_id'   => 1,
                'item_id'  => 1,
                'langcode' => 'de',
                'href'     => 'a?x&#61;1&#38;y&#61;2',
                'title'    => 'T &#60;b&#62;',
            ]
        );
        $this->connection->insert(
            'tl_metamodel_translatedurl',
            ['att_id' => 1, 'item_id' => 2, 'langcode' => 'de', 'href' => 'plain', 'title' => null]
        );

        $this->migration(
            ['tl_metamodel_translatedurl' => ['href' => 'fullyEncoded', 'title' => 'fullyEncoded']]
        )->run();

        self::assertSame(
            [['a?x=1&y=2', 'T <b>'], ['plain', null]],
            $this->connection->fetchAllNumeric('SELECT href, title FROM tl_metamodel_translatedurl ORDER BY item_id')
        );
    }

    public function testSerializedValuesAreDecodedPerEntry(): void
    {
        $this->createRegistry(0);
        $this->connection->executeStatement('ALTER TABLE mm_people ADD COLUMN "select" TEXT');
        $this->addKeywordAttribute();
        $this->insertPerson(1, serialize(['A &#38; B', 'k' => 'x &#60; y']));

        $this->migration([])->run();

        self::assertSame(
            ['A & B', 'k' => 'x < y'],
            unserialize((string) $this->connection->fetchOne('SELECT "select" FROM mm_people WHERE id = 1'))
        );
    }
}
