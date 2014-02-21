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

use DcGeneral\DataContainerInterface;
use MetaModels\Factory;
use MetaModels\IMetaModel;

/**
 * This class is used from DCA tl_metamodel_rendersetting for various callbacks.
 *
 * @package	   MetaModels
 * @subpackage Backend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */

class DcaSetting extends Helper
{
	/**
	 * @var DcaSetting
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
	 * @return DcaSetting
	 */
	public static function getInstance()
	{
		if (self::$objInstance == null) {
			self::$objInstance = new DcaSetting();
		}
		return self::$objInstance;
	}

	public function createDataContainer($strTableName)
	{
		if ($strTableName != 'tl_metamodel_dcasetting')
		{
			return;
		}

		if (\Input::getInstance()->get('subpaletteid'))
		{
			$GLOBALS['TL_DCA']['tl_metamodel_dcasetting']['dca_config']['childCondition'][0]['setOn'][] = array
			(
				'to_field'    => 'subpalette',
				'value'       => \Input::getInstance()->get('subpaletteid')
			);
			$GLOBALS['TL_DCA']['tl_metamodel_dcasetting']['dca_config']['childCondition'][0]['filter'][] = array
			(
				'local'        => 'subpalette',
				'remote_value' => \Input::getInstance()->get('subpaletteid'),
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
	}

	protected function getMetaModelFromDC($objDC)
	{
		// check for predefined values.
		$objDB = \Database::getInstance();
		// fetch current values of the field from DB.
		$objField = $objDB->prepare('
			SELECT pid
			FROM tl_metamodel_dca
			WHERE id=?'
		)
			->limit(1)
			->executeUncached($objDC->getEnvironment()->getCurrentModel()->getProperty('pid'));
		return Factory::byId($objField->pid);
	}

	/**
	 * @param DataContainerInterface $objDC
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
				->prepare('SELECT * FROM tl_metamodel_dca WHERE id=?')
				->execute($objDC->getEnvironment()->getCurrentModel()->getProperty('pid'));

			$this->objMetaModel = Factory::byId($this->objSetting->pid);
		}

		if (\Input::getInstance()->get('act'))
		{
			// act present, but we have an id
			switch (\Input::getInstance()->get('act'))
			{
				case 'edit':
					if (\Input::getInstance()->get('id'))
					{
						$this->objSetting = \Database::getInstance()->prepare('
						SELECT tl_metamodel_dcasetting.*,
								tl_metamodel_dca.pid AS tl_metamodel_dca_pid
						FROM tl_metamodel_dcasetting
						LEFT JOIN tl_metamodel_dca
						ON (tl_metamodel_dcasetting.pid = tl_metamodel_dca.id)
						WHERE (tl_metamodel_dcasetting.id=?)')
							->execute(\Input::getInstance()->get('id'));
						$this->objMetaModel = Factory::byId($this->objSetting->tl_metamodel_dca_pid);
					}
					break;
				default:;
			}
		} else {
		}
	}
}

