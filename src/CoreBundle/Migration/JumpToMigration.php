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
use function array_key_exists;
use function array_keys;
use function array_map;
use function array_values;
use function count;
use function implode;
use function strtolower;

/**
 * Adds the "metamodel_jumpTo" column to "tl_content" and "tl_module" and copies
 * any existing "jumpTo" values over.
 *
 * Introduced: MetaModels pre-release 1.0.
 */
final class JumpToMigration extends AbstractMigration
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
        return 'MetaModels: Add "metamodel_jumpTo" column to "tl_content" and "tl_module".';
    }

    #[\Override]
    public function shouldRun(): bool
    {
        return !empty($this->findTablesNeedingMigration());
    }

    #[\Override]
    public function run(): MigrationResult
    {
        $messages = [];
        foreach ($this->findTablesNeedingMigration() as $tableName => $hasJumpTo) {
            $this->connection->executeStatement(
                'ALTER TABLE `' . $tableName . '`'
                . " ADD COLUMN `metamodel_jumpTo` int(10) unsigned NOT NULL default '0'"
            );
            if ($hasJumpTo) {
                $this->connection->executeStatement(
                    'UPDATE `' . $tableName . '` SET `metamodel_jumpTo`=`jumpTo`'
                );
            }
            $messages[] = $tableName;
        }

        return new MigrationResult(true, 'Added "metamodel_jumpTo" to: ' . implode(', ', $messages));
    }

    /**
     * Returns a map of table name → whether a "jumpTo" source column exists.
     *
     * @return array<string, bool>
     */
    private function findTablesNeedingMigration(): array
    {
        $result = [];
        foreach (['tl_content', 'tl_module'] as $tableName) {
            if (!$this->tablesExist([$tableName])) {
                continue;
            }
            $columnNames = array_keys(
                $this->connection->createSchemaManager()->listTableColumns($tableName)
            );
            if (\in_array('metamodel_jumpto', $columnNames, true)) {
                continue;
            }
            $result[$tableName] = \in_array('jumpto', $columnNames, true);
        }

        return $result;
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
