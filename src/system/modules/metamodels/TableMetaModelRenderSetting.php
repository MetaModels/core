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

class TableMetaModelRenderSetting extends TableMetaModelHelper
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
			self::$objInstance = new TableMetaModelRenderSetting();
		}
		return self::$objInstance;
	}

	/**
	 * Protected constructor for singleton instance.
	 */
	protected function __construct()
	{
		parent::__construct();

		// toggling of a render setting?
		if($this->Input->get('tid') && ($this->Input->get('table') == 'tl_metamodel_rendersetting'))
		{
			// Update database
			$this->Database->prepare('
				UPDATE tl_metamodel_rendersetting
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
		if ($strTableName != 'tl_metamodel_rendersetting')
		{
			return;
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
			$GLOBALS['TL_DCA']['tl_metamodel_rendersetting']['metapalettes'][$objAttribute->get('id') . ' extends ' . $strTypeName] = array();
		}
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
			$this->objSetting = $this->Database->prepare('SELECT * FROM tl_metamodel_rendersettings WHERE id=?')->execute($objDC->activeRecord->pid);
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
						$this->objSetting = $this->Database->prepare('
							SELECT tl_metamodel_rendersetting.*,
								tl_metamodel_rendersettings.pid AS tl_metamodel_rendersettings_pid,
								tl_metamodel_rendersettings.name AS name
							FROM tl_metamodel_rendersetting
							LEFT JOIN tl_metamodel_rendersettings
							ON (tl_metamodel_rendersetting.pid = tl_metamodel_rendersettings.id)
							WHERE (tl_metamodel_rendersetting.id=?)')
							->execute($this->Input->get('id'));
						$this->objMetaModel = MetaModelFactory::byId($this->objSetting->tl_metamodel_rendersettings_pid);
					}
					break;
				default:;
			}
		} else {
		}

		if ($this->objMetaModel)
		{
			$GLOBALS['TL_LANG']['MSC']['editRecord'] = sprintf(
				$GLOBALS['TL_LANG']['MSC']['metamodel_rendersetting']['editRecord'],
				$this->objSetting->name,
				$this->objMetaModel->getName()
			);

			$GLOBALS['TL_DCA']['tl_metamodel_rendersetting']['config']['label'] = 'Hello' . sprintf(
				$GLOBALS['TL_LANG']['MSC']['metamodel_rendersetting']['label'],
				$this->objSetting->name,
				$this->objMetaModel->getName()
			);
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

		foreach ($objMetaModel->getAttributes() as $objAttribute)
		{
			$strTypeName = $objAttribute->get('type');
			$arrResult[$objAttribute->get('id')] = $objAttribute->getName() . ' [' . $strTypeName . ']';
		}

		return $arrResult;
	}

	public function drawSetting($arrRow, $strLabel = '', DataContainer $objDC = null, $imageAttribute='', $blnReturnImage=false, $blnProtected=false)
	{
		$objSetting = $this->Database->prepare('SELECT * FROM tl_metamodel_rendersettings WHERE id=?')->execute($arrRow['pid']);
		$objMetaModel = MetaModelFactory::byId($objSetting->pid);

		$objAttribute = $objMetaModel->getAttributeById($arrRow['attr_id']);

		if (!$objAttribute)
		{
			return array();
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
			$GLOBALS['TL_LANG']['tl_metamodel_filtersetting']['row'],
			$strImage,
			$strLabel ? $strLabel : $objAttribute->get('type'),
			$objAttribute->get('type')
		);
		return $strReturn;
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
		return $this->getTemplatesForBase('mm_attr_' . $objAttribute->get('type'));
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
		$this->loadLanguageFile('tl_metamodel_rendersetting');

		$this->Template = new BackendTemplate('be_autocreateview');

		$this->Template->cacheMessage = '';
		$this->Template->updateMessage = '';

		$this->Template->href = $this->getReferer(true);
		$this->Template->headline = $GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['addall'][1];

		// severity: error, confirm, info, new
		$arrMessages = array();

		$objPalette = $this->Database->prepare('SELECT * FROM tl_metamodel_rendersettings WHERE id=?')->execute($this->Input->get('id'));

		$objMetaModel = MetaModelFactory::byId($objPalette->pid);

		$objAlreadyExist = $this->Database->prepare('SELECT * FROM tl_metamodel_rendersetting WHERE pid=?')->execute($this->Input->get('id'));

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
					$arrData = array_replace_recursive(
						(array)$objAttribute->getDefaultRenderSettings(),
						array(
							'pid'      => $this->Input->get('id'),
							'sorting'  => $intMax,
							'tstamp'   => time(),
							'attr_id'  => $objAttribute->get('id'),
						)
					);

					$intMax *= 128;
					$this->Database->prepare('INSERT INTO tl_metamodel_rendersetting %s')->set($arrData)->execute();
					$arrMessages[] = array
					(
						'severity' => 'confirm',
						'message'  => sprintf('added attribute %s to rendersetting.', $objAttribute->getName()),
					);
				}
			}
		} else {
			// loop over all attributes now.
			foreach ($objMetaModel->getAttributes() as $objAttribute)
			{
				if (array_key_exists($objAttribute->get('id'), $arrKnown))
				{
					$arrMessages[] = array
					(
						'severity' => 'info',
						'message'  => sprintf('Attribute %s already in rendersetting.', $objAttribute->getName()),
					);
				} else {
					$arrMessages[] = array
					(
						'severity' => 'confirm',
						'message'  => sprintf('will add attribute %s to rendersetting.', $objAttribute->getName()),
					);
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

