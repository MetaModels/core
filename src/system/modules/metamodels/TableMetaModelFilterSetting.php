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
 * This class is used from DCA tl_metamodel_filtersetting for various callbacks.
 *
 * @package	   MetaModels
 * @subpackage Backend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */

class TableMetaModelFilterSetting extends TableMetaModelHelper
{
	/**
	 * @var MetaPalettes
	 */
	protected static $objInstance = null;

	/**
	 * The MetaModel instance relevant to the current item in view.
	 *
	 * @var IMetaModel
	 */
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
		if($this->Input->get('tid') && ($this->Input->get('table') == 'tl_metamodel_filtersetting'))
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
		$this->objectsFromUrl($strTableName);
	}

	protected function objectsFromUrl($objDC)
	{
		if (!(($this->Input->get('do') == 'metamodels')
		&& ((is_object($objDC) && $objDC->table != 'tl_metamodel_filtersetting') || ($objDC == 'tl_metamodel_filtersetting'))))
		{
			return;
		}

		// TODO: detect all other ways we might end up here and fetch $objMetaModel accordingly.
		if ($this->objMetaModel)
		{
			return;
		}

		if(is_object($objDC) && $objDC->getCurrentModel())
		{
			$this->strSettingType = $objDC->getCurrentModel()->getProperty('type');

			$this->objFilter = $this->Database
				->prepare('SELECT * FROM tl_metamodel_filter WHERE id=?')
				->execute($objDC->getCurrentModel()->getProperty('fid'));

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
								$this->objFilter = $this->Database
									->prepare('SELECT * FROM tl_metamodel_filter WHERE id=?')
									->execute($this->Input->get('id'));

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
									->execute($this->Input->get('source'));
								$this->strSettingType = $this->objFilter->tl_metamodel_filtersetting_type;
								$this->objMetaModel = MetaModelFactory::byId($this->objFilter->pid);
							break;
						}
					}
					break;
				case 'create':
					$this->objFilter = $this->Database
						->prepare('SELECT * FROM tl_metamodel_filter WHERE id=?')
						->execute($this->Input->get('id'));

					$this->objMetaModel = MetaModelFactory::byId($this->objFilter->pid);
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

		if ($this->objMetaModel)
		{
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
	}

	/**
	 * Retrieve the MetaModel instance relevant for the current filter setting in view.
	 *
	 * @param DC_General $objDC The data container.
	 *
	 * @return IMetaModel
	 */
	public function getMetaModel($objDC)
	{
		$this->objectsFromUrl($objDC);
		return $this->objMetaModel;
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
			return '';
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
			return 0;
		}
		$strName = str_replace($this->objMetaModel->getTableName() . '_', '', $strValue);

		$objAttribute = $this->objMetaModel->getAttribute($strName);
		if (!$objAttribute)
		{
			return 0;
		}

		return $objAttribute->get('id');
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
	 * @param DC_General $objDC the data container calling.
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

		$arrTypeFilter = $GLOBALS['METAMODELS']['filters'][$objDC->getCurrentModel()->getProperty('type')]['attr_filter'];
		foreach ($objMetaModel->getAttributes() as $objAttribute)
		{
			$strTypeName = $objAttribute->get('type');
			if ($arrTypeFilter && (!in_array($strTypeName, $arrTypeFilter)))
			{
				continue;
			}
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
	 * get frontend templates for filter elements
	 * @param DataContainer
	 * @return array
	 */
	public function getSubTemplates(DataContainer $dc)
	{
		return $this->getTemplatesForBase('mm_filteritem_');
	}

	/**
	 * Set the parent condition for the current fid.
	 *
	 * @param string     $strTable The tablename - must be tl_metamodel_filtersetting.
	 *
	 * @param DC_General $objDC    The DataContainer calling us.
	 *
	 * @return string The value "tl_metamodel_filtersetting".
	 */
	public function loadTableCallback($strTable, $objDC)
	{
		if ($strTable == 'tl_metamodel_filtersetting')
		{
			$GLOBALS['TL_DCA']['tl_metamodel_filtersetting']['dca_config']['childCondition'][0]['filter'][] = array
			(
				'local'        => 'fid',
				'remote_value' => $this->Input->get('id'),
				'operation'    => '=',
			);

			$GLOBALS['TL_DCA']['tl_metamodel_filtersetting']['dca_config']['rootEntries']['self']['setOn'][] = array
			(
				'property'    => 'fid',
				'value'       => $this->Input->get('id'),
			);

			$GLOBALS['TL_DCA']['tl_metamodel_filtersetting']['dca_config']['rootEntries']['self']['filter'][] = array
			(
				'property'    => 'fid',
				'operation'   => '=',
				'value'       => $this->Input->get('id'),
			);
		}

		return $strTable;
	}

	/**
	 * when creating a new item, we need to populate the fid column.
	 */
	public function create_callback($strTable, $insertID, $arrRow, $objDC)
	{
		// If we come from overview use pid
		if($this->Input->get('id') != "")
		{
			$intFid = $this->Input->get('id');
		}
		// If we use the "save and new" btt use the pid instead
		elseif($this->Input->get('pid') != "")
		{
			// Get fid from pid
			$arrFid = $this->Database
			->prepare('SELECT fid FROM tl_metamodel_filtersetting WHERE id=?')
			->execute($this->Input->get('pid'))
			->fetchEach('fid');

			// Check if we have a pid
			if(count($arrFid) == 0)
			{
				throw new Exception("Could not find FID. Please create a new entry from main overview.");
			}

			// Set fid by pid`s fid
			$intFid = $arrFid[0];
		}

		$objResult = $this->Database->prepare('UPDATE tl_metamodel_filtersetting %s WHERE id=?')
		->set(array('fid' => $intFid))
		->execute($insertID);
	}

	/**
	 * provide options for default selection
	 *
	 * @param DC_General $objDC The data container.
	 *
	 * @return array
	 */
	public function getSelectDefault($objDC)
	{
		$objMetaModel = $this->getMetaModel($objDC);

		if(!$objMetaModel)
		{
			return array();
		}

		$objAttribute = $objMetaModel->getAttributeById($objDC->getCurrentModel()->getProperty('attr_id'));
		if(!$objAttribute)
		{
			return array();
		}

		$blnOnlyUsed = $objDC->getCurrentModel()->getProperty('onlyused') ? true : false;

		$arrCount = array();
		$arrOptions = $objAttribute->getFilterOptions(null, $blnOnlyUsed, $arrCount);

		// Remove empty values.
		foreach ($arrOptions as $mixOptionKey => $mixOptionValue)
		{
			// Remove html/php tags.
			$mixOptionValue = strip_tags($mixOptionValue);
			$mixOptionValue = trim($mixOptionValue);

			if(($mixOptionValue === '') || ($mixOptionValue === null) || ($blnOnlyUsed && ($arrCount[$mixOptionKey] === 0)))
			{
				unset($arrOptions[$mixOptionKey]);
			}
		}

		return $arrOptions;
	}

	public function drawOrCondition($arrRow, $strLabel, DataContainer $objDC = null, $imageAttribute='', $strImage)
	{
		if (!empty($arrRow['comment']))
		{
			$arrRow['comment'] = sprintf($GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typedesc']['_comment_'], specialchars($arrRow['comment']));
		}
		
		$strReturn = sprintf(
		$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typedesc']['conditionor'],
		'<a href="' . $this->addToUrl('act=edit&amp;id='.$arrRow['id']). '">' . $strImage . '</a>',
		$strLabel ? $strLabel : $arrRow['type'],
		$arrRow['comment'],
		$arrRow['type']
		);

		return $strReturn;
	}

	public function drawAndCondition($arrRow, $strLabel, DataContainer $objDC = null, $imageAttribute='', $strImage)
	{
		if (!empty($arrRow['comment']))
		{
			$arrRow['comment'] = sprintf($GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typedesc']['_comment_'], specialchars($arrRow['comment']));
		}
		
		$strReturn = sprintf(
		$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typedesc']['conditionand'],
		'<a href="' . $this->addToUrl('act=edit&amp;id='.$arrRow['id']). '">' . $strImage . '</a>',
		$strLabel ? $strLabel : $arrRow['type'],
		$arrRow['comment'],
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
		
		if (!empty($arrRow['comment']))
		{
			$arrRow['comment'] = sprintf($GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typedesc']['_comment_'], specialchars($arrRow['comment']));
		}

		$strReturn = sprintf(
		$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typedesc']['simplelookup'],
		'<a href="' . $this->addToUrl('act=edit&amp;id='.$arrRow['id']). '">' . $strImage . '</a>',
		$strLabel ? $strLabel : $arrRow['type'],
		$arrRow['comment'],
		$strAttrName,
		($arrRow['urlparam'] ? $arrRow['urlparam'] : $strAttrColName)
		);

		return $strReturn;
	}

	/**
	 * backend list display of fe-filter
	 * @param array
	 * @param string
	 * @param object
	 * @param string
	 * @param string
	 * @return string
	 */
	public function infoCallback($arrRow, $strLabel, $objDC, $imageAttribute, $strImage)
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
		
		if (!empty($arrRow['comment']))
		{
			$arrRow['comment'] = sprintf($GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typedesc']['_comment_'], specialchars($arrRow['comment']));
		}

		$strReturn = sprintf(
			$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typedesc']['fefilter'],
			'<a href="' . $this->addToUrl('act=edit&amp;id='.$arrRow['id']). '">' . $strImage . '</a>',
			$strLabel,
			$arrRow['comment'],
			$strAttrName,
			$arrRow['urlparam'] ? $arrRow['urlparam'] : $strAttrColName
		);

		return $strReturn;
	}

	protected $objCallback = null;

	public function drawSetting($arrRow, $strLabel, DataContainer $objDC = null, $imageAttribute='', $blnReturnImage=false, $blnProtected=false)
	{
		$strImage = $GLOBALS['METAMODELS']['filters'][$arrRow['type']]['image'];

		if (!$strImage || !file_exists(TL_ROOT . '/' . $strImage))
		{
			$strImage = 'system/modules/metamodels/html/filter_default.png';
		}

		if (!$arrRow['enabled'])
		{
			$intPos=strrpos($strImage, '.');
			if ($intPos !== false)
			{
				$strImage = substr_replace($strImage, '_1', $intPos, 0);
			}
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
			$this->import($GLOBALS['METAMODELS']['filters'][$arrRow['type']]['info_callback'][0], 'objCallback');
			$strReturn = $this->objCallback->{$GLOBALS['METAMODELS']['filters'][$arrRow['type']]['info_callback'][1]}(
				$arrRow,
				$strLabel,
				$objDC,
				$imageAttribute,
				$strImage
			);
			$this->objCallback = null;
		} else {
			if(!empty($arrRow['comment']))
			{
				$arrRow['comment'] = sprintf($GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typedesc']['_comment_'], specialchars($arrRow['comment']));
			}
			
			$strReturn = sprintf(
			$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['typedesc']['_default_'],
			'<a href="' . $this->addToUrl('act=edit&amp;id='.$arrRow['id']). '">' . $strImage . '</a>',
			$strLabel ? $strLabel : $arrRow['type'],
			$arrRow['comment'],
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

		if ($arrRow['id'] > 0)
		{
			if ($disablePA)
			{
				$return = $this->generateImage('pasteafter_.gif', '', 'class="blink"').' ';
			} else {
				$imagePasteAfter = $this->generateImage('pasteafter.gif', sprintf($GLOBALS['TL_LANG'][$strTable]['pasteafter'][1], $arrRow['id']), 'class="blink"');

				$strAdd2UrlAfter = sprintf(
					'act=%s&amp;mode=1&amp;pid=%s&amp;after=%s&amp;source=%s&amp;childs=%s',
					$arrClipboard['mode'],
					$arrClipboard['id'],
					$arrRow['id'],
					$arrClipboard['source'],
					$arrClipboard['childs']
				);

				if ($arrClipboard['pdp'] != '')
				{
					$strAdd2UrlAfter .= '&amp;pdp=' . $arrClipboard['pdp'];
				}

				if ($arrClipboard['cdp'] != '')
				{
					$strAdd2UrlAfter .= '&amp;cdp=' . $arrClipboard['cdp'];
				}

				$return = sprintf(
					' <a href="%s" title="%s" onclick="Backend.getScrollOffset()">%s</a> ',
					$this->addToUrl($strAdd2UrlAfter),
					specialchars($GLOBALS['TL_LANG'][$strTable]['pasteafter'][0]),
					$imagePasteAfter
				);
			}

			if ($disablePI)
			{
				$return .= $this->generateImage('pasteinto_.gif', '', 'class="blink"').' ';
			} else {
				$imagePasteInto = $this->generateImage('pasteinto.gif', sprintf($GLOBALS['TL_LANG'][$strTable]['pasteinto'][1], $arrRow['id']), 'class="blink"');

				$strAdd2UrlInto = sprintf(
					'act=%s&amp;mode=2&amp;pid=%s&amp;after=%s&amp;source=%s&amp;childs=%s',
					$arrClipboard['mode'],
					$arrClipboard['id'],
					$arrRow['id'],
					$arrClipboard['source'],
					$arrClipboard['childs']
				);

				if ($arrClipboard['pdp'] != '')
				{
					$strAdd2UrlInto .= '&amp;pdp=' . $arrClipboard['pdp'];
				}

				if ($arrClipboard['cdp'] != '')
				{
					$strAdd2UrlInto .= '&amp;cdp=' . $arrClipboard['cdp'];
				}

				$return .= sprintf(
					' <a href="%s" title="%s" onclick="Backend.getScrollOffset()">%s</a> ',
					$this->addToUrl($strAdd2UrlInto),
					specialchars($GLOBALS['TL_LANG'][$strTable]['pasteinto'][0]),
					$imagePasteInto
				);
			}
		} else {
			$imagePasteInto = $this->generateImage('pasteinto.gif', sprintf($GLOBALS['TL_LANG'][$strTable]['pasteinto'][1], $arrRow['id']), 'class="blink"');

			$strAdd2UrlInto = sprintf(
				'act=%s&amp;mode=2&amp;after=0&amp;pid=0&amp;id=%s&amp;source=%s&amp;childs=%s',
				$arrClipboard['mode'],
				$arrClipboard['id'],
				$arrClipboard['source'],
				$arrClipboard['childs']
			);

			if ($arrClipboard['pdp'] != '')
			{
				$strAdd2UrlInto .= '&amp;pdp=' . $arrClipboard['pdp'];
			}

			if ($arrClipboard['cdp'] != '')
			{
				$strAdd2UrlInto .= '&amp;cdp=' . $arrClipboard['cdp'];
			}

			$return .= sprintf(
				' <a href="%s" title="%s" onclick="Backend.getScrollOffset()">%s</a> ',
				$this->addToUrl($strAdd2UrlInto),
				specialchars($GLOBALS['TL_LANG'][$strTable]['pasteinto'][0]),
				$imagePasteInto
			);
		}

		return $return;
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

