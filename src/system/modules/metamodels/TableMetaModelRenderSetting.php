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
 * This class is used from DCA tl_metamodel_rendersetting for various callbacks.
 *
 * @package	   MetaModels
 * @subpackage Backend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */

class TableMetaModelRenderSetting extends Backend
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
	}

	public function createDataContainer($strTableName)
	{
		if ($strTableName != 'tl_metamodel_rendersetting')
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
								tl_metamodel_rendersettings.pid AS tl_metamodel_rendersettings_pid
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
			$strImage = 'system/modules/metamodels/html/filter_default_icon.png';
		}

		$strImage = $this->generateImage($strImage, '', $imageAttribute);

		// Return the image only
		if ($blnReturnImage)
		{
			return $strImage;
		}

		$strLabel = $objAttribute->getName();

		$strReturn = sprintf(
			$GLOBALS['TL_LANG']['tl_metamodel_rendersetting']['typedesc']['_default_'],
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

		return $this->getTemplateGroup('mm_attr_' . $objAttribute->get('type') /*, theme id how the heck shall I fetch that one? */);
	}
}

?>