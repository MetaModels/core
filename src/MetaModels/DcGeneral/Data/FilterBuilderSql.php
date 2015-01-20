<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\DcGeneral\Data;

use Contao\Database;
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
     * @var Database
     */
    private $dataBase;

    /**
     * Create a new instance.
     *
     * @param string   $tableName The table name.
     *
     * @param string   $combiner  The combiner (AND or OR).
     *
     * @param Database $dataBase  The database to use.
     */
    public function __construct($tableName, $combiner, Database $dataBase)
    {
        $this->tableName = $tableName;
        $this->combiner  = strtoupper($combiner);
        $this->dataBase  = $dataBase;
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
                $this->dataBase
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
            rtrim(str_repeat('?,', count($operation['values'])), ',')
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
        if (!is_array($child)) {
            throw new \RuntimeException('Error Processing sub filter: ' . var_export($child, true), 1);
        }

        switch (strtoupper($child['operation'])) {
            // Currently not supported but we will add it in the future.
            case 'AND':
            case 'OR':
                return $this->getAndOrFilter($child);

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
}
