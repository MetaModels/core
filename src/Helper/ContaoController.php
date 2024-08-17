<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2023 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Helper;

use Contao\Controller;
use Contao\System;

/**
 * Gateway to the Contao "Controller" class for usage of the core without
 * importing any class.
 *
 * This is achieved using the magic functions which will relay the call
 * to the parent class Controller. See there for a list of function that can
 * be called (everything in Controller.php that is declared as protected).
 *
 * @deprecated Deprecated in favor of contao-events-bindings
 */
class ContaoController extends Controller
{
    /**
     * The instance.
     *
     * @var ContaoController|null
     */
    protected static $objInstance = null;

    /**
     * Get the static instance.
     *
     * @static
     * @return ContaoController
     */
    public static function getInstance()
    {
        if (self::$objInstance == null) {
            self::$objInstance = new self();
        }
        return self::$objInstance;
    }

    /**
     * Protected constructor for singleton instance.
     */
    protected function __construct()
    {
        parent::__construct();
        System::getContainer()->get('database_connection');
    }

    /**
     * Makes all protected methods from class Controller callable publically.
     *
     * @param string $strMethod The method to call.
     *
     * @param array  $arrArgs   The arguments.
     *
     * @return mixed
     *
     * @throws \RuntimeException When the method is unknown.
     */
    public function __call($strMethod, $arrArgs)
    {
        if (method_exists($this, $strMethod)) {
            return call_user_func_array(array($this, $strMethod), $arrArgs);
        }
        throw new \RuntimeException('undefined method: Controller::' . $strMethod);
    }

    /**
     * Makes all protected methods from class Controller callable publically from static context (requires PHP 5.3).
     *
     * @param string $strMethod The method to call.
     *
     * @param array  $arrArgs   The arguments.
     *
     * @return mixed
     *
     * @throws \RuntimeException When the method is unknown.
     */
    public static function __callStatic($strMethod, $arrArgs)
    {
        if (method_exists(__CLASS__, $strMethod)) {
            return call_user_func_array(array(self::getInstance(), $strMethod), $arrArgs);
        }
        throw new \RuntimeException('undefined method: Controller::' . $strMethod);
    }
}
