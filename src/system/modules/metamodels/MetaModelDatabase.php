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

		$arrDCA['config']['label'] = $objMetaModel->get('name');

		if($objMetaModel->hasVariants())
		{
			$arrDCA['list']['sorting']['mode'] = 5;
			$arrDCA['dca_config']['data_provider']['parent']['source'] = $objMetaModel->getTableName();
			$arrDCA['dca_config']['joinCondition']['self'] = array
			(
				array
				(
					'srcField'    => 'varbase',
					'dstField'    => '',
					'operation'   => '=0'
				),
				array
				(
					'srcField'    => 'vargroup',
					'dstField'    => 'vargroup',
					'operation'   => '='
				),
			);
			$arrDCA['dca_config']['rootEntries']['self'] = array
			(
				array
				(
					'field'       => 'varbase',
					'operation'   => '=',
					'value'       => '1'
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
				'icon'                => 'system/modules/metamodels/html/createvariant.gif',
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
			$arrDCA['list']['sorting']['mode'] = $objMetaModel->get('mode');
			if($objMetaModel->get('ptable'))
			{
				$arrDCA['dca_config']['data_provider']['parent']['source'] = $objMetaModel->get('ptable');
			}

			switch ($objMetaModel->get('mode'))
			{
				case 5:
					$arrDCA['dca_config']['joinCondition'] = array
					(
						'self' => array(
							array
							(
								'srcField'    => 'id',
								'dstField'    => 'pid',
								'operation'   => '='
							)
						)
					);
					$arrDCA['dca_config']['rootEntries'] = array('pid = 0');
					break;
				default:
			}
		}

		$strPalette='';
		foreach($objMetaModel->getAttributes() as $objAttribute)
		{
			$arrDCA = array_replace_recursive($arrDCA, $objAttribute->getItemDCA());

			if($objAttribute->insertBreak && strlen($objAttribute->legendTitle))
			{
				$legendName = $objAttribute->getColName().'_legend';
				$GLOBALS['TL_LANG'][$objMetaModel->getTableName()][$legendName] = $objAttribute->legendTitle;
				$strPalette .= ((strlen($strPalette)>0 ? ';':'') . '{'.$legendName.(($objAttribute->legendHide)?':hide':'').'},');
			} else
				$strPalette .= (strlen($strPalette)>0 ? ',':'');
			$strPalette .= $objAttribute->getColName();
		}
		$arrDCA['palettes']['default'] = $strPalette;
		$GLOBALS['TL_DCA'][$strTableName] = array_replace_recursive($arrDCA, (array)$GLOBALS['TL_DCA'][$objMetaModel->getTableName()]);

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