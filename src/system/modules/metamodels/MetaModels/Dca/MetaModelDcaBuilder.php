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

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\LoadDataContainerEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\ResizeImageEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\BackendBindings;
use MetaModels\BackendIntegration\InputScreen\IInputScreen;
use MetaModels\BackendIntegration\Module;
use MetaModels\BackendIntegration\ViewCombinations;
use MetaModels\Factory;
use MetaModels\Helper\ContaoController;
use MetaModels\Helper\ToolboxFile;
use MetaModels\IMetaModel;
use MetaModels\Render\Setting\Factory as RenderFactory;

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
	 * Cache for "dcasetting id" <=> "MM attribute colname" mapping.
	 *
	 * @var array
	 */
	protected static $arrColNameChache = array();

	protected $arrPTables = array();

	/**
	 * The singleton instance
	 *
	 * @var MetaModelDcaBuilder
	 */
	protected static $objInstance;

	protected function __construct()
	{
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

	protected static function getDB()
	{
		return \Database::getInstance();
	}

	public static function getBackendIcon($strBackendIcon)
	{
		// determine image to use.
		if ($strBackendIcon && file_exists(TL_ROOT . '/' . $strBackendIcon))
		{
			return ContaoController::getInstance()->getImage(ContaoController::getInstance()->urlEncode($strBackendIcon), 16, 16);
		} else {
			return 'system/modules/metamodels/html/metamodels.png';
		}
	}

	/**
	 * Inject child tables for the given table name as operations.
	 *
	 * @param string $strTable The table to inject into.
	 *
	 * @return void
	 */
	public function injectChildTablesIntoDCA($strTable)
	{
		$arrTableDCA = &$GLOBALS['TL_DCA'][$strTable];

		$screens = ViewCombinations::getParentedInputScreens();

		foreach ($screens as $screen)
		{
			$metaModel = $screen->getMetaModel();

			$arrCaption = array(
				'',
				sprintf(
					$GLOBALS['TL_LANG']['MSC']['metamodel_edit_as_child']['label'],
					$metaModel->getName()
				)
			);

			foreach ($screen->getBackendCaption() as $arrLangEntry)
			{
				if ($arrLangEntry['label'] != '' && $arrLangEntry['langcode'] == $GLOBALS['TL_LANGUAGE'])
				{
					$arrCaption = array($arrLangEntry['description'], $arrLangEntry['label']);
				}

				$arrTableDCA['list']['operations']['edit_' . $metaModel->getTableName()] = array
				(
					'label'               => $arrCaption,
					'href'                => 'table='.$metaModel->getTableName(),
					'icon'                => self::getBackendIcon($screen->getIcon()),
					'attributes'          => 'onclick="Backend.getScrollOffset()"'
				);

				// Is the destination table a metamodel with variants?
				if ($metaModel->hasVariants())
				{
					$arrTableDCA['list']['operations']['edit_' . $metaModel->getTableName()]['idparam'] = 'id_'.$strTable;
				}
			}
		}
	}

	/**
	 * @param IInputScreen $inputScreen
	 */
	protected function handleStandalone($inputScreen)
	{
		$metaModel  = $inputScreen->getMetaModel();
		$dispatcher = $GLOBALS['container']['event-dispatcher'];

		$strModuleName = 'metamodel_' . $metaModel->getTableName();

		$strTableCaption = $metaModel->getName();

		$icon = ToolboxFile::convertValueToPath($inputScreen->getIcon());
		// Determine image to use.
		if ($icon && file_exists(TL_ROOT . '/' . $icon))
		{
			$event = new ResizeImageEvent($icon, 16, 16);
			/** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher */
			$dispatcher->dispatch(ContaoEvents::IMAGE_RESIZE, $event);
			$strIcon = $event->getResultImage();
		} else {
			$strIcon = 'system/modules/metamodels/html/metamodels.png';
		}

		$section = $inputScreen->getBackendSection();

		if (!$section)
		{
			$section = 'metamodels';
		}

		$GLOBALS['BE_MOD'][$section][$strModuleName] = array
		(
			// 'tables'			=> array($metaModel->getTableName()),
			'icon'				=> $strIcon,
			'callback'			=> 'MetaModels\BackendIntegration\Module'
		);

		$arrCaption = array($strTableCaption);
		foreach (deserialize($inputScreen->getBackendCaption(), true) as $arrLangEntry)
		{
			if ($arrLangEntry['label'] != '' && ($arrLangEntry['langcode'] == $GLOBALS['TL_CONFIG']['language']))
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
		foreach (ViewCombinations::getStandaloneInputScreens() as $inputScreen)
		{
			$this->handleStandalone($inputScreen);
		}
	}

	public function injectIntoBackendModules()
	{
		$screens = ViewCombinations::getParentedInputScreens();

		$pTables = array();
		foreach ($screens as $screen)
		{
			$ptable = $screen->getParentTable();

			$pTables[$ptable][] = $screen->getMetaModel();
		}

		$this->arrPTables = $pTables;

		$intCount = count($pTables);
		// loop until all tables are injected or until there was no injection during one run.
		// This is important, as we might have models that are child of another model.
		while ($pTables)
		{
			foreach ($pTables as $strTable => $arrModels)
			{
				foreach ($GLOBALS['BE_MOD'] as $strGroup => $arrModules)
				{
					foreach ($arrModules as $strModule => $arrConfig)
					{
						if (isset($arrConfig['tables']) && in_array($strTable, $arrConfig['tables']))
						{
							$arrSubTables = array();
							foreach ($arrModels as $metaModel)
							{
								/** @var IMetaModel $metaModel */
								$arrSubTables[] = $metaModel->getTableName();
							}
							$GLOBALS['BE_MOD'][$strGroup][$strModule]['tables'] = array_merge(
								$GLOBALS['BE_MOD'][$strGroup][$strModule]['tables'],
								$arrSubTables
							);
							unset($pTables[$strTable]);
						}
					}
				}
			}
			if (count($pTables) == $intCount)
			{
				break;
			}
			$intCount = count($pTables);
		}
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
				$pidValue = \Input::getInstance()->get('id_' . $arrDCASetting['ptable']);
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
					'to' => $objMetaModel->getTableName(),
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
				$arrDCA['dca_config']['child_list'][$objMetaModel->getTableName()]['fields'] = array(
					'id', 'tstamp'
				);
				$arrDCA['dca_config']['data_provider']['parent']['source'] = $objMetaModel->getTableName();

				$arrDCA['dca_config']['rootEntries'][$objMetaModel->getTableName()] = array
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
						'from' => $objMetaModel->getTableName(),
						'to' => $objMetaModel->getTableName(),
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
	 * @throws \Exception
	 *
	 * @return bool true on success, false otherwise.
	 */
	public function createDataContainer($strTableName)
	{
		if (substr($strTableName, 0, 3) === 'mm_')
		{
			/** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher */
			$dispatcher = $GLOBALS['container']['event-dispatcher'];
			$event      = new LoadDataContainerEvent('tl_metamodel_item');
			$dispatcher->dispatch(ContaoEvents::CONTROLLER_LOAD_DATA_CONTAINER, $event);

			$GLOBALS['TL_DCA'][$strTableName] = array_replace_recursive(
				(array)$GLOBALS['TL_DCA']['tl_metamodel_item'],
				(array)$GLOBALS['TL_DCA'][$strTableName]
			);

			return true;
		}

		$this->injectChildTablesIntoDCA($strTableName);

		if (!in_array($strTableName, Factory::getAllTables()))
			return false;

		// call the loadDataContainer from Controller.php for the base DCA.
		ContaoController::getInstance()->loadDataContainer('tl_metamodel_item');
		ContaoController::getInstance()->loadLanguageFile('tl_metamodel_item');

		$GLOBALS['TL_DCA'][$strTableName] = array_replace_recursive((array)$GLOBALS['TL_DCA']['tl_metamodel_item'], (array) $GLOBALS['TL_DCA'][$strTableName]);
		$arrDCA = &$GLOBALS['TL_DCA'][$strTableName];

		$arrDCA['dca_config']['data_provider']['default']['source'] = $strTableName;

		$objMetaModel = Factory::byTableName($strTableName);
		if ($objMetaModel->isTranslated())
		{
			ContaoController::getInstance()->loadLanguageFile('languages');
		}

		$arrDCASettings  = ViewCombinations::getInputScreenDetails($objMetaModel->get('id'));
		$arrViewSettings = ViewCombinations::getRenderSettingDetails($objMetaModel->get('id'));

		if (!$arrDCASettings)
		{
			// FIXME: refactor user lookup.
			$strMessage = sprintf($GLOBALS['TL_LANG']['ERR']['no_palette'], $objMetaModel->getName(), self::getUser()->username);
			Module::addMessageEntry(
				$strMessage, METAMODELS_ERROR, ContaoController::getInstance()->addToUrl('do=metamodels&table=tl_metamodel_dca&id=' . $objMetaModel->get('id'))
			);
			ContaoController::getInstance()->log($strMessage, 'MetaModelDatabase createDataContainer()', TL_ERROR);
			return true;
		}

		if (!$arrViewSettings)
		{
			// FIXME: refactor user lookup.
			$strMessage = sprintf($GLOBALS['TL_LANG']['ERR']['no_view'], $objMetaModel->getName(), self::getUser()->username);
			Module::addMessageEntry(
				$strMessage, METAMODELS_ERROR, ContaoController::getInstance()->addToUrl('do=metamodels&table=tl_metamodel_rendersettings&id=' . $objMetaModel->get('id'))
			);
			ContaoController::getInstance()->log($strMessage, 'MetaModelDatabase createDataContainer()', TL_ERROR);
			return true;
		}

		$arrDCA['config']['metamodel_view'] = $arrViewSettings['id'];
		$arrDCA['palettes']['default'] = $this->getPaletteAndFields($arrDCASettings['id'], $objMetaModel, $arrDCA);


		$objView = RenderFactory::byId($objMetaModel, $arrViewSettings['id']);

		if (!$objView)
		{
			throw new \Exception('No backend screen defined.');
		}

		$arrDCA['list']['label'] = array
		(
			'fields' => $objView->getSettingNames(),
			'format' => trim(str_repeat('%s ', count($objView->getSettingNames()))),
		);

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
	 * Return the paste button.
	 *
	 * @param \DC_General                             $objDC
	 *
	 * @param array                                   $arrRow
	 *
	 * @param string                                  $strTable
	 *
	 * @param boolean                                 $cr
	 *
	 * @param \DcGeneral\Clipboard\ClipboardInterface $objClipboard
	 *
	 * @return string
	 */
	public function pasteButton(\DC_General $objDC, $arrRow, $strTable, $cr, $objClipboard)
	{
		if($objClipboard == null)
		{
			return;
		}

		$disablePA = true;
		$disablePI = true;
		
		$strMode = $objClipboard->getMode();
		$arrIds = $objClipboard->getContainedIds();
		$intID = $arrIds[0];
		$arrChildren = (count($arrIds) > 1) ? array_slice($arrIds, 1, count($arrIds) - 1) : array();

		// FIXME: whoa, this is hacky, the DC should provide a better way to obtain all of this.
		$objSrcProvider = $objDC->getEnvironment()->getDataDriver($strTable);
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
