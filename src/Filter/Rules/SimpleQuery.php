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
 * @author     Cliff Parnitzky <github@cliff-parnitzky.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Filter\Rules;

use Contao\Database;
use Contao\System;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use MetaModels\Filter\FilterRule;

/**
 * This is the MetaModelFilterRule class for executing a simple database query.
 */
class SimpleQuery extends FilterRule
{
    /**
     * The query string.
     *
     * @var string
     */
    private string $queryString;

    /**
     * The query parameters.
     *
     * @var array
     */
    private array $params;

    /**
     * The name of the id column in the query.
     *
     * @var string
     */
    private string $idColumn;

    /**
     * The database instance to use.
     *
     * @var Connection
     */
    private Connection $connection;

    /**
     * The parameter types.
     *
     * @var array
     */
    private array $types;

    /**
     * Create a rule instance from the passed query builder.
     *
     * @param QueryBuilder $builder    The builder to extract query information from.
     * @param string       $columnName The column to retrieve.
     *
     * @return SimpleQuery
     */
    public static function createFromQueryBuilder(QueryBuilder $builder, $columnName = 'id')
    {
        /** @psalm-suppress DeprecatedMethod */
        return new self(
            $builder->getSQL(),
            $builder->getParameters(),
            $columnName,
            $builder->getConnection(),
            $builder->getParameterTypes()
        );
    }

    /**
     * Creates an instance of a simple query filter rule.
     *
     * @param string     $queryString The query that shall be executed.
     * @param array      $params      The query parameters that shall be used.
     * @param string     $idColumn    The column where the item id is stored in.
     * @param Connection $connection  The database to use.
     * @param array      $types       The types.
     *
     * @throws \RuntimeException Throws invalid id column.
     */
    public function __construct($queryString, $params = [], $idColumn = 'id', $connection = null, $types = [])
    {
        parent::__construct();

        if (empty($idColumn)) {
            throw new \RuntimeException('Invalid id column');
        }

        $this->queryString = $queryString;
        $this->params      = $params;
        $this->idColumn    = $idColumn;
        /** @psalm-suppress DeprecatedMethod */
        $this->connection = $this->sanitizeConnection($connection);
        $this->types      = $types;
    }

    /**
     * {@inheritdoc}
     */
    public function getMatchingIds()
    {
        $matches = $this->connection->executeQuery($this->queryString, $this->params, $this->types);
        $ids     = [];
        foreach ($matches->fetchAllAssociative() as $value) {
            $ids[] = (string) $value[$this->idColumn];
        }

        return $ids;
    }

    /**
     * Sanitize the connection value
     *
     * @param Connection|\Contao\Database|null $connection The connection value.
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

        // @codingStandardsIgnoreStart
        // @codeCoverageIgnoreStart
        // BC layer - we used to accept a Contao database instance here.
        if ($connection instanceof Database) {
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
        // @codeCoverageIgnoreEnd
        // @codingStandardsIgnoreEnd
    }
}
