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

use DcGeneral\DataContainerInterface;
use MetaModels\IMetaModel;
use MetaModels\Attribute\IAttribute;
use MetaModels\Factory as ModelFactory;
use MetaModels\Attribute\Factory as AttributeFactory;

/**
 * This class is used from tl_metamodel for various callbacks.
 *
 * @package    MetaModels
 * @subpackage Backend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class Attribute extends Helper
{
	/**
	 * @var Helper
	 */
	protected static $objInstance = null;

	/**
	 * Get the static instance.
	 *
	 * @static
	 * @return Helper
	 */
	public static function getInstance()
	{
		if (self::$objInstance == null) {
			self::$objInstance = new Attribute();
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

	/**
	 * Buffer property to hold the instance of the current field being edited.
	 *
	 * @var \MetaModels\Attribute\IAttribute
	 */
	protected static $objCurrentField = null;

	/**
	 * Retrieve and buffer the current value of the column frm the DB.
	 * This will later be used for the on submit and onsave callbacks.
	 *
	 * Used from tl_metamodel_attribute DCA
	 *
	 * @param \DcGeneral\DataContainerInterface $objDC the data container that issued this callback.
	 *
	 * @throws \RuntimeException
	 */
	public function onLoadCallback($objDC)
	{
		// FIXME: we have DC_General available here, so alter the quirky hacks using the parameters from the Url.
		// do nothing if not in edit/create mode.
		if(!((\Input::getInstance()->get('pid') || \Input::getInstance()->get('id')) && in_array(\Input::getInstance()->get('act'), array('create', 'edit'))))
		{
			return;
		}

		if (\Input::getInstance()->get('pid'))
		{
			$objMetaModel = ModelFactory::byId(\Input::getInstance()->get('pid'));
		} else {
			$objMetaModel = ModelFactory::byId(
				$this->Database->prepare('SELECT pid FROM tl_metamodel_attribute WHERE id=?')
					->execute(\Input::getInstance()->get('id'))
					->pid
			);
		}

		if (!$objMetaModel)
		{
			throw new \RuntimeException('unexpected condition, metamodel unknown', 1);
		}

		if (!$objMetaModel->hasVariants())
		{
			unset($GLOBALS['TL_DCA']['tl_metamodel_attribute']['fields']['isvariant']);
		}
	}

	/**
	 * Keep a copy of the attribute values.
	 *
	 * @param \DcGeneral\Data\ModelInterface $objModel the current Model active in the DC.
	 *
	 * @return void
	 */
	public function onModelBeforeUpdateCallback($objModel)
	{
		self::$objCurrentField = AttributeFactory::createFromArray($objModel->getPropertiesAsArray());
	}

	/**
	 * Called when the attribute has been saved.
	 *
	 * @param \DcGeneral\Data\ModelInterface $objModel The model that has been updated.
	 *
	 * @param \DcGeneral\DataContainerInterface $objDC the data container that issued this callback.
	 */
	public function onSaveCallback($objModel, $objDC)
	{
		/**
		 * The currently edited field.
		 *
		 * @var \MetaModels\Attribute\IAttribute $objField
		 */
		$objField = self::$objCurrentField;
		$arrNewField = $objModel->getPropertiesAsArray();

		if($objField)
		{
			$oldType = $objField->get('type');
		} else {
			$oldType = null;
		}

		if ($oldType != $arrNewField['type'])
		{
			// destroy old instance...
			if($objField)
			{
				$objField->destroyAUX();
			}
			// ... prepare new instance.
			$objField = AttributeFactory::createFromArray($arrNewField);
			// create new instance' aux info.
			if($objField)
			{
				self::$objCurrentField = $objField;
				$objField->initializeAUX();
			}
		}

		if($objField)
		{
			// now loop over all values and update the meta in the instance.
			foreach ($arrNewField as $strKey => $varValue)
			{
				$objField->handleMetaChange($strKey, $varValue);
			}
		}
	}

	/**
	 * @param \DcGeneral\DataContainerInterface $objDC The DataContainer.
	 */
	public function onDeleteCallback($objDC)
	{
		$objField = AttributeFactory::createFromArray($objDC->getCurrentModel()->getPropertiesAsArray());
		if($objField)
		{
			$objField->destroyAUX();
		}
	}

	/**
	 * @param \DcGeneral\DataContainerInterface $objDC The DataContainer.
	 *
	 * @return \MetaModels\IMetaModel
	 */
	protected function getMetaModelFromDC($objDC)
	{
		return ModelFactory::byId($objDC->getEnvironment()->getCurrentModel()->getProperty('pid'));
	}

	/**
	 * Add the type of input field
	 * @param array
	 * @return string
	 */
	public function renderField($arrRow)
	{

		$objMetaModel = ModelFactory::byId($arrRow['pid']);

		$strColName = $arrRow['colname'];
		$strType = $arrRow['type'];
		$strImages = '';

		$arrName = deserialize($arrRow['name']);
		if(is_array($arrName))
		{
			$strName = $arrName[$GLOBALS['TL_LANGUAGE']];
			if(!$strName)
			{
				$strName = $arrName[$objMetaModel->getFallbackLanguage()];
			}
		} else {
			$strName = $arrRow['name'];
		}

		$arrDescription = deserialize($arrRow['description']);
		if(is_array($arrDescription))
		{
			$strDescription = $arrDescription[$GLOBALS['TL_LANGUAGE']];
			if(!$strDescription)
			{
				$strDescription = $arrDescription[$objMetaModel->getFallbackLanguage()];
			}
		} else {
			$strDescription = $arrRow['name'];
		}

		$strTypeImage = '<img src="' . $GLOBALS['METAMODELS']['attributes'][$strType]['image'] . '" />';

		return
			sprintf(
				'<div class="field_heading cte_type"><strong>%s</strong> <em>[%s]</em></div>
				<div class="field_type block">
					<div style="padding-top:3px; float:right;">%s</div>
					%s<strong>%s</strong> - %s<br />
					'.($arrRow['insertBreak'] ? '<span style="padding-left:20px;" class="legend" title="'.$GLOBALS['TL_LANG']['tl_metamodel_attribute']['legendTitle'][0].'"> '.$arrRow['legendTitle'] .'</span>' : '').'
</div>',
				$strColName,
				$strType,
				$strImages,
				$strTypeImage,
				$strName,
				$strDescription
			);
	}
}
