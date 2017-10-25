<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2017 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\Attribute;

use Doctrine\DBAL\Connection;
use MetaModels\Helper\TableManipulator;

/**
 * This is an abstract factory to query instances of attributes.
 *
 * Extend your own attribute factories from this class and register them when the create attribute factory event is
 * triggered.
 */
class AbstractSimpleAttributeTypeFactory extends AbstractAttributeTypeFactory
{
    /**
     * Database connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * Table manipulator.
     *
     * @var TableManipulator
     */
    private $tableManipulator;

    /**
     * Construct.
     *
     * @param Connection       $connection       Database connection.
     * @param TableManipulator $tableManipulator Table manipulator.
     */
    protected function __construct(Connection $connection, TableManipulator $tableManipulator)
    {
        $this->connection       = $connection;
        $this->tableManipulator = $tableManipulator;
    }

    /**
     * {@inheritDoc}
     */
    public function createInstance($information, $metaModel)
    {
        return new $this->typeClass($metaModel, $information, $this->connection, $this->tableManipulator);
    }
}
