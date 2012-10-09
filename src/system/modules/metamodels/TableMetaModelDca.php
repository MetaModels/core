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
 * This class is used from DCA tl_metamodel_dca for various callbacks.
 *
 * @package	   MetaModels
 * @subpackage Backend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class TableMetaModelDca extends Backend
{
	/**
	 * Render a row for the list view in the backend.
	 *
	 * @param array         $arrRow   the current data row.
	 * @param string        $strLabel the label text.
	 * @param DataContainer $objDC    the DataContainer instance that called the method.
	 */
	public function drawSetting($arrRow, $strLabel = '', DataContainer $objDC = null, $imageAttribute='', $blnReturnImage=false, $blnProtected=false)
	{
		return $strLabel . ($arrRow['isdefault']? ' <span style="color:#b3b3b3; padding-left:3px">[' . $GLOBALS['TL_LANG']['MSC']['fallback'] . ']</span>' : '');
	}

	/**
	 * Check if the MM has a varsupport. If so disable some options in dca.
	 * User the oncreate_callback.
	 *
	 * @param String $strTable
	 * @return void
	 */
	public function checkSortMode($strTable)
	{
		// Get Current id
		$intID = $this->Input->get('id');

		if (empty($intID) || ($strTable != 'tl_metamodel_dca'))
		{
			return;
		}

		// Get current dataset
		$objResult = $this->Database
			->prepare("SELECT pid FROM tl_metamodel_dca WHERE id=?")
			->limit(1)
			->execute($intID);

		if ($objResult->numRows == 0)
		{
			return;
		}

		// Check if in list mode and if corresponding MM has varsupport.
		if (in_array($objResult->mode, array(3, 4, 5, 6)) || (($objMetaModel = MetaModelFactory::byId($objResult->pid)) && $objMetaModel->hasVariants()))
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
	}

	/**
	 * Fetch all attributes from the parenting MetaModel. Called as options_callback.
	 * User the oncreate_callback.
	 *
	 * @return array
	 */
	public function getAllAttributes()
	{
		$intID = $this->Input->get('id');
		$intPID = $this->Input->get('pid');

		$arrReturn = array();

		if (empty($intPID))
		{
			$objResult = $this->Database
				->prepare('SELECT pid FROM tl_metamodel_dca WHERE id=?')
				->limit(1)
				->execute($intID);

			if ($objResult->numRows == 0)
			{
				return $arrReturn;
			}
			$objMetaModel = MetaModelFactory::byId($objResult->pid);
		} else {
			$objMetaModel = MetaModelFactory::byId($intPID);
		}

		foreach ($objMetaModel->getAttributes() as $objAttribute)
		{
			$arrReturn[$objAttribute->getColName()] = $objAttribute->getName();
		}
		return $arrReturn;
	}

	/**
	 * Panel picker for convenient picking of panel layout strings.
	 * @return string
	 */
	public function getPanelpicker($objDC)
	{
		return sprintf(
				' <a href="system/modules/metamodels/popup.php?tbl=%s&fld=%s&inputName=ctrl_%s&id=%s&item=PALETTE_PANEL_PICKER" rel="lightbox[files 765 60%%]" data-lightbox="files 765 60%%">%s</a>',
				$objDC->table,
				$objDC->field,
				$objDC->inputName,
				$objDC->id,
				$this->generateImage('system/modules/metamodels/html/palette_wizard.png', $GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['panelpicker'], 'style="vertical-align:top;"')
		);
	}
}

?>