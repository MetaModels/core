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
 * Replaces the legacy "isclosed" column in "tl_metamodel_dca" with the three
 * separate flags "iseditable", "iscreatable", and "isdeleteable", deriving their
 * values via bitwise XOR (isclosed^1 = the inverse).
 */
final class IsClosedMigration extends AbstractMigration
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
        return 'MetaModels: Replace "isclosed" with "iseditable", "iscreatable", "isdeleteable" in "tl_metamodel_dca".';
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

        return in_array('isclosed', $columnNames, true);
    }

    #[\Override]
    public function run(): MigrationResult
    {
        $schemaManager = $this->connection->createSchemaManager();
        $columnNames   = array_keys($schemaManager->listTableColumns('tl_metamodel_dca'));

        foreach (['iseditable', 'iscreatable', 'isdeleteable'] as $col) {
            if (!in_array($col, $columnNames, true)) {
                $this->connection->executeStatement(
                    'ALTER TABLE `tl_metamodel_dca`'
                    . " ADD COLUMN `{$col}` char(1) NOT NULL default ''"
                );
            }
        }

        $this->connection->executeStatement(
            'UPDATE `tl_metamodel_dca`
             SET `iseditable`=`isclosed`^1, `iscreatable`=`isclosed`^1, `isdeleteable`=`isclosed`^1'
        );

        $this->connection->executeStatement(
            'ALTER TABLE `tl_metamodel_dca` DROP COLUMN `isclosed`'
        );

        return new MigrationResult(
            true,
            'Replaced "isclosed" with "iseditable", "iscreatable", "isdeleteable" in "tl_metamodel_dca".'
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
