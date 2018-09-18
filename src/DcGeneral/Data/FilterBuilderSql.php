<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2018 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\DcGeneral\Data;

use Contao\Database;
use Contao\System;
use Doctrine\DBAL\Connection;
use MetaModels\Filter\Rules\SimpleQuery;

/**
 * Class to generate a MetaModels filter from a data configuration.
 */
class FilterBuilderSql
{
    /**
     * The table name.
     *
     * @var string
     */
    protected $tableName;

    /**
     * The combiner (AND or OR).
     *
     * @var string
     */
    private $combiner;

    /**
     * The SQL procedure.
     *
     * @var string
     */
    private $procedures = array();

    /**
     * The SQL query parameters.
     *
     * @var array
     */
    private $parameter = array();

    /**
     * The database instance.
     *
     * @var Connection
     */
    private $connection;

    /**
     * Create a new instance.
     *
     * @param string     $tableName  The table name.
     *
     * @param string     $combiner   The combiner (AND or OR).
     *
     * @param Connection $connection The database connection.
     */
    public function __construct($tableName, $combiner, $connection)
    {
        $this->tableName  = $tableName;
        $this->combiner   = strtoupper($combiner);
        $this->connection = $this->sanitizeConnection($connection);
    }

    /**
     * Check if the builder has any procedure to compile.
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->procedures);
    }

    /**
     * Build the procedure and return it.
     *
     * @return string
     */
    public function getProcedure()
    {
        return '(' . implode(' ' . $this->combiner . ' ', $this->procedures) . ')';
    }

    /**
     * Retrieve the parameter list.
     *
     * @return array
     */
    public function getParameters()
    {
        return $this->parameter;
    }

    /**
     * Retrieve the filter rule if anything to filter.
     *
     * @return SimpleQuery|null
     */
    public function build()
    {
        if (!$this->isEmpty()) {
            return new SimpleQuery(
                sprintf('SELECT id FROM %s WHERE %s', $this->tableName, $this->getProcedure()),
                $this->getParameters(),
                'id',
                $this->connection
            );
        }

        return null;
    }

    /**
     * Build the sub query for a comparing operator like =,<,>.
     *
     * @param array $operation The operation to apply.
     *
     * @return FilterBuilderSql
     */
    protected function getFilterForComparingOperator($operation)
    {
        $this->parameter[]  = $operation['value'];
        $this->procedures[] = sprintf('(%s %s ?)', $operation['property'], $operation['operation']);

        return $this;
    }

    /**
     * Return the filter query for a "foo IN ('a', 'b')" filter.
     *
     * @param array $operation The operation to apply.
     *
     * @return FilterBuilderSql
     */
    protected function getFilterForInList($operation)
    {
        $this->parameter    = array_merge($this->parameter, array_values($operation['values']));
        $this->procedures[] = sprintf(
            '(%s IN (%s))',
            $operation['property'],
            rtrim(str_repeat('?,', \count($operation['values'])), ',')
        );

        return $this;
    }

    /**
     * Return the filter query for a "foo LIKE '%ba_r%'" filter.
     *
     * The searched value may contain the wildcards '*' and '?' which will get converted to proper SQL.
     *
     * @param array $operation The operation to apply.
     *
     * @return FilterBuilderSql
     */
    protected function getFilterForLike($operation)
    {
        $this->parameter[]  = str_replace(array('*', '?'), array('%', '_'), $operation['value']);
        $this->procedures[] = sprintf('(%s LIKE ?)', $operation['property']);

        return $this;
    }

    /**
     * Add a filter condition child.
     *
     * @param array $child The child.
     *
     * @return FilterBuilderSql
     *
     * @throws \RuntimeException When an invalid filter array has been passed.
     */
    public function addChild($child)
    {
        if (!\is_array($child)) {
            throw new \RuntimeException('Error Processing sub filter: ' . var_export($child, true), 1);
        }

        switch (strtoupper($child['operation'])) {
            case '=':
            case '>':
            case '<':
                return $this->getFilterForComparingOperator($child);

            case 'IN':
                return $this->getFilterForInList($child);

            case 'LIKE':
                return $this->getFilterForLike($child);

            default:
        }

        throw new \RuntimeException('Error processing filter array ' . var_export($child, true), 1);
    }

    /**
     * Add a sub procedure.
     *
     * @param FilterBuilderSql $subProcedure The sub procedure to add.
     *
     * @return FilterBuilderSql
     */
    public function addSubProcedure(FilterBuilderSql $subProcedure)
    {
        $this->procedures[] = $subProcedure->getProcedure();
        $this->parameter    = array_merge($this->parameter, $subProcedure->getParameters());

        return $this;
    }

    /**
     * Sanitize the connection value
     *
     * @param Connection|\Contao\Database $connection The connection value.
     *
     * @return Connection
     *
     * @throws \RuntimeException Throws could not obtain doctrine connection.
     *
     * @deprecated To be removed in 3.0 - you should ALWAYS pass the proper connection.
     */
    private function sanitizeConnection($connection)
    {
        if ($connection instanceof Connection) {
            return $connection;
        }

        // BC layer - we used to accept a Contao database instance here.
        if ($connection instanceof Database) {
            // @codingStandardsIgnoreStart
            @trigger_error(
                '"' . __METHOD__ . '" now accepts doctrine instances - ' .
                'passing Contao database instances is deprecated.',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
            $reflection = new \ReflectionProperty(Database::class, 'resConnection');
            $reflection->setAccessible(true);
            return $reflection->getValue($connection);
        }
        if (null === $connection) {
            // @codingStandardsIgnoreStart
            @trigger_error(
                'You should pass a doctrine database connection to "' . __METHOD__ . '".',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
            $connection = System::getContainer()->get('database_connection');
        }

        if (!($connection instanceof Connection)) {
            throw new \RuntimeException('Could not obtain doctrine connection.');
        }

        return $connection;
    }
}
