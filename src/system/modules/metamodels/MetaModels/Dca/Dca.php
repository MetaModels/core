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

namespace MetaModels\Dca;

use DcGeneral\DC_General;
use MetaModels\IMetaModel;
use MetaModels\Factory;

/**
 * This class is used from DCA tl_metamodel_dca for various callbacks.
 *
 * @package	   MetaModels
 * @subpackage Backend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class Dca
{
	/**
	 * Return the MetaModel currently in scope of the given DataContainer.
	 *
	 * @param \DcGeneral\DC_General $objDC The DataContainer instance.
	 *
	 * @return IMetaModel The MetaModel instance.
	 */
	protected function getMetaModel(DC_General $objDC)
	{
		if (\Input::getInstance()->get('act') == 'create' && \Input::getInstance()->get('pid'))
		{
			return Factory::byId($this->Input->get('pid'));
		}
		// Get Current id.
		$intID = \Input::getInstance()->get('id');

		if (empty($intID))
		{
			return null;
		}
		return Factory::byId($objDC->getEnvironment()->getCurrentModel()->getProperty('pid'));
	}

	/**
	 * Return all render types available.
	 *
	 * Currently the only supported render types are standalone and ctable.
	 *
	 * @param \DcGeneral\DC_General $objDC The DataContainer instance that called the method.
	 *
	 * @return array
	 */
	public function getRenderTypes(DC_General $objDC)
	{
/*
		if (!$this->getMetaModel($objDC)->hasVariants())
		{
			return array('standalone', 'ctable');
		}
*/
		return array('standalone', 'ctable');
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
	public function checkSortMode($strTable)
	{
		// Get Current id.
		$intID = \Input::getInstance()->get('id');

		if (empty($intID) || ($strTable != 'tl_metamodel_dca'))
		{
			return;
		}

		// Get current dataset.
		$objResult = \Database::getInstance()
			->prepare('SELECT pid FROM tl_metamodel_dca WHERE id=?')
			->limit(1)
			->execute($intID);

		if ($objResult->numRows == 0)
		{
			return;
		}

		// Check if in list mode and if corresponding MM has varsupport.
		if (in_array($objResult->mode, array(3, 4, 5, 6))
			|| (($objMetaModel = Factory::byId($objResult->pid)) && $objMetaModel->hasVariants()))
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

	/**
	 * Fetch all attributes from the parenting MetaModel. Called as options_callback.
	 *
	 * @return array
	 */
	public function getAllAttributes()
	{
		$intID  = \Input::getInstance()->get('id');
		$intPid = \Input::getInstance()->get('pid');

		$arrReturn = array();

		if (empty($intPid))
		{
			$objResult = \Database::getInstance()
				->prepare('SELECT pid FROM tl_metamodel_dca WHERE id=?')
				->limit(1)
				->execute($intID);

			if ($objResult->numRows == 0)
			{
				return $arrReturn;
			}
			$objMetaModel = Factory::byId($objResult->pid);
		} else {
			$objMetaModel = Factory::byId($intPid);
		}

		foreach ($objMetaModel->getAttributes() as $objAttribute)
		{
			$arrReturn[$objAttribute->getColName()] = $objAttribute->getName();
		}
		return $arrReturn;
	}

	/**
	 * Make sure there is only one default per mm
	 *
	 * @param mixed
	 * @param DataContainer
	 * @return mixed
	 * @throws \RuntimeException Maybe, but not now.
	 */
	// @codingStandardsIgnoreStart - only error left, is the warning about having always a return value.
	public function checkDefault($varValue, DC_General $dc)
	{
		if ($varValue == '')
		{
			return '';
		}

		// Get Parent MM
		$intParentMm = null;
		if ($dc->id)
		{
			// Get current row.
			$objRendersettings = \Database::getInstance()
				->prepare('SELECT id, pid
						FROM tl_metamodel_dca
						WHERE id=?')
				->execute($dc->id);

			if ($objRendersettings->numRows == 0)
			{
				return '';
			}

			// Get all siblings
			$intParentMm = $objRendersettings->pid;
		}
		else if (\Input::getInstance()->get('pid'))
		{
			$intParentMm = \Input::getInstance()->get('pid');
		}
		else
		{
			return '';
		}

		$objSiblingRendersettings = \Database::getInstance()
			->prepare('SELECT id
					FROM tl_metamodel_dca
					WHERE pid=?
						AND isdefault=1')
			->execute($intParentMm);

		// Check if we have some.
		if ($objSiblingRendersettings->numRows == 0)
		{
			return $varValue;
		}

		// Reset all default flags.
		$arrSiblings = $objSiblingRendersettings->fetchEach('id');
		$arrSiblings = array_map('intval', $arrSiblings);

		\Database::getInstance()
			->prepare('UPDATE tl_metamodel_dca
					SET isdefault = ""
					WHERE id IN(' . implode(', ', $arrSiblings) . ')
						AND isdefault=1')
			->execute();

		return $varValue;
	}
	// @codingStandardsIgnoreEnd

}
