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
 * This class is used from tl_metamodel for various callbacks.
 *
 * @package	   MetaModels
 * @subpackage Backend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class TableMetaModelAttribute extends TableMetaModelHelper
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
		if (self::$objInstance == null) {
			self::$objInstance = new TableMetaModelAttribute();
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
	 * @var IMetaModelAttribute
	 */
	protected static $objCurrentField = null;


	protected function setNameAndDescription(IMetaModel $objMetaModel)
	{
		$GLOBALS['TL_DCA']['tl_metamodel_attribute']['fields']['name'] = array_replace_recursive(
			parent::makeMultiColumnName(
				$objMetaModel,
				$GLOBALS['TL_LANG']['tl_metamodel_attribute']['name_langcode'],
				$GLOBALS['TL_LANG']['tl_metamodel_attribute']['name_value']
			),
			$GLOBALS['TL_DCA']['tl_metamodel_attribute']['fields']['name']
		);

		$GLOBALS['TL_DCA']['tl_metamodel_attribute']['fields']['description'] = array_replace_recursive(
			parent::makeMultiColumnName(
				$objMetaModel,
				$GLOBALS['TL_LANG']['tl_metamodel_attribute']['name_langcode'],
				$GLOBALS['TL_LANG']['tl_metamodel_attribute']['name_value'],
				true
			),
			$GLOBALS['TL_DCA']['tl_metamodel_attribute']['fields']['description']
		);
	}

	/**
	 * Retrieve and buffer the current value of the column frm the DB.
	 * This will later be used for the on submit and onsave callbacks.
	 *
	 * Used from tl_metamodel_attribute DCA
	 *
	 * @param DataContainer $objDC the data container that issued this callback.
	 */
	public function onLoadCallback($objDC)
	{
		// do nothing if not in edit/create mode.
		if(!(($this->Input->get('pid') || $this->Input->get('id')) && in_array($this->Input->get('act'), array('create', 'edit'))))
		{
			return;
		}

		if ($this->Input->get('pid'))
		{
			$objMetaModel = MetaModelFactory::byId($this->Input->get('pid'));
		} else {
			$objMetaModel = MetaModelFactory::byId(
				$this->Database->prepare('SELECT pid FROM tl_metamodel_attribute WHERE id=?')
							   ->execute($this->Input->get('id'))
							   ->pid
			);
		}

		if (!$objMetaModel)
		{
			throw new Exception('unexpected condition, metamodel unknown', 1);
		}

		$this->setNameAndDescription($objMetaModel);

		if (!$objMetaModel->hasVariants())
		{
			unset($GLOBALS['TL_DCA']['tl_metamodel_attribute']['fields']['isvariant']);
		}
	}

	/**
	 * Keep a copy of the attribute values.
	 *
	 * @param InterfaceGeneralModel $objModel the current Model active in the DC.
	 *
	 * @return void
	 */
	public function onModelBeforeUpdateCallback($objModel)
	{
		self::$objCurrentField = MetaModelAttributeFactory::createFromArray($objModel->getPropertiesAsArray());
	}

	/**
	 * Called when the attribute has been saved.
	 *
	 * @param InterfaceGeneralModel $objModel The model that has been updated.
	 *
	 * @param DataContainer $objDC the data container that issued this callback.
	 */
	public function onSaveCallback($objModel, $objDC)
	{
		/**
		 * The currently edited field.
		 *
		 * @var IMetaModelAttribute
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
			$objField = MetaModelAttributeFactory::createFromArray($arrNewField);
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

	public function onDeleteCallback($objDC)
	{
		$objField = MetaModelAttributeFactory::createFromArray($objDC->getCurrentModel()->getPropertiesAsArray());
		if($objField)
		{
			$objField->destroyAUX();
		}
	}

	/**
	 * Get all valid fieldtypes
	 *
	 * @return string[] the field type identifiers for all valid fields for the current MetaModel.
	 */
	public function fieldTypesCallback($objDC)
	{
		$objMetaModel = MetaModelFactory::byId($objDC->getCurrentModel()->getProperty('pid'));

		return MetaModelAttributeFactory::getAttributeTypes($objMetaModel->isTranslated(), $objMetaModel->hasVariants());
	}

	protected function getMetaModelFromDC($objDC)
	{
		return MetaModelFactory::byId($objDC->getCurrentModel()->getProperty('pid'));
	}

	public function decodeNameAndDescription($varValue, $objDC)
	{
		return parent::decodeLangArray($varValue, $this->getMetaModelFromDC($objDC));
	}

	public function encodeNameAndDescription($varValue, $objDC)
	{
		return parent::encodeLangArray($varValue, $this->getMetaModelFromDC($objDC));
	}

	/**
	 * Add the type of input field
	 * @param array
	 * @return string
	 */
	public function renderField($arrRow)
	{

		$objMetaModel = MetaModelFactory::byId($arrRow['pid']);

		$strColName = $arrRow['colname'];
		$strType = $arrRow['type'];
		$strImages = '';
		$strTypeImage = '';

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
	'.($arrRow['insertBreak'] ? '<span style="padding-left:20px;" class="legend" title="'.$GLOBALS['TL_LANG']['tl_metamodel_attribute']['legendTitle'][0].'">'.$legendImage.' '.$arrRow['legendTitle'] .'</span>' : '').'
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

?>