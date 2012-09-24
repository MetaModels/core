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
		if(TL_MODE=='BE')
		{
			return BackendUser::getInstance();
		} else if(TL_MODE=='FE')
		{
			return FrontendUser::getInstance();
		}
		return null;
	}

	protected function getPaletteCombination($objMetaModel)
	{
		$objUser = self::getUser();
		if (get_class($objUser) == 'BackendUser')
		{
			$strGrpCol = 'be_group';
                        $isAdmin = $objUser->admin;
		} else {
			$strGrpCol = 'fe_group';
		}

		// there might be a NULL in there :/
                // SH: Yes. Admins have no groups and user might have one but it is a not must have. 
                // I would prefer a default group for both, fe and be groups.
                
                // If admin try to get this group first
                if($isAdmin == true)
                {
                    $objPossibleMatches = Database::getInstance()
                            ->prepare(sprintf('SELECT * FROM tl_metamodel_dca_combine WHERE pid=? AND %s = "sysAdmin"', $strGrpCol))
                            ->limit(1)
                            ->execute($objMetaModel->get('id'));
                    
                    // Check if we have a result
                    if($objPossibleMatches->numRows != 0)
                    {
                        return $objPossibleMatches->row();
                    }
                }
                
                // Try to get the group
                $arrGroups = array_filter($objUser->groups);                
               
                if(count($arrGroups) != 0)
                {
                     $objPossibleMatches = Database::getInstance()->prepare(
			sprintf('SELECT * FROM tl_metamodel_dca_combine WHERE pid=? AND %s IN (%s) ORDER BY sorting ASC',
				$strGrpCol,
				implode(',', $arrGroups))
			)->limit(1)
			->execute($objMetaModel->get('id'));
                     
                     // Check if we have a result
                    if($objPossibleMatches->numRows != 0)
                    {
                        return $objPossibleMatches->row();
                    }
                }     
                
                // Try to load the default groups
                $objPossibleMatches = Database::getInstance()
                            ->prepare(sprintf('SELECT * FROM tl_metamodel_dca_combine WHERE pid=? AND %s = "default"', $strGrpCol))
                            ->limit(1)
                            ->execute($objMetaModel->get('id'));
                 
                // Return false or a group
		return $objPossibleMatches->row();
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
		if(!in_array($strTableName, MetaModelFactory::getAllTables()))
			return false;

		// call the loadDataContainer from Controller.php for the base DCA.
		parent::loadDataContainer('tl_metamodel_item');
		parent::loadLanguageFile('tl_metamodel_item');

		$arrDCA = $GLOBALS['TL_DCA']['tl_metamodel_item'];

		$arrDCA['dca_config']['data_provider']['default']['source'] = $strTableName;

		$objMetaModel=MetaModelFactory::byTableName($strTableName);
		if ($objMetaModel->isTranslated())
		{
			$this->loadLanguageFile('languages');
		}

		$arrCombination = $this->getPaletteCombination($objMetaModel);
		if (!$arrCombination)
		{
			$this->log('Attempt to access the metamodel "' . $objMetaModel->getName() . '" without palette combination for current user.', 'MetaModelDatabase createDataContainer()', TL_ERROR);
			$this->redirect('contao/main.php?act=error');
		}

		$arrDCA['config']['metamodel_view'] = $arrCombination['view_id'];

		$strPalette='';
		$objDCASettings = Database::getInstance()->prepare('SELECT * FROM tl_metamodel_dcasetting WHERE pid=?')->execute($arrCombination['dca_id']);
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
						$strPalette .= (strlen($strPalette)>0 ? ',':'') . $objAttribute->getColName();
					}
				break;
				case 'legend':
					$legendName = standardize($objDCASettings->legendtitle).'_legend';
					$GLOBALS['TL_LANG'][$objMetaModel->getTableName()][$legendName] = $objDCASettings->legendtitle;
					$strPalette .= ((strlen($strPalette)>0 ? ';':'') . '{'.$legendName.$objAttribute->legendhide.'}');
				break;
				default:
					throw new Exception("Unknown palette rendering mode " . $objDCASettings->dcatype);
			}
		}
		$arrDCA['palettes']['default'] = $strPalette;

		$arrDCA['config']['label'] = $objMetaModel->get('name');

		// FIXME: if we have variants, we force mode 5 here, no matter what the DCA configs say.
		if($objMetaModel->hasVariants())
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
							'to_field'    => 'vargroup',
							'from_field'  => 'vargroup',
							// 'value'    => ''
						),
					),
					'filter' => array
					(
						// TODO: filtering for parent id = vargroup works but we need another way to limit the scope.
						array
						(
							'local'       => 'vargroup',
							'remote'      => 'id',
							'operation'   => '=',
						),
						array
						(
							'local'       => 'vargroup',
							'remote'      => 'vargroup',
							'operation'   => '='
						),
						array
						(
							'local'        => 'varbase',
							'remote_value' => '0',
							'operation'    => '=',
						),
					),
				),
			);

			$arrDCA['dca_config']['rootEntries']['self'] = array
			(
				'setOn' => array
				(
					array(
						'property'    => 'varbase',
						'value'       => '0'
					),
				),

				'filter' => array
				(
					array
					(
						'property'    => 'varbase',
						'operation'   => '=',
						'value'       => '1'
					)
				)
			);

			// TODO: do only show variant bases if we are told so, i.e. render child view.
			$arrDCA['fields']['varbase'] = array
			(
				'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_item']['varbase'],
				'inputType'               => 'checkbox',
				'eval'                    => array
				(
					'submitOnChange'=>true,
					'doNotShow' => true
				)
			);

			$arrOperationCreateVariant = array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_item']['createvariant'],
				'href'                => 'act=createvariant',
				'icon'                => 'system/modules/metamodels/html/variants.png',
				'button_callback'     => array('MetaModelDatabase', 'buttonCallbackCreateVariant')
			);

			// search for copy operation and insert just behind that one
			$intPos = array_search('copy', array_keys($arrDCA['list']['operations']));
			if ($intPos !== false)
			{
				array_insert($arrDCA['list']['operations'], $intPos+1,
					array(
						'createvariant' => $arrOperationCreateVariant
					)
				);
			} else {
				// or append to the end, if copy operation has not been found
				$arrDCA['list']['operations']['createvariant'] = $arrOperationCreateVariant;
			}
		} else {
			switch ($objMetaModel->get('rendertype')) {
				case 'ctable':
					$arrDCA['dca_config']['data_provider']['parent']['source'] = $objMetaModel->get('ptable');
					$arrDCA['list']['sorting']['child_record_callback'] = array();

					if (substr($objMetaModel->get('ptable'), 0, 2) == 'mm')
					{
						// metamodels can be filtered on other fields than id=>pid
					} else {
						// for tl prefix, the only unique target can be the id? maybe load parent dc and scan for uniques in config then.
						$arrDCA['dca_config']['childCondition'] = array
						(
							array(
								'from' => $objMetaModel->get('ptable'),
								'to' => 'self',
								'setOn' => array
								(
									array(
										'to_field'    => 'pid',
										'from_field'  => 'id',
									),
								),
								'filter' => array
								(
									array
									(
										'local'       => 'pid',
										'remote'      => 'id',
										'operation'   => '=',
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

			$arrDCA['list']['sorting']['mode'] = $objMetaModel->get('mode');
			if (in_array($objMetaModel->get('mode'), array(3, 4, 6)))
			if($objMetaModel->get('ptable'))
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
								'property'    => 'pid',
								'value'       => '0'
							),
						),

						'filter' => array
						(
							array
							(
								'property'    => 'pid',
								'operation'   => '=',
								'value'       => '0'
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
									'to_field'    => 'pid',
									'from_field'  => 'id',
								),
							),
							'filter' => array
							(
								array
								(
									'local'       => 'pid',
									'remote'      => 'id',
									'operation'   => '=',
								)
							),
						),
					);
					break;
				default:
			}

			// determine image to use.
			if ($objMetaModel->get('backendicon') && file_exists(TL_ROOT . '/' . $objMetaModel->get('backendicon')))
			{
				$arrDCA['list']['sorting']['icon'] = $this->getImage($this->urlEncode($objMetaModel->get('backendicon')), 16, 16);
			} else {
				$arrDCA['list']['sorting']['icon'] = 'system/modules/metamodels/html/metamodels.png';
			}
		}

		$GLOBALS['TL_DCA'][$strTableName] = array_replace_recursive($arrDCA, (array)$GLOBALS['TL_DCA'][$objMetaModel->getTableName()]);

		$GLOBALS['TL_LANG'][$objMetaModel->getTableName()] = array_replace_recursive($GLOBALS['TL_LANG']['tl_metamodel_item'] , (array)$GLOBALS['TL_LANG'][$objMetaModel->getTableName()]);

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
		return sprintf('<a href="%s" title="%s"%s>%s</a> ',
			$this->addToUrl($strHref.'&amp;act=createvariant&amp;id='.$arrRow['id']),
			specialchars($strTitle),
			$strAttributes,
			$strImg?$strImg:$strLabel
		);
	}
}

?>