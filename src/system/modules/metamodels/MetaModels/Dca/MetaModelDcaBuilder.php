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
use ContaoCommunityAlliance\Contao\Bindings\Events\Backend\AddToUrlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\LoadDataContainerEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\ResizeImageEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\IdSerializer;
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

	/**
	 * All parent tables.
	 *
	 * Key is table name.
	 *
	 * @var IMetaModel[]
	 */
	protected $arrPTables = array();

	/**
	 * The singleton instance.
	 *
	 * @var MetaModelDcaBuilder
	 */
	protected static $objInstance;

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
	 * Get Contao Database instance.
	 *
	 * @return \Database
	 */
	protected static function getDB()
	{
		return \Database::getInstance();
	}

	/**
	 * Get a 16x16 pixel resized icon of the passed image if it exists, return the default icon otherwise.
	 *
	 * @param string $icon        The icon to resize.
	 *
	 * @param string $defaultIcon The default icon.
	 *
	 * @return string
	 */
	public static function getBackendIcon($icon, $defaultIcon = 'system/modules/metamodels/html/metamodels.png')
	{
		/** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher */
		$dispatcher = $GLOBALS['container']['event-dispatcher'];
		// Determine image to use.
		if ($icon && file_exists(TL_ROOT . '/' . $icon))
		{
			$event = new ResizeImageEvent($icon, 16, 16);
			$dispatcher->dispatch(ContaoEvents::IMAGE_RESIZE, $event);
			return $event->getResultImage();
		}

		return $defaultIcon;
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
			if ($screen->getParentTable() !== $strTable)
			{
				continue;
			}

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
					'attributes'          => 'onclick="Backend.getScrollOffset()"',
				);

				// Is the destination table a metamodel with variants?
				if ($metaModel->hasVariants())
				{
					$arrTableDCA['list']['operations']['edit_' . $metaModel->getTableName()]['idparam'] = 'id_'.$strTable;
				}
				else
				{
					$arrTableDCA['list']['operations']['edit_' . $metaModel->getTableName()]['idparam'] = 'pid';
				}

				// Compatibility with DC_Table.
				if ($arrTableDCA['config']['dataContainer'] !== 'General')
				{
					$arrTableDCA['list']['operations']['edit_' . $metaModel->getTableName()]['button_callback'] =
						array(
							__CLASS__,
							'buildChildButton'
						);
				}
			}
		}
	}

	/**
	 * This method exists only for being compatible when MetaModels are being used as child table from DC_Table context.
	 *
	 * @param array  $arrRow     The current data row.
	 *
	 * @param string $href       The href to be appended.
	 *
	 * @param string $label      The operation label.
	 *
	 * @param string $name       The operation name.
	 *
	 * @param string $icon       The icon path.
	 *
	 * @param string $attributes The button attributes.
	 *
	 * @param string $table      The table name.
	 *
	 * @return string
	 */
	public function buildChildButton($arrRow, $href, $label, $name, $icon, $attributes, $table)
	{
		if (preg_match('#class="([^"]*)"#i', $attributes, $matches))
		{
			$operation = $matches[1];
		}
		else
		{
			$operation = $name;
		}

		$dispatcher = $GLOBALS['container']['event-dispatcher'];
		$idparam    = $GLOBALS['TL_DCA'][$table]['list']['operations'][$operation]['idparam'];
		$id         = IdSerializer::fromValues($table, $arrRow['id']);
		$urlEvent   = new AddToUrlEvent($href. '&amp;' . $idparam . '=' . $id->getSerialized());
		/** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher */
		$dispatcher->dispatch(ContaoEvents::BACKEND_ADD_TO_URL, $urlEvent);

		$imageEvent = new GenerateHtmlEvent($this->getBackendIcon($icon), $label);
		$dispatcher->dispatch(ContaoEvents::IMAGE_GET_HTML, $imageEvent);

		$title = sprintf($label ?: $name, $arrRow['id']);
		return '<a href="' . $urlEvent->getUrl() . '" title="' .
			specialchars($title) . '"' . $attributes . '>' . $imageEvent->getHtml() .
		'</a> ';
	}

	/**
	 * Handle stand alone integration in the backend.
	 *
	 * @param IInputScreen $inputScreen The input screen containing the information.
	 *
	 * @return void
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
			'tables'			=> array($metaModel->getTableName()),
			'icon'				=> $strIcon,
			'callback'			=> 'MetaModels\BackendIntegration\Module'
		);

		$arrCaption = array($strTableCaption);
		foreach (deserialize($inputScreen->getBackendCaption(), true) as $arrLangEntry)
		{
			if ($arrLangEntry['langcode'] == 'en')
			{
				$arrCaption = array($arrLangEntry['label'], $arrLangEntry['description']);
			}

			if ($arrLangEntry['label'] != '' && ($arrLangEntry['langcode'] == $GLOBALS['TL_CONFIG']['language']))
			{
				$arrCaption = array($arrLangEntry['label'], $arrLangEntry['description']);
				break;
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

	/**
	 * Inject all meta models into their corresponding parent tables.
	 *
	 * @return void
	 */
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
	 * @param int                    $intPaletteId The id of the palette to retrieve.
	 *
	 * @param \MetaModels\IMetaModel $objMetaModel The MetaModel for which the palette shall be built.
	 *
	 * @param array                  $arrDCA       The DCA that shall get populated (used by reference).
	 *
	 * @return string the palette string.
	 *
	 * @throws \RuntimeException When an entry is neither an attribute nor a legend.
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
		if (in_array($strTableName, Factory::getAllTables()))
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

		return true;

		// FIXME: if we have variants, we force mode 5 here, no matter what the DCA configs say.
		if ($objMetaModel->hasVariants())
		{
			$this->createDataContainerWithVariants($objMetaModel, $arrDCASettings, $arrDCA);
		}
		else
		{
			$this->createDataContainerNormal($objMetaModel, $arrDCASettings, $arrDCA);
		}

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
	}
}
