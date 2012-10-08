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
class TableMetaModelRenderSettings extends Backend
{

	/**
	 * @var TableMetaModelRenderSetting
	 */
	protected static $objInstance = null;

	/**
	 * Get the static instance.
	 *
	 * @static
	 * @return MetaPalettes
	 */
	public static function getInstance()
	{
		if (self::$objInstance == null)
		{
			self::$objInstance = new TableMetaModelRenderSettings();
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

	public function drawSetting($arrRow, $strLabel = '', DataContainer $objDC = null, $imageAttribute = '', $blnReturnImage = false, $blnProtected = false)
	{
		return $strLabel . ($arrRow['isdefault'] ? ' <span style="color:#b3b3b3; padding-left:3px">[' . $GLOBALS['TL_LANG']['MSC']['fallback'] . ']</span>' : '');
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
		return $this->getTemplateGroup('metamodel_');
	}

	public function getPanelpicker($objDC)
	{
		return sprintf(
				' <a href="system/modules/metamodels/popup.php?tbl=%s&fld=%s&inputName=ctrl_%s&id=%s&item=PALETTE_PANEL_PICKER" rel="lightbox[files 765 60%%]" data-lightbox="files 765 60%%">%s</a>', $objDC->table, $objDC->field, $objDC->inputName, $objDC->id, $this->generateImage('system/modules/metamodels/html/palette_wizard.png', $GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['panelpicker'], 'style="vertical-align:top;"')
		);
	}

	/**
	 * Check if the MM has a varsupport. If so disable some options in dca.
	 * User the oncreate_callback.
	 * 
	 * @param String $strTable
	 * @return String
	 */
	public function checkSortMode($strTable)
	{
		// Get Current id
		$intID = $this->Input->get("id");

		if (empty($intID))
		{
			return null;
		}

		// Check current table
		if ($strTable != 'tl_metamodel_rendersettings')
		{
			return null;
		}

		// Get current dataset
		$objResult = $this->Database
			->prepare("SELECT pid FROM tl_metamodel_rendersettings WHERE id=?")
			->limit(1)
			->execute($intID);

		if ($objResult->numRows == 0)
		{
			return null;
		}

		// Get mm dataset
		$objResult = $this->Database
			->prepare('SELECT varsupport FROM tl_metamodel WHERE id=?')
			->limit(1)
			->execute($objResult->pid);

		if ($objResult->numRows == 0)
		{
			return null;
		}

		// Check varsupport
		if ($objResult->varsupport == 1 || in_array($objResult->mode, array(3, 4, 5, 6)))
		{
			// Unset fields
			unset($GLOBALS['TL_DCA'][$strTable]['fields']['mode']);
			unset($GLOBALS['TL_DCA'][$strTable]['fields']['flag']);
			unset($GLOBALS['TL_DCA'][$strTable]['fields']['panelLayout']);
			unset($GLOBALS['TL_DCA'][$strTable]['fields']['fields']);

			// Unset palettes
			$arrParts = trimsplit(";", $GLOBALS['TL_DCA'][$strTable]['palettes']['default']);
			foreach ($arrParts as $key => $value)
			{
				if (stripos($value, '{expert_legend}') !== false)
				{
					unset($arrParts[$key]);
					break;
				}
			}
			$GLOBALS['TL_DCA'][$strTable]['palettes']['default'] = implode(";", $arrParts);
		}

		return null;
	}

	/**
	 * Check if the MM has a varsupport. If so disable some options in dca.
	 * User the oncreate_callback.
	 * 
	 * @param String $strTable
	 * @return String
	 */
	public function getAllAttributes()
	{
		$intID = $this->Input->get('id');
		$intPID = $this->Input->get('pid');

		$objMetaModel = null;
		$objAttributes = null;
		$arrReturn = array();

		if (empty($intPID))
		{
			$objResult = $this->Database
				->prepare('SELECT pid FROM tl_metamodel_rendersettings WHERE id=?')
				->limit(1)
				->execute($intID);

			if ($objResult->numRows == 0)
			{
				return $arrReturn;
			}

			$objMetaModel = MetaModelFactory::byId($objResult->pid);

			$intPID = $objResult->pid;
		}

		$objAttributes = $this->Database
			->prepare('SELECT * FROM tl_metamodel_attribute WHERE pid=?')
			->execute($intPID);

		$objMetaModel = MetaModelFactory::byId($intPID);

		while ($objAttributes->next())
		{
			if (is_array(deserialize($objAttributes->name)))
			{
				$arrLanguage = deserialize($objAttributes->name);

				if (array_key_exists($GLOBALS['TL_LANGUAGE'], $arrLanguage))
				{
					$arrReturn[$objAttributes->colname] = $arrLanguage[$GLOBALS['TL_LANGUAGE']];
				}
				else if (array_key_exists($objMetaModel->getFallbackLanguage(), $arrLanguage))
				{
					$arrReturn[$objAttributes->colname] = $arrLanguage[$objMetaModel->getFallbackLanguage()];
				}
				else
				{
					$arrReturn[$objAttributes->colname] = $objAttributes->colname;
				}

				$arrReturn[$objAttributes->colname] .= ' [' . $objAttributes->colname . ']';				
			}
			else
			{
				$arrReturn[$objAttributes->colname] = $objAttributes->name . ' [' . $objAttributes->colname . ']';
			}
		}

		return $arrReturn;
	}

}

?>