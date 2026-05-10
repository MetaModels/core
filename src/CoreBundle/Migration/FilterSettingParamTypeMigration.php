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

use function array_keys;
use function in_array;

/**
 * Adds the column `param_type` to `tl_metamodel_filtersetting` if it does not yet exist and pre-fills all existing
 * rows with `slugNget`.
 *
 * Background: the DCA default for new filter rules is `slug`. Existing (legacy) filter rules stored no value for this
 * column and must therefore receive `slugNget` so their behaviour stays unchanged after the column is introduced.
 */
final class FilterSettingParamTypeMigration extends AbstractMigration
{
    private const TABLE  = 'tl_metamodel_filtersetting';
    private const COLUMN = 'param_type';

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
        return 'Add column param_type to tl_metamodel_filtersetting and pre-fill legacy rows with slugNget.';
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    #[\Override]
    public function shouldRun(): bool
    {
        $schemaManager = $this->connection->createSchemaManager();
        if (!$schemaManager->tablesExist([self::TABLE])) {
            return false;
        }

        $columns = array_keys($schemaManager->listTableColumns(self::TABLE));

        return !in_array(self::COLUMN, $columns, true);
    }

    /**
     * {@inheritDoc}
     *
     * @throws Exception
     */
    #[\Override]
    public function run(): MigrationResult
    {
        $this->connection->executeStatement(
            "ALTER TABLE `" . self::TABLE . "`
             ADD COLUMN `" . self::COLUMN . "` varchar(10) NOT NULL default 'slug'"
        );

        $this->connection->executeStatement(
            "UPDATE `" . self::TABLE . "` SET `" . self::COLUMN . "` = 'slugNget'"
        );

        return new MigrationResult(
            true,
            'Added column ' . self::TABLE . '.' . self::COLUMN . ' and pre-filled existing rows with slugNget.'
        );
    }
}
