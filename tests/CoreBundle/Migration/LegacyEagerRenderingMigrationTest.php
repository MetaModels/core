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
use Doctrine\DBAL\Schema\AbstractSchemaManager;
use MetaModels\CoreBundle\Migration\AbstractAddRenderSettingsColumnMigration;
use MetaModels\CoreBundle\Migration\LegacyEagerRenderingMigration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LegacyEagerRenderingMigration::class)]
#[CoversClass(AbstractAddRenderSettingsColumnMigration::class)]
class LegacyEagerRenderingMigrationTest extends TestCase
{
    public function testShouldRunIsFalseWhenTheTableDoesNotExist(): void
    {
        $schemaManager = $this->createMock(AbstractSchemaManager::class);
        $schemaManager->method('tablesExist')->with(['tl_metamodel_rendersettings'])->willReturn(false);

        $connection = $this->createMock(Connection::class);
        $connection->method('createSchemaManager')->willReturn($schemaManager);

        self::assertFalse((new LegacyEagerRenderingMigration($connection))->shouldRun());
    }

    public function testShouldRunIsFalseWhenTheColumnAlreadyExists(): void
    {
        $schemaManager = $this->createMock(AbstractSchemaManager::class);
        $schemaManager->method('tablesExist')->willReturn(true);

        $connection = $this->createMock(Connection::class);
        $connection->method('createSchemaManager')->willReturn($schemaManager);
        $connection->method('fetchOne')->willReturn('1');

        self::assertFalse((new LegacyEagerRenderingMigration($connection))->shouldRun());
    }

    public function testShouldRunIsTrueWhenTheTableExistsButNotTheColumn(): void
    {
        $schemaManager = $this->createMock(AbstractSchemaManager::class);
        $schemaManager->method('tablesExist')->willReturn(true);

        $connection = $this->createMock(Connection::class);
        $connection->method('createSchemaManager')->willReturn($schemaManager);
        $connection->method('fetchOne')->willReturn('0');

        self::assertTrue((new LegacyEagerRenderingMigration($connection))->shouldRun());
    }

    public function testRunAddsTheColumnAndEnablesItForExistingRows(): void
    {
        $statements = [];

        $connection = $this->createMock(Connection::class);
        $connection->method('executeStatement')->willReturnCallback(
            function (string $sql) use (&$statements): int {
                $statements[] = $sql;

                return \str_contains($sql, 'ADD COLUMN') ? 0 : 3;
            }
        );

        $result = (new LegacyEagerRenderingMigration($connection))->run();

        self::assertTrue($result->isSuccessful());
        self::assertStringContainsString('enabled it for 3 existing rows', $result->getMessage());
        self::assertCount(2, $statements);
        self::assertStringContainsString('ADD COLUMN `legacyEagerRendering`', $statements[0]);
        self::assertStringContainsString("SET `legacyEagerRendering` = '1'", $statements[1]);
    }
}
