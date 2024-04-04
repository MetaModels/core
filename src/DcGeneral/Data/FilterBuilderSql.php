<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2024 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
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
    private string $combiner;

    /**
     * The SQL procedure.
     *
     * @var array
     */
    private array $procedures = [];

    /**
     * The SQL query parameters.
     *
     * @var array
     */
    private array $parameter = [];

    /**
     * The database instance.
     *
     * @var Connection
     */
    private Connection $connection;

    /**
     * The table alias to use.
     *
     * @var string
     */
    private string $tableAlias;

    /**
     * Create a new instance.
     *
     * @param string     $tableName  The table name.
     * @param string     $combiner   The combiner (AND or OR).
     * @param Connection $connection The database connection.
     * @param string     $tableAlias The table alias prefix (defaults to 't.').
     */
    public function __construct($tableName, $combiner, $connection, string $tableAlias = 't.')
    {
        $this->tableName  = $tableName;
        $this->combiner   = strtoupper($combiner);
        /** @psalm-suppress DeprecatedMethod */
        $this->connection = $this->sanitizeConnection($connection);
        $this->tableAlias = $tableAlias;
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
        return '(' . \implode(' ' . $this->combiner . ' ', $this->procedures) . ')';
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
                \sprintf('SELECT t.id FROM %s AS t WHERE %s', $this->tableName, $this->getProcedure()),
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
        $this->procedures[] = \sprintf(
            '(%s%s %s ?)',
            $this->tableAlias,
            $operation['property'],
            $operation['operation']
        );

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
        $this->parameter    = \array_merge($this->parameter, \array_values($operation['values']));
        $this->procedures[] = \sprintf(
            '(%s%s IN (%s))',
            $this->tableAlias,
            $operation['property'],
            \rtrim(\str_repeat('?,', \count($operation['values'])), ',')
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
        $this->parameter[]  = \str_replace(array('*', '?'), array('%', '_'), $operation['value']);
        $this->procedures[] = \sprintf('(%s%s LIKE ?)', $this->tableAlias, $operation['property']);

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
        switch (\strtoupper($child['operation'])) {
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

        throw new \RuntimeException('Error processing filter array ' . \var_export($child, true), 1);
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
        $this->parameter    = \array_merge($this->parameter, $subProcedure->getParameters());

        return $this;
    }

    /**
     * Sanitize the connection value
     *
     * @param \Contao\Database|Connection|null $connection The connection value.
     *
     * @return Connection
     *
     * @throws \RuntimeException Throws could not obtain doctrine connection.
     *
     * @deprecated To be removed in 3.0 - you should ALWAYS pass the proper connection.
     */
    private function sanitizeConnection(Connection|Database|null $connection): Connection
    {
        if ($connection instanceof Connection) {
            return $connection;
        }

        // @codingStandardsIgnoreStart
        // @codeCoverageIgnoreStart
        // BC layer - we used to accept a Contao database instance here.
        if ($connection instanceof Database) {
            @trigger_error(
                '"' . __METHOD__ . '" now accepts doctrine instances - ' .
                'passing Contao database instances is deprecated.',
                E_USER_DEPRECATED
            );
            $reflection = new \ReflectionProperty(Database::class, 'resConnection');
            $reflection->setAccessible(true);

            return $reflection->getValue($connection);
        }

        if (null === $connection) {
            @trigger_error(
                'You should pass a doctrine database connection to "' . __METHOD__ . '".',
                E_USER_DEPRECATED
            );
            $connection = System::getContainer()->get('database_connection');
        }

        if (!($connection instanceof Connection)) {
            throw new \RuntimeException('Could not obtain doctrine connection.');
        }

        return $connection;
        // @codeCoverageIgnoreEnd
        // @codingStandardsIgnoreEnd
    }
}
