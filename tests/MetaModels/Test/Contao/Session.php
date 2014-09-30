<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package    MetaModels
 * @subpackage Tests
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Test\Contao;

class Session
{
    public static $values;

    /**
     * Return a get variable. NOTE: this only mimics the real class - this stub does not encode.
     *
     * @param string $strKey The variable name.
     *
     * @return mixed The cleaned variable value
     */
    public static function get($strKey)
    {
        if (!isset(static::$values[$strKey])) {
            return null;
        }

        return static::$values[$strKey];
    }
    /**
     * Set a get variable. NOTE: this only mimics the real class - this stub does not encode.
     *
     * @param string $strKey   The variable name.
     *
     * @param mixed  $varValue The variable value.
     *
     * @return void
     */
    public static function set($strKey, $varValue)
    {
        unset(static::$values[$strKey]);

        if ($varValue !== null) {
            static::$values[$strKey] = $varValue;
        }
    }


    /**
     * Return the object instance (Singleton).
     *
     * @return \Session The object instance
     *
     * @deprecated Session is now a static class
     */
    public static function getInstance()
    {
        return new static();
    }
}
