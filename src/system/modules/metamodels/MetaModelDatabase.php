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

/**
 * This is the MetaModel Database interface binder class.
 *
 * @package	   MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class MetaModelDatabase extends Controller
{

	protected static function getDB()
	{
		return Database::getInstance();
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
	 * Retrieves a palette from the database and populates the passed DCA 'fields' section with the correct settings.
	 *
	 * @param int        $intPaletteId the id of the palette to retrieve
	 *
	 * @param IMetaModel $objMetaModel the MetaModel for which the palette shall be built.
	 *
	 * @param array      $arrDCA       the DCA that shall get populated (used by reference).
	 *
	 * @return string the palette string.
	 */
	protected function getPaletteAndFields($intPaletteId, $objMetaModel, &$arrDCA)
	{
		$strPalette = '';
		$objDCASettings = Database::getInstance()
			->prepare('SELECT * FROM tl_metamodel_dcasetting WHERE pid=? ORDER by sorting ASC')
			->execute($intPaletteId);

		while ($objDCASettings->next())
		{
			switch ($objDCASettings->dcatype)
			{
				case 'attribute':
					$objAttribute = $objMetaModel->getAttributeById($objDCASettings->attr_id);
					if ($objAttribute)
					{
						$arrDCA = array_replace_recursive($arrDCA, $objAttribute->getItemDCA());
						if ($objDCASettings->tl_class)
						{
							$arrDCA['fields'][$objAttribute->getColName()]['eval']['tl_class'] = $objDCASettings->tl_class;
						}
						$strPalette .= (strlen($strPalette) > 0 ? ',' : '') . $objAttribute->getColName();
						// sorting flag override
						if ($objDCASettings->flag)
						{
							$arrDCA['fields'][$objAttribute->getColName()]['flag'] = $objDCASettings->flag;
						}

						// panel conditions.
						if ($objDCASettings->filterable)
						{
							$arrDCA['fields'][$objAttribute->getColName()]['filter'] = true;
						}
						if ($objDCASettings->searchable)
						{
							$arrDCA['fields'][$objAttribute->getColName()]['search'] = true;
						}
						if ($objDCASettings->sortable)
						{
							$arrDCA['fields'][$objAttribute->getColName()]['sorting'] = true;
							// add sorting fields.
							$arrDCA['list']['sorting']['fields'][] = $objAttribute->getColName();
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
					$strPalette .= ((strlen($strPalette) > 0 ? ';' : '') . '{' . $legendName . $objAttribute->legendhide . '}');
					break;
				default:
					throw new Exception("Unknown palette rendering mode " . $objDCASettings->dcatype);
			}
		}
		return $strPalette;
	}

	protected function createDataContainerWithVariants($objMetaModel, $arrDCASetting, &$arrDCA)
	{
		$arrDCA['list']['sorting']['mode'] = 5;
		$arrDCA['dca_config']['data_provider']['parent']['source'] = $objMetaModel->getTableName();

		$arrDCA['dca_config']['child_list']['self']['fields'] = array(
		    'id', 'tstamp'
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
				$pidValue = $this->Input->get('id_' . $objMetaModel->get('ptable'));
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

		$arrDCA['list']['operations']['copy']['href'] = 'act=paste&mode=copy';

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
			$arrDCA['list']['sorting']['icon'] = $this->getImage($this->urlEncode($objMetaModel->get('backendicon')), 16, 16);
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
		if (!in_array($strTableName, MetaModelFactory::getAllTables()))
			return false;

		// call the loadDataContainer from Controller.php for the base DCA.
		parent::loadDataContainer('tl_metamodel_item');
		parent::loadLanguageFile('tl_metamodel_item');

		$GLOBALS['TL_DCA'][$strTableName] = array_replace_recursive($GLOBALS['TL_DCA']['tl_metamodel_item'], (array) $GLOBALS['TL_DCA'][$strTableName]);
		$arrDCA = &$GLOBALS['TL_DCA'][$strTableName];

		$arrDCA['dca_config']['data_provider']['default']['source'] = $strTableName;

		$objMetaModel = MetaModelFactory::byTableName($strTableName);
		if ($objMetaModel->isTranslated())
		{
			$this->loadLanguageFile('languages');
		}

		$arrDCASettings = MetaModelDcaBuilder::getInstance()->getDca($objMetaModel->get('id'));
		$arrViewSettings = MetaModelDcaBuilder::getInstance()->getView($objMetaModel->get('id'));

		if (!$arrDCASettings)
		{
			$strMessage = sprintf($GLOBALS['TL_LANG']['ERR']['no_palette'], $objMetaModel->getName(), self::getUser()->username);
			MetaModelBackendModule::addMessageEntry(
				$strMessage, METAMODELS_ERROR, $this->addToUrl('do=metamodels&table=tl_metamodel_dca&id=' . $objMetaModel->get('id'))
			);
			$this->log($strMessage, 'MetaModelDatabase createDataContainer()', TL_ERROR);
			return true;
		}

		if (!$arrViewSettings)
		{
			$strMessage = sprintf($GLOBALS['TL_LANG']['ERR']['no_view'], $objMetaModel->getName(), self::getUser()->username);
			MetaModelBackendModule::addMessageEntry(
				$strMessage, METAMODELS_ERROR, $this->addToUrl('do=metamodels&table=tl_metamodel_rendersettings&id=' . $objMetaModel->get('id'))
			);
			$this->log($strMessage, 'MetaModelDatabase createDataContainer()', TL_ERROR);
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

		$strImg = $this->generateImage($strIcon, $strLabel);
		return sprintf('<a href="%s" title="%s"%s>%s</a> ', $this->addToUrl($strHref . '&amp;act=createvariant&amp;id=' . $arrRow['id']), specialchars($strTitle), $strAttributes, $strImg ? $strImg : $strLabel
		);
	}

}

