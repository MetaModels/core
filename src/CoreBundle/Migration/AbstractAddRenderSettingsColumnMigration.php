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
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;

/**
 * Shared base for migrations that add one deprecated-from-the-start boolean column to
 * `tl_metamodel_rendersettings` and enable it for all rows that already existed at upgrade time -
 * see LegacyAttributeWrapperMigration and LegacyEagerRenderingMigration for the two concrete cases
 * and their docblocks (or ".claude/lazy-attribut-rendering.md"/"attribut-template-wrapper.md") for
 * why the columns exist and why the age of a row, not a property of it, is what decides the value.
 */
abstract class AbstractAddRenderSettingsColumnMigration extends AbstractMigration
{
    private const TABLE = 'tl_metamodel_rendersettings';

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
     * The column this migration adds.
     *
     * @return string
     */
    abstract protected function column(): string;

    /**
     * {@inheritDoc}
     *
     * @throws Exception When the database access fails.
     */
    #[\Override]
    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();
        if (!$schemaManager->tablesExist([self::TABLE])) {
            return false;
        }

        // Deliberately not listTableColumns(): the schema manager caches its table metadata for the lifetime of the
        // connection, and "contao:migrate" asks again after running the migrations. With the cached list this
        // migration would still look pending and fail on the second attempt with a duplicate column.
        $found = $this->connection->fetchOne(
            'SELECT COUNT(*) FROM information_schema.columns
              WHERE table_schema = DATABASE() AND table_name = ? AND column_name = ?',
            [self::TABLE, $this->column()]
        );

        return 0 === (int) $found;
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception When the database access fails.
     */
    #[\Override]
    public function run(): MigrationResult
    {
        $column = $this->column();

        $this->connection->executeStatement(
            'ALTER TABLE `' . self::TABLE . '`
             ADD COLUMN `' . $column . "` char(1) NOT NULL default ''"
        );

        $count = $this->connection->executeStatement(
            'UPDATE `' . self::TABLE . '` SET `' . $column . "` = '1'"
        );

        return new MigrationResult(
            true,
            'Added column ' . self::TABLE . '.' . $column . ' and enabled it for ' . $count . ' existing rows.'
        );
    }
}
