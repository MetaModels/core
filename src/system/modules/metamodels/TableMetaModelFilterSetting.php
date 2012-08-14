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
	/**
	 * @var MetaPalettes
	 */
	protected static $objInstance = null;

	protected $objMetaModel = null;

	protected $strSettingType = null;

	protected $objFilter = null;

	/**
	 * Get the static instance.
	 *
	 * @static
	 * @return MetaPalettes
	 */
	public static function getInstance()
	{
		if (self::$objInstance == null) {
			self::$objInstance = new TableMetaModelFilterSetting();
		}
		return self::$objInstance;
	}

	/**
	 * Protected constructor for singleton instance.
	 */
	protected function __construct()
	{
		parent::__construct();

		// toggling of a filter setting?
		if($this->Input->get('tid'))
		{
			// Update database
			$this->Database->prepare('
				UPDATE tl_metamodel_filtersetting
				SET enabled=?
				WHERE id=?'
				)->execute(
					($this->Input->get('state')=='1'?'1':''),
					$this->Input->get('tid')
				);
			exit;
		}
	}

	public function createDataContainer($strTableName)
	{
		if ($strTableName != 'tl_metamodel_filtersetting')
		{
			return;
		}
		$this->objectsFromUrl(null);
	}

	protected function objectsFromUrl($objDC)
	{
		// TODO: detect all other ways we might end up here and fetch $objMetaModel accordingly.
		if ($this->objMetaModel)
		{
			return;
		}

		if($objDC && $objDC->activeRecord)
		{
			$this->strSettingType = $objDC->activeRecord->type;
			$this->objFilter = $this->Database->prepare('SELECT * FROM tl_metamodel_filter WHERE id=?')->execute($objDC->activeRecord->fid);
			$this->objMetaModel = MetaModelFactory::byId($this->objFilter->pid);
		}

		if ($this->Input->get('act'))
		{
			// act present, but we have an id
			switch ($this->Input->get('act'))
			{
				case 'edit':
					if ($this->Input->get('id'))
					{
						$strSettingType = $objDC->activeRecord->type;
						$this->objFilter = $this->Database->prepare('
							SELECT tl_metamodel_filter.*,
								tl_metamodel_filtersetting.type AS tl_metamodel_filtersetting_type,
								tl_metamodel_filtersetting.id AS tl_metamodel_filtersetting_id
							FROM tl_metamodel_filtersetting
							LEFT JOIN tl_metamodel_filter
							ON (tl_metamodel_filtersetting.fid = tl_metamodel_filter.id)
							WHERE (tl_metamodel_filtersetting.id=?)')
							->execute($this->Input->get('id'));
						$this->strSettingType = $this->objFilter->tl_metamodel_filtersetting_type;
						$this->objMetaModel = MetaModelFactory::byId($this->objFilter->pid);
					}
					break;
				case 'paste':
					if ($this->Input->get('id'))
					{
						switch ($this->Input->get('mode'))
						{
							case 'create':
								$this->objFilter = $this->Database->prepare('SELECT * FROM tl_metamodel_filter WHERE id=?')->execute($this->Input->get('id'));
								$this->objMetaModel = MetaModelFactory::byId($this->objFilter->pid);
							break;
							case 'cut':
								$this->objFilter = $this->Database->prepare('
									SELECT tl_metamodel_filter.*,
										tl_metamodel_filtersetting.type AS tl_metamodel_filtersetting_type,
										tl_metamodel_filtersetting.id AS tl_metamodel_filtersetting_id
									FROM tl_metamodel_filtersetting
									LEFT JOIN tl_metamodel_filter
									ON (tl_metamodel_filtersetting.fid = tl_metamodel_filter.id)
									WHERE (tl_metamodel_filtersetting.id=?)')
									->execute($this->Input->get('id'));
								$this->strSettingType = $this->objFilter->tl_metamodel_filtersetting_type;
								$this->objMetaModel = MetaModelFactory::byId($this->objFilter->pid);
							break;
						}
					}
					break;
				default:;
			}
		} else {
			// no act but we have an id, should be list mode then, no type name available.
			if ($this->Input->get('id'))
			{
				$this->objFilter = $this->Database->prepare('SELECT * FROM tl_metamodel_filter WHERE id=?')->execute($this->Input->get('id'));
				$this->objMetaModel = MetaModelFactory::byId($this->objFilter->pid);
			}
		}

		// select all root entries for the current filter.
		$GLOBALS['TL_DCA']['tl_metamodel_filtersetting']['list']['sorting']['root'] = 
			$this->Database->prepare('SELECT * FROM tl_metamodel_filtersetting WHERE fid=? AND pid=0')
			->execute($this->objFilter->id)
			->fetchEach('id');
		$GLOBALS['TL_DCA']['tl_metamodel_filtersetting']['list']['sorting']['rootPaste'] = true;

		$GLOBALS['TL_LANG']['MSC']['editRecord'] = sprintf(
			$GLOBALS['TL_LANG']['MSC']['metamodel_filtersetting']['editRecord'],
			$this->objFilter->name,
			$this->objMetaModel->getName()
		);

		$GLOBALS['TL_DCA']['tl_metamodel_filtersetting']['config']['label'] = sprintf(
			$GLOBALS['TL_LANG']['MSC']['metamodel_filtersetting']['label'],
			$this->objFilter->name,
			$this->objMetaModel->getName()
		);
	}

	/**
	 * translates an id to a generated alias {@see TableMetaModelFilterSetting::getAttributeNames()}
	 * 
	 * @param string        $strValue the id to translate.
	 * 
	 * @param DataContainer $objDC    the data container calling.
	 * 
	 * @return string
	 */
	public function attrIdToName($strValue, $objDC)
	{
		$this->objectsFromUrl($objDC);
		if (!($this->objMetaModel && $strValue))
		{
			return;
		}
		$objAttribute = $this->objMetaModel->getAttributeById($strValue);
		if ($objAttribute)
		{
			return $this->objMetaModel->getTableName() .'_' . $objAttribute->getColName();
		}
	}

	/**
	 * translates an generated alias {@see TableMetaModelFilterSetting::getAttributeNames()}
	 * to the corresponding attribute id.
	 * 
	 * @param string        $strValue the id to translate.
	 * 
	 * @param DataContainer $objDC    the data container calling.
	 * 
	 * @return int
	 */
	public function nameToAttrId($strValue, $objDC)
	{
		$this->objectsFromUrl($objDC);
		if (!$this->objMetaModel)
		{
			return;
		}
		$strName = str_replace($this->objMetaModel->getTableName() . '_', '', $strValue);
		return $this->objMetaModel->getAttribute($strName)->get('id');
	}

	/**
	 * Translates an attribute id to the human readable name defined.
	 * 
	 * @return string the human readable name.
	 */
	public function attrIdToHumanName($strValue, $objDC)
	{
		$this->objectsFromUrl($objDC);
		return $this->objMetaModel->getAttributeById($strValue)->getName();
	}

	/**
	 * Prepares a option list with alias => name connection for all attributes.
	 * This is used in the attr_id select box.
	 * 
	 * @param DataContainer $objDC the data container calling.
	 * 
	 * @return 
	 */
	public function getAttributeNames($objDC)
	{
		$this->objectsFromUrl($objDC);
		$arrResult = array();
		if (!$this->objMetaModel)
		{
			return;
		}
		$objMetaModel = $this->objMetaModel;

		foreach ($objMetaModel->getAttributes() as $objAttribute)
		{
			$strTypeName = $objAttribute->get('type');
			$strSelectVal = $objMetaModel->getTableName() .'_' . $objAttribute->getColName();
			$arrResult[$strSelectVal] = $objAttribute->getName() . ' [' . $strTypeName . ']';
		}
		return $arrResult;
	}

	/**
	 * Prepares the sub palettes for simple look up filter setting types.
	 * 
	 * @return void
	 */
	public function preparePalettes()
	{
		$this->objectsFromUrl(null);
		if (!($this->objMetaModel && $this->objFilter))
		{
			return;
		}
		$objMetaModel = $this->objMetaModel;

		foreach ($objMetaModel->getAttributes() as $objAttribute)
		{
			$strTypeName = $objAttribute->get('type');
			$strSelectVal = $objMetaModel->getTableName() .'_' . $objAttribute->getColName();
			if ($GLOBALS['TL_DCA']['tl_metamodel_filtersetting'][$this->strSettingType . '_palettes'][$strTypeName])
			{
				$GLOBALS['TL_DCA']['tl_metamodel_filtersetting']['metasubselectpalettes']['attr_id'][$strSelectVal] = $GLOBALS['TL_DCA']['tl_metamodel_filtersetting'][$this->strSettingType . '_palettes'][$strTypeName];
			}
		}
	}

	/**
	 * returns all registered filter setting types.
	 * 
	 * @return string[]
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

		if ($objAttribute)
		{
			$strAttrName = $objAttribute->getName();
			$strAttrColName = $objAttribute->getColName();
		} else {
			$strAttrName = $arrRow['attr_id'];
			$strAttrColName = $arrRow['attr_id'];
		}

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
		if ($arrRow['id'] && (!$GLOBALS['METAMODELS']['filters'][$arrRow['type']]['nestingAllowed']))
		{
			$disablePI = true;
		}

		// Return the buttons
		$imagePasteAfter = $this->generateImage('pasteafter.gif', sprintf($GLOBALS['TL_LANG'][$strTable]['pasteafter'][1], $arrRow['id']), 'class="blink"');
		$imagePasteInto = $this->generateImage('pasteinto.gif', sprintf($GLOBALS['TL_LANG'][$strTable]['pasteinto'][1], $arrRow['id']), 'class="blink"');

		if ($arrRow['id'] > 0)
		{
			$return = $disablePA 
				? $this->generateImage('pasteafter_.gif', '', 'class="blink"').' '
				: '<a href="'.$this->addToUrl('act='.$arrClipboard['mode'].'&amp;mode=1&amp;pid='.$arrRow['id'].(!is_array($arrClipboard['id']) ? '&amp;id='.$arrClipboard['id'] : '')).'" title="'.specialchars(sprintf($GLOBALS['TL_LANG'][$strTable]['pasteafter'][1], $arrRow['id'])).'" onclick="Backend.getScrollOffset()">'.$imagePasteAfter.'</a> ';
		}

		return $return.($disablePI
			? $this->generateImage('pasteinto_.gif', '', 'class="blink"').' '
			: '<a href="'.$this->addToUrl('act='.$arrClipboard['mode'].'&amp;mode=2&amp;pid='.$arrRow['id'].(!is_array($arrClipboard['id']) ? '&amp;id='.$arrClipboard['id'] : '')).'" title="'.specialchars(sprintf($GLOBALS['TL_LANG'][$strTable]['pasteinto'][1], $arrRow['id'])).'" onclick="Backend.getScrollOffset()">'.$imagePasteInto.'</a> ');
	}

	/**
	 * Return the "toggle visibility" button
	 * @param array
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @return string
	 */
	public function toggleIcon($row, $href, $label, $title, $icon, $attributes)
	{
		$href .= '&amp;tid='.$row['id'].'&amp;state='.($row['enabled'] ? '0' : '1');
		if (!$row['enabled'])
		{
			$icon = 'invisible.gif';
		}
		return '<a href="'.$this->addToUrl($href).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
	}

}

?>