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

	protected $intViewId = null;

	/**
	 * Set the DC
	 *
	 * @param DC_General $objDC
	 */
	public function setDC($objDC)
	{
		$this->objDC = $objDC;
		parent::setDC($objDC);

		$arrDCA = $this->objDC->getDCA();
		$this->intViewId = $arrDCA['config']['metamodel_view'];
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

		$objTemplate->items     = new MetaModelItems(array($objItem));
		$objTemplate->view      = $objSettings;
		$objTemplate->data      = array($objItem->parseValue('html5', $objSettings));
	}

	/**
	 * Render an item in the backend (list/tree)
	 *
	 * @param InterfaceGeneralModel $objModelRow the model to be drawn
	 *
	 * @return void
	 */
	protected function drawItem(InterfaceGeneralModel $objModelRow)
	{
		$objNativeItem = $objModelRow->getItem();
		$objMetaModel = $objNativeItem->getMetaModel();

		$objView = MetaModelRenderSettingsFactory::byId($objMetaModel, $this->intViewId);
		if ($objView)
		{
			$objTemplate = new MetaModelTemplate($objView->get('template'));
		} else {
			return 'No rendersetting defined.';
		}

		if ($objMetaModel->hasVariants() && !$objNativeItem->isVariantBase())
		{
			// create a clone to have a seperate copy of the object as we are going to manipulate it here.
			$objView = clone $objView;
			// loop over all attributes and remove those from rendering that are not desired.
			foreach (array_keys($objMetaModel->getInVariantAttributes()) as $strAttrName)
			{
				$objView->setSetting($strAttrName, NULL);
			}
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

	/**
	 * Callback when an item is opened in edit view.
	 * This method filters invariant attributes from the palette, when editing a variant.
	 *
	 * @param string the input palette
	 *
	 * @return string the filtered palette.
	 */
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
				foreach (array_keys($objMetaModel->getInVariantAttributes()) as $strAttrName)
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
