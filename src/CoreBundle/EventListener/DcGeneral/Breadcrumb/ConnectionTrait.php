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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Breadcrumb;

use Doctrine\DBAL\Connection;

/**
 * This class provides obtaining the connection.
 */
trait ConnectionTrait
{
    /**
     * The connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * Set the database connection.
     *
     * @param Connection $connection The database connection.
     *
     * @return void
     */
    public function setConnection(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Retrieve the row.
     *
     * @param string $rowId The id.
     *
     * @param string $table The table to fetch from.
     *
     * @return object
     */
    private function getRow($rowId, $table)
    {
        return (object) $this->connection
            ->createQueryBuilder()
            ->select('*')
            ->from($table)
            ->where('id=:id')
            ->setParameter('id', $rowId)
            ->setMaxResults(1)
            ->execute()
            ->fetch(\PDO::FETCH_ASSOC);
    }
}
