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
use function sprintf;
use function time;

/**
 * Converts the legacy "flag" column in "tl_metamodel_dca" into proper sort-group
 * entries in "tl_metamodel_dca_sortgroup" and drops the column afterwards.
 *
 * The flag value encodes both the grouping type and sort direction; odd values
 * sort ascending, even values sort descending.
 *
 * Flag mapping:
 *   1–2   → char grouping, length 1
 *   3–4   → char grouping, length 2
 *   5–6   → day grouping
 *   7–8   → month grouping
 *   9–10  → year grouping
 *   11–12 → digit grouping
 *   other → no grouping
 */
final class InputScreenFlagMigration extends AbstractMigration
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
        return 'MetaModels: Migrate "flag" column to sort-group entries in "tl_metamodel_dca_sortgroup".';
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

        return in_array('flag', $columnNames, true);
    }

    #[\Override]
    public function run(): MigrationResult
    {
        $this->ensureSortGroupTableExists();

        $dcaRows = $this->connection->fetchAllAssociative('SELECT * FROM `tl_metamodel_dca`');
        $count   = 0;
        foreach ($dcaRows as $dca) {
            [$renderGroupType, $renderGroupLen] = $this->resolveGroupType((int) $dca['flag']);

            $this->connection->insert(
                'tl_metamodel_dca_sortgroup',
                [
                    'pid'             => (int) $dca['id'],
                    'sorting'         => 128,
                    'tstamp'          => time(),
                    'name'            => null,
                    'isdefault'       => '1',
                    'ismanualsort'    => '1',
                    'rendergrouptype' => $renderGroupType,
                    'rendergrouplen'  => $renderGroupLen,
                    'rendergroupattr' => 0,
                    'rendersort'      => in_array((int) $dca['flag'], [2, 4, 6, 8, 10, 12], true) ? 'desc' : 'asc',
                    'rendersortattr'  => 0,
                ]
            );
            $count++;
        }

        $this->connection->executeStatement(
            'ALTER TABLE `tl_metamodel_dca` DROP COLUMN `flag`'
        );

        return new MigrationResult(
            true,
            sprintf('Created %d sort-group row(s) and dropped "flag" column from "tl_metamodel_dca".', $count)
        );
    }

    /**
     * Returns [renderGroupType, renderGroupLen] for the given flag value.
     *
     * @return array{string, int}
     */
    private function resolveGroupType(int $flag): array
    {
        if (in_array($flag, [1, 2, 3, 4], true)) {
            return ['char', in_array($flag, [1, 2], true) ? 1 : 2];
        }
        if (in_array($flag, [5, 6], true)) {
            return ['day', 0];
        }
        if (in_array($flag, [7, 8], true)) {
            return ['month', 0];
        }
        if (in_array($flag, [9, 10], true)) {
            return ['year', 0];
        }
        if (in_array($flag, [11, 12], true)) {
            return ['digit', 0];
        }

        return ['none', 0];
    }

    private function ensureSortGroupTableExists(): void
    {
        if ($this->tablesExist(['tl_metamodel_dca_sortgroup'])) {
            return;
        }

        $this->connection->executeStatement(
            'CREATE TABLE `tl_metamodel_dca_sortgroup` (
                `id` int(10) unsigned NOT NULL auto_increment,
                `pid` int(10) unsigned NOT NULL default \'0\',
                `sorting` int(10) unsigned NOT NULL default \'0\',
                `tstamp` int(10) unsigned NOT NULL default \'0\',
                `name` text NULL,
                `isdefault` char(1) NOT NULL default \'\',
                `ismanualsort` char(1) NOT NULL default \'\',
                `rendergrouptype` varchar(10) NOT NULL default \'none\',
                `rendergrouplen` int(10) unsigned NOT NULL default \'1\',
                `rendergroupattr` int(10) unsigned NOT NULL default \'0\',
                `rendersort` varchar(10) NOT NULL default \'asc\',
                `rendersortattr` int(10) unsigned NOT NULL default \'0\',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
        $this->existsCache = [];
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
