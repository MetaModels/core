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

namespace MetaModels\Test;

abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @param $post
     *
     * @param $get
     *
     * @param $cookies
     */
    protected function initializeContaoInputClass($get = null, $post = null, $cookies = null)
    {
        if (!class_exists('Contao\Input', false)) {
            class_alias('MetaModels\Test\Contao\Input', 'Contao\Input');
            class_alias('MetaModels\Test\Contao\Input', 'Input');
        }

        Contao\Input::$get    = $get;
        Contao\Input::$post   = $post;
        Contao\Input::$cookie = $cookies;
    }
}
