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
 * This class is used from DCA tl_metamodel_dca for various callbacks.
 *
 * @package	   MetaModels
 * @subpackage Backend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class TableMetaModelDca extends Backend
{
	/**
	 * Return the MetaModel currently in scope of the given DataContainer.
	 *
	 * @param DataContainer $objDC The DataContainer instance.
	 *
	 * @return IMetaModel The MetaModel instance.
	 */
	protected function getMetaModel(DataContainer $objDC)
	{
		if ($this->Input->get('act') == 'create' && $this->Input->get('pid'))
		{
			return MetaModelFactory::byId($this->Input->get('pid'));
		}
		// Get Current id.
		$intID = $this->Input->get('id');

		if (empty($intID))
		{
			return;
		}
		return MetaModelFactory::byId($objDC->getCurrentModel()->getProperty('pid'));
	}

	/**
	 * Render a row for the list view in the backend.
	 *
	 * @param array         $arrRow   The current data row.
	 *
	 * @param string        $strLabel The label text.
	 *
	 * @param DataContainer $objDC    The DataContainer instance that called the method.
	 *
	 * @return string
	 */
	public function drawSetting($arrRow, $strLabel = '', DataContainer $objDC = null)
	{
		return $strLabel . (
			$arrRow['isdefault']
			? ' <span style="color:#b3b3b3; padding-left:3px">[' . $GLOBALS['TL_LANG']['MSC']['fallback'] . ']</span>'
			: ''
		);
	}

	/**
	 * Return all render types available.
	 *
	 * Currently the only supported render types are standalone and ctable.
	 *
	 * @param DataContainer $objDC The DataContainer instance that called the method.
	 *
	 * @return array
	 */
	public function getRenderTypes(DataContainer $objDC)
	{
		if (!$this->getMetaModel($objDC)->hasVariants())
		{
			return array('standalone', 'ctable');
		}
		return array('standalone', 'ctable');
	}

	/**
	 * Returns an array with all valid tables that can be used as parent table.
	 *
	 * Excludes the metamodel table itself in ctable mode, as that one would be "selftree" then and not ctable.
	 *
	 * @param DC_General $objDC The general DataContainer calling us.
	 *
	 * @return string[] the tables.
	 */
	public function getTables(DC_General $objDC)
	{
		if ($objDC->getCurrentModel()->getProperty('rendertype') == 'ctable')
		{
			$blnOmit = $this->getMetaModel($objDC)->getTableName();
		}
		$tables = array();
		foreach ($this->Database->listTables() as $table)
		{
			if (!($blnOmit && ($blnOmit == $table)))
			{
				$tables[$table] = $table;
			}
		}
		return $tables;
	}

	/**
	 * Prefix the given value with "mode_" to prevent the DC from using numeric ids.
	 *
	 * @param int $varValue The mode to prefix.
	 *
	 * @return string
	 */
	public function modeLoad($varValue)
	{
		return 'mode_' . $varValue;
	}

	/**
	 * Strip the mode prefix from the given value.
	 *
	 * @param string $varValue The mode to strip the prefix from.
	 *
	 * @return string
	 */
	public function modeSave($varValue)
	{
		$arrSplit = explode('_', $varValue);
		return $arrSplit[1];
	}

	/**
	 * Return all valid modes for the current MetaModels rendertype.
	 *
	 * @param DataContainer $objDC The DataContainer instance that called the method.
	 *
	 * @return array
	 */
	public function getValidModes(DataContainer $objDC)
	{
		$arrResult = array();
		switch ($objDC->getCurrentModel()->getProperty('rendertype'))
		{
			case 'ctable':
				$arrResult = array('mode_3', 'mode_4', 'mode_6');
				break;
			case 'standalone':
				$arrResult = array('mode_0', 'mode_1', 'mode_2', 'mode_5');
				break;
			default:
				$arrResult = array();
				break;
		}
		return $arrResult;
	}

	/**
	 * Retrieve all backend section keys, like "content", "system" etc.
	 *
	 * @return array
	 */
	public function backendSectionCallback()
	{
		return array_keys($GLOBALS['BE_MOD']);
	}

	/**
	 * Check if the MM has a varsupport. If so disable some options in dca.
	 *
	 * Used in the oncreate_callback.
	 *
	 * @param String $strTable The table name to check.
	 *
	 * @return void
	 */
	// @codingStandardsIgnoreStart - only error left, is the warning about having always a return value.
	public function checkSortMode($strTable)
	{
		// Get Current id.
		$intID = $this->Input->get('id');

		if (empty($intID) || ($strTable != 'tl_metamodel_dca'))
		{
			return;
		}

		// Get current dataset.
		$objResult = $this->Database
			->prepare('SELECT pid FROM tl_metamodel_dca WHERE id=?')
			->limit(1)
			->execute($intID);

		if ($objResult->numRows == 0)
		{
			return;
		}

		// Check if in list mode and if corresponding MM has varsupport.
		if (in_array($objResult->mode, array(3, 4, 5, 6))
		|| (($objMetaModel = MetaModelFactory::byId($objResult->pid)) && $objMetaModel->hasVariants()))
		{
			// Unset fields.
			unset($GLOBALS['TL_DCA'][$strTable]['fields']['mode']);
			unset($GLOBALS['TL_DCA'][$strTable]['fields']['flag']);
			unset($GLOBALS['TL_DCA'][$strTable]['fields']['panelLayout']);
			unset($GLOBALS['TL_DCA'][$strTable]['fields']['fields']);

			// Unset palettes.
			$arrParts = trimsplit(';', $GLOBALS['TL_DCA'][$strTable]['palettes']['default']);
			foreach ($arrParts as $key => $value)
			{
				if (stripos($value, '{expert_legend}') !== false)
				{
					unset($arrParts[$key]);
					break;
				}
			}
			$GLOBALS['TL_DCA'][$strTable]['palettes']['default'] = implode(';', $arrParts);
		}
	}
	// @codingStandardsIgnoreEnd

	/**
	 * Fetch all attributes from the parenting MetaModel. Called as options_callback.
	 *
	 * @return array
	 */
	public function getAllAttributes()
	{
		$intID  = $this->Input->get('id');
		$intPid = $this->Input->get('pid');

		$arrReturn = array();

		if (empty($intPid))
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
			$objMetaModel = MetaModelFactory::byId($intPid);
		}

		foreach ($objMetaModel->getAttributes() as $objAttribute)
		{
			$arrReturn[$objAttribute->getColName()] = $objAttribute->getName();
		}
		return $arrReturn;
	}

	/**
	 * Panel picker for convenient picking of panel layout strings.
	 *
	 * @param DataContainer $objDC The DataContainer instance that called the method.
	 *
	 * @return string
	 */
	public function getPanelpicker($objDC)
	{
		$strOpenAction = '';
		if (version_compare(VERSION, '3.0', '<'))
		{
			return sprintf(
				// @codingStandardsIgnoreStart - we know that this line is long.
				' <a href="system/modules/metamodels/popup.php?tbl=%s&fld=%s&inputName=ctrl_%s&id=%s&item=PALETTE_PANEL_PICKER" rel="lightbox[files 765 60%%]" data-lightbox="files 765 60%%">%s</a>',
				// @codingStandardsIgnoreEnd
				$objDC->table,
				$objDC->field,
				$objDC->inputName,
				$objDC->id,
				$this->generateImage(
					'system/modules/metamodels/html/palette_wizard.png',
					$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['panelpicker'], 'style="vertical-align:top;"'
				)
			);
		}
		return sprintf(
			// @codingStandardsIgnoreStart - we know that this line is long.
			' <a href="system/modules/metamodels/popup.php?tbl=%s&fld=%s&inputName=ctrl_%s&id=%s&item=PALETTE_PANEL_PICKER" onclick="Backend.getScrollOffset();Backend.openModalIframe({\'width\':765,\'title\':\'%s\',\'url\':this.href,\'id\':\'%s\'});return false">%s</a>',
			// @codingStandardsIgnoreEnd
			$objDC->table,
			$objDC->field,
			$objDC->inputName,
			$objDC->id,
			$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['panelpicker'],
			$objDC->id,
			$this->generateImage(
				'system/modules/metamodels/html/palette_wizard.png',
				$GLOBALS['TL_LANG']['tl_metamodel_rendersettings']['panelpicker'],
				'style="vertical-align:top;"'
			)
		);
	}
}

