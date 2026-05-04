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
 * Converts old sub-palette settings in "tl_metamodel_dcasetting" into proper
 * property-value conditions in "tl_metamodel_dcasetting_condition" and drops
 * the legacy "subpalette" column afterwards.
 */
final class SubPalettesToConditionsMigration extends AbstractMigration
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
        return 'MetaModels: Migrate sub-palettes to input field conditions in "tl_metamodel_dcasetting".';
    }

    #[\Override]
    public function shouldRun(): bool
    {
        if (!$this->tablesExist(['tl_metamodel_dcasetting'])) {
            return false;
        }

        $columnNames = array_keys(
            $this->connection->createSchemaManager()->listTableColumns('tl_metamodel_dcasetting')
        );

        return in_array('subpalette', $columnNames, true);
    }

    #[\Override]
    public function run(): MigrationResult
    {
        $this->ensureConditionTableExists();
        $count = $this->migrateSubPaletteRows();
        $this->connection->executeStatement(
            'ALTER TABLE `tl_metamodel_dcasetting` DROP COLUMN `subpalette`'
        );

        return new MigrationResult(
            true,
            sprintf(
                'Migrated %d sub-palette row(s) to conditions and dropped "subpalette" column.',
                $count
            )
        );
    }

    private function ensureConditionTableExists(): void
    {
        if ($this->tablesExist(['tl_metamodel_dcasetting_condition'])) {
            return;
        }

        $this->connection->executeStatement(
            'CREATE TABLE `tl_metamodel_dcasetting_condition` (
                `id` int(10) unsigned NOT NULL auto_increment,
                `pid` int(10) unsigned NOT NULL default \'0\',
                `settingId` int(10) unsigned NOT NULL default \'0\',
                `sorting` int(10) unsigned NOT NULL default \'0\',
                `tstamp` int(10) unsigned NOT NULL default \'0\',
                `enabled` char(1) NOT NULL default \'\',
                `type` varchar(255) NOT NULL default \'\',
                `attr_id` int(10) unsigned NOT NULL default \'0\',
                `comment` varchar(255) NOT NULL default \'\',
                `value` blob NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );
        $this->existsCache = [];
    }

    private function migrateSubPaletteRows(): int
    {
        $subpalettes = $this->connection->fetchAllAssociative(
            'SELECT * FROM `tl_metamodel_dcasetting` WHERE `subpalette` != 0'
        );

        if ([] === $subpalettes) {
            return 0;
        }

        // Build attr_id lookup: dcasetting.id → dcasetting.attr_id (for non-subpalette attribute settings).
        $checkboxRows = $this->connection->fetchAllAssociative(
            "SELECT `id`, `attr_id` FROM `tl_metamodel_dcasetting` WHERE `subpalette`=0 AND `dcatype`='attribute'"
        );
        $checkboxAttrById = [];
        foreach ($checkboxRows as $row) {
            $checkboxAttrById[(int) $row['id']] = (int) $row['attr_id'];
        }

        // Build colName lookup: attr_id → colName.
        $attributeRows = $this->connection->fetchAllAssociative(
            'SELECT `attribute`.`id`, `attribute`.`colName`
             FROM `tl_metamodel_dcasetting` AS `setting`
             LEFT JOIN `tl_metamodel_attribute` AS `attribute` ON (`setting`.`attr_id` = `attribute`.`id`)
             WHERE `setting`.`dcatype` = \'attribute\''
        );
        $colNameByAttrId = [];
        foreach ($attributeRows as $row) {
            $colNameByAttrId[(int) $row['id']] = $row['colName'];
        }

        $count = 0;
        foreach ($subpalettes as $subpalette) {
            $parentSettingId = (int) $subpalette['subpalette'];
            $parentAttrId    = $checkboxAttrById[$parentSettingId] ?? 0;
            $parentColName   = $colNameByAttrId[$parentAttrId] ?? '';

            $this->connection->insert(
                'tl_metamodel_dcasetting_condition',
                [
                    'pid'       => 0,
                    'settingId' => (int) $subpalette['id'],
                    'sorting'   => 128,
                    'tstamp'    => time(),
                    'enabled'   => '1',
                    'type'      => 'conditionpropertyvalueis',
                    'attr_id'   => $parentAttrId,
                    'comment'   => sprintf('Only show when checkbox "%s" is checked', $parentColName),
                    'value'     => '1',
                ]
            );

            $this->connection->update(
                'tl_metamodel_dcasetting',
                ['subpalette' => 0],
                ['id' => (int) $subpalette['id']]
            );
            $this->connection->update(
                'tl_metamodel_dcasetting',
                ['submitOnChange' => 1],
                ['id' => $parentSettingId]
            );

            $count++;
        }

        return $count;
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
