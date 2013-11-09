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
 * @author     Christian de la Haye <service@delahaye.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

/**
 * Collects the dca combinations for each MetaModel, that is matching the current user.
 *
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @package    MetaModels
 * @subpackage Core
 */
class MetaModelDcaBuilder
{
	/**
	 * all MetaModel combinations for lookup.
	 * @var array
	 */
	protected $arrInformation = array();

	protected $arrPTables = array();

	/**
	 * The singleton instance
	 *
	 * @var MetaModelDcaBuilder
	 */
	protected static $objInstance;

	protected function __construct()
	{
		$this->bufferModels();
	}

	/**
	 * Retrieve the singleton.
	 *
	 * @return MetaModelDcaBuilder
	 */
	public static function getInstance()
	{
		if (!self::$objInstance)
		{
			self::$objInstance = new MetaModelDcaBuilder();
		}
		return self::$objInstance;
	}

	/**
	 * Returns the proper user object for the current context.
	 *
	 * @return BackendUser|FrontendUser|null the BackendUser when TL_MODE == 'BE', the FrontendUser when TL_MODE == 'FE' or null otherwise
	 */
	protected static function getUser()
	{
		if (TL_MODE == 'BE')
		{
			return BackendUser::getInstance();
		}
		else if (TL_MODE == 'FE')
		{
			return FrontendUser::getInstance();
		}
		return null;
	}

	/**
	 * Fetch the palette view configurations valid for the given group ids.
	 *
	 * @param string $strGroupCol  the group column that shall be examined. either fe_group or be_group
	 *
	 * @param int[]  $arrGroupIds  the group ids that are valid
	 *
	 * @return int[] the ids that have not been resolved as no combination has been defined and therefore the default must be used for.
	 *
	 */
	protected function getPaletteCombinationRows($strGroupCol, $arrGroupIds)
	{
		$objCombinations = Database::getInstance()
			->prepare(sprintf('SELECT * FROM tl_metamodel_dca_combine WHERE %s IN (%s) ORDER BY pid,sorting ASC', $strGroupCol, implode(',', $arrGroupIds)))
			->execute();

		$arrSuccess = array();

		while ($objCombinations->next())
		{
			// already a combination present, continue with next one.
			if ($this->arrInformation[$objCombinations->pid]['comb'])
			{
				continue;
			}
			$this->arrInformation[$objCombinations->pid]['comb'] = $objCombinations->row();
			$arrSuccess[] = $objCombinations->pid;
		}
		return array_diff(array_keys($this->arrInformation), $arrSuccess);
	}

	/**
	 * Get the default combination of palette and view (if any has been defined).
	 *
	 * @param int[] $arrMetaModels the MetaModels for which combinations shall be retrieved.
	 *
	 * @return array|null the matching combination or null if no combination has been found.
	 *
	 */
	protected function getPaletteCombinationDefault($arrMetaModels)
	{
		$objDca = Database::getInstance()
			->prepare(sprintf('SELECT * FROM tl_metamodel_dca WHERE pid IN (%s) AND isdefault=1', implode(',', $arrMetaModels)))
			->execute();

		while ($objDca->next())
		{
			$this->arrInformation[$objDca->pid]['comb']['dca_id'] = $objDca->id;
		}

		$objRender = Database::getInstance()
			->prepare(sprintf('SELECT * FROM tl_metamodel_rendersettings WHERE pid IN (%s) AND isdefault=1', implode(',', $arrMetaModels)))
			->execute();

		while ($objRender->next())
		{
			$this->arrInformation[$objRender->pid]['comb']['view_id'] = $objRender->id;
		}

	}

	/**
	 * Pull in all DCA settings for the local MetaModels.
	 */
	protected function getDCAs()
	{
		$arrDCAs = array();
		foreach ($this->arrInformation as $arrInfo)
		{
			if ($arrInfo['comb']['dca_id'])
			{
				$arrDCAs[] = $arrInfo['comb']['dca_id'];
			}
		}

		if (!$arrDCAs) return;

		$objDca = Database::getInstance()
			->prepare(sprintf('SELECT * FROM tl_metamodel_dca WHERE id IN (%s)', implode(',', $arrDCAs)))
			->execute();

		while ($objDca->next())
		{
			$this->arrInformation[$objDca->pid]['dca'] = $objDca->row();
			// store the ptable lookup.
			if ($objDca->rendertype == 'ctable')
			{
				$this->arrPTables[$objDca->ptable][] = $objDca->pid;
			}
		}
	}

	/**
	 * Collect all metamodels from the Database, that have an relation to the current user.
	 * Buffer them in a local list to have them handy when a corresponding parent table is being instantiated etc.
	 */
	protected function bufferModels()
	{
		$objModels=Database::getInstance()->execute('SELECT id FROM tl_metamodel order by sorting');
		while ($objModels->next())
		{
			$this->arrInformation[$objModels->id] = array
			(
				'comb' => array(),
				'dca'  => array(),
				'view' => array()
			);
		}

		$strGrpCol = 'fe_group';
		$objUser = self::getUser();

		// Try to get the group
		// there might be a NULL in there as BE admins have no groups and user might have one but it is a not must have.
		// I would prefer a default group for both, fe and be groups.
		$arrGroups = $objUser->groups ? array_filter($objUser->groups) : array();

		// special case in combinations, admins have the implicit group id -1
		if (get_class($objUser) == 'BackendUser')
		{
			$strGrpCol = 'be_group';
			if ($objUser->admin)
			{
				$arrGroups[] = -1;
			}
		}

		if ($arrGroups)
		{
			$arrFallbacks = $this->getPaletteCombinationRows($strGrpCol, $arrGroups);
		} else {
			$arrFallbacks = array_keys($this->arrInformation);
		}
		if ($arrFallbacks)
		{
			$this->getPaletteCombinationDefault($arrFallbacks);
		}

		$this->getDCAs();
	}

	public function getView($intMetaModel)
	{
		if (!$this->arrInformation[$intMetaModel]['view'])
		{
			$this->arrInformation[$intMetaModel]['view'] = Database::getInstance()
			->prepare('SELECT * FROM tl_metamodel_rendersettings WHERE id=?')
			->limit(1)
			->execute($this->arrInformation[$intMetaModel]['comb']['view_id'])
			->row();
		}
		return $this->arrInformation[$intMetaModel]['view'];
	}

	public function getDca($intMetaModel)
	{
		return $this->arrInformation[$intMetaModel]['dca'];
	}

	public function getModelsWithPtable($strTablename)
	{
		$arrResult = array();
		foreach ((array)$this->arrPTables[$strTablename] as $intId)
		{
			$arrResult[] = MetaModelFactory::byId($intId);
		}
		return $arrResult;
	}

	public static function getBackendIcon($strBackendIcon)
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
	 * Inject child tables for the given table name as operations.
	 *
	 * @param string $strTable the table to inject into.
	 *
	 * @param array  $arrDca   the DCA corresponding to that table.
	 *
	 * @return void
	 */
	public function injectChildTablesIntoDCA($strTable, &$arrTableDCA)
	{
		$objMetaModels = $this->getModelsWithPtable($strTable);

		foreach ($objMetaModels as $objMetaModel)
		{
			if ($objMetaModel)
			{
				$arrDca = $this->getDca($objMetaModel->get('id'));

				$arrCaption = array('', sprintf($GLOBALS['TL_LANG']['MSC']['metamodel_edit_as_child']['label'], $objMetaModel->getName()));
				foreach (deserialize($arrDca['backendcaption'], true) as $arrLangEntry)
				{
					if ($arrLangEntry['label'] != '' && $arrLangEntry['langcode'] == self::getUser()->language)
					{
						$arrCaption = array($arrLangEntry['description'], $arrLangEntry['label']);
					}
				}

				$arrTableDCA['list']['operations']['edit_'.$objMetaModel->getTableName()] = array
				(
					'label'               => $arrCaption,
					'href'                => 'table='.$objMetaModel->getTableName(),
					'icon'                => self::getBackendIcon($arrDca['backendicon']),
					'attributes'          => 'onclick="Backend.getScrollOffset()"'
				);

				// is the destination table a metamodel with variants?
				if ($objMetaModel->hasVariants())
				{
					$arrTableDCA['list']['operations']['edit_'.$objMetaModel->getTableName()]['idparam'] = 'id_'.$strTable;
				}
			}
		}
	}

	protected function handleCTable($arrDCA)
	{
		// TODO: nothing to do anymore, appearantly.
		// create a child relation in parent.
//		self::$arrTableInjections[$arrDCA['ptable']][] = $objMetaModel->tableName;
//		self::registerLateConfig();
	}

	protected function handleStandalone($arrDCA)
	{
		$objMetaModel = MetaModelFactory::byId($arrDCA['pid']);
		$strModuleName = 'metamodel_' . $objMetaModel->getTableName();
		$strTableCaption = $objMetaModel->getName();

		// determine image to use.
		if ($arrDCA['backendicon'] && file_exists(TL_ROOT . '/' . $arrDCA['backendicon']))
		{
			$strIcon = MetaModelController::getImage(MetaModelController::urlEncode($arrDCA['backendicon']), 16, 16);
		} else {
			$strIcon = 'system/modules/metamodels/html/metamodels.png';
		}

		$strSection = (trim($arrDCA['backendsection'])) ? $arrDCA['backendsection'] : 'metamodels';

		$GLOBALS['BE_MOD'][$strSection][$strModuleName] = array
		(
			'tables'			=> array($objMetaModel->getTableName()),
			'icon'				=> $strIcon,
			'callback'			=> 'MetaModelBackendModule'
		);

		$arrCaption = array($strTableCaption);
		foreach (deserialize($arrDCA['backendcaption'], true) as $arrLangEntry)
		{
			if ($arrLangEntry['label'] != '' && $arrLangEntry['langcode'] == self::getUser()->language)
			{
				$arrCaption = array($arrLangEntry['label'], $arrLangEntry['description']);
			}
		}
		$GLOBALS['TL_LANG']['MOD'][$strModuleName] = $arrCaption;
	}

	/**
	 * Inject MetaModels in the backend menu.
	 *
	 * @return void
	 */
	public function injectBackendMenu()
	{
		foreach ($this->arrInformation as $intModel => $arrInfo)
		{
			//
			switch ($arrInfo['dca']['rendertype'])
			{
				case '':
					// not configured yet.
					break;
				case 'ctable':
					$this->handleCTable($arrInfo['dca']);
					break;

			case 'selftree':
				// => mode 5 - Records are displayed as self containing tree (see site structure)
				// must provide backend section then, as no external parent available.
				break;

			case 'standalone':
				$this->handleStandalone($arrInfo['dca']);
				break;
			}
		}
	}

	public function injectIntoBackendModules()
	{
		$arrPTables = $this->arrPTables;
		$intCount = count($arrPTables);
		// loop until all tables are injected or until there was no injection during one run.
		// This is important, as we might have models that are child of another model.
		while ($arrPTables)
		{
			foreach ($arrPTables as $strTable => $arrModels)
			{
				foreach ($GLOBALS['BE_MOD'] as $strGroup => $arrModules)
				{
					foreach ($arrModules as $strModule => $arrConfig)
					{
						if (isset($arrConfig['tables']) && in_array($strTable, $arrConfig['tables']))
						{
							$arrSubTables = array();
							foreach ($arrModels as $intModel)
							{
								$arrSubTables[] = MetaModelFactory::byId($intModel)->getTableName();
							}
							$GLOBALS['BE_MOD'][$strGroup][$strModule]['tables'] = array_merge($GLOBALS['BE_MOD'][$strGroup][$strModule]['tables'], $arrSubTables);
							unset($arrPTables[$strTable]);
						}
					}
				}
			}
			if (count($arrPTables) == $intCount)
			{
				break;
			}
			$intCount = count($arrPTables);
		}
	}
}