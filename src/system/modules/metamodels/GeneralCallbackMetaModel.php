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
	 * when rendered via a template, this returns the values to be stored in the template.
	 */
	protected function prepareTemplate(MetaModelTemplate $objTemplate, IMetaModelItem $objItem, $objSettings = null)
	{
		$objTemplate->settings  = $objSettings;
		$objTemplate->item      = $objItem;
		$objTemplate->data      = $objItem->parseValue('html5', $objSettings);
	}

	/**
	 * Call the customer label callback
	 *
	 * @param InterfaceGeneralModel $objModelRow
	 * @param string $mixedLabel
	 * @param array $args
	 * @return string
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
		if (is_array($arrCallback))
		{
			return parent::labelCallback($objModelRow, $mixedLabel, $args);
		}

		$objNativeItem = $objModelRow->getItem();
		$objMetaModel = $objNativeItem->getMetaModel();

		$arrValues = array_merge(array('id', 'pid', 'sorting', 'tstamp', 'varbase', 'vargroup'), array_keys($objMetaModel->getAttributes()));

		$objView = new MetaModelRenderSettings();
		// TODO: allow definition of a custom backend render view here.
		if (!$this->metamodelview)
		{
			$objView->createDefaultFrom($objMetaModel);
		}

		$objTemplate = new MetaModelTemplate('be_metamodel_full');
		$this->prepareTemplate($objTemplate, $objNativeItem, $objView);
		return $objTemplate->parse('html5', true);
	}
}

?>
