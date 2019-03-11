<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2019 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Christopher Boelter <c.boelter@cogizz.de>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Oliver Hoff <oliver@hofff.com>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Attribute;

use MetaModels\IMetaModel;
use MetaModels\Render\Setting\ISimple as ISimpleRenderSetting;
use MetaModels\Render\Setting\Simple;
use MetaModels\Render\Template;

/**
 * This is the main MetaModels-attribute base class.
 * To create a MetaModelAttribute instance, use the {@link MetaModelAttributeFactory}
 * This class is the reference implementation for {@link IMetaModelAttribute}.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
abstract class Base implements IAttribute
{
    /**
     * The MetaModel instance this object belongs to.
     *
     * @var IMetaModel
     */
    private $metaModel;

    /**
     * The meta information of this attribute.
     *
     * @var array
     */
    protected $arrData = array();

    /**
     * Instantiate an MetaModel attribute.
     *
     * Note that you should not use this directly but use the factory classes to instantiate attributes.
     *
     * @param IMetaModel $objMetaModel The MetaModel instance this attribute belongs to.
     *
     * @param array      $arrData      The information array, for attribute information, refer to documentation of
     *                                 table tl_metamodel_attribute and documentation of the certain attribute classes
     *                                 for information what values are understood.
     */
    public function __construct(IMetaModel $objMetaModel, $arrData = array())
    {
        // Meta information.
        foreach (array_intersect($this->getAttributeSettingNames(), array_keys($arrData)) as $strSettingName) {
            $this->set($strSettingName, $arrData[$strSettingName]);
        }
        $this->metaModel = $objMetaModel;
    }

    /**
     * Helper method for generating a list of "?" to be used in a Contao query.
     *
     * Usage:
     *  $params = array('1', '2', 'potential SQL injection string');
     *  $result = $database
     *       ->prepare('SELECT * FROM tl_something WHERE id IN (' . $this->parameterMask($params) . ')')
     *       ->execute($params);
     *
     * @param array $parameters The parameters to be used in a query.
     *
     * @return string
     */
    protected function parameterMask($parameters)
    {
        return implode(',', array_fill(0, count($parameters), '?'));
    }

    /**
     * Retrieve the human readable name (or title) from the attribute.
     *
     * If the MetaModel is translated, the currently active language is used,
     * with properly falling back to the defined fallback language.
     *
     * @return string the human readable name
     */
    public function getName()
    {
        if (is_array($this->arrData['name'])) {
            return $this->getLangValue($this->get('name')) ?: $this->getColName();
        }
        return $this->arrData['name'] ?: $this->getColName();
    }

    /**
     * This extracts the value for the given language from the given language array.
     *
     * If the language is not contained within the value array, the fallback language from the parenting
     * {@link IMetaModel} instance is tried as well.
     *
     * @param array  $arrValues   The array holding all language values in the form array('langcode' => $varValue).
     *
     * @param string $strLangCode The language code of the language to fetch. Optional, if not given,
     *                            $GLOBALS['TL_LANGUAGE'] is used.
     *
     * @return mixed|null the value for the given language or the fallback language, NULL if neither is present.
     */
    protected function getLangValue($arrValues, $strLangCode = null)
    {
        if (!($this->getMetaModel()->isTranslated() && is_array($arrValues))) {
            return $arrValues;
        }

        if ($strLangCode === null) {
            return $this->getLangValue($arrValues, $this->getMetaModel()->getActiveLanguage());
        }

        if (array_key_exists($strLangCode, $arrValues)) {
            return $arrValues[$strLangCode];
        }

        // Language code not set, use fallback.
        return $arrValues[$this->getMetaModel()->getFallbackLanguage()];
    }

    /**
     * Hook additional attribute formatter that want to format the value.
     *
     * @param array   $arrBaseFormatted The current result array. The keys "raw" and "text" are always populated.
     *
     * @param array   $arrRowData       The Raw values from the database.
     *
     * @param string  $strOutputFormat  The output format to use.
     *
     * @param ISimpleRenderSetting $objSettings      The output format settings.
     *
     * @return mixed
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function hookAdditionalFormatters($arrBaseFormatted, $arrRowData, $strOutputFormat, $objSettings)
    {
        $arrResult = $arrBaseFormatted;

        if (isset($GLOBALS['METAMODEL_HOOKS']['parseValue']) && is_array($GLOBALS['METAMODEL_HOOKS']['parseValue'])) {
            foreach ($GLOBALS['METAMODEL_HOOKS']['parseValue'] as $callback) {
                list($strClass, $strMethod) = $callback;

                $objCallback = (in_array('getInstance', get_class_methods($strClass)))
                    ? call_user_func(array($strClass, 'getInstance'))
                    : new $strClass();

                $arrResult = $objCallback->$strMethod(
                    $this,
                    $arrResult,
                    $arrRowData,
                    $strOutputFormat,
                    $objSettings
                );
            }
        }

        return $arrResult;
    }

    /**
     * When rendered via a template, this populates the template with values.
     *
     * @param Template $objTemplate The Template instance to populate.
     *
     * @param array    $arrRowData  The row data for the current item.
     *
     * @param ISimpleRenderSetting  $objSettings The render settings to use for this attribute.
     *
     * @return void
     */
    protected function prepareTemplate(Template $objTemplate, $arrRowData, $objSettings)
    {
        $objTemplate->setData(array(
            'attribute'        => $this,
            'settings'         => $objSettings,
            'row'              => $arrRowData,
            'raw'              => $arrRowData[$this->getColName()],
            'additional_class' => $objSettings->get('additional_class')
                ? ' ' . $objSettings->get('additional_class')
                : ''
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getColName()
    {
        return $this->arrData['colname'];
    }

    /**
     * {@inheritdoc}
     */
    public function getMetaModel()
    {
        return $this->metaModel;
    }

    /**
     * {@inheritdoc}
     */
    public function get($strKey)
    {
        return isset($this->arrData[$strKey]) ? $this->arrData[$strKey] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function set($strKey, $varValue)
    {
        if (in_array($strKey, $this->getAttributeSettingNames())) {
            if (!is_array($varValue) && (substr($varValue, 0, 2) == 'a:')) {
                $unSerialized = unserialize($varValue);
            }

            if (isset($unSerialized) && is_array($unSerialized)) {
                $this->arrData[$strKey] = $unSerialized;
            } else {
                $this->arrData[$strKey] = $varValue;
            }
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function handleMetaChange($strMetaName, $varNewValue)
    {
        // By default we accept any change of meta information.
        $this->set($strMetaName, $varNewValue);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function destroyAUX()
    {
        // No-op.
    }

    /**
     * {@inheritdoc}
     */
    public function initializeAUX()
    {
        // No-op.
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeSettingNames()
    {
        return array(
            // Settings originating from tl_metamodel_attribute.
            'id', 'pid', 'tstamp', 'name', 'description', 'type', 'colname', 'isvariant',
            // Settings originating from tl_metamodel_dcasetting.
            'tl_class', 'readonly');
    }

    /**
     * Set the language strings.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function setLanguageStrings()
    {
        // Only overwrite the language if not already set.
        if (empty($GLOBALS['TL_LANG'][$this->getMetaModel()->getTableName()][$this->getColName()])) {
            $GLOBALS['TL_LANG'][$this->getMetaModel()->getTableName()][$this->getColName()] = array
            (
                $this->getLangValue($this->get('name')),
                $this->getLangValue($this->get('description')),
            );
        }
    }

    /**
     * Retrieve the base definition by the user from dca_config.
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function getBaseDefinition()
    {
        $this->setLanguageStrings();
        $tableName  = $this->getMetaModel()->getTableName();
        $definition = array();
        if (isset($GLOBALS['TL_DCA'][$tableName]['fields'][$this->getColName()])) {
            $definition = $GLOBALS['TL_DCA'][$tableName]['fields'][$this->getColName()];
        }

        return array_replace_recursive(
            array
            (
                'label' => &$GLOBALS['TL_LANG'][$tableName][$this->getColName()],
                'eval'  => array()
            ),
            $definition
        );
    }

    /**
     * Check if a value may be overridden.
     *
     * @param string $name The name of the value.
     *
     * @return bool
     */
    private function isAllowedValue($name)
    {
        // Load the allowed overrides only once.
        $allowedSettings = array_flip($this->getAttributeSettingNames());

        return isset($allowedSettings[$name]);
    }

    /**
     * Extract an override value.
     *
     * @param string $name      The name of the value.
     *
     * @param array  $overrides The overrides containing the values to be overridden.
     *
     * @return mixed
     */
    protected function getOverrideValue($name, $overrides)
    {
        if ($this->isAllowedValue($name) && isset($overrides[$name])) {
            return $overrides[$name];
        }

        return $this->get($name);
    }

    /**
     * Extract an override value.
     *
     * @param array $fieldDefinition The field definition.
     *
     * @param array $overrides       The overrides containing the values to be overridden.
     *
     * @return array
     */
    private function setBaseEval($fieldDefinition, $overrides)
    {
        if ($this->isAllowedValue('isunique')) {
            $fieldDefinition['eval']['unique'] = (bool) $this->getOverrideValue('isunique', $overrides);
        }

        foreach (array(
            'tl_class',
            'mandatory',
            'alwaysSave',
            'chosen',
            'allowHtml',
            'preserveTags',
            'decodeEntities',
            'rte',
            'rows',
            'cols',
            'spaceToUnderscore',
            'includeBlankOption',
            'submitOnChange',
            'readonly'
        ) as $name) {
            if (empty($fieldDefinition['eval'][$name])
                && ($value = $this->getOverrideValue($name, $overrides))
            ) {
                $fieldDefinition['eval'][$name] = $value;
            }
        }

        // If we have unique, enforce mandatory.
        if (!empty($fieldDefinition['eval']['unique'])) {
            $fieldDefinition['eval']['mandatory'] = true;
        }

        return $fieldDefinition;
    }

    /**
     * {@inheritdoc}
     */
    public function getFieldDefinition($arrOverrides = array())
    {
        $arrFieldDef = $this->setBaseEval($this->getBaseDefinition(), $arrOverrides);

        if ($this->isAllowedValue('trailingSlash')) {
            $trailingSlash = $this->getOverrideValue('trailingSlash', $arrOverrides);
            if ($trailingSlash != 2) {
                $arrFieldDef['eval']['trailingSlash'] = (bool) $arrOverrides['trailingSlash'];
            }
        }

        // Panel conditions.
        if ($this->isAllowedValue('filterable')) {
            $arrFieldDef['filter'] = (bool) $this->getOverrideValue('filterable', $arrOverrides);
        }
        if ($this->isAllowedValue('searchable')) {
            $arrFieldDef['search'] = (bool) $this->getOverrideValue('searchable', $arrOverrides);
        }

        return $arrFieldDef;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     *
     * @deprecated Use DataDefinition builders in DC_General 2.0.0
     */
    public function getItemDCA($arrOverrides = array())
    {
        $arrReturn = array
        (
            'fields' => array_merge(
                array($this->getColName() => $this->getFieldDefinition($arrOverrides)),
                (array) $GLOBALS['TL_DCA'][$this->getMetaModel()->getTableName()]['fields'][$this->getColName()]
            ),
        );

        return $arrReturn;
    }

    /**
     * {@inheritdoc}
     */
    public function valueToWidget($varValue)
    {
        return $varValue;
    }

    /**
     * {@inheritdoc}
     */
    public function widgetToValue($varValue, $itemId)
    {
        return $varValue;
    }

    /**
     * {@inheritdoc}
     */
    public function getDefaultRenderSettings()
    {
        $objSetting = new Simple(
            array
            (
                'template' => 'mm_attr_' . $this->get('type')
            )
        );
        return $objSetting;
    }

    /**
     * {@inheritdoc}
     */
    public function parseValue($arrRowData, $strOutputFormat = 'text', $objSettings = null)
    {
        $arrResult = array(
            'raw' => $arrRowData[$this->getColName()],
        );

        /** @var ISimpleRenderSetting $objSettings */
        if ($objSettings && $objSettings->get('template')) {
            $strTemplate = $objSettings->get('template');

            $objTemplate = new Template($strTemplate);

            $this->prepareTemplate($objTemplate, $arrRowData, $objSettings);

            // Now the desired format.
            if ($strValue = $objTemplate->parse($strOutputFormat, false)) {
                $arrResult[$strOutputFormat] = $strValue;
            }

            // Text rendering is mandatory, try with the current setting,
            // upon exception, try again with the default settings, as the template name might have changed.
            // if this fails again, we are definately out of luck and bail the exception.
            try {
                $arrResult['text'] = $objTemplate->parse('text', true);
            } catch (\Exception $e) {
                $objSettingsFallback = $this->getDefaultRenderSettings()->setParent($objSettings->getParent());

                $objTemplate = new Template($objSettingsFallback->get('template'));
                $this->prepareTemplate($objTemplate, $arrRowData, $objSettingsFallback);

                $arrResult['text'] = $objTemplate->parse('text', true);
            }
        } else {
            // Text rendering is mandatory, therefore render using default render settings.
            $arrResult = $this->parseValue($arrRowData, 'text', $this->getDefaultRenderSettings());
        }

        // HOOK: apply additional formatters to attribute.
        $arrResult = $this->hookAdditionalFormatters($arrResult, $arrRowData, $strOutputFormat, $objSettings);

        return $arrResult;
    }

    /**
     * {@inheritdoc}
     *
     * This base implementation returns the value itself.
     */
    public function getFilterUrlValue($varValue)
    {
        // We are parsing as text here as this was the way before this method was implemented. See #216.
        $arrResult = $this->parseValue(array($this->getColName() => $varValue), 'text');

        return urlencode($arrResult['text']);
    }

    /**
     * {@inheritdoc}
     */
    public function sortIds($idList, $strDirection)
    {
        // Base implementation, do not perform any sorting.
        return $idList;
    }

    /**
     * {@inheritdoc}
     * Base implementation, do not perform any search;
     */
    public function searchFor($strPattern)
    {
        return array();
    }

    /**
     * {@inheritdoc}
     *
     * Base implementation, return empty array.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function filterGreaterThan($varValue, $blnInclusive = false)
    {
        return array();
    }

    /**
     * {@inheritdoc}
     *
     * Base implementation, return empty array.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function filterLessThan($varValue, $blnInclusive = false)
    {
        return array();
    }

    /**
     * {@inheritdoc}
     * Base implementation, merge the result of filterLessThan() and filterGreaterThan().
     */
    public function filterNotEqual($varValue)
    {
        return array_merge($this->filterLessThan($varValue), $this->filterGreaterThan($varValue));
    }

    /**
     * {@inheritdoc}
     * Base implementation, do not perform anything.
     */
    public function modelSaved($objItem)
    {
        // No-op.
    }
}
