<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2019 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types = 1);

namespace MetaModels\Schema\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Comparator;

/**
 * This updates the database to be in sync with the passed schema.
 */
class DoctrineSchemaManipulator
{
    /**
     * The doctrine connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * Create a new instance.
     *
     * @param Connection $connection The database connection to use.
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Update the database to be in sync with the passed doctrine information.
     *
     * @param DoctrineSchemaInformation $schemaInformation The schema information.
     *
     * @return void
     */
    public function updateDatabase(DoctrineSchemaInformation $schemaInformation): void
    {
        $manager = $this->connection->getSchemaManager();
        $current = $manager->createSchema();
        $diff    = Comparator::compareSchemas($current, $schemaInformation->getSchema());
        $queries = $diff->toSaveSql($this->connection->getDatabasePlatform());

        foreach ($queries as $query) {
            $this->connection->exec($query);
        }
    }
}
