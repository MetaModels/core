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

	/**
	 * Fetch the template group for the detail view of the current MetaModel module.
	 *
	 * @param DataContainerInterface $objDC the datacontainer calling this method.
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
		$this->loadLanguageFile('tl_metamodel_dcasetting');

		$this->Template = new \BackendTemplate('be_autocreatepalette');

		$this->Template->cacheMessage = '';
		$this->Template->updateMessage = '';

		$this->Template->href = $this->getReferer(true);
		$this->Template->headline = $GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['addall'][1];

		// severity: error, confirm, info, new
		$arrMessages = array();

		$objPalette = \Database::getInstance()->prepare('SELECT * FROM tl_metamodel_dca WHERE id=?')->execute(\Input::getInstance()->get('id'));

		$objMetaModel = Factory::byId($objPalette->pid);

		$objAlreadyExist = \Database::getInstance()->prepare('SELECT * FROM tl_metamodel_dcasetting WHERE pid=? AND dcatype=?')->execute(\Input::getInstance()->get('id'), 'attribute');

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
		if (\Input::getInstance()->post('act') == 'perform')
		{
			// loop over all attributes now.
			foreach ($objMetaModel->getAttributes() as $objAttribute)
			{
				if (!array_key_exists($objAttribute->get('id'), $arrKnown))
				{
					$intMax += 128;
					\Database::getInstance()->prepare('INSERT INTO tl_metamodel_dcasetting %s')->set(array(
						'pid'      => \Input::getInstance()->get('id'),
						'sorting'  => $intMax,
						'tstamp'   => time(),
						'dcatype'  => 'attribute',
						'attr_id'  => $objAttribute->get('id'),
						'tl_class' => '',
						'subpalette' => (\Input::getInstance()->get('subpaletteid')) ? \Input::getInstance()->get('subpaletteid') : 0,
					))->execute();

					// Get msg for adding at main palette or a subpalette
					if (\Input::getInstance()->get('subpaletteid'))
					{
						$strPartentAttributeName = \Input::getInstance()->get('subpaletteid');

						// Get parent setting.
						$objParentDcaSetting = \Database::getInstance()
							->prepare("SELECT attr_id FROM tl_metamodel_dcasetting WHERE id=?")
							->execute(\Input::getInstance()->get('subpaletteid'));

						// Check if we have a attribute
						$objPartenAttribute = $objMetaModel->getAttributeById($objParentDcaSetting->attr_id);

						if (!is_null($objPartenAttribute))
						{
							// Multilanguage support.
							if(is_array($objPartenAttribute->get('name')))
							{
								$arrName = $objPartenAttribute->get('name');

								if (key_exists($objMetaModel->getActiveLanguage(), $arrName))
								{
									$strPartentAttributeName = $arrName[$objMetaModel->getActiveLanguage()];
								}
								else
								{
									$strPartentAttributeName = $arrName[$objMetaModel->getFallbackLanguage()];
								}
							}
							else
							{
								$strPartentAttributeName = $objPartenAttribute->get('name');
							}
						}

						$arrMessages[sprintf($GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['addAll_addsuccess_subpalette'], $objAttribute->getName(), $strPartentAttributeName)] = 'confirm';
					}
					else
					{
						$arrMessages[sprintf($GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['addAll_addsuccess'], $objAttribute->getName())] = 'confirm';
					}
				}
			}
		} else {
			// loop over all attributes now.
			foreach ($objMetaModel->getAttributes() as $objAttribute)
			{
				if (array_key_exists($objAttribute->get('id'), $arrKnown))
				{
					$arrMessages[sprintf($GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['addAll_alreadycontained'], $objAttribute->getName())] = 'info';
				} else {
					$arrMessages[sprintf($GLOBALS['TL_LANG']['tl_metamodel_dcasetting']['addAll_willadd'], $objAttribute->getName())] = 'confirm';
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
}

