<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2020 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Oliver Hoff <oliver@hofff.com>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Cliff Parnitzky <github@cliff-parnitzky.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2020 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Dca;

use Contao\StringUtil;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\Translator\TranslatorInterface;
use MetaModels\IMetaModel;

/**
 * This class is used as base class from dca handler classes for various callbacks.
 */
class Helper
{
    /**
     * Decode a language array.
     *
     * @param array|string $varValue     The value to decode.
     *
     * @param IMetaModel   $objMetaModel The MetaModel holding the languages.
     *
     * @return string
     */
    public static function decodeLangArray($varValue, IMetaModel $objMetaModel)
    {
        $arrLangValues = StringUtil::deserialize($varValue);
        if (!$objMetaModel->isTranslated()) {
            // If we have an array, return the first value and exit, if not an array, return the value itself.
            return is_array($arrLangValues)
                ? serialize($arrLangValues[key($arrLangValues)])
                : serialize($arrLangValues);
        }

        // Sort like in MetaModel definition.
        $arrLanguages = $objMetaModel->getAvailableLanguages();
        $arrOutput    = [];

        if ($arrLanguages) {
            foreach ($arrLanguages as $strLangCode) {
                if (is_array($arrLangValues)) {
                    $varSubValue = $arrLangValues[$strLangCode];
                } else {
                    $varSubValue = $arrLangValues;
                }

                if (is_array($varSubValue)) {
                    $arrOutput[] = array_merge($varSubValue, array('langcode' => $strLangCode));
                } else {
                    $arrOutput[] = array('langcode' => $strLangCode, 'value' => $varSubValue);
                }
            }
        }
        return serialize($arrOutput);
    }

    /**
     * Decode a language array.
     *
     * @param array|string $varValue     The value to decode.
     *
     * @param IMetaModel   $objMetaModel The MetaModel holding the languages.
     *
     * @return string
     */
    public static function encodeLangArray($varValue, IMetaModel $objMetaModel)
    {
        // Not translated, make it a plain string.
        if (!$objMetaModel->isTranslated()) {
            return $varValue;
        }
        $arrLangValues = StringUtil::deserialize($varValue);
        $arrOutput     = [];
        foreach ($arrLangValues as $varSubValue) {
            $strLangCode = $varSubValue['langcode'];
            unset($varSubValue['langcode']);
            if (count($varSubValue) > 1) {
                $arrOutput[$strLangCode] = $varSubValue;
            } else {
                $arrKeys                 = array_keys($varSubValue);
                $arrOutput[$strLangCode] = $varSubValue[$arrKeys[0]];
            }
        }
        return serialize($arrOutput);
    }

    /**
     * Extract all languages from the MetaModel and return them as array.
     *
     * @param IMetaModel          $metaModel  The MetaModel to extract the languages from.
     *
     * @param TranslatorInterface $translator The translator to use.
     *
     * @return \string[]
     */
    private static function buildLanguageArray(IMetaModel $metaModel, TranslatorInterface $translator)
    {
        $languages = [];
        foreach ((array) $metaModel->getAvailableLanguages() as $langCode) {
            $languages[$langCode] = $translator->translate('LNG.' . $langCode, 'languages');
        }

        return $languages;
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
        $arrValues
    ) {
        if (!$metaModel->isTranslated()) {
            $extra = $property->getExtra();

            $extra['tl_class'] .= empty($extra['tl_class']) ? 'w50' : ' w50';

            $property
                ->setWidgetType('text')
                ->setExtra($extra);

            return;
        }

        $fallback  = $metaModel->getFallbackLanguage();
        $languages = self::buildLanguageArray($metaModel, $environment->getTranslator());

        // Ensure we have values for all languages present.
        if (array_diff_key($languages, $arrValues)) {
            foreach (array_keys($languages) as $langCode) {
                $arrValues[$langCode] = '';
            }
        }

        $rowClasses = [];
        foreach (array_keys($arrValues) as $langCode) {
            $rowClasses[] = ($langCode == $fallback) ? 'fallback_language' : 'normal_language';
        }

        $extra = $property->getExtra();

        $extra['minCount']       =
        $extra['maxCount'] = count($languages);
        $extra['disableSorting'] = true;
        $extra['tl_class']       = 'clr w50';
        $extra['columnFields']   = [
            'langcode' => [
                'label'     => $languageLabel,
                'exclude'   => true,
                'inputType' => 'justtextoption',
                'options'   => $languages,
                'eval'      => [
                    'rowClasses' => $rowClasses,
                    'valign'     => 'center',
                    'style'      => 'min-width:50px;display:block;'
                ]
            ],
            'value'    => [
                'label'     => $valueLabel,
                'exclude'   => true,
                'inputType' => $isTextArea ? 'textarea' : 'text',
                'eval'      => [
                    'rowClasses' => $rowClasses,
                    'style'      => 'width:100%;',
                    'rows'       => 3
                ]
            ],
        ];

        $property
            ->setWidgetType('multiColumnWizard')
            ->setExtra($extra);
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
        $scanResult = [];
        $result     = [];
        // Check if we have a file or folder.
        if (is_dir(TL_ROOT . '/' . $folder)) {
            $scanResult = scan(TL_ROOT . '/' . $folder);
        }

        // Run each value.
        foreach ($scanResult as $value) {
            if (!is_file(TL_ROOT . '/' . $folder . '/' . $value)) {
                $result += self::searchFiles($folder . '/' . $value, $extension);
            } else {
                if (preg_match('/' . $extension . '$/i', $value)) {
                    $result[$folder][$folder . '/' . $value] = $value;
                }
            }
        }

        return $result;
    }
}
