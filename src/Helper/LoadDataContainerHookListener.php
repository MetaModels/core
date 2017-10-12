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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
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
