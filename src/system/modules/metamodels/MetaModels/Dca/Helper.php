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

use MetaModels\IMetaModel;

/**
 * This class is used as base class from dca handler classes for various callbacks.
 *
 * @package    MetaModels
 * @subpackage Backend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class Helper extends \Backend
{

	public function decodeLangArray($varValue, IMetaModel $objMetaModel)
	{
		$arrLangValues = deserialize($varValue);
		if (!$objMetaModel->isTranslated())
		{
			// if we have an array, return the first value and exit, if not an array, return the value itself.
			return is_array($arrLangValues) ? $arrLangValues[key($arrLangValues)] : $arrLangValues;
		}

		// sort like in MetaModel definition
		$arrLanguages = $objMetaModel->getAvailableLanguages();
		$arrOutput    = array();

		if ($arrLanguages)
		{
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

	public function encodeLangArray($varValue, IMetaModel $objMetaModel)
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
	 * Create a widget for naming contexts. Use the language and translation information from the MetaModel.
	 *
	 * @param IMetaModel $objMetaModel  The MetaModel
	 *
	 * @param string     $strLabelLang  The label to use for the language indicator
	 *
	 * @param string     $strLabelValue The label to use for the input field.
	 *
	 * @param bool       $blnIsTextarea If true, the widget will become a textarea, false otherwise.
	 *
	 * @param array      $arrValues     The values for the widget, needed to highlight the fallback language.
	 *
	 * @return array
	 */
	public function makeMultiColumnName(IMetaModel $objMetaModel, &$strLabelLang, &$strLabelValue, $blnIsTextarea, $arrValues)
	{
		if(!$objMetaModel->isTranslated())
		{
			$arrWidget = array
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

			// Ensure we have the values present.
			if (empty($arrValues))
			{
				foreach ((array)$objMetaModel->getAvailableLanguages() as $strLangCode)
				{
					$arrValues[$strLangCode] = '';
				}
			}

			$arrRowClasses = array();
			foreach (array_keys(deserialize($arrValues)) as $strLangcode)
			{
				$arrRowClasses[] = ($strLangcode == $objMetaModel->getFallbackLanguage()) ? 'fallback_language' : 'normal_language';
			}

			$arrWidget = array
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
								'rowClasses'        => $arrRowClasses,
								'valign'            => 'center',
								'style'             => 'min-width:75px;display:block;'
							)
						),
						'value' => array
						(
							'label'                 => &$strLabelValue,
							'exclude'               => true,
							'inputType'             => $blnIsTextarea ? 'textarea' : 'text',
							'eval'                  => array
							(
								'rowClasses'        => $arrRowClasses,
								'style'             => 'width:400px;',
								'rows'              => 3
							)
						),
					)
				)
			);
		}
		return $arrWidget;
	}

	/**
	 * Fetch the template group for the detail view of the current MetaModel module.
	 *
	 * @param string $strBase The base for the templates to retrieve.
	 *
	 * @return array
	 */
	public function getTemplatesForBase($strBase)
	{
		$arrTemplates = array();
		foreach ($this->getTemplateGroup($strBase) as $strTemplate)
		{
			$arrTemplates[$strTemplate] = sprintf($GLOBALS['TL_LANG']['MSC']['template_in_theme'], $strTemplate, $GLOBALS['TL_LANG']['MSC']['no_theme']);
		}

		$objThemes = \Database::getInstance()->prepare('SELECT id,name FROM tl_theme')->execute();

		while ($objThemes->next())
		{
			foreach ($this->getTemplateGroup($strBase, $objThemes->id) as $strTemplate)
			{
				if (!array_key_exists($strTemplate, $arrTemplates))
				{
					$arrTemplates[$strTemplate] = sprintf($GLOBALS['TL_LANG']['MSC']['template_in_theme'], $strTemplate, $objThemes->name);
				}
			}
		}
		ksort($arrTemplates);

		return array_unique($arrTemplates);
	}

	/**
	 * Get a list with all allowed attributes for meta description.
	 * 
	 * @param DataContainer $objDC
	 * 
	 * @return array A list with all found attributes.
	 */
	 public function getAttributeNamesForModel($intMetaModel, $arrTypes = array())
	{
		$arrAttributeNames = array();

		$objMetaModel = MetaModelFactory::byId($intMetaModel);
		if ($objMetaModel)
		{
			foreach ($objMetaModel->getAttributes() as $objAttribute)
			{
				if (empty($arrTypes) || in_array($objAttribute->get('type'), $arrTypes))
				{
					$arrAttributeNames[$objAttribute->getColName()] = $objAttribute->getName() . ' [' . $objAttribute->getColName() . ']';
				}
			}
		}

		return $arrAttributeNames;
	}
}

