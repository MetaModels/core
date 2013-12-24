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

namespace MetaModels\BackendIntegration\Contao2;

/**
 * This class is used in the backend to build the menu when running in Contao 2.11 environment.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class initializeSystemHOOKHack extends \MetaModels\BackendIntegration\Boot
{
	/**
	 * Autoloader helper hack to simulate initializeSystem HOOK.
	 *
	 * @param $strClass
	 *
	 * @return bool
	 */
	public static function checkBackendLoad($strClass)
	{
		if ($strClass == 'Backend')
		{
			spl_autoload_unregister(array(__CLASS__, 'checkBackendLoad'));
			self::perform();
		}
		return false;
	}

	/**
	 * Register an auto loader which will transport the config variables and un register itself when loading class Backend.
	 */
	public static function register()
	{
		spl_autoload_register(array(__CLASS__, 'checkBackendLoad'), true, true);
		if (version_compare(VERSION, '3.0', '<') && !in_array('__autoload', spl_autoload_functions()))
		{
			spl_autoload_register('__autoload');
		}
	}
}

