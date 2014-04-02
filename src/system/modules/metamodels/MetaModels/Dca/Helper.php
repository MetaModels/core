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

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use MetaModels\IMetaModel;
use MetaModels\Factory as MetaModelFactory;

/**
 * This class is used as base class from dca handler classes for various callbacks.
 *
 * @package    MetaModels
 * @subpackage Backend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class Helper
{
	public static function decodeLangArray($varValue, IMetaModel $objMetaModel)
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

	public static function encodeLangArray($varValue, IMetaModel $objMetaModel)
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
	 *
	 * @deprecated Use prepareLanguageAwareWidget from BuildWidgetEvent.
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
	 * Create a widget for naming contexts. Use the language and translation information from the MetaModel.
	 *
	 * @param EnvironmentInterface $environment   The environment.
	 *
	 * @param PropertyInterface    $property      The property.
	 *
	 * @param IMetaModel           $metaModel     The MetaModel.
	 *
	 * @param string               $languageLabel The label to use for the language indicator.
	 *
	 * @param string               $valueLabel    The label to use for the input field.
	 *
	 * @param bool                 $isTextArea    If true, the widget will become a textarea, false otherwise.
	 *
	 * @param array                $arrValues     The values for the widget, needed to highlight the fallback language.
	 *
	 * @return void
	 */
	public static function prepareLanguageAwareWidget(
		EnvironmentInterface $environment,
		PropertyInterface $property,
		IMetaModel $metaModel,
		$languageLabel,
		$valueLabel,
		$isTextArea,
		$arrValues)
	{
		if (!$metaModel->isTranslated())
		{
			$extra = $property->getExtra();

			$extra['tl_class'] .= 'w50';

			$property
				->setWidgetType('text')
				->setExtra($extra);

			return;
		}

		$fallback = $metaModel->getFallbackLanguage();

		$languages = array();
		foreach ((array)$metaModel->getAvailableLanguages() as $langCode)
		{
			$languages[$langCode] = $environment->getTranslator()->translate('LNG.' . $langCode, 'languages');
		}
		asort($languages);

		// Ensure we have the values present.
		if (empty($arrValues))
		{
			foreach (array_keys($languages) as $langCode)
			{
				$arrValues[$langCode] = '';
			}
		}

		$rowClasses = array();
		foreach (array_keys($arrValues) as $langCode)
		{
			$rowClasses[] = ($langCode == $fallback) ? 'fallback_language' : 'normal_language';
		}

		$extra = $property->getExtra();

		$extra['minCount']       =
		$extra['maxCount']       = count($languages);
		$extra['disableSorting'] = true;
		$extra['tl_class']       = 'clr';
		$extra['columnFields']   = array(
			'langcode' => array
			(
				'label'                 => $languageLabel,
				'exclude'               => true,
				'inputType'             => 'justtextoption',
				'options'               => $languages,
				'eval'                  => array
				(
					'rowClasses'        => $rowClasses,
					'valign'            => 'center',
					'style'             => 'min-width:75px;display:block;'
				)
			),
			'value' => array
			(
				'label'                 => $valueLabel,
				'exclude'               => true,
				'inputType'             => $isTextArea ? 'textarea' : 'text',
				'eval'                  => array
				(
					'rowClasses'        => $rowClasses,
					'style'             => 'width:400px;',
					'rows'              => 3
				)
			),
		);

		$property
			->setWidgetType('multiColumnWizard')
			->setExtra($extra);
	}

	/**
	 * Fetch the template group for the detail view of the current MetaModel module.
	 *
	 * @param string $strBase The base for the templates to retrieve.
	 *
	 * @return array
	 */
	public static function getTemplatesForBase($strBase)
	{
		$arrTemplates = array();
		foreach (\Backend::getTemplateGroup($strBase) as $strTemplate)
		{
			$arrTemplates[$strTemplate] = sprintf(
				$GLOBALS['TL_LANG']['MSC']['template_in_theme'],
				$strTemplate,
				$GLOBALS['TL_LANG']['MSC']['no_theme']
			);
		}

		$objThemes = \Database::getInstance()->prepare('SELECT id,name FROM tl_theme')->execute();

		while ($objThemes->next())
		{
			foreach (\Backend::getTemplateGroup($strBase, $objThemes->id) as $strTemplate)
			{
				if (!array_key_exists($strTemplate, $arrTemplates))
				{
					$arrTemplates[$strTemplate] = sprintf(
						$GLOBALS['TL_LANG']['MSC']['template_in_theme'],
						$strTemplate,
						$objThemes->name
					);
				}
			}
		}
		ksort($arrTemplates);

		return array_unique($arrTemplates);
	}

	/**
	 * Get a list with all allowed attributes for meta description.
	 *
	 * If the optional parameter arrTypes is not given, all attributes will be retrieved.
	 *
	 * @param int      $intMetaModel The id of the MetaModel from which the attributes shall be retrieved from.
	 *
	 * @param string[] $arrTypes     The attribute type names that shall be retrieved (optional).
	 *
	 * @return array A list with all found attributes.
	 */
	public static function getAttributeNamesForModel($intMetaModel, $arrTypes = array())
	{
		$arrAttributeNames = array();

		$objMetaModel = MetaModelFactory::byId($intMetaModel);
		if ($objMetaModel)
		{
			foreach ($objMetaModel->getAttributes() as $objAttribute)
			{
				if (empty($arrTypes) || in_array($objAttribute->get('type'), $arrTypes))
				{
					$arrAttributeNames[$objAttribute->getColName()] =
						sprintf('%s [%s]',
							$objAttribute->getName(),
							$objAttribute->getColName()
						);
				}
			}
		}

		return $arrAttributeNames;
	}

	/**
	 * Search all files with the given file extension below the given path.
	 *
	 * @param string $folder    The folder to scan.
	 *
	 * @param string $extension The file extension.
	 *
	 * @return array
	 */
	public static function searchFiles($folder, $extension)
	{
		$scanResult = array();
		$result     = array();
		// Check if we have a file or folder.
		if (is_dir(TL_ROOT . '/' . $folder))
		{
			$scanResult = scan(TL_ROOT . '/' . $folder);
		}

		// Run each value.
		foreach ($scanResult as $value)
		{
			if (!is_file(TL_ROOT . '/' . $folder . '/' . $value))
			{
				$result += self::searchFiles($folder . '/' . $value, $extension);
			}
			else
			{
				if (preg_match('/'.$extension.'$/i', $value))
				{
					$result[$folder][$folder . '/' . $value] = $value;
				}
			}
		}

		return $result;
	}
}
