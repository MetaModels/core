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
	 * Fetch the palette view configuration valid for the given group ids.
	 *
	 * @param int    $intMetaModel the MetaModel for which a combination shall be retrieved.
	 *
	 * @param string $strGroupCol  the group column that shall be examined. either fe_group or be_group
	 *
	 * @param int[]  $arrGroupIds  the group ids that are valid
	 *
	 * @return array|null the matching combination or null if no combination has been found.
	 *
	 */
	protected function getPaletteCombinationRow($intMetaModel, $strGroupCol, $arrGroupIds)
	{
		$objPossibleMatches = Database::getInstance()
			->prepare(sprintf('SELECT * FROM tl_metamodel_dca_combine WHERE pid=? AND %s IN (%s)', $strGroupCol, implode(',', $arrGroupIds)))
			->limit(1)
			->execute($intMetaModel);
		// Check if we have a result
		return ($objPossibleMatches->numRows) ? $objPossibleMatches->row() : null;
	}

	/**
	 * Get the default combination of palette and view (if any has been defined).
	 *
	 * @param int $intMetaModel the MetaModel for which a combination shall be retrieved.
	 *
	 * @return array|null the matching combination or null if no combination has been found.
	 *
	 */
	protected function getPaletteCombinationDefault($intMetaModel)
	{
		$objDca = Database::getInstance()
			->prepare('SELECT * FROM tl_metamodel_dca WHERE pid=? AND isdefault=1')
			->limit(1)
			->execute($intMetaModel);

		$objRender = Database::getInstance()
			->prepare('SELECT * FROM tl_metamodel_rendersettings WHERE pid=? AND isdefault=1')
			->limit(1)
			->execute($intMetaModel);

		return array
		    (
		    'dca_id' => $objDca->id,
		    'view_id' => $objRender->id,
		);
	}

	/**
	 * Get the default combination of palette and view (if any has been defined) for the current user.
	 *
	 * @param IMetaModel $objMetaModel the MetaModel for which a combination shall be retrieved.
	 *
	 * @return array|null the matching combination or null if no combination has been found.
	 *
	 */
	protected function getPaletteCombination($objMetaModel)
	{
		$objUser = self::getUser();
		if (get_class($objUser) == 'BackendUser')
		{
			$strGrpCol = 'be_group';
			if ($objUser->admin)
			{
				$arrMatch = $this->getPaletteCombinationRow($objMetaModel->get('id'), $strGrpCol, array(-1));
				if ($arrMatch)
				{
					return $arrMatch;
				}
			}
		}
		else
		{
			$strGrpCol = 'fe_group';
		}

		// Try to get the group
		// there might be a NULL in there as BE admins have no groups and user might have one but it is a not must have.
		// I would prefer a default group for both, fe and be groups.
		$arrGroups = array_filter($objUser->groups);
		if (count($arrGroups) > 0)
		{
			$arrMatch = $this->getPaletteCombinationRow($objMetaModel->get('id'), $strGrpCol, $arrGroups);
			if ($arrMatch)
			{
				return $arrMatch;
			}
		}

		return $this->getPaletteCombinationDefault($objMetaModel->get('id'));
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

		$arrCombination = $this->getPaletteCombination($objMetaModel);

		if (!$arrCombination['dca_id'])
		{
			$strMessage = sprintf($GLOBALS['TL_LANG']['ERR']['no_palette'], $objMetaModel->getName(), self::getUser()->username);
			MetaModelBackendModule::addMessageEntry(
				$strMessage, METAMODELS_ERROR, $this->addToUrl('do=metamodels&table=tl_metamodel_dca&id=' . $objMetaModel->get('id'))
			);
			$this->log($strMessage, 'MetaModelDatabase createDataContainer()', TL_ERROR);
			return true;
		}

		if (!$arrCombination['view_id'])
		{
			$strMessage = sprintf($GLOBALS['TL_LANG']['ERR']['no_view'], $objMetaModel->getName(), self::getUser()->username);
			MetaModelBackendModule::addMessageEntry(
				$strMessage, METAMODELS_ERROR, $this->addToUrl('do=metamodels&table=tl_metamodel_rendersettings&id=' . $objMetaModel->get('id'))
			);
			$this->log($strMessage, 'MetaModelDatabase createDataContainer()', TL_ERROR);
			return true;
		}

		$arrDCA['config']['metamodel_view'] = $arrCombination['view_id'];
		$arrDCA['palettes']['default'] = $this->getPaletteAndFields($arrCombination['dca_id'], $objMetaModel, $arrDCA);

		$arrDCA['config']['label'] = $objMetaModel->get('name');

		// FIXME: if we have variants, we force mode 5 here, no matter what the DCA configs say.
		if ($objMetaModel->hasVariants())
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

			if ($objMetaModel->get('ptable'))
			{
				if ($objMetaModel->get('ptable') == $objMetaModel->get('tableName'))
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
				array_insert($arrDCA['list']['operations'], $intPos + 1, array(
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
		else
		{
			switch ($objMetaModel->get('rendertype'))
			{
				case 'ctable':
					$arrDCA['dca_config']['data_provider']['parent']['source'] = $objMetaModel->get('ptable');
					$arrDCA['list']['sorting']['child_record_callback'] = array();

					if (substr($objMetaModel->get('ptable'), 0, 2) == 'mm')
					{
						// metamodels can be filtered on other fields than id=>pid
					}
					else
					{
						// for tl prefix, the only unique target can be the id? maybe load parent dc and scan for uniques in config then.
						$arrDCA['dca_config']['childCondition'] = array
						    (
						    array(
							'from' => $objMetaModel->get('ptable'),
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
					}
					break;

				case 'selftree':
					// => mode 5 - Records are displayed as self containing tree (see site structure)
					// must provide backend section then, as no external parent available.
					break;

				case 'standalone':
					break;


				default:
					throw new Exception("Unknown Backend rendering mode for metamodel " . $objMetaModel->get('rendertype'));

					break;
			}

			// TODO: implement a proper class that handles all of this when moving backend integration also to dca table.
			$objDca = Database::getInstance()
				->prepare('SELECT * FROM tl_metamodel_dca WHERE id=?')
				->limit(1)
				->execute($arrCombination['dca_id']);

			$arrDCA['list']['sorting']['mode'] = $objMetaModel->get('mode');

			// ToDo: SH:CS: We have now 2 places for mode, one in mm and on in rendersettings :(
			// If mm-mode 1, use the renderingsettings as overwrite
			if ($objMetaModel->get('mode') == 1 || $objMetaModel->get('mode') == 5)
			{
				// Set the new mode
				$arrDCA['list']['sorting']['mode'] = $objDca->mode;

				// Set Sorting flag from current renderSettings
				$arrDCA['list']['sorting']['flag'] = $objDca->flag;

				// Set filter/sorting fields
				$arrSorting = array();

				// FIXME: We might want to push these config options to the tl_metamodel_dcasetting table to have it tied to it's definition.
				foreach (deserialize($objDca->fields, true) as $field)
				{
					if($field['filterable'])
					{
						$arrDCA['fields'][$field['field_attribute']]['filter'] = true;
					}

					if($field['searchable'])
					{
						$arrDCA['fields'][$field['field_attribute']]['search'] = true;
					}

					if($field['sortable'])
					{
						$arrSorting[] = $field['field_attribute'];
						$arrDCA['fields'][$field['field_attribute']]['sorting'] = true;
					}
				}

				// Set Sorting flag from current renderSettings
				$arrDCA['list']['sorting']['fields'] = $arrSorting;

				// Set Sorting panelLayout from current renderSettings
				$arrDCA['list']['sorting']['panelLayout'] = $objDca->panelLayout;
			}

			if (in_array($objMetaModel->get('mode'), array(3, 4, 6)))
				if ($objMetaModel->get('ptable'))
				{
					$arrDCA['dca_config']['data_provider']['parent']['source'] = $objMetaModel->get('ptable');
				}

			switch ($objMetaModel->get('mode'))
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

?>