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
 * This class is used from DCA tl_metamodel_filtersetting for various callbacks.
 *
 * @package	   MetaModels
 * @subpackage Backend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */

class TableMetaModelFilterSetting extends Backend
{
	protected $objMetaModel = null;

	protected function objectsFromUrl($objDC)
	{
		if ($this->objMetaModel)
		{
			return;
		}

		if($objDC->activeRecord)
		{
			$objFilter = $this->Database->prepare('SELECT * FROM tl_metamodel_filter WHERE id=?')->execute($objDC->activeRecord->fid);
			$this->objMetaModel = MetaModelFactory::byId($objFilter->pid);
		}

		if ($this->Input->get('act'))
		{
			// act present, but we have an id
			switch ($this->Input->get('act'))
			{
				case 'edit': 
					break;
				default:;
			}
			
		} else {
			// no act but we have an id, list mode
			if ($this->Input->get('id'))
			{
				$objFilter = $this->Database->prepare('SELECT * FROM tl_metamodel_filter WHERE id=?')->execute($this->Input->get('id'));
				$this->objMetaModel = MetaModelFactory::byId($objFilter->pid);
			}
		}
//		var_dump($_GET, $objDC->activeRecord, $this->objMetaModel);
	}

	/*
	 * We are performing heavy typename to column name and vice versa calculations here to dynamically
	 * create the metapalettes for the type of the currently selected attribute.
	 * The resulting meta palettes are:
	 * 'mm_metamodeltablename_attributename extends attributetype' => array()
	 * where:
	 * mm_metamodeltablename_attributename = the tablename and the attribute name combined by an "_".
	 * attributetype                       = the attributes type name.
	 * 
	 * This way, attribute types can register their filter settings via
	 */

	public function attrIdToName($strValue, $objDC)
	{
		$this->objectsFromUrl($objDC);
		$objAttribute = $this->objMetaModel->getAttributeById($strValue);
		return $this->objMetaModel->getTableName() .'_' . $objAttribute->getColName();
	}

	public function nameToAttrId($strValue, $objDC)
	{
		$this->objectsFromUrl($objDC);

		$strName = str_replace($this->objMetaModel->getTableName() . '_', '', $strValue);
		return $this->objMetaModel->getAttribute($strName)->get('id');
	}

	public function attrIdToHumanName($strValue, $objDC)
	{
		$this->objectsFromUrl($objDC);
		return $this->objMetaModel->getAttributeById($strValue)->getName();
	}

	public function getAttributeNames($objDC)
	{
		$this->objectsFromUrl($objDC);
		$arrResult = array();
		if (!$this->objMetaModel)
		{
			return;
		}
		foreach ($this->objMetaModel->getAttributes() as $objAttribute)
		{
			$arrResult[$this->objMetaModel->getTableName() .'_' . $objAttribute->getColName()] = $objAttribute->getName();
		}
		return $arrResult;
	}

/*
	public function prepareMetaPalettes($strTableName)
	{
		if($strTableName != 'tl_metamodel_filtersetting')
		{
			return false;
		}

		$objMetaModel = null;
		if ($this->Input->get('pid'))
		{
			$objMetaModel = MetaModelFactory::byId($this->Input->get('pid'));
		}
		// TODO: detect all other ways we might end up here and fetch $objMetaModel accordingly.
		if (!$objMetaModel)
		{
			return;
		}

		foreach ($objMetaModel->getAttributes() as $objAttribute)
		{
			$strTypeName = $objAttribute->get('type');
			$strBase = 'default';
			foreach (array_keys($GLOBALS['TL_DCA']['tl_metamodel_filtersetting']['metapalettes']) as $strPaletteName)
			{
				if (strncmp($strPaletteName, $strTypeName, strlen($strTypeName)) == 0)
				{
					$strBase = $strTypeName;
				}
			}
			$strPalette = $objMetaModel->getTableName() .'_' . $objAttribute->getColName() . ' extends ' . $strBase;
			$GLOBALS['TL_DCA']['tl_metamodel_filtersetting']['metapalettes'][$strPalette] = array();
		}
	}
*/
	public function getSettingTypes()
	{
		return array_keys($GLOBALS['METAMODELS']['filters']);
	}

	/**
	 * when creating a new item, we need to populate the fid column.
	 */
	public function create_callback($strTable, $insertID, $set, $objDC)
	{
		$objResult = $this->Database->prepare('UPDATE tl_metamodel_filtersetting %s WHERE id=?')
		->set(array('fid' => $this->Input->get('id')))
		->execute($insertID);
	}

	public function drawOrCondition($arrRow, $strLabel, DataContainer $objDC = null, $imageAttribute='', $strImage)
	{
		$strReturn = sprintf(
		$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typedesc']['conditionor'],
		$strImage,
		$strLabel ? $strLabel : $arrRow['type'],
		$arrRow['type']
		);

		return $strReturn;
	}

	public function drawAndCondition($arrRow, $strLabel, DataContainer $objDC = null, $imageAttribute='', $strImage)
	{
		$strReturn = sprintf(
		$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typedesc']['conditionand'],
		$strImage,
		$strLabel ? $strLabel : $arrRow['type'],
		$arrRow['type']
		);
		return $strReturn;
	}

	public function drawSimpleLookup($arrRow, $strLabel, DataContainer $objDC = null, $imageAttribute='', $strImage)
	{
		$this->objectsFromUrl($objDC);

		$objAttribute = $this->objMetaModel->getAttributeById($arrRow['attr_id']);

		$strAttrName = $objAttribute->getName();
		$strAttrColName = $objAttribute->getColName();

		$strReturn = sprintf(
		$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typedesc']['simplelookup'],
		$strImage,
		$strLabel ? $strLabel : $arrRow['type'],
		$strAttrName,
		($arrRow['urlparam'] ? $arrRow['urlparam'] : $strAttrColName)
		);

		return $strReturn;
	}

	public function drawSetting($arrRow, $strLabel, DataContainer $objDC = null, $imageAttribute='', $blnReturnImage=false, $blnProtected=false)
	{
		$strImage = $GLOBALS['METAMODELS']['filters'][$arrRow['type']]['image'];

		if (!$strImage || !file_exists(TL_ROOT . '/' . $strImage))
		{
			$strImage = 'system/modules/metamodels/html/filter_default_icon.png';
		}

		// Return the image only
		if ($blnReturnImage)
		{
			return $this->generateImage($strImage, '', $imageAttribute);
		}

		$strImage = $this->generateImage($strImage, '', $imageAttribute);
		$strLabel = $GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typenames'][$arrRow['type']];

		// if a setting wants to render itself, let it do so.
		if ($GLOBALS['METAMODELS']['filters'][$arrRow['type']]['info_callback'])
		{
			$objCallback = new $GLOBALS['METAMODELS']['filters'][$arrRow['type']]['info_callback'][0];
			$strReturn = $objCallback->{$GLOBALS['METAMODELS']['filters'][$arrRow['type']]['info_callback'][1]}($arrRow, $strLabel, $objDC, $imageAttribute, $strImage);
		} else {
			$strReturn = sprintf(
			$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typedesc']['_default_'],
			$strImage,
			$strLabel ? $strLabel : $arrRow['type'],
			$arrRow['type']
			);
		}
		return $strReturn;
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
	public function pasteButton(DataContainer $objDC, $arrRow, $strTable, $cr, $arrClipboard=false)
	{
		$disablePA = false;
		$disablePI = false;

		// Disable all buttons if there is a circular reference
		if ($arrClipboard !== false && ($arrClipboard['mode'] == 'cut' && ($cr == 1 || $arrClipboard['id'] == $arrRow['id']) || $arrClipboard['mode'] == 'cutAll' && ($cr == 1 || in_array($arrRow['id'], $arrClipboard['id']))))
		{
			$disablePA = true;
			$disablePI = true;
		}

		// if setting does not support childs, omit them.
		if (!$GLOBALS['METAMODELS']['filters'][$arrRow['type']]['nestingAllowed'])
		{
			$disablePI = true;
		}

		// Return the buttons
		$imagePasteAfter = $this->generateImage('pasteafter.gif', sprintf($GLOBALS['TL_LANG'][$strTable]['pasteafter'][1], $arrRow['id']), 'class="blink"');
		$imagePasteInto = $this->generateImage('pasteinto.gif', sprintf($GLOBALS['TL_LANG'][$strTable]['pasteinto'][1], $arrRow['id']), 'class="blink"');

		if ($row['id'] > 0)
		{
			$return = $disablePA 
				? $this->generateImage('pasteafter_.gif', '', 'class="blink"').' '
				: '<a href="'.$this->addToUrl('act='.$arrClipboard['mode'].'&amp;mode=1&amp;pid='.$arrRow['id'].(!is_array($arrClipboard['id']) ? '&amp;id='.$arrClipboard['id'] : '')).'" title="'.specialchars(sprintf($GLOBALS['TL_LANG'][$strTable]['pasteafter'][1], $arrRow['id'])).'" onclick="Backend.getScrollOffset()">'.$imagePasteAfter.'</a> ';
		}

		return $return.($disablePI
			? $this->generateImage('pasteinto_.gif', '', 'class="blink"').' '
			: '<a href="'.$this->addToUrl('act='.$arrClipboard['mode'].'&amp;mode=2&amp;pid='.$arrRow['id'].(!is_array($arrClipboard['id']) ? '&amp;id='.$arrClipboard['id'] : '')).'" title="'.specialchars(sprintf($GLOBALS['TL_LANG'][$strTable]['pasteinto'][1], $arrRow['id'])).'" onclick="Backend.getScrollOffset()">'.$imagePasteInto.'</a> ');
	}
}

?>