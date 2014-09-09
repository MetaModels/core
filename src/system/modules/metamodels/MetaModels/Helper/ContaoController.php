<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Helper;

/**
 * Gateway to the Contao "Controller" class for usage of the core without
 * importing any class.
 *
 * This is achieved using the magic functions which will relay the call
 * to the parent class Controller. See there for a list of function that can
 * be called (everything in Controller.php that is declared as protected).
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 *
 * @deprecated Deprecated in favor of contao-events-bindings
 */
class ContaoController extends \Controller
{
	/**
	 * @var ContaoController
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
		$this->import('Database');
	}

	/**
	 * Makes all protected methods from class Controller callable publically.
	 */
	public function __call($strMethod, $arrArgs)
	{
		if (method_exists($this, $strMethod))
		{
			return call_user_func_array(array($this, $strMethod), $arrArgs);
		}
		throw new \RuntimeException('undefined method: Controller::' . $strMethod);
	}

	/**
	 * Makes all protected methods from class Controller callable publically from static context
	 * (requires PHP 5.3).
	 */
	public static function __callStatic($strMethod, $arrArgs)
	{
		if (method_exists(__CLASS__, $strMethod))
		{
			return call_user_func_array(array(self::getInstance(), $strMethod), $arrArgs);
		}
		throw new \RuntimeException('undefined method: Controller::' . $strMethod);
	}
}

