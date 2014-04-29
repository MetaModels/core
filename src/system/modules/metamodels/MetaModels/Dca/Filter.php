<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Dca;

use DcGeneral\DC_General;
use DcGeneral\Clipboard\ClipboardInterface;
use MetaModels\Helper\ContaoController;
use MetaModels\IMetaModel;
use MetaModels\Factory as ModelFactory;

/**
 * This class is used from DCA tl_metamodel_filtersetting for various callbacks.
 *
 * @package    MetaModels
 * @subpackage Backend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */

class Filter extends Helper
{
	/**
	 * @var Filter
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
	 * @return Filter
	 */
	public static function getInstance()
	{
		if (self::$objInstance == null) {
			self::$objInstance = new Filter();
		}
		return self::$objInstance;
	}

	/**
	 * Protected constructor for singleton instance.
	 */
	protected function __construct()
	{
	}

	/**
	 * Retrieve the MetaModel instance relevant for the current filter setting in view.
	 *
	 * @param \DcGeneral\DC_General $objDC The data container.
	 *
	 * @return IMetaModel
	 */
	public function getMetaModel($objDC)
	{
		$this->objectsFromUrl($objDC);
		return $this->objMetaModel;
	}

	/**
	 * Translates an attribute id to the human readable name defined.
	 *
	 * @param $strValue
	 *
	 * @param $objDC
	 *
	 * @return string the human readable name.
	 */
	public function attrIdToHumanName($strValue, $objDC)
	{
		$this->objectsFromUrl($objDC);
		return $this->objMetaModel->getAttributeById($strValue)->getName();
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
}
