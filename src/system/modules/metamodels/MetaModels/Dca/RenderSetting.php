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
use MetaModels\IMetaModel;
use MetaModels\Factory as ModelFactory;

/**
 * This class is used from DCA tl_metamodel_rendersetting for various callbacks.
 *
 * @package    MetaModels
 * @subpackage Backend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */

class RenderSetting extends Helper
{
	/**
	 * @var RenderSetting
	 */
	protected static $objInstance = null;

	/**
	 * @var IMetaModel
	 */
	protected $objMetaModel = null;

	protected $objSetting = null;

	/**
	 * Get the static instance.
	 *
	 * @static
	 * @return RenderSetting
	 */
	public static function getInstance()
	{
		if (self::$objInstance == null) {
			self::$objInstance = new RenderSetting();
		}
		return self::$objInstance;
	}

	/**
	 * Protected constructor for singleton instance.
	 */
	protected function __construct()
	{
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

	/**
	 * @param \DcGeneral\DC_General $objDC
	 */
	protected function objectsFromUrl($objDC)
	{
		// TODO: detect all other ways we might end up here and fetch $objMetaModel accordingly.
		if ($this->objMetaModel)
		{
			return;
		}

		if($objDC && $objDC->getEnvironment()->getCurrentModel())
		{
			$this->objSetting = \Database::getInstance()
				->prepare('SELECT * FROM tl_metamodel_rendersettings WHERE id=?')
				->execute($objDC->getEnvironment()->getCurrentModel()->getProperty('pid'));

			$this->objMetaModel = ModelFactory::byId($this->objSetting->pid);
		}
		// TODO: I guess the whole block here is not needed anymore since we are using DC_General. Check it.
		elseif (\Input::getInstance()->get('act'))
		{
			// act present, but we have an id
			switch (\Input::getInstance()->get('act'))
			{
				case 'edit':
					if (\Input::getInstance()->get('id'))
					{
						$this->objSetting = \Database::getInstance()->prepare('
							SELECT tl_metamodel_rendersetting.*,
								tl_metamodel_rendersettings.pid AS tl_metamodel_rendersettings_pid,
								tl_metamodel_rendersettings.name AS name
							FROM tl_metamodel_rendersetting
							LEFT JOIN tl_metamodel_rendersettings
							ON (tl_metamodel_rendersetting.pid = tl_metamodel_rendersettings.id)
							WHERE (tl_metamodel_rendersetting.id=?)')
							->execute(\Input::getInstance()->get('id'));
						$this->objMetaModel = ModelFactory::byId($this->objSetting->tl_metamodel_rendersettings_pid);
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
	 * @param \DcGeneral\DC_General $objDC the data container calling.
	 *
	 * @return array
	 */
	public function getAttributeNames($objDC)
	{
		$this->objectsFromUrl($objDC);
		$arrResult = array();

		if (!$this->objMetaModel)
		{
			return array();
		}
		$objMetaModel = $this->objMetaModel;

		foreach ($objMetaModel->getAttributes() as $objAttribute)
		{
			$strTypeName = $objAttribute->get('type');
			$arrResult[$objAttribute->get('id')] = $objAttribute->getName() . ' [' . $strTypeName . ']';
		}

		return $arrResult;
	}

	/**
	 * Fetch the template group for the detail view of the current MetaModel module.
	 *
	 * @param \DcGeneral\DC_General $objDC the datacontainer calling this method.
	 *
	 * @return array
	 *
	 */
	public function getTemplates(DC_General $objDC)
	{
		if (!($this->objMetaModel))
		{
			return array();
		}

		$objAttribute = $this->objMetaModel->getAttributeById($objDC->getEnvironment()->getCurrentModel()->getProperty('attr_id'));

		if (!$objAttribute)
		{
			return array();
		}
		return $this->getTemplatesForBase('mm_attr_' . $objAttribute->get('type'));
	}
}

