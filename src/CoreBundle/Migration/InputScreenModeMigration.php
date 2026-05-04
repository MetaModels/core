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

use function array_intersect;
use function array_keys;
use function array_map;
use function array_values;
use function count;
use function in_array;

/**
 * Replaces the legacy numeric "mode" column in "tl_metamodel_dca" with the
 * string-based "rendermode" column ("flat", "parented", "hierarchical").
 */
final class InputScreenModeMigration extends AbstractMigration
{
    /**
     * The database connection.
     *
     * @var Connection
     */
    private Connection $connection;

    /** @var list<string> */
    private array $existsCache = [];

    /**
     * Create a new instance.
     *
     * @param Connection $connection The database connection.
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    #[\Override]
    public function getName(): string
    {
        return 'MetaModels: Replace numeric "mode" with string "rendermode" in "tl_metamodel_dca".';
    }

    #[\Override]
    public function shouldRun(): bool
    {
        if (!$this->tablesExist(['tl_metamodel_dca'])) {
            return false;
        }

        $columnNames = array_keys(
            $this->connection->createSchemaManager()->listTableColumns('tl_metamodel_dca')
        );

        return in_array('mode', $columnNames, true);
    }

    #[\Override]
    public function run(): MigrationResult
    {
        $columnNames = array_keys(
            $this->connection->createSchemaManager()->listTableColumns('tl_metamodel_dca')
        );

        if (!in_array('rendermode', $columnNames, true)) {
            $this->connection->executeStatement(
                "ALTER TABLE `tl_metamodel_dca` ADD COLUMN `rendermode` varchar(12) NOT NULL default ''"
            );
        }

        $this->connection->executeStatement(
            "UPDATE `tl_metamodel_dca` SET `rendermode`='flat' WHERE `mode` IN (0, 1, 2, 3)"
        );
        $this->connection->executeStatement(
            "UPDATE `tl_metamodel_dca` SET `rendermode`='parented' WHERE `mode` IN (4)"
        );
        $this->connection->executeStatement(
            "UPDATE `tl_metamodel_dca` SET `rendermode`='hierarchical' WHERE `mode` IN (5, 6)"
        );

        $this->connection->executeStatement(
            'ALTER TABLE `tl_metamodel_dca` DROP COLUMN `mode`'
        );

        return new MigrationResult(
            true,
            'Replaced numeric "mode" with string "rendermode" in "tl_metamodel_dca".'
        );
    }

    private function tablesExist(array $tableNames): bool
    {
        if ([] === $this->existsCache) {
            $this->existsCache = array_values($this->connection->createSchemaManager()->listTableNames());
        }

        return count($tableNames) === count(
            array_intersect($tableNames, array_map('strtolower', $this->existsCache))
        );
    }
}
