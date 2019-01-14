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

namespace MetaModels\Helper;

/**
 * Interim callback listener class that calls registered closures for a table and operation button.
 */
class OperationButtonCallbackListener
{
    /**
     * The list of instances by table name.
     *
     * @var callable[]
     */
    protected static $callbacks = array();

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
     * @param string   $tableName     The table name.
     *
     * @param string   $operationName The operation name.
     *
     * @param callable $callable      The callable.
     *
     * @return array
     *
     * @@SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public static function generateFor($tableName, $operationName, $callable)
    {
        if (!isset(self::$callbacks[$tableName])) {
            self::$callbacks[$tableName] = array();
        }

        self::$callbacks[$tableName][$operationName] = $callable;

        return array(__CLASS__, 'handleCallback');
    }

    /**
     * Handle operation buttons.
     *
     * @param array  $row        The current data row.
     *
     * @param string $href       The href to be appended.
     *
     * @param string $label      The operation label.
     *
     * @param string $name       The operation name.
     *
     * @param string $icon       The icon path.
     *
     * @param string $attributes The button attributes.
     *
     * @param string $table      The table name.
     *
     * @return string
     */
    public function handleCallback($row, $href, $label, $name, $icon, $attributes, $table)
    {
        if (preg_match('#class="([^"]*)"#i', $attributes, $matches)) {
            $name = $matches[1];
        }

        if (!isset(self::$callbacks[$table][$name])) {
            return '';
        }

        return call_user_func(self::$callbacks[$table][$name], $row, $href, $label, $name, $icon, $attributes, $table);
    }
}
