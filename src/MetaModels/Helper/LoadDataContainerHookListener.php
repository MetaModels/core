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

namespace MetaModels\Helper;

/**
 * Interim hook listener class that calls registered closures for a table.
 */
class LoadDataContainerHookListener
{
    /**
     * The list of instances by table name.
     *
     * @var \Closure[]
     */
    protected static $tableMap = array();

    /**
     * Create a new instance.
     */
    public function __construct()
    {
        // No op.
    }

    /**
     * Prepare a new instance for the given container.
     *
     * @param string   $tableName The table name.
     *
     * @param callable $callable  The callable.
     *
     * @return void
     *
     * @@SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public static function attachFor($tableName, $callable)
    {
        if (!in_array(array(__CLASS__, 'handleLoadDataContainer'), $GLOBALS['TL_HOOKS']['loadDataContainer'])) {
            $GLOBALS['TL_HOOKS']['loadDataContainer'][] = array(__CLASS__, 'handleLoadDataContainer');
        }

        if (!isset(self::$tableMap[$tableName])) {
            self::$tableMap[$tableName] = array();
        }

        self::$tableMap[$tableName][] = $callable;
    }

    /**
     * Handle the load data container HOOK call.
     *
     * @param string $tableName The table name.
     *
     * @return void
     */
    public function handleLoadDataContainer($tableName)
    {

        if (!isset(self::$tableMap[$tableName])) {
            return;
        }

        foreach (self::$tableMap[$tableName] as $callable) {
            call_user_func($callable, $tableName);
        }
    }
}
