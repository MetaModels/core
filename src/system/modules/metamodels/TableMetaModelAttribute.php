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
		// do nothing if not in edit mode.
		if(
			!($objDC->id && $this->Input->get('act'))
		|| ($this->Input->get('act') == 'paste')
		)
		{
			return;
		}

		$objDB = Database::getInstance();
		// fetch current values of the field from DB.
		$objField = $objDB->prepare('
			SELECT *
			FROM tl_metamodel_attribute
			WHERE id=?'
		)
		->limit(1)
		->executeUncached($objDC->id);

		if ($objField->numRows == 0)
		{
			return;
		}

		$this->Session->set('tl_metamodel_attribute', $objField->row());

		$objMetaModel = MetaModelFactory::byId($objField->pid);

		$this->setNameAndDescription($objMetaModel);

		if (!$objMetaModel->hasVariants())
		{
			unset($GLOBALS['TL_DCA']['tl_metamodel_attribute']['fields']['isvariant']);
		}

		// inline create the instance of this field and buffer it.
		self::$objCurrentField = MetaModelAttributeFactory::createFromDB($objField);

		// hijack all onsave_callbacks to pass meta data change calls to field class.
		foreach ($GLOBALS['TL_DCA']['tl_metamodel_attribute']['fields'] as $strFieldname=>&$arrFieldDef)
		{
			$arrFieldDef['save_callback'][] = array('TableMetaModelAttribute', 'onSaveCallback');
		}
	}

	/**
	 * onsubmit_callback
	 * Used from tl_metamodel_attribute DCA
	 *
	 * @param DataContainer $objDC the data container that issued this callback.
	 */
	public function onSubmitCallback($objDC)
	{

	}

	public function onSaveCallback($varValue, $objDC)
	{
		/**
		 * The currently edited field.
		 *
		 * @var IMetaModelAttribute
		 */
		$objField = self::$objCurrentField;

		if($objField)
		{
			$oldValue = $objField->get($objDC->field);
		} else {
			$oldValue = null;
		}

		if ($objDC->field == 'type' && $oldValue != $varValue)
		{
			// we are changing the field type, destroy old instance and prepare new instance.
			if($objField)
			{
				$objField->destroyAUX();
			}

			if (method_exists($objDC, 'getCurrentModel'))
			{
				$arrNewField = $objDC->getCurrentModel()->getPropertiesAsArray();
			} else {
				$arrNewField = $objDC->activeRecord->row();
			}

			// GOTCHA: potential problem when a field requires input not available here (later loop cycle in DC_Table save_callback).
			$arrNewField['type'] = $varValue;
			$objField = MetaModelAttributeFactory::createFromArray($arrNewField);
			if($objField)
			{
				self::$objCurrentField = $objField;
				$objField->initializeAUX();
			}
		}

		if($objField)
		{
			$objField->handleMetaChange($objDC->field, $varValue);
		}

		return $varValue;
	}

	public function onDeleteCallback($objDC)
	{
		throw new Exception("DELETE unsupported!", 1);
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