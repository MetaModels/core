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
 * @copyright  CyberSpectrum
 * @license    private
 * @filesource
 */
if (!defined('TL_ROOT'))
{
	die('You cannot access this file directly!');
}

/**
 * This is the MetaModel backend interface.
 * It is used in the backend to build the menu, pack all the
 *
 * @package	   MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class MetaModelBackend
{
	/**
	 * Returns the proper user object for the current context.
	 *
	 * @return BackendUser|FrontendUser|null the BackendUser when TL_MODE == 'BE', the FrontendUser when TL_MODE == 'FE' or null otherwise
	 */
	protected static function getUser()
	{
		if(TL_MODE=='BE')
		{
			return BackendUser::getInstance();
		} else if(TL_MODE=='FE')
		{
			return FrontendUser::getInstance();
		}
		return null;
	}

	/**
	 * This initializes the Contao Singleton object stack as it must be,
	 * when using singletons within the config.php file of an Extension.
	 *
	 * @return void
	 */
	protected static function initializeContaoObjectStack()
	{
		// all of these getInstance calls are neccessary to keep the instance stack intact
		// and therefore prevent an Exception in unknown on line 0.
		// Hopefully this will get fixed with Contao Reloaded or Contao 3.
		Config::getInstance();
		Environment::getInstance();
		Input::getInstance();

		// request token became available in 2.11
		if (version_compare(TL_VERSION, '2.11', '>='))
		{
			RequestToken::getInstance();
		}

		self::getUser();

		Database::getInstance();
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
		$Env = Environment::getInstance();
		$Env->base = $Env->url . $GLOBALS['TL_CONFIG']['websitePath'] . '/';
		$Env->script = preg_replace('/^' . preg_quote($GLOBALS['TL_CONFIG']['websitePath'], '/') . '\/?/i', '', $Env->scriptName);

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
			return MetaModelController::getImage(MetaModelController::urlEncode($strBackendIcon), 16, 16);;
		} else {
			return 'system/modules/metamodels/html/metamodels.png';
		}
	}

	/**
	 * Add the child tables to the DCA if any present.
	 */
	public function createDataContainer($strTable)
	{
		$objDCA = Database::getInstance()->prepare('SELECT * FROM tl_metamodel WHERE rendertype=? AND ptable=?')->execute('ctable', $strTable);
		while ($objDCA->next())
		{
			$objMetaModel = MetaModelFactory::byId($objDCA->id);
			if ($objMetaModel)
			{
				$arrCaption = array('', sprintf($GLOBALS['TL_LANG']['MSC']['metamodel_edit_as_child']['label'], $objMetaModel->getName()));
				foreach (deserialize($objMetaModel->get('backendcaption'), true) as $arrLangEntry)
				{
					if ($arrLangEntry['langcode'] == self::getUser()->language)
					{
						$arrCaption = array($arrLangEntry['description'], $arrLangEntry['label']);
					}
				}

				$GLOBALS['TL_DCA'][$strTable]['list']['operations']['edit_'.$objMetaModel->getTableName()] = array
				(
					'label'               => $arrCaption,
					'href'                => 'table='.$objMetaModel->getTableName(),
					'icon'                => $this->getBackendIcon($objMetaModel->get('backendicon')),
					'attributes'          => 'onclick="Backend.getScrollOffset()"'
				);
			}
		}
	}

	/**
	 * Prepare the backend layout for a certain MM.
	 */
	protected static function handleModel($objMetaModel)
	{
		if (!$objMetaModel->rendertype)
		{
			return;
		}

		switch ($objMetaModel->rendertype)
		{
			case 'ctable':
				// create a child relation in parent.
				self::$arrTableInjections[$objMetaModel->ptable][] = $objMetaModel->tableName;
				$blnPostConfig = true;
				self::registerLateConfig();
				break;

			case 'selftree':
				// => mode 5 - Records are displayed as self containing tree (see site structure)
				// must provide backend section then, as no external parent available.
				break;

			case 'standalone':
				$strModuleName = 'metamodel_' . $objMetaModel->tableName;
				$strTableCaption = $objMetaModel->name;

				// determine image to use.
				if ($objMetaModel->backendicon && file_exists(TL_ROOT . '/' . $objMetaModel->backendicon))
				{
					$strIcon = MetaModelController::getImage(MetaModelController::urlEncode($objMetaModel->backendicon), 16, 16);;
				} else {
					$strIcon = 'system/modules/metamodels/html/metamodels.png';
				}

				$GLOBALS['BE_MOD'][$objMetaModel->backendsection][$strModuleName] = array
				(
					'tables'			=> array($objMetaModel->tableName),
					'icon'				=> $strIcon,
					'callback'			=> 'MetaModelBackendModule'
				);

				$arrCaption = array($strTableCaption);
				foreach (deserialize($objMetaModel->backendcaption, true) as $arrLangEntry)
				{
					if ($arrLangEntry['langcode'] == self::getUser()->language)
					{
						$arrCaption = array($arrLangEntry['label'], $arrLangEntry['description']);
					}
				}
				$GLOBALS['TL_LANG']['MOD'][$strModuleName] = $arrCaption;
				break;

			default:
				throw new Exception("Unknown Backend rendering mode " . $objMetaModel->rendertype);

				break;
		}
	}

	protected static function addTablesToParent($arrTableNames, $strPTableName)
	{
		foreach ($GLOBALS['BE_MOD'] as $strGroup => $arrModules)
		{
			foreach ($arrModules as $strModule => $arrConfig)
			{
				if (isset($arrConfig['tables']) && in_array($strPTableName, $arrConfig['tables']))
				{
					$GLOBALS['BE_MOD'][$strGroup][$strModule]['tables'] = array_merge($GLOBALS['BE_MOD'][$strGroup][$strModule]['tables'], $arrTableNames);
				}
			}
		}
	}

	protected static $arrTableInjections = array();

	public static function checkBackendLoad($strClass)
	{
		if ($strClass == 'Backend')
		{
			foreach (self::$arrTableInjections as $strParent => $arrInjection)
			{
				self::addTablesToParent($arrInjection, $strParent);
			}
			spl_autoload_unregister(array('MetaModelBackend', 'checkBackendLoad'));
		}
		return false;
	}

	protected static function registerLateConfig()
	{
		if (count(self::$arrTableInjections))
		{
			// register a autoloader which will transport the config variables and unregister itself when loading class Backend.
			spl_autoload_register(array('MetaModelBackend', 'checkBackendLoad'), true, true);
			if (!in_array('__autoload', spl_autoload_functions()))
			{
				spl_autoload_register('__autoload');
			}
		}
	}

	/**
	 * Called from config.php in TL_MODE == 'BE' to register everything neccessary for the backend.
	 *
	 * @return void
	 */
	public static function buildBackendMenu()
	{
		self::initializeContaoObjectStack();

		$GLOBALS['TL_CSS'][] = 'system/modules/metamodels/html/style.css';
		array_insert($GLOBALS['BE_MOD']['system'], 0, array
		('metamodels' => array(
			'tables'				=> array_merge(array('tl_metamodel', 'tl_metamodel_attribute', 'tl_metamodel_filter', 'tl_metamodel_filtersetting', 'tl_metamodel_rendersettings', 'tl_metamodel_rendersetting', 'tl_metamodel_dca', 'tl_metamodel_dcasetting', 'tl_metamodel_dca_combine')),
			'icon'					=> 'system/modules/metamodels/html/metamodels.png',

			'dca_addall'			=> array('TableMetaModelDcaSetting', 'addAll'),
			'rendersetting_addall'	=> array('TableMetaModelRenderSetting', 'addAll'),
			'callback'				=> 'MetaModelBackendModule'
		)));

		$objDB = Database::getInstance();
		if ($objDB)
		{
			if (!$objDB->tableExists('tl_metamodel'))
			{
				// I can't work without a properly installed database.
				return;
			}

			// if no backend user authenticated, we will get redirected.
			self::authenticateBackendUser();

			$blnPostConfig = false;

			$objMetaModels = Database::getInstance()->prepare('SELECT * FROM tl_metamodel')->execute();
			while ($objMetaModels->next())
			{
				// sort out all that may not be viewed.
				if (!MetaModelPermissions::hasUserAccessTo(self::getUser(), $objMetaModels->id))
				{
					continue;
				}
				self::handleModel($objMetaModels);
			}

			if ($blnPostConfig)
			{
				self::registerLateConfig();
			}
		}
	}
}

?>