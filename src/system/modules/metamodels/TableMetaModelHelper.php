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
 * This class is used from tl_metamodel for various callbacks.
 *
 * @package	   MetaModels
 * @subpackage Backend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class TableMetaModelHelper extends Backend
{

	public function decodeLangArray($varValue, IMetaModel $objMetaModel)
	{
		$arrLangValues = deserialize($varValue);
		if (!$objMetaModel->isTranslated())
		{
			// if we have an array, return the first value and exit, if not an array, return the value itself.
			return is_array($arrLangValues) ? $arrLangValues[key($arrLangValues)] : $arrLangValues;
		}

		// sort like in metamodel definition
		$arrLanguages = $objMetaModel->getAvailableLanguages();
		if ($arrLanguages)
		{
			$arrOutput = array();
			foreach($arrLanguages as $strLangCode)
			{
				if (is_array($arrLangValues))
				{
					$varSubValue = $arrLangValues[$strLangCode];
				} else {
					$varSubValue = $arrLangValues;
				}

				if (is_array($varSubValue))
				{
					$arrOutput[] = array_merge($varSubValue, array('langcode' => $strLangCode));
				} else {
					$arrOutput[] = array('langcode' => $strLangCode, 'value' => $varSubValue);
				}
			}
		}
		return serialize($arrOutput);
	}

	public function encodeLangArray($varValue, MetaModel $objMetaModel)
	{
		// not translated, make it a plain string.
		if (!$objMetaModel->isTranslated())
		{
			return $varValue;
		}
		$arrLangValues = deserialize($varValue);
		$arrOutput = array();
		foreach ($arrLangValues as $varSubValue)
		{
			$strLangCode = $varSubValue['langcode'];
			unset($varSubValue['langcode']);
			if (count($varSubValue) > 1)
			{
				$arrOutput[$strLangCode] = $varSubValue;
			} else {
				$arrKeys = array_keys($varSubValue);
				$arrOutput[$strLangCode] = $varSubValue[$arrKeys[0]];
			}
		}
		return serialize($arrOutput);
	}

	/**
	 * create a widget for naming contexts. Use the language and translation information from the MetaModel.
	 *
	 * @param IMetaModel $objMetaModel  the metamodel
	 *
	 * @param string     $strLabelLang  the label to use for the language indicator
	 *
	 * @param string     $strLabelValue the label to use for the input field.
	 *
	 * @param bool       $blnIsTextarea if true, the widget will become a textarea, false otherwise.
	 */
	public function makeMultiColumnName(IMetaModel $objMetaModel, &$strLabelLang, &$strLabelValue, $blnIsTextarea = false)
	{
		$arrwidget = array();
		if(!$objMetaModel->isTranslated())
		{
			$arrwidget = array
			(
				'inputType'            => 'text',
				'eval'                 => array
				(
					'tl_class'         => 'w50',
				)
			);
		} else {
			$this->loadLanguageFile('languages');
			$arrLanguages = array();
			foreach((array)$objMetaModel->getAvailableLanguages() as $strLangCode)
			{
				$arrLanguages[$strLangCode] = $GLOBALS['TL_LANG']['LNG'][$strLangCode];
			}
			asort($arrLanguages);

			$arrwidget = array
			(
				'inputType'        => 'multiColumnWizard',
				'eval'             => array
				(
					'minCount' => count($arrLanguages),
					'maxCount' => count($arrLanguages),
					'disableSorting' => true,
					'tl_class' => 'clr',
					'columnFields' => array
					(
						'langcode' => array
						(
							'label'                 => &$strLabelLang,
							'exclude'               => true,
							'inputType'             => 'justtextoption',
							'options'               => $arrLanguages,
							'eval'                  => array
							(
								'valign'            => 'center'
							)
						),
						'value' => array
						(
							'label'                 => &$strLabelValue,
							'exclude'               => true,
							'inputType'             => $blnIsTextarea ? 'textarea' : 'text',
							'eval'                     => array
							(
								'style'             => 'width:400px;',
								'rows'		    => 3
							)
						),
					)
				)
			);
		}
		return $arrwidget;
	}
}

?>