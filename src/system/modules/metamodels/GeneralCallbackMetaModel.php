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

		$strValues = '';
		foreach ($arrValues as $strColumn)
		{
			$objAttribute = $objMetaModel->getAttribute($strColumn);

			if($objAttribute)
			{
				$arrResult = $objNativeItem->parseAttribute($strColumn, 'html5');
				if ($arrResult['html5'])
				{
					$strLabel = $arrResult['html5'];
				} else {
					$strLabel = $arrResult['text'];
				}
				$strValues .= sprintf('<div><em>%s:</em> %s</div>', $objAttribute->getName(), $strLabel);
			}
			else
			{
				$strValue = $objNativeItem->get($strColumn);
				$strLabel = $GLOBALS['TL_LANG']['tl_metamodel_item'][$strColumn]?$GLOBALS['TL_LANG']['tl_metamodel_item'][$strColumn][0]:$strColumn;
				$strValues .= sprintf('<div><em>%s:</em> %s</div>', $strLabel, $strValue);
			}
		}
		// TODO: use templating in here.
		return '<div class="field_type block">'.$strValues.'</div>';
	}
}

?>
