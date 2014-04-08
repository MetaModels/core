<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package	   MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\BackendIntegration;

use MetaModels\Dca\MetaModelDcaBuilder;
use MetaModels\Helper\ContaoController;

/**
 * This is the MetaModel backend interface.
 * It is used in the backend to build the menu, pack all the
 *
 * @package	   MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class Boot
{
	/**
	 * Returns the proper user object for the current context.
	 *
	 * @return \BackendUser|\FrontendUser|null the BackendUser when TL_MODE == 'BE', the FrontendUser when TL_MODE == 'FE' or null otherwise
	 */
	protected static function getUser()
	{
		if(TL_MODE=='BE')
		{
			return \BackendUser::getInstance();
		} else if(TL_MODE=='FE')
		{
			return \FrontendUser::getInstance();
		}
		return null;
	}

	/**
	 * This initializes the Contao Singleton object stack as it must be,
	 * when using singletons within the config.php file of an Extension.
	 *
	 * @return bool
	 */
	protected static function initializeContaoObjectStack()
	{
		if (!file_exists(TL_ROOT . '/system/config/localconfig.php'))
		{
			return false;
		}

		// all of these getInstance calls are neccessary to keep the instance stack intact
		// and therefore prevent an Exception in unknown on line 0.
		// Hopefully this will get fixed with Contao Reloaded or Contao 3.

		require_once(TL_ROOT . '/system/config/localconfig.php');

		\Config::getInstance();

		\Environment::getInstance();
		\Input::getInstance();

		self::getUser();

		\Database::getInstance();

		return true;
	}

	protected static function isDBInitialized()
	{
		// When coming from install.php or somewhere else when localconfig.php
		// has not yet completely been initialized, we will run into an exception here.
		try
		{
			$objDB = \Database::getInstance();
			return $objDB && $objDB->tableExists('tl_metamodel');
		}
		catch (\Exception $e)
		{
			return false;
		}
	}

	protected static function authenticateBackendUser()
	{
		$objUser = self::getUser();
		// work around as the TL_PATH constant is set after this routine has been run.
		// if this is not in place, BackendUser::authenticate() will redirect us to
		// http://domain.tldtl_path/contao/index.php
		// if no user is properly logged in (note the missing slash in the middle right after .tld).
		// We also have to fix up the "script" parameter, as this one will otherwise try to redirect from
		// "contao/index.php" to "/contao/index.php" therefore creating an infinite redirect loop.
		$Env = \Environment::getInstance();

		// issue #66 - contao/install.php is not working anymore. Thanks to Stefan Lindecke (@lindesbs)
		if (strpos($Env->request,"install.php") !== false)
		{
			return;
		}

		// Fix issue #397 - the security patch rendered our redirect method non working (websitePath can now be null).
		$path        = constant('TL_PATH') ?: $GLOBALS['TL_CONFIG']['websitePath'];
		$Env->base   = $Env->url . $path . '/';
		$Env->script = preg_replace('/^' . preg_quote($path, '/') . '\/?/i', '', $Env->scriptName);

		// Bugfix: If the user is not authenticated, contao will redirect to contao/index.php
		// But in this moment the TL_PATH is not defined, so the $this->Environment->request
		// generate a url without replacing the basepath(TL_PATH) with an empty string.
		if (!defined(TL_PATH))
		{
			define('TL_PATH', $path);
		}

		// TODO: double, triple and quadro check that this is really safe context here.
		$objUser->authenticate();
		// restore initial settings.
		$Env->base = null;
		$Env->script = null;
	}

	public function getBackendIcon($strBackendIcon)
	{
		// determine image to use.
		if ($strBackendIcon && file_exists(TL_ROOT . '/' . $strBackendIcon))
		{
			return ContaoController::getInstance()->getImage(ContaoController::getInstance()->urlEncode($strBackendIcon), 16, 16);;
		} else {
			return 'system/modules/metamodels/html/metamodels.png';
		}
	}

	/**
	 * Add the child tables to the DCA as operation (if any child tables are present).
	 */
	public function createDataContainer($strTable)
	{
		if (self::isDBInitialized())
		{
			MetaModelDcaBuilder::getInstance()->injectChildTablesIntoDCA($strTable, $GLOBALS['TL_DCA'][$strTable]);
		}
	}

	public static function checkBackendLoad($strClass)
	{
		if ($strClass == 'Backend')
		{
			MetaModelDcaBuilder::getInstance()->injectIntoBackendModules();
			spl_autoload_unregister(array('MetaModels\BackendIntegration\Boot', 'checkBackendLoad'));
		}
		return false;
	}

	protected static function registerLateConfig()
	{
		// register a autoloader which will transport the config variables and unregister itself when loading class Backend.
		spl_autoload_register(array('MetaModels\BackendIntegration\Boot', 'checkBackendLoad'), true, true);
		if (version_compare(VERSION, '3.0', '<') && !in_array('__autoload', spl_autoload_functions()))
		{
			spl_autoload_register('__autoload');
		}
	}

	/**
	 * Called from config.php in TL_MODE == 'BE' to register everything neccessary for the backend.
	 *
	 * @return void
	 */
	public static function buildBackendMenu()
	{
		if (!self::initializeContaoObjectStack())
		{
			return;
		}

		try
		{
			if (self::isDBInitialized())
			{
				// if no backend user authenticated, we will get redirected.
				self::authenticateBackendUser();

				MetaModelDcaBuilder::getInstance()->injectBackendMenu();
				self::registerLateConfig();
			}
		}
		catch (\Exception $exc)
		{
			// Note: do NOT use the logging prvided by class System here as that one logs into the DB
			// which is pretty useless as the DB most likely was the one throwing the exception.
			log_message('Exception in MetaModels\BackendIntegration\Boot::buildBackendMenu() - ' . $exc->getMessage());
		}
	}
}

