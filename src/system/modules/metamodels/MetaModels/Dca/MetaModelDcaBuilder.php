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

namespace MetaModels\Dca;

use MetaModels\BackendIntegration\Module;
use MetaModels\Factory;
use MetaModels\Helper\ContaoController;
use MetaModels\Helper\TableManipulation;
use MetaModels\IMetaModel;

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
	 * Cache for "dcasetting id" <=> "MM attribute colname" mapping.
	 *
	 * @var array
	 */
	protected static $arrColNameChache = array();

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
	 * @return \BackendUser|\FrontendUser|null the BackendUser when TL_MODE == 'BE', the FrontendUser when TL_MODE == 'FE' or null otherwise
	 */
	protected static function getUser()
	{
		if (TL_MODE == 'BE')
		{
			return \BackendUser::getInstance();
		}
		else if (TL_MODE == 'FE')
		{
			return \FrontendUser::getInstance();
		}
		return null;
	}

	protected static function getDB()
	{
		return \Database::getInstance();
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
		$objCombinations = $this->getDB()
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
		$objDca = $this->getDB()
			->prepare(sprintf('SELECT * FROM tl_metamodel_dca WHERE pid IN (%s) AND isdefault=1', implode(',', $arrMetaModels)))
			->execute();

		while ($objDca->next())
		{
			$this->arrInformation[$objDca->pid]['comb']['dca_id'] = $objDca->id;
		}

		$objRender = $this->getDB()
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

		$objDca = $this->getDB()
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
		if (!\Database::getInstance()->tableExists('tl_metamodel', null, true))
		{
			return;
		}

		$objModels = $this->getDB()->execute('SELECT id FROM tl_metamodel order by sorting');
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
		if(version_compare(VERSION, '3.0', '>'))
		{
			$strBackendUserClass = 'Contao\BackendUser';
		}
		else
		{
			$strBackendUserClass = 'BackendUser';
		}	
		
		if ($objUser instanceof $strBackendUserClass)
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
			$this->arrInformation[$intMetaModel]['view'] = $this->getDB()
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
			$arrResult[] = Factory::byId($intId);
		}
		return $arrResult;
	}

	public static function getBackendIcon($strBackendIcon)
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
	 * Inject child tables for the given table name as operations.
	 *
	 * @param string $strTable     the table to inject into.
	 *
	 * @param array  $arrTableDCA  the DCA corresponding to that table.
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
				/* @var IMetaModel $objMetaModel */
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
		$objMetaModel = Factory::byId($arrDCA['pid']);
		$strModuleName = 'metamodel_' . $objMetaModel->getTableName();
		$strTableCaption = $objMetaModel->getName();
 		$strBackendIcon = $arrDCA['backendicon'];

		// If we have a c3 replace the id/uuid with the path.
		if($strBackendIcon && version_compare(VERSION, '3.0', '>'))
		{
			$objFile = \FilesModel::findByPk($strBackendIcon);
			$strBackendIcon = $objFile->path;
		}

		// determine image to use.
		if ($strBackendIcon && file_exists(TL_ROOT . '/' . $strBackendIcon))
		{
			$strIcon = ContaoController::getInstance()->getImage(ContaoController::getInstance()->urlEncode($strBackendIcon), 16, 16);
		} else {
			$strIcon = 'system/modules/metamodels/html/metamodels.png';
		}

		$strSection = (trim($arrDCA['backendsection'])) ? $arrDCA['backendsection'] : 'metamodels';

		$GLOBALS['BE_MOD'][$strSection][$strModuleName] = array
		(
			'tables'			=> array($objMetaModel->getTableName()),
			'icon'				=> $strIcon,
			'callback'			=> 'MetaModels\BackendIntegration\Module'
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
								$arrSubTables[] = Factory::byId($intModel)->getTableName();
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

	/**
	 * Get from a dcasetting the colum name
	 *
	 * @param \MetaModels\IMetaModel $objMetaModel the MetaModel for which the palette shall be built.
	 *
	 * @param int                    $intID ID of an entry from the tl_metamodel_dcasetting
	 *
	 * @return string Name of the column
	 */
	protected function getColNameByDcaSettingId($objMetaModel, $intID)
	{
		// Get name from cache.
		if(in_array($intID, self::$arrColNameChache))
		{
			return self::$arrColNameChache[$intID];
		}

		// Get the attr_id for MM.
		$objDCASettings = $this->getDB()
			->prepare('SELECT attr_id FROM tl_metamodel_dcasetting WHERE id=?')
			->execute($intID);

		// Check if we have the selector id.
		if ($objDCASettings->numRows == 0)
		{
			self::$arrColNameChache[$intID] = false;
		}
		else
		{
			// Get name from attribute.
			$objAttribute = $objMetaModel->getAttributeById($objDCASettings->attr_id);
			self::$arrColNameChache[$intID] = $objAttribute->getColName();
		}

		return self::$arrColNameChache[$intID];
	}

	/**
	 * Check if a field is a selector for a subpalette.
	 *
	 * @param int $intID ID of an entry from the tl_metamodel_dcasetting
	 *
	 * @return boolean
	 */
	protected function isSelector($intID)
	{
		// Count subpalette elements.
		$objDCASettings = $this->getDB()
			->prepare('SELECT count(id) as count FROM tl_metamodel_dcasetting WHERE subpalette=?')
			->execute($intID);

		if($objDCASettings->count != 0)
		{
			return true;
		}

		return false;
	}

	/**
	 * Retrieves a palette from the database and populates the passed DCA 'fields' section with the correct settings.
	 *
	 * @param int                    $intPaletteId  the id of the palette to retrieve
	 *
	 * @param \MetaModels\IMetaModel $objMetaModel  the MetaModel for which the palette shall be built.
	 *
	 * @param array                  &$arrDCA       the DCA that shall get populated (used by reference).
	 *
	 * @return string the palette string.
	 *
	 * @throws \RuntimeException
	 */
	protected function getPaletteAndFields($intPaletteId, $objMetaModel, &$arrDCA)
	{
		$strPalette = '';
		$objDCASettings = $this->getDB()
			->prepare('SELECT * FROM tl_metamodel_dcasetting WHERE pid=? AND published = 1 ORDER by sorting ASC')
			->execute($intPaletteId);
		$arrMyDCA = array();
		while ($objDCASettings->next())
		{
			switch ($objDCASettings->dcatype)
			{
				case 'attribute':
					$objAttribute = $objMetaModel->getAttributeById($objDCASettings->attr_id);
					if ($objAttribute)
					{
						// Get basics.
						$arrFieldSetting = $objDCASettings->row();

						// Overwrite submitOnChange if we have a selector.
						if ($this->isSelector($objDCASettings->id))
						{
							$arrFieldSetting = array_replace_recursive(
								$objDCASettings->row(),
								array('submitOnChange' => true)
							);
						}

						$arrMyDCA = array_replace_recursive($arrMyDCA, $objAttribute->getItemDCA($arrFieldSetting));

						// Check if we have a subpalette. If false, add to normal palettes.
						if ($objDCASettings->subpalette == 0)
						{
							$strPalette .= (strlen($strPalette) > 0 ? ',' : '') . $objAttribute->getColName();
						}
						// Else add as subpalettes.
						else
						{
							$strSelector = $this->getColNameByDcaSettingId($objMetaModel, $objDCASettings->subpalette);

							// This should never ever be true. If so, we have dead entries in the database.
							if($strSelector === false)
							{
								break;
							}

							$arrMyDCA['metasubpalettes'][$strSelector][] = $objAttribute->getColName();
						}
					}
					break;
				case 'legend':
					$arrLegend = deserialize($objDCASettings->legendtitle);
					if (is_array($arrLegend))
					{
						// try to use the language string from the array.
						$strLegend = $arrLegend[$GLOBALS['TL_LANGUAGE']];
						if (!$strLegend)
						{
							// use the fallback
							$strLegend = $arrLegend[$objMetaModel->getFallbackLanguage()];
							if (!$strLegend)
							{
								// last resort, simply "legend"
								$strLegend = 'legend';
							}
						}
					}
					else
					{
						$strLegend = $objDCASettings->legendtitle ? $objDCASettings->legendtitle : 'legend';
					}

					$legendName = standardize($strLegend) . '_legend';
					$GLOBALS['TL_LANG'][$objMetaModel->getTableName()][$legendName] = $strLegend;

					$strPalette .= ((strlen($strPalette) > 0 ? ';' : '') .
						'{' . $legendName . ($objDCASettings->legendhide ? ':hide' : '') . '}');
					break;
				default:
					throw new \RuntimeException("Unknown palette rendering mode " . $objDCASettings->dcatype);
			}
		}
		$arrDCA = array_replace_recursive($arrDCA, $arrMyDCA);

		return $strPalette;
	}

	/**
	 * @param \MetaModels\IMetaModel $objMetaModel
	 *
	 * @param array                  $arrDCASetting
	 *
	 * @param array                  $arrDCA
	 */
	protected function createDataContainerWithVariants($objMetaModel, $arrDCASetting, &$arrDCA)
	{
		$arrDCA['list']['sorting']['mode'] = 5;
		$arrDCA['dca_config']['data_provider']['parent']['source'] = $objMetaModel->getTableName();

		$arrDCA['dca_config']['child_list']['self']['fields'] = array
		(
			'id',
			'tstamp'
		);

		$arrDCA['dca_config']['childCondition'] = array
		(
			array(
				'from' => 'self',
				'to' => 'self',
				'setOn' => array
				(
					array(
						'to_field' => 'varbase',
						'value' => '0'
					),
					array(
						'to_field' => 'vargroup',
						'from_field' => 'vargroup',
					),
				),
				'filter' => array
				(
					// TODO: filtering for parent id = vargroup works but we need another way to limit the scope.
					array
					(
						'local' => 'vargroup',
						'remote' => 'id',
						'operation' => '=',
					),
					array
					(
						'local' => 'vargroup',
						'remote' => 'vargroup',
						'operation' => '='
					),
					array
					(
						'local' => 'varbase',
						'remote_value' => '0',
						'operation' => '=',
					),
				),
			),
		);

		$arrDCA['dca_config']['rootEntries']['self'] = array
		(
			'setOn' => array
			(
				array(
					'property' => 'varbase',
					'value' => '1'
				),
				// NOTE: vargroup will be set to the item's id when being saved. This is done in the MetaModel itself, as we have no idea beforehand - DC_General is out here.
			),
			'filter' => array
			(
				array
				(
					'property' => 'varbase',
					'operation' => '=',
					'value' => '1'
				)
			)
		);

		// TODO: do only show variant bases if we are told so, i.e. render child view.
		$arrDCA['fields']['varbase'] = array
		(
			'label' => &$GLOBALS['TL_LANG']['tl_metamodel_item']['varbase'],
			'inputType' => 'checkbox',
			'eval' => array
			(
				'submitOnChange' => true,
				'doNotShow' => true
			)
		);

		if ($arrDCASetting['ptable'])
		{
			if ($arrDCASetting['ptable'] == $objMetaModel->get('tableName'))
			{
				$pidValue = 0;
			}
			else
			{
				$pidValue = \Input::getInstance()->get('id_' . $objMetaModel->get('ptable'));
			}

			$arrDCA['list']['sorting']['filter'] = array_merge_recursive
			(
				array(array('pid', $pidValue)), (array) $arrDCA['list']['sorting']['filter']
			);
			$arrDCA['dca_config']['rootEntries']['self']['filter'][] = array('property' => 'pid', 'operation' => '=', 'value' => $pidValue);
			$arrDCA['dca_config']['rootEntries']['self']['setOn'][] = array('property' => 'pid', 'value' => $pidValue);
		}

		$arrOperationCreateVariant = array
		(
			'label' => &$GLOBALS['TL_LANG']['tl_metamodel_item']['createvariant'],
			'href' => 'act=createvariant',
			'icon' => 'system/modules/metamodels/html/variants.png',
			'button_callback' => array('MetaModelDatabase', 'buttonCallbackCreateVariant')
		);

		// only rewrite the url if the operation has been registered.
		if (array_key_exists('copy', $arrDCA['list']['operations']))
		{
			$arrDCA['list']['operations']['copy']['href'] = 'act=paste&mode=copy';
		}

		// search for copy operation and insert just behind that one
		$intPos = array_search('copy', array_keys($arrDCA['list']['operations']));
		if ($intPos !== false)
		{
			array_insert($arrDCA['list']['operations'], $intPos + 1, array
				(
					'createvariant' => $arrOperationCreateVariant
				)
			);
		}
		else
		{
			// or append to the end, if copy operation has not been found
			$arrDCA['list']['operations']['createvariant'] = $arrOperationCreateVariant;
		}
	}

	/**
	 * @param \MetaModels\IMetaModel $objMetaModel
	 *
	 * @param array                  $arrDCASettings
	 *
	 * @param array                  $arrDCA
	 *
	 * @return void
	 */
	protected function createDataContainerNormal($objMetaModel, $arrDCASettings, &$arrDCA)
	{
		if ($arrDCASettings['rendertype'] == 'ctable')
		{
			$arrDCA['dca_config']['data_provider']['parent']['source'] = $arrDCASettings['ptable'];
			$arrDCA['list']['sorting']['child_record_callback'] = array();

			// for tl prefix, the only unique target can be the id? maybe load parent dc and scan for uniques in config then.
			$arrDCA['dca_config']['childCondition'] = array
			(
				array
				(
					'from' => $arrDCASettings['ptable'],
					'to' => 'self',
					'setOn' => array
					(
						array
						(
							'to_field' => 'pid',
							'from_field' => 'id',
						),
					),
					'filter' => array
					(
						array
						(
							'local' => 'pid',
							'remote' => 'id',
							'operation' => '=',
						)
					),
				),
			);
		}
		$arrDCA['list']['sorting']['mode'] = $arrDCASettings['mode'];
		// Set Sorting flag from current renderSettings
		$arrDCA['list']['sorting']['flag'] = $arrDCASettings['flag'];

		// Set filter/sorting fields
		$arrSorting = array();

		// add sorting field to sortable field list.
		// TODO: empty this list when toggling manual sort.
		$arrDCA['list']['sorting']['fields'][] = 'sorting';

		// Set Sorting panelLayout from current renderSettings
		$arrDCA['list']['sorting']['panelLayout'] = $arrDCASettings['panelLayout'];
		switch ($arrDCASettings['mode'])
		{
			case 5:
				$arrDCA['dca_config']['child_list']['self']['fields'] = array(
					'id', 'tstamp'
				);

				$arrDCA['dca_config']['rootEntries']['self'] = array
				(
					'setOn' => array
					(
						array(
							'property' => 'pid',
							'value' => '0'
						),
					),
					'filter' => array
					(
						array
						(
							'property' => 'pid',
							'operation' => '=',
							'value' => '0'
						)
					)
				);

				$arrDCA['dca_config']['childCondition'] = array
				(
					array(
						'from' => 'self',
						'to' => 'self',
						'setOn' => array
						(
							array(
								'to_field' => 'pid',
								'from_field' => 'id',
							),
						),
						'filter' => array
						(
							array
							(
								'local' => 'pid',
								'remote' => 'id',
								'operation' => '=',
							)
						),
					),
				);
				$arrDCA['list']['operations']['copy']['href'] = 'act=paste&mode=copy';
				break;
			default:
		}

		// determine image to use.
		if ($objMetaModel->get('backendicon') && file_exists(TL_ROOT . '/' . $objMetaModel->get('backendicon')))
		{
			$arrDCA['list']['sorting']['icon'] = ContaoController::getInstance()->getImage(ContaoController::getInstance()->urlEncode($objMetaModel->get('backendicon')), 16, 16);
		}
		else
		{
			$arrDCA['list']['sorting']['icon'] = 'system/modules/metamodels/html/metamodels.png';
		}
	}

	/**
	 * Create the data container of a metamodel table.
	 *
	 * @param string $strTableName the name of the meta model table that shall be created.
	 *
	 * @return bool true on success, false otherwise.
	 */
	public function createDataContainer($strTableName)
	{
		if (!in_array($strTableName, Factory::getAllTables()))
			return false;

		// call the loadDataContainer from Controller.php for the base DCA.
		ContaoController::getInstance()->loadDataContainer('tl_metamodel_item');
		ContaoController::getInstance()->loadLanguageFile('tl_metamodel_item');

		$GLOBALS['TL_DCA'][$strTableName] = array_replace_recursive($GLOBALS['TL_DCA']['tl_metamodel_item'], (array) $GLOBALS['TL_DCA'][$strTableName]);
		$arrDCA = &$GLOBALS['TL_DCA'][$strTableName];

		$arrDCA['dca_config']['data_provider']['default']['source'] = $strTableName;

		$objMetaModel = Factory::byTableName($strTableName);
		if ($objMetaModel->isTranslated())
		{
			ContaoController::getInstance()->loadLanguageFile('languages');
		}

		$arrDCASettings = $this->getDca($objMetaModel->get('id'));
		$arrViewSettings = $this->getView($objMetaModel->get('id'));

		if (!$arrDCASettings)
		{
			$strMessage = sprintf($GLOBALS['TL_LANG']['ERR']['no_palette'], $objMetaModel->getName(), self::getUser()->username);
			Module::addMessageEntry(
				$strMessage, METAMODELS_ERROR, ContaoController::getInstance()->addToUrl('do=metamodels&table=tl_metamodel_dca&id=' . $objMetaModel->get('id'))
			);
			ContaoController::getInstance()->log($strMessage, 'MetaModelDatabase createDataContainer()', TL_ERROR);
			return true;
		}

		if (!$arrViewSettings)
		{
			$strMessage = sprintf($GLOBALS['TL_LANG']['ERR']['no_view'], $objMetaModel->getName(), self::getUser()->username);
			Module::addMessageEntry(
				$strMessage, METAMODELS_ERROR, ContaoController::getInstance()->addToUrl('do=metamodels&table=tl_metamodel_rendersettings&id=' . $objMetaModel->get('id'))
			);
			ContaoController::getInstance()->log($strMessage, 'MetaModelDatabase createDataContainer()', TL_ERROR);
			return true;
		}

		$arrDCA['config']['metamodel_view'] = $arrViewSettings['id'];
		$arrDCA['palettes']['default'] = $this->getPaletteAndFields($arrDCASettings['id'], $objMetaModel, $arrDCA);

		if ($arrDCASettings['backendcaption'])
		{
			$arrCaptions = deserialize($arrDCASettings['backendcaption'], true);
			foreach ($arrCaptions as $arrLangEntry)
			{
				if ($arrLangEntry['label'] != '' && $arrLangEntry['langcode'] == $objMetaModel->getActiveLanguage())
				{
					$arrDCA['config']['label'] = $arrLangEntry['label'];
				} else if (($arrLangEntry['label'] != '') && (!$arrDCA['config']['label']) && ($arrLangEntry['langcode'] == $objMetaModel->getFallbackLanguage())) {
					$arrDCA['config']['label'] = $arrLangEntry['label'];
				}
			}
		}

		if (!$arrDCA['config']['label'])
		{
			$arrDCA['config']['label'] = $objMetaModel->get('name');
		}

		// Check access level.
		if ($arrDCASettings['isclosed'])
		{
			$arrDCA['config']['closed']       = true;
			$arrDCA['config']['notDeletable'] = true;
			unset($arrDCA['list']['operations']['delete']);
		}

		// FIXME: if we have variants, we force mode 5 here, no matter what the DCA configs say.
		if ($objMetaModel->hasVariants())
		{
			$this->createDataContainerWithVariants($objMetaModel, $arrDCASettings, $arrDCA);
		}
		else
		{
			$this->createDataContainerNormal($objMetaModel, $arrDCASettings, $arrDCA);
		}
		$GLOBALS['TL_LANG'][$objMetaModel->getTableName()] = array_replace_recursive($GLOBALS['TL_LANG']['tl_metamodel_item'], (array) $GLOBALS['TL_LANG'][$objMetaModel->getTableName()]);
		// TODO: add a HOOK here for extensions to manipulate the DCA. loadMetaModelDataContainer($objMetaModel)
		//$GLOBALS['METAMODEL_HOOKS']['loadDataContainer']

		return true;
	}

	public function buttonCallbackCreateVariant($arrRow, $strHref, $strLabel, $strTitle, $strIcon, $strAttributes)
	{
		// only create a button if this is a variant base.
		if (!$arrRow['varbase'])
		{
			return '';
		}

		$strImg = ContaoController::getInstance()->generateImage($strIcon, $strLabel);
		return sprintf('<a href="%s" title="%s"%s>%s</a> ', ContaoController::getInstance()->addToUrl($strHref . '&amp;act=createvariant&amp;id=' . $arrRow['id']), specialchars($strTitle), $strAttributes, $strImg ? $strImg : $strLabel
		);
	}

	/**
	 * Return the paste page button
	 * @param DataContainer
	 * @param array
	 * @param string
	 * @param boolean
	 * @param array
	 * @return string
	 */
	public function pasteButton(\DC_General $objDC, $arrRow, $strTable, $cr, DcGeneral\Clipboard\ClipboardInterface $objClipboard=null)
	{
		if($objClipboard == null)
		{
			return;
		}

		$disablePA = true;
		$disablePI = true;

		// FIXME: whoa, this is hacky, the DC should provide a better way to obtain all of this.
		$objSrcProvider = $objDC->getDataProvider($strTable);
		if (\Input::getInstance()->get('source'))
		{
			$objModel = $objSrcProvider->fetch($objSrcProvider->getEmptyConfig()->setId(\Input::getInstance()->get('source')));
		} else {
			$objModel = null;
		}

		if ($objModel && isset($arrRow['id']) && strlen($arrRow['id']) && ($arrRow['id'] != $objModel->getID()))
		{
			// Insert a varbase after any other varbase, for sorting.
			if ($objModel->getProperty('varbase') == 1 && $arrRow['id'] != $objModel->getID() && $arrRow['varbase'] != 0)
			{
				$disablePA = false;
			}
			// Move items in here vargroup and only there.
			else if($objModel->getProperty('varbase') == 0 && $arrRow['vargroup'] == $objModel->getProperty('vargroup') && $arrRow['varbase'] != 1)
			{
				$disablePA = false;
			}
		}
		elseif($objModel == null && $arrRow['varbase'] == 0)
		{
			$disablePA = true;
		}
		else
		{
			$disablePA = false;
			// If we are in create mode, disaple the paste into.
			$disablePI = !($arrRow['varbase'] == 1 && $objClipboard->getMode() != 'create');
		}
		
		// Return the buttons
		$imagePasteAfter = ContaoController::getInstance()->generateImage('pasteafter.gif', sprintf($GLOBALS['TL_LANG'][$strTable]['pasteafter'][1], $arrRow['id']), 'class="blink"');
		$imagePasteInto = ContaoController::getInstance()->generateImage('pasteinto.gif', sprintf($GLOBALS['TL_LANG'][$strTable]['pasteinto'][1], $arrRow['id']), 'class="blink"');

		// Get the id`s from the clipboard.		
		$arrContainId	 = $objClipboard->getContainedIds();
		$intID			 = array_shift($arrContainId);
		$arrChilds		 = (count($arrContainId) > 1) ? $arrContainId : array();

		$strAdd2UrlAfter = sprintf(
			'act=%s&amp;mode=1&amp;pid=%s&amp;after=%s&amp;childs=%s',
			$objClipboard->getMode(),
			$intID,
			$arrRow['id'],
			implode(',', $arrChilds)
		);

		$strAdd2UrlInto = sprintf(
			'act=%s&amp;mode=2&amp;pid=%s&amp;after=%s&amp;childs=%s',
			$objClipboard->getMode(),
			$intID,
			$arrRow['id'],
			implode(',', $arrChilds)
		);

		$strPasteBtn = '';

		if ($disablePA)
		{
			$strPasteBtn = ContaoController::getInstance()->generateImage('pasteafter_.gif', '', 'class="blink"').' ';
		} else {
			$strPasteBtn = sprintf(
				' <a href="%s" title="%s" onclick="Backend.getScrollOffset()">%s</a> ',
				ContaoController::getInstance()->addToUrl($strAdd2UrlAfter),
				specialchars($GLOBALS['TL_LANG'][$strTable]['pasteafter'][0]),
				$imagePasteAfter
			);
		}

		if ($disablePI)
		{
			$strPasteBtn .= ContaoController::getInstance()->generateImage('pasteinto_.gif', '', 'class="blink"').' ';
		} else {
			$strPasteBtn .= sprintf(
				' <a href="%s" title="%s" onclick="Backend.getScrollOffset()">%s</a> ',
				ContaoController::getInstance()->addToUrl($strAdd2UrlInto),
				specialchars($GLOBALS['TL_LANG'][$strTable]['pasteinto'][0]),
				$imagePasteInto
			);
		}

		// special case, the root paste into.
		if (!(isset($arrRow['id']) && strlen($arrRow['id'])))
		{
			if ($objModel && ($objModel->getProperty('varbase') == 1))
			{
				$strPasteBtn = sprintf(
					' <a href="%s" title="%s" onclick="Backend.getScrollOffset()">%s</a> ',
					ContaoController::getInstance()->addToUrl(sprintf(
						'act=%s&amp;mode=2&amp;after=0&amp;pid=0&amp;id=%s&amp;childs=%s',
						$objClipboard->getMode(),
						$intID,
						implode(',', $arrChilds)
					)),
					specialchars($GLOBALS['TL_LANG'][$strTable]['pasteinto'][0]),
					$imagePasteInto
				);
			} elseif (!$objModel ) {
				$strPasteBtn = sprintf(
					' <a href="%s" title="%s" onclick="Backend.getScrollOffset()">%s</a> ',
					ContaoController::getInstance()->addToUrl(sprintf(
						'act=%s&amp;mode=2&amp;after=0&amp;pid=0&amp;id=%s&amp;childs=%s',
						$objClipboard->getMode(),
						$intID,
						implode(',', $arrChilds)
					)),
					specialchars($GLOBALS['TL_LANG'][$strTable]['pasteinto'][0]),
					$imagePasteInto
				);
			} else {
				$strPasteBtn = ContaoController::getInstance()->generateImage('pasteinto_.gif', '', 'class="blink"').' ';
			}
		}

		return $strPasteBtn;
	}
}
