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
 * This class is used from DCA tl_metamodel_rendersetting for various callbacks.
 *
 * @package	   MetaModels
 * @subpackage Backend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */

class TableMetaModelDcaSetting extends TableMetaModelHelper
{
	/**
	 * @var TableMetaModelRenderSetting
	 */
	protected static $objInstance = null;

	protected $objMetaModel = null;

	protected $objSetting = null;

	/**
	 * Get the static instance.
	 *
	 * @static
	 * @return MetaPalettes
	 */
	public static function getInstance()
	{
		if (self::$objInstance == null) {
			self::$objInstance = new TableMetaModelDcaSetting();
		}
		return self::$objInstance;
	}

	/**
	 * Protected constructor for singleton instance.
	 */
	protected function __construct()
	{
		parent::__construct();
	}

	public function createDataContainer($strTableName)
	{
		if ($strTableName != 'tl_metamodel_dcasetting')
		{
			return;
		}

		if ($this->Input->get('subpaletteid'))
		{
			$GLOBALS['TL_DCA']['tl_metamodel_dcasetting']['dca_config']['childCondition'][0]['setOn'][] = array
			(
				'to_field'    => 'subpalette',
				'value'       => $this->Input->get('subpaletteid')
			);
			$GLOBALS['TL_DCA']['tl_metamodel_dcasetting']['dca_config']['childCondition'][0]['filter'][] = array
			(
				'local'        => 'subpalette',
				'remote_value' => $this->Input->get('subpaletteid'),
				'operation'   => '=',
			);
		} else {
			$GLOBALS['TL_DCA']['tl_metamodel_dcasetting']['dca_config']['childCondition'][0]['filter'][] = array
			(
				'local'        => 'subpalette',
				'remote_value' => 0,
				'operation'   => '=',
			);
		}

		$this->objectsFromUrl(null);

		if (!$this->objMetaModel)
		{
			return;
		}

		$objMetaModel=$this->objMetaModel;
		foreach ($objMetaModel->getAttributes() as $objAttribute)
		{
			$strColName = sprintf('%s_%s', $objAttribute->getColName(), $objAttribute->get('id'));
			$strTypeName = $objAttribute->get('type');
			// GOTCHA: watch out to never ever use anyting numeric in the palettes anywhere else.
			// We have the problem, that DC_Table does not call the load callback when determining the palette.
			// FIXME: implement this using $type_$id as key and implement the proper save and load callback.
			$GLOBALS['TL_DCA']['tl_metamodel_dcasetting']['metasubselectpalettes']['attr_id'][$objAttribute->get('id')] = &$GLOBALS['TL_DCA']['tl_metamodel_dcasetting']['metasubselectpalettes']['attr_id'][$strTypeName];
		}
	}

	protected function getMetaModelFromDC($objDC)
	{
		// check for predefined values.
		$objDB = Database::getInstance();
		// fetch current values of the field from DB.
		$objField = $objDB->prepare('
			SELECT pid
			FROM tl_metamodel_dca
			WHERE id=?'
		)
		->limit(1)
		->executeUncached($objDC->getCurrentModel()->getProperty('pid'));
		return MetaModelFactory::byId($objField->pid);
	}

	public function decodeLegendTitle($varValue, $objDC)
	{
		return parent::decodeLangArray($varValue, $this->getMetaModelFromDC($objDC));
	}

	public function encodeLegendTitle($varValue, $objDC)
	{
		return parent::encodeLangArray($varValue, $this->getMetaModelFromDC($objDC));
	}

	/**
	 * Retrieve the current values of the model and create the title widget information.
	 *
	 * @param InterfaceGeneralModel $objModel the current Model active in the DC.
	 *
	 * @param DC_General            $objDC    the Datacontainer calling us.
	 */
	public function onModelUpdatedCallback($objModel, $objDC)
	{
		// do nothing if not in edit mode.
		if(!(($this->Input->get('act') == 'create') || ($this->Input->get('act') == 'edit')))
		{
			return;
		}
		$this->objectsFromUrl($objDC);
		$GLOBALS['TL_DCA']['tl_metamodel_dcasetting']['fields']['legendtitle'] = array_replace_recursive(
			parent::makeMultiColumnName(
				$this->objMetaModel,
				$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['name_langcode'],
				$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['name_value']
				),
			$GLOBALS['TL_DCA']['tl_metamodel_dcasetting']['fields']['legendtitle']
		);
	}

	protected function objectsFromUrl($objDC)
	{
		// TODO: detect all other ways we might end up here and fetch $objMetaModel accordingly.
		if ($this->objMetaModel)
		{
			return;
		}
		if($objDC && $objDC->getCurrentModel())
		{
			$this->objSetting = $this->Database->prepare('SELECT * FROM tl_metamodel_dca WHERE id=?')->execute($objDC->getCurrentModel()->getProperty('pid'));
			$this->objMetaModel = MetaModelFactory::byId($this->objSetting->pid);
		}

		if ($this->Input->get('act'))
		{
			// act present, but we have an id
			switch ($this->Input->get('act'))
			{
				case 'edit':
				if ($this->Input->get('id'))
				{
					$this->objSetting = $this->Database->prepare('
						SELECT tl_metamodel_dcasetting.*,
								tl_metamodel_dca.pid AS tl_metamodel_dca_pid
						FROM tl_metamodel_dcasetting
						LEFT JOIN tl_metamodel_dca
						ON (tl_metamodel_dcasetting.pid = tl_metamodel_dca.id)
						WHERE (tl_metamodel_dcasetting.id=?)')
						->execute($this->Input->get('id'));
						$this->objMetaModel = MetaModelFactory::byId($this->objSetting->tl_metamodel_dca_pid);
					}
					break;
				default:;
			}
		} else {
		}
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
		$objSettings = $this->Database->prepare('SELECT attr_id FROM tl_metamodel_dcasetting WHERE pid=? AND dcatype="attribute" AND ((subpalette=0) OR (subpalette=?))')
			->execute($objDC->getCurrentModel()->getProperty('pid'), $objDC->getCurrentModel()->getProperty('subpalette'));

		$arrAlreadyTaken = $objSettings->fetchEach('attr_id');

		foreach ($objMetaModel->getAttributes() as $objAttribute)
		{
			if ((!($objAttribute->get('id') == $objDC->getCurrentModel()->getProperty('attr_id')))
			&& in_array($objAttribute->get('id'), $arrAlreadyTaken))
			{
				continue;
			}
			$strTypeName = $objAttribute->get('type');
			$arrResult[$objAttribute->get('id')] = $objAttribute->getName() . ' [' . $strTypeName . ']';
		}

		return $arrResult;
	}

	public function getRichTextEditors()
	{
		$configs=array();
		foreach(glob(TL_ROOT . '/system/config/tiny*.php') as $name)
		{
			$name = basename($name);
			if((strpos($name, 'tiny')===0) && (substr($name, -4, 4)=='.php'))
				$configs[]=substr($name, 0, -4);
		}
		return $configs;
	}

	public function drawSetting($arrRow, $strLabel = '', DataContainer $objDC = null, $imageAttribute='', $blnReturnImage=false, $blnProtected=false)
	{
		switch ($arrRow['dcatype'])
		{
			case 'attribute':
				$objSetting = $this->Database->prepare('SELECT * FROM tl_metamodel_dca WHERE id=?')->execute($arrRow['pid']);
				$objMetaModel = MetaModelFactory::byId($objSetting->pid);

				if (!$objMetaModel)
				{
					return '';
				}

				$objAttribute = $objMetaModel->getAttributeById($arrRow['attr_id']);

				if (!$objAttribute)
				{
					return '';
				}

				$strImage = $GLOBALS['METAMODELS']['attributes'][$objAttribute->get('type')]['image'];

				if (!$strImage || !file_exists(TL_ROOT . '/' . $strImage))
				{
					$strImage = 'system/modules/metamodels/html/filter_default.png';
				}

				$strImage = $this->generateImage($strImage, '', $imageAttribute);

				// Return the image only
				if ($blnReturnImage)
				{
					return $strImage;
				}

				$strLabel = $objAttribute->getName();
				$strReturn = sprintf(
					$GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['row'],
					$strImage,
					$strLabel ? $strLabel : $objAttribute->get('type'),
					$arrRow['mandatory'] ? '*' : '',
					$objAttribute->get('type'),
					$arrRow['tl_class'] ? sprintf('[%s]', $arrRow['tl_class']) : ''
				);
				return $strReturn;
			break;

			case 'legend':
                $arrLegend = deserialize($arrRow['legendtitle']);
                if(is_array($arrLegend))
                {
                    $strLegend = $arrLegend[$GLOBALS['TL_LANGUAGE']];

                    if(!$strLegend)
                    {
                        // TODO: Get the fallback language here
                        $strLegend = 'legend';
                    }
                } else {
                    $strLegend = $arrRow['legendtitle'] ? $arrRow['legendtitle'] : 'legend';
                }

                $strReturn = sprintf(
                    $GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['legend_row'],
                    $strLegend, $arrRow['legendhide'] ? ':hide' : ''
                );

				return $strReturn;
			break;

			default:
			break;
		}
	}

	/**
	 * Fetch the template group for the detail view of the current MetaModel module.
	 *
	 * @param DataContainer $objDC the datacontainer calling this method.
	 *
	 * @return array
	 *
	 */
	public function getTemplates(DataContainer $objDC)
	{
		if (!($this->objMetaModel))
		{
			return array();
		}

		$objAttribute = $this->objMetaModel->getAttributeById($objDC->activeRecord->attr_id);

		if (!$objAttribute)
		{
			return array();
		}

		return $this->getTemplateGroup('mm_attr_' . $objAttribute->get('type') /*, theme id how the heck shall I fetch that one? */);
	}

	/**
	 * Fetch the template group for the detail view of the current MetaModel module.
	 *
	 * @param DataContainer $objDC the datacontainer calling this method.
	 *
	 * @return array
	 *
	 */
	/**
	 * Generate module
	 */
	public function addAll()
	{
		$this->loadLanguageFile('default');
		$this->loadLanguageFile('tl_metamodel_dcasetting');

		$this->Template = new BackendTemplate('be_autocreatepalette');

		$this->Template->cacheMessage = '';
		$this->Template->updateMessage = '';

		$this->Template->href = $this->getReferer(true);
		$this->Template->headline = $GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['addall'][1];

		// severity: error, confirm, info, new
		$arrMessages = array();

		$objPalette = $this->Database->prepare('SELECT * FROM tl_metamodel_dca WHERE id=?')->execute($this->Input->get('id'));

		$objMetaModel = MetaModelFactory::byId($objPalette->pid);

		$objAlreadyExist = $this->Database->prepare('SELECT * FROM tl_metamodel_dcasetting WHERE pid=? AND dcatype=?')->execute($this->Input->get('id'), 'attribute');

		$arrKnown = array();
		$intMax = 128;
		while ($objAlreadyExist->next())
		{
			$arrKnown[$objAlreadyExist->attr_id] = $objAlreadyExist->row();
			if ($intMax< $objAlreadyExist->sorting)
			{
				$intMax = $objAlreadyExist->sorting;
			}
		}

		$blnWantPerform = false;
		// perform the labour work
		if ($this->Input->post('act') == 'perform')
		{
			// loop over all attributes now.
			foreach ($objMetaModel->getAttributes() as $objAttribute)
			{
				if (!array_key_exists($objAttribute->get('id'), $arrKnown))
				{
					$intMax *= 128;
					$this->Database->prepare('INSERT INTO tl_metamodel_dcasetting %s')->set(array(
						'pid'      => $this->Input->get('id'),
						'sorting'  => $intMax,
						'tstamp'   => time(),
						'dcatype'  => 'attribute',
						'attr_id'  => $objAttribute->get('id'),
						'tl_class' => ''
					))->execute();
					$arrMessages[sprintf($GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['addAll_addsuccess'], $objAttribute->getName())] = 'confirm';
				}
			}
		} else {
			// loop over all attributes now.
			foreach ($objMetaModel->getAttributes() as $objAttribute)
			{
				if (array_key_exists($objAttribute->get('id'), $arrKnown))
				{
					$arrMessages[sprintf($GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['addAll_alreadycontained'], $objAttribute->getName())] = 'info';
				} else {
					$arrMessages[sprintf($GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['addAll_willadd'], $objAttribute->getName())] = 'confirm';
					$blnWantPerform = true;
				}
			}
		}

		if ($blnWantPerform)
		{
			$this->Template->action = ampersand($this->Environment->request);
			$this->Template->submit = $GLOBALS['TL_LANG']['MSC']['continue'];
		} else {
			$this->Template->action = ampersand($this->getReferer(true));
			$this->Template->submit = $GLOBALS['TL_LANG']['MSC']['saveNclose'];
		}

		$this->Template->error = $arrMessages;

		return $this->Template->parse();
	}

	public function getStylepicker($objDC)
	{
		return sprintf(
			' <a href="system/modules/metamodels/popup.php?tbl=%s&fld=%s&inputName=ctrl_%s&id=%s&item=PALETTE_STYLE_PICKER" rel="lightbox[files 765 60%%]" data-lightbox="files 765 60%%">%s</a>',
			$objDC->table,
			$objDC->field,
			$objDC->inputName,
			$objDC->id,
			$this->generateImage('system/modules/metamodels/html/dca_wizard.png', $GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['stylepicker'], 'style="vertical-align:top;"')
		);
	}

	public function subpaletteButton($row, $href, $label, $title, $icon, $attributes)
	{
		// Check if we have a attribute
		if($row['dcatype'] != 'attribute' || strlen($this->Input->get('subpaletteid')) != 0)
		{
			return '';
		}		
		
		// Get MM and check if we have a valide one.
		$intId = $this->Database
			->prepare('SELECT pid FROM tl_metamodel_dca WHERE id=?')
			->execute($row['pid'])
			->pid;
		$objMetaModel = MetaModelFactory::byId($intId);
		if(is_null($objMetaModel))
		{
			return '';
		}

		// Get attribute and check if we have a valide one.
		$objAttribute = $objMetaModel->getAttributeById($row['attr_id']);

		if(is_null($objAttribute))
		{
			return '';
		}

		// TODO: add some attribute::supports method to add only for attributes that indeed support subpaletting.
		// For the moment we add a dirty check, only for checkboxes.
		if (in_array($objAttribute->get('type'), array('checkbox')))
		{
			return '<a href="'.$this->addToUrl($href.'&amp;id='. $row['pid'] . '&amp;subpaletteid='.$row['id']).'" title="'.specialchars($title).'"'.$attributes.'>'.$this->generateImage($icon, $label).'</a> ';
		}

		return '';
	}
}

