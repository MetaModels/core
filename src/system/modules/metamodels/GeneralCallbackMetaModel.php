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
 * Callback class for DC_General
 *
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @package    MetaModels
 * @subpackage Core
 */
class GeneralCallbackMetaModel extends GeneralCallbackDefault
{
	/**
	 * The DC
	 * @var DC_General
	 */
	private $objDC;

	/**
	 * Set the DC
	 *
	 * @param DC_General $objDC
	 */
	public function setDC($objDC)
	{
		$this->objDC = $objDC;
		parent::setDC($objDC);
	}

	/**
	 * Populate the provided Template with the items and render them (in html5 mode as we are in the backend).
	 *
	 * @param MetaModelTemplate        $objTemplate The template to populate
	 *
	 * @param IMetaModelItem           $objItem     The MetaModel attached to the items.
	 *
	 * @param IMetaModelRenderSettings $objSettings The render settings to use (optional, defaults to: null).
	 *
	 *@return void
	 */
	protected function prepareTemplate(MetaModelTemplate $objTemplate, IMetaModelItem $objItem, $objSettings = null)
	{
		$objTemplate->settings  = $objSettings;
		$objTemplate->item      = $objItem;
		$objTemplate->data      = $objItem->parseValue('html5', $objSettings);
	}


	protected function drawItem(InterfaceGeneralModel $objModelRow)
	{
		$objNativeItem = $objModelRow->getItem();
		$objMetaModel = $objNativeItem->getMetaModel();
		// TODO: we definately need an algorithm to tell frontend and backend render settings apart. this way of template switching is just plain evil.
		$objView = MetaModelRenderSettingsFactory::byId($objMetaModel, $this->metamodelview);
		if ($objView)
		{
			$objTemplate = new MetaModelTemplate('be_' . $objView->get('template'));
		} else {
			// fallback to default.
			$objTemplate = new MetaModelTemplate('be_metamodel_full');
		}

		$arrFields = array_keys($GLOBALS['TL_DCA'][$objMetaModel->getTableName()]['fields']);
		$arrClearFields = array_diff(array_keys($objMetaModel->getAttributes()), $arrFields);

		if ($objMetaModel->hasVariants() && !$objNativeItem->isVariantBase())
		{
			$arrClearFields = array_merge($arrClearFields, array_keys($objMetaModel->getInVariantAttributes()));
		}

		// create a clone to have a seperate copy of the object as we are going to manipulate it here.
		$objView = clone $objView;
		// loop over all attributes and remove those from rendering that are not desired.
		foreach ($arrClearFields as $strAttrName)
		{
			$objView->setSetting($strAttrName, NULL);
		}

		$this->prepareTemplate($objTemplate, $objNativeItem, $objView);

		return $objTemplate->parse('html5', true);
	}


	/**
	 * Call the customer label callback
	 *
	 * @param InterfaceGeneralModel $objModelRow The Model to "draw".
	 *
	 * @param string                $mixedLabel
	 *
	 * @param array                 $args
	 *
	 * @return string the label string.
	 */
	public function labelCallback(InterfaceGeneralModel $objModelRow, $mixedLabel, $args)
	{
		if (!($objModelRow instanceof GeneralModelMetaModel))
		{
			throw new Exception('ERROR: incompatible object passed to GeneralCallbackMetaModel::labelCallback()');
		}
		// Load DCA
		$arrDCA      = $this->objDC->getDCA();
		$arrCallback = $arrDCA['list']['label']['label_callback'];

		// Check Callback
		if (is_array($arrCallback) && count($arrCallback))
		{
			return parent::labelCallback($objModelRow, $mixedLabel, $args);
		}
		return $this->drawItem($objModelRow);
	}

	/**
	 * Call the child record callback
	 *
	 * @param InterfaceGeneralModel $objModel
	 * @return string|null
	 */
	public function childRecordCallback(InterfaceGeneralModel $objModel)
	{
		if (!($objModel instanceof GeneralModelMetaModel))
		{
			throw new Exception('ERROR: incompatible object passed to GeneralCallbackMetaModel::labelCallback()');
		}
		// Load DCA
		$arrDCA      = $this->objDC->getDCA();
		$arrCallback = $arrDCA['list']['sorting']['child_record_callback'];

		// Check Callback
		if (is_array($arrCallback) && count($arrCallback))
		{
			return parent::childRecordCallback($objModel);
		}
		return $this->drawItem($objModel);
	}

	public function parseRootPaletteCallback($arrPalette)
	{
		$objModelRow = $this->objDC->getCurrentModel();

		if ($objModelRow)
		{
			$objNativeItem = $objModelRow->getItem();
			$objMetaModel = $objNativeItem->getMetaModel();
			if ($objMetaModel->hasVariants() && !$objNativeItem->isVariantBase())
			{
				// loop over all attributes and remove those from rendering that are invariant.
				foreach ($objMetaModel->getInVariantAttributes() as $strAttrName => $objAttribute)
				{
					foreach ($arrPalette as $intKey => $arrPaletteDef)
					{
						if (($intPos = array_search($strAttrName, $arrPaletteDef['palette'])) !== false)
						{
							unset($arrPalette[$intKey]['palette'][$intPos]);
						}
					}
				}
			}
		}
		$arrPalette = parent::parseRootPaletteCallback($arrPalette);

		return $arrPalette;
	}
}

?>
