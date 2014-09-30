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

class Input
{
    public static $get;

    public static $post;

    public static $cookie;


    /**
     * Return a get variable. NOTE: this only mimics the real class - this stub does not encode.
     *
     * @param string $strKey The variable name.
     *
     * @return mixed The cleaned variable value
     */
    public static function get($strKey)
    {
        if (!isset(static::$get[$strKey])) {
            return null;
        }

        return static::$get[$strKey];
    }


    /**
     * Return a post variable. NOTE: this only mimics the real class - this stub does not encode.
     *
     * @param string $strKey The variable name.
     *
     * @return mixed The cleaned variable value
     */
    public static function post($strKey)
    {
        if (!isset(static::$post[$strKey])) {
            return null;
        }

        return static::$post[$strKey];
    }


    /**
     * Return a post variable. NOTE: this only mimics the real class - this stub does not encode.
     *
     * @param string $strKey The variable name.
     *
     * @return mixed The cleaned variable value
     */
    public static function postHtml($strKey)
    {
        return static::post($strKey);
    }

    /**
     * Return a post variable. NOTE: this only mimics the real class - this stub does not encode.
     *
     * @param string $strKey The variable name.
     *
     * @return mixed The cleaned variable value
     */
    public static function postRaw($strKey)
    {
        return static::post($strKey);
    }


    /**
     * Return a cookie variable. NOTE: this only mimics the real class - this stub does not encode.
     *
     * @param string $strKey The variable name.
     *
     * @return mixed The cleaned variable value
     */
    public static function cookie($strKey)
    {
        if (!isset(static::$cookie[$strKey])) {
            return null;
        }

        return static::$cookie[$strKey];
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
    public static function setGet($strKey, $varValue)
    {
        unset(static::$get[$strKey]);

        if ($varValue !== null) {
            static::$get[$strKey] = $varValue;
        }
    }


    /**
     * Set a post variable. NOTE: this only mimics the real class - this stub does not encode.
     *
     * @param string $strKey   The variable name.
     *
     * @param mixed  $varValue The variable value.
     *
     * @return void
     */
    public static function setPost($strKey, $varValue)
    {
        unset(static::$post[$strKey]);

        if ($varValue !== null) {
            static::$post[$strKey] = $varValue;
        }
    }


    /**
     * Set a $_COOKIE variable
     *
     * @param string $strKey   The variable name
     * @param mixed  $varValue The variable value
     */
    public static function setCookie($strKey, $varValue)
    {
        unset(static::$cookie[$strKey]);

        if ($varValue !== null) {
            static::$cookie[$strKey] = $varValue;
        }
    }


    /**
     * Fallback to the session form data if there is no post data
     *
     * @param string $strKey The variable name
     *
     * @return mixed The variable value
     */
    public static function findPost($strKey)
    {
        return static::post($strKey);
    }


    /**
     * Return the object instance (Singleton).
     *
     * @return \Input The object instance
     *
     * @deprecated Input is now a static class
     */
    public static function getInstance()
    {
        return new static();
    }
}
