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

	public function createDataContainer($strTableName)
	{
		if(!in_array($strTableName, MetaModelFactory::getAllTables()))
			return false;

		// call the loadDataContainer from Controller.php for the base DCA.
		parent::loadDataContainer('tl_metamodel_item');
		parent::loadLanguageFile('tl_metamodel_item');

		$arrDCA = $GLOBALS['TL_DCA']['tl_metamodel_item'];

		$objMetaModel=MetaModelFactory::byTableName($strTableName);

		if(TL_MODE == 'BE')
		{
			// add a direct link to edit the fields but for admins only
			$this->import('BackendUser', 'User');
			if($this->User->isAdmin)
			{
				$arrDCA['list']['global_operations']['fields'] = array
				(
					'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_item']['fields'],
					'href'                => 'do=metamodel&table=tl_metamodel_attribute&id=' . $objMetaModel->get('id'),
					'class'               => 'header_css_fields',
					'attributes'          => 'onclick="Backend.getScrollOffset();"'
				);
			}
		}

		$arrDCA['list']['sorting']['mode'] = $objMetaModel->get('mode');
		if($objMetaModel->get('ptable'))
		{
			$arrDCA['config']['ptable'] = $objMetaModel->get('ptable');
		}

		if($objMetaModel->hasVariants())
		{
			$arrDCA['list']['sorting']['filter'] = array(
				array('varbase=?', 1)
			);

			$arrDCA['fields']['varbase'] = array
			(
				'label'                   => &$GLOBALS['TL_LANG']['tl_metamodel_item']['varbase'],
				'inputType'               => 'checkbox',
				'eval'                    => array('submitOnChange'=>true)
			);

			$arrOperationCreateVariant = array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_metamodel_item']['createvariant'],
				'href'                => 'act=copy',
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
		}

		$strPalette='';
		foreach($objMetaModel->getAttributes() as $objAttribute)
		{
			$arrDCA = array_replace_recursive($arrDCA, $objAttribute->getItemDCA());

			if($objAttribute->insertBreak && strlen($objAttribute->legendTitle))
			{
				$legendName = $objAttribute->colName.'_legend';
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


// TODO: move to DC_MetaModel?
	/**
	 * Add the type of input field
	 * @param array
	 * @return string
	 */
	public function renderRow($arrRow)
	{
//		var_dump($arrRow);
		foreach ($arrRow as $key => $value) {
			
		}
		// TODO: use templating in here.
		return
'<div class="field_heading cte_type"><strong>' . $arrRow['colName'] . '</strong> <em>['.$arrRow['type'].']</em></div>
<div class="field_type block">
</div>';

	}

// TODO: move to DC_MetaModel?
	/**
	 * Add the type of input field
	 * @param array
	 * @return string
	 */
	public function labelCallback($arrRow, $strLabel, DataContainer $objDC, $strFolderAttribute = '', $blnUnknown = false, $blnProtected = false)
	{
		$strValues = '';
		$objMetaModel = MetaModelFactory::byTableName($objDC->table);
		foreach ($arrRow as $strKey => $strValue)
		{
			if(in_array($strKey, array('id', 'pid', 'sorting', 'tstamp', 'varbase', 'vargroup')))
			{
				$strValues .= sprintf('<div><em>%s:</em> %s</div>', $strKey, $strValue);
			}
		}
		foreach($objMetaModel->getAttributes() as $objAttribute)
		{
			$arrResult = $objAttribute->parseValue($arrRow);
			$strValues .= sprintf('<div><em>%s:</em> %s</div>', $objAttribute->getName(), $arrResult['html']);
		}

		// TODO: use templating in here.
		return '<div class="field_type block">'.$strValues.'</div>';

	}

}

?>