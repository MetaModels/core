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
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Test;

/**
 * This test case ensures compatibility of Contao Core bundle versions with root namespace aliasing (4.4).
 */
class AutoLoadingTestCase extends \PHPUnit\Framework\TestCase
{
    /**
     * This is the hack to mimic the Contao auto loader.
     *
     * @param string $class The class to load.
     *
     * @return void
     */
    public static function contaoAutoload($class)
    {
        if (0 === strpos($class, 'Contao\\')) {
            return;
        }
        $result = class_exists('Contao\\' . $class);
        if ($result) {
            class_alias('Contao\\' . $class, $class);
        }
    }

    /**
     * Register Contao 4 autoloader.
     *
     * @return void
     */
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();
        spl_autoload_register(self::class . '::contaoAutoload');
    }

    /**
     * Unregister Contao 4 autoloader.
     *
     * @return void
     */
    public static function tearDownAfterClass()
    {
        spl_autoload_unregister(self::class . '::contaoAutoload');
        parent::tearDownAfterClass();
    }
}
