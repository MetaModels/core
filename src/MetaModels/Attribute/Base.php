<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Attribute;

use MetaModels\IMetaModel;
use MetaModels\Render\Setting\ISimple;
use MetaModels\Render\Setting\Simple;
use MetaModels\Render\Template;

/**
 * This is the main MetaModels-attribute base class.
 * To create a MetaModelAttribute instance, use the {@link MetaModelAttributeFactory}
 * This class is the reference implementation for {@link IMetaModelAttribute}.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
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
        foreach ($this->getAttributeSettingNames() as $strSettingName) {
            if (isset($arrData[$strSettingName])) {
                $this->set($strSettingName, $arrData[$strSettingName]);
            }
        }
        $this->metaModel = $objMetaModel;
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
            return $this->getLangValue($this->get('name'));
        }
        return $this->arrData['name'];
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
     * @param ISimple $objSettings      The output format settings.
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
                    $arrBaseFormatted,
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
     * @param ISimple  $objSettings The render settings to use for this attribute.
     *
     * @return void
     */
    protected function prepareTemplate(Template $objTemplate, $arrRowData, $objSettings = null)
    {
        $objTemplate->attribute        = $this;
        $objTemplate->settings         = $objSettings;
        $objTemplate->row              = $arrRowData;
        $objTemplate->raw              = $arrRowData[$this->getColName()];
        $objTemplate->additional_class = $objSettings->get('additional_class')
            ? ' ' . $objSettings->get('additional_class')
            : '';
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

            if (isset ($unSerialized) && is_array($unSerialized)) {
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
            'id', 'pid', 'sorting', 'tstamp', 'name', 'description', 'type', 'colname', 'isvariant',
            // Settings originating from tl_metamodel_dcasetting.
            'tl_class', 'readonly');
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function getFieldDefinition($arrOverrides = array())
    {
        $strTableName = $this->getMetaModel()->getTableName();
        // Only overwrite the language if not already set.
        if (empty($GLOBALS['TL_LANG'][$strTableName][$this->getColName()])) {
            $GLOBALS['TL_LANG'][$strTableName][$this->getColName()] = array
            (
                $this->getLangValue($this->get('name')),
                $this->getLangValue($this->get('description')),
            );
        }

        $arrFieldDef = array();
        if (isset($GLOBALS['TL_DCA'][$strTableName]['fields'][$this->getColName()])) {
            $arrFieldDef = $GLOBALS['TL_DCA'][$strTableName]['fields'][$this->getColName()];
        }

        $arrFieldDef = array_replace_recursive(
            array
            (
                'label' => &$GLOBALS['TL_LANG'][$strTableName][$this->getColName()],
                'eval'  => array()
            ),
            $arrFieldDef
        );

        $arrSettingNames = $this->getAttributeSettingNames();

        $arrFieldDef['eval']['unique']     = $this->get('isunique') && in_array('isunique', $arrSettingNames);
        $arrFieldDef['eval']['mandatory']  = (!empty($arrFieldDef['eval']['unique']))
            || ($this->get('mandatory') && in_array('mandatory', $arrSettingNames));
        $arrFieldDef['eval']['alwaysSave'] = (!empty($arrFieldDef['eval']['alwaysSave']))
            || ($this->get('alwaysSave') && in_array('alwaysSave', $arrSettingNames));

        foreach (array
        (
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
        ) as $strEval) {
            if (in_array($strEval, $arrSettingNames) && $arrOverrides[$strEval]) {
                $arrFieldDef['eval'][$strEval] = $arrOverrides[$strEval];
            }
        }

        if (in_array('trailingSlash', $arrSettingNames) && ($arrOverrides['trailingSlash'] != 2)) {
            $arrFieldDef['eval']['trailingSlash'] = (bool) $arrOverrides['trailingSlash'];
        }

        // Sorting flag override.
        if (in_array('flag', $arrSettingNames) && $arrOverrides['flag']) {
            $arrFieldDef['flag'] = $arrOverrides['flag'];
        }
        // Panel conditions.
        if (in_array('filterable', $arrSettingNames) && $arrOverrides['filterable']) {
            $arrFieldDef['filter'] = true;
        }
        if (in_array('searchable', $arrSettingNames) && $arrOverrides['searchable']) {
            $arrFieldDef['search'] = true;
        }
        if (in_array('sortable', $arrSettingNames) && $arrOverrides['sortable']) {
            $arrFieldDef['sorting'] = true;
        }
        return $arrFieldDef;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
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
        // Add sorting fields.
        if (in_array('sortable', $this->getAttributeSettingNames()) && $arrOverrides['sortable']) {
            $arrReturn['list']['sorting']['fields'][] = $this->getColName();
        }
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
    public function widgetToValue($varValue, $intId)
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
    public function sortIds($arrIds, $strDirection)
    {
        // Base implementation, do not perform any sorting.
        return $arrIds;
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
