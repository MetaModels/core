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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Dca;

use MetaModels\Factory;

/**
 * Implementation of the breadcrumb for MetaModels.
 *
 * @package	   MetaModels
 * @subpackage Core
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 */
class BreadcrumbBuilder
{

	/**
	 * Current id for lookups.
	 *
	 * @var int
	 */
	protected $intID;

	/**
	 * Callback and startpoint for this programm.
	 *
	 * @param \DC_General $objDC
	 *
	 * @return array
	 */
	public function generateBreadcrumbItems($objDC)
	{
		// Store init id.
		$this->intID = $objDC->getId();

		$arrReturn = array();

		// Build navigation.
		// Each get*Level() call will alter the id property to the next pid.
		switch ($objDC->getTable())
		{
			case 'tl_metamodel_attribute':
				$arrReturn[] = $this->getSecondLevel('tl_metamodel_attribute', 'fields.png');
				break;

			case 'tl_metamodel_rendersetting':
				$arrReturn[] = $this->getThirdLevel('tl_metamodel_rendersetting', 'tl_metamodel_rendersettings', 'render_setting.png');
			case 'tl_metamodel_rendersettings':
				$arrReturn[] = $this->getSecondLevel('tl_metamodel_rendersettings', 'render_settings.png');
				break;

			case 'tl_metamodel_dcasetting':
				if (\Input::getInstance()->get('subpaletteid'))
				{
					$arrReturn[] = $this->getFourthLevel('metamodel_dcasetting_subpalette', 'tl_metamodel_dcasetting', 'dca_subpalette.png');
				}
				$arrReturn[] = $this->getThirdLevel('tl_metamodel_dcasetting', 'tl_metamodel_dca', 'dca_setting.png');
			case 'tl_metamodel_dca':
				$arrReturn[] = $this->getSecondLevel('tl_metamodel_dca', 'dca.png');
				break;

			case 'tl_metamodel_filtersetting':
				$arrReturn[] = $this->getThirdLevel('tl_metamodel_filtersetting', 'tl_metamodel_filter', 'filter_setting.png');
			case 'tl_metamodel_filter':
				$arrReturn[] = $this->getSecondLevel('tl_metamodel_filter', 'filter.png');
				break;

			default:
				break;
		}

		// Always add root from mm as first entry.
		$arrReturn[] = $this->getFirstLevel();

		// Return array.
		return array_reverse($arrReturn);
	}

	/**
	 * Create root entry
	 *
	 * @return array
	 */
	protected function getFirstLevel()
	{
		return array(
			'url' => 'contao/main.php?do=metamodels',
			'text' => $GLOBALS['TL_LANG']['BRD']['metamodels'],
			'icon' => \Environment::getInstance()->base . 'system/modules/metamodels/html/logo.png'
		);
	}

	/**
	 * Get the second level from metamodel
	 *
	 * @param string $strTable Name of current table
	 * @param string $strIcon Name of icon
	 *
	 * @return array
	 */
	protected function getSecondLevel($strTable, $strIcon)
	{
		$objMetaModel = Factory::byId($this->intID);

		return array(
			'url' => 'contao/main.php?do=metamodels&table=' . $strTable . '&id=' . $this->intID,
			'text' => sprintf($this->getLanguage($strTable), $objMetaModel->getName()),
			'icon' => (!empty($strIcon)) ? \Environment::getInstance()->base . '/system/modules/metamodels/html/' . $strIcon : null
		);
	}

	/**
	 * Get the third level from metamodel
	 *
	 * @param string $strTable Name of current table
	 * @param string $strParentTable Name of parent table for name/title lookup
	 * @param string $strIcon Name of icon
	 *
	 * @return array
	 */
	protected function getThirdLevel($strTable, $strParentTable, $strIcon)
	{
		// Get name from parent.
		if (!empty($strParentTable))
		{
			$objParent = \Database::getInstance()
				->prepare('SELECT id, pid, name FROM ' . $strParentTable . ' WHERE id=?')
				->executeUncached($this->intID);

			// Change id for next entry.
			$strName = $objParent->name;
			$intCurrrentID = $this->intID;
			$this->intID = $objParent->pid;
		}
		else
		{
			$objResult = \Database::getInstance()
				->prepare('SELECT id, pid FROM ' . $strTable . ' WHERE pid=?')
				->executeUncached($this->intID);

			// Change id for next entry.
			$strName = $objResult->id;
			$intCurrrentID = $this->intID;
			$this->intID = $objResult->pid;
		}

		return array(
			'url' => 'contao/main.php?do=metamodels&table=' . $strTable . '&id=' . $intCurrrentID,
			'text' => sprintf($this->getLanguage($strTable), $strName),
			'icon' => (!empty($strIcon)) ? \Environment::getInstance()->base . '/system/modules/metamodels/html/' . $strIcon : null
		);
	}

	protected function getFourthLevel($strTable, $strParentTable, $strIcon)
	{
		$strUrl = 'contao/main.php?do=metamodels&table=' . $strTable . '&id=' . $intCurrrentID;

		switch ($strTable)
		{
			case 'metamodel_dcasetting_subpalette':
				$objParent = \Database::getInstance()
					->prepare('SELECT id, pid, name FROM tl_metamodel_attribute WHERE id=(SELECT attr_id FROM tl_metamodel_dcasetting WHERE pid=? AND id=?)')
					->executeUncached($this->intID, Input::getInstance()->get('subpaletteid'));

				$objMetaModel = Factory::byId($objParent->pid);
				$objAttribute = $objMetaModel->getAttributeById($objParent->id);

				// Change id for next entry.
				$strName = $objAttribute->getName();
				$intCurrrentID = $this->intID;
				//$this->intID = $objParent->pid;

				$strSubfilter = 'subpaletteid=' . \Input::getInstance()->get('subpaletteid');
				$strUrl = 'contao/main.php?do=metamodels&table=' . $strParentTable . '&id=' . $intCurrrentID . '&' . $strSubfilter;
				break;
		}

		return array(
			'url' => $strUrl,
			'text' => sprintf($this->getLanguage($strTable), $strName),
			'icon' => (!empty($strIcon)) ? \Environment::getInstance()->base . '/system/modules/metamodels/html/' . $strIcon : null
		);
	}

	/**
	 * Get for a table the human readable name or a fallback
	 *
	 * @param string $strTable Name of table
	 *
	 * @return string Human readable name
	 */
	protected function getLanguage($strTable)
	{
		$strShortTable = str_replace('tl_', '', $strTable);

		// Search for translation.
		if (array_key_exists($strShortTable, $GLOBALS['TL_LANG']['BRD']))
		{
			return specialchars($GLOBALS['TL_LANG']['BRD'][$strShortTable]);
		}

		// Fallback.
		$strShortTable = str_replace('tl_metamodel_', '', $strTable);
		return strtoupper(substr($strShortTable, 0, 1)) . substr($strShortTable, 1, strlen($strShortTable) - 1) . ' %s';
	}

}
