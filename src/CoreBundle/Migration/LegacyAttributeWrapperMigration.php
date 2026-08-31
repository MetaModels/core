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
 * Adds the column `legacyAttributeWrapper` to `tl_metamodel_rendersettings` and enables it for all existing rows.
 *
 * Enabling it keeps their output as it was before MetaModels 2.5 moved the enclosing block into the
 * attribute templates. Both the column and this migration are meant to go with MetaModels 3.0.
 *
 * Background: since 2.5 the enclosing block (field, label, value) is rendered by the attribute templates instead of
 * the item template. New render settings therefore start with the column disabled. Render settings that already
 * existed keep the old behaviour, because their installations may carry custom attribute templates which do not
 * render the block - those would silently lose their markup otherwise.
 *
 * The distinguishing criterion is not a property of the row but its age: ADD COLUMN plus UPDATE in one run catches
 * exactly the rows that existed at upgrade time. Everything created afterwards falls back to the DCA default.
 */
final class LegacyAttributeWrapperMigration extends AbstractMigration
{
    private const TABLE  = 'tl_metamodel_rendersettings';
    private const COLUMN = 'legacyAttributeWrapper';

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
     * {@inheritDoc}
     */
    #[\Override]
    public function getName(): string
    {
        return 'Add column legacyAttributeWrapper to tl_metamodel_rendersettings and enable it for existing rows.';
    }

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
            [self::TABLE, self::COLUMN]
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
        $this->connection->executeStatement(
            'ALTER TABLE `' . self::TABLE . '`
             ADD COLUMN `' . self::COLUMN . "` char(1) NOT NULL default ''"
        );

        $count = $this->connection->executeStatement(
            'UPDATE `' . self::TABLE . '` SET `' . self::COLUMN . "` = '1'"
        );

        return new MigrationResult(
            true,
            'Added column ' . self::TABLE . '.' . self::COLUMN . ' and enabled it for ' . $count . ' existing rows.'
        );
    }
}
