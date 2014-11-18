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
