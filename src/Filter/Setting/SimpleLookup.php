<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2022 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2022 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Filter\Setting;

use Contao\StringUtil;
use MetaModels\Attribute\IAttribute;
use MetaModels\FrontendIntegration\FrontendFilterOptions;
use MetaModels\IItem;
use MetaModels\Filter\IFilter;
use MetaModels\Filter\Rules\StaticIdList as FilterRuleStaticIdList;
use MetaModels\Filter\Rules\SearchAttribute as FilterRuleSimpleLookup;
use MetaModels\Render\Setting\ICollection as IRenderSettings;

/**
 * Filter setting implementation performing a search for a value on a
 * configured attribute.
 */
class SimpleLookup extends Simple
{
    /**
     * Retrieve the filter parameter name to react on.
     *
     * @return string|null
     */
    protected function getParamName()
    {
        if ($this->get('urlparam')) {
            return $this->get('urlparam');
        }

        $objAttribute = $this->getFilteredAttribute();
        if ($objAttribute) {
            return $objAttribute->getColName();
        }

        return null;
    }

    /**
     * Return the label to use.
     *
     * @return string|null
     */
    protected function getLabel()
    {
        if (null === ($attribute = $this->getFilteredAttribute())) {
            return null;
        }
        if ($label = $this->get('label')) {
            return $label;
        }

        return $attribute->getName();
    }

    /**
     * Determine if this filter setting shall return all matches if no url param has been specified.
     *
     * @return bool true if all matches shall be returned, false otherwise.
     */
    public function allowEmpty()
    {
        return (bool) $this->get('allow_empty');
    }

    /**
     * Internal helper function for descendant classes to retrieve the options.
     *
     * @param IAttribute    $objAttribute The attribute to search.
     *
     * @param string[]|null $arrIds       The Id list of items for which to retrieve the options.
     *
     * @param array         $arrCount     If non null, the amount of matches will get returned.
     *
     * @return array
     */
    protected function getParameterFilterOptions($objAttribute, $arrIds, &$arrCount = null)
    {
        $arrOptions = $objAttribute->getFilterOptions(
            $this->get('onlypossible') ? $arrIds : null,
            (bool) $this->get('onlyused'),
            $arrCount
        );

        // Remove empty values.
        foreach ($arrOptions as $mixOptionKey => $mixOptions) {
            // Remove html/php tags.
            $mixOptions = strip_tags($mixOptions);
            $mixOptions = trim($mixOptions);

            if ($mixOptions === '' || $mixOptions === null) {
                unset($arrOptions[$mixOptionKey]);
            }
        }

        switch ($this->get('apply_sorting')) {
            case 'natsort_asc':
                \natcasesort($arrOptions);
                break;
            case 'natsort_desc':
                \rsort($arrOptions, SORT_NATURAL | SORT_FLAG_CASE);
                break;
        }

        return $arrOptions;
    }

    /**
     * Determine if this filter setting shall be available for frontend filter widget generating.
     *
     * @return bool true if available, false otherwise.
     */
    public function enableFEFilterWidget()
    {
        return (bool) $this->get('fe_widget');
    }

    /**
     * {@inheritdoc}
     */
    public function prepareRules(IFilter $objFilter, $arrFilterUrl)
    {
        $objMetaModel = $this->getMetaModel();
        $objAttribute = $this->getFilteredAttribute();
        $strParam     = $this->getParamName();

        if ($objAttribute && $strParam) {
            if ($arrFilterValue = $this->determineFilterValue($arrFilterUrl, $strParam)) {
                if ($objMetaModel->isTranslated() && $this->get('all_langs')) {
                    $arrLanguages = $objMetaModel->getAvailableLanguages();
                } else {
                    $arrLanguages = array($objMetaModel->getActiveLanguage());
                }
                $objFilterRule = new FilterRuleSimpleLookup($objAttribute, $arrFilterValue, $arrLanguages);
                $objFilter->addFilterRule($objFilterRule);
                return;
            }

            // We found an attribute but no match in URL. So ignore this filter setting if allow_empty is set.
            if ($this->allowEmpty()) {
                $objFilter->addFilterRule(new FilterRuleStaticIdList(null));
                return;
            }
        }
        // Either no attribute found or no match in url, do not return anything.
        $objFilter->addFilterRule(new FilterRuleStaticIdList(array()));
    }

    /**
     * {@inheritdoc}
     */
    public function generateFilterUrlFrom(IItem $objItem, IRenderSettings $objRenderSetting)
    {
        if ($attribute = $this->getFilteredAttribute()) {
            return [$this->getParamName() => $attribute->getFilterUrlValue($objItem->get($attribute->getColName()))];
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return ($strParamName = $this->getParamName()) ? array($strParamName) : array();
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function getParameterDCA()
    {
        // If defined as static, return nothing as not to be manipulated via editors.
        if (!$this->get('predef_param')) {
            return array();
        }

        $objAttribute = $this->getFilteredAttribute();
        $arrOptions   = $objAttribute->getFilterOptions(null, (bool) $this->get('onlyused'));

        return array(
            $this->getParamName() => array
            (
                'label'   => array(
                    sprintf(
                        $GLOBALS['TL_LANG']['MSC']['metamodel_filtersettings_parameter']['simplelookup'][0],
                        $objAttribute->getName()
                    ),
                    sprintf(
                        $GLOBALS['TL_LANG']['MSC']['metamodel_filtersettings_parameter']['simplelookup'][1],
                        $objAttribute->getName()
                    )
                ),
                'inputType'    => 'select',
                'options' => $arrOptions,
                'eval' => array(
                    'includeBlankOption' => true,
                    'style' => 'min-width:450px;width:450px;margin-bottom:16px;margin-right:10px;'
                )
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParameterFilterNames()
    {
        if (($label = $this->getLabel()) && ($paramName = $this->getParamName())) {
            return array(
                $paramName => $label
            );
        }

        return array();
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function getParameterFilterWidgets(
        $arrIds,
        $arrFilterUrl,
        $arrJumpTo,
        FrontendFilterOptions $objFrontendFilterOptions
    ) {
        // If defined as static, return nothing as not to be manipulated via editors.
        if (!$this->enableFEFilterWidget()) {
            return array();
        }

        if (!($attribute = $this->getFilteredAttribute())) {
            return array();
        }

        $GLOBALS['MM_FILTER_PARAMS'][] = $this->getParamName();

        $cssID = StringUtil::deserialize($this->get('cssID'), true);

        $arrCount  = array();
        $arrWidget = [
            'label'     => [
                $this->getLabel(),
                'GET: ' . $this->getParamName()
            ],
            'inputType' => 'select',
            'options'   => $this->getParameterFilterOptions($attribute, $arrIds, $arrCount),
            'count'     => $arrCount,
            'showCount' => $objFrontendFilterOptions->isShowCountValues(),
            'eval'      => [
                'includeBlankOption' => (
                $this->get('blankoption') && !$objFrontendFilterOptions->isHideClearFilter()
                    ? true
                    : false
                ),
                'blankOptionLabel'   => $this->get('label_as_blankoption')
                    ? $this->getLabel()
                    : $GLOBALS['TL_LANG']['metamodels_frontendfilter']['do_not_filter'],
                'colname'            => $attribute->getColname(),
                'urlparam'           => $this->getParamName(),
                'onlyused'           => $this->get('onlyused'),
                'onlypossible'       => $this->get('onlypossible'),
                'template'           => $this->get('template'),
                'hide_label'         => $this->get('hide_label'),
                'cssID'              => !empty($cssID[0]) ? ' id="' . $cssID[0] . '"' : '',
                'class'              => !empty($cssID[1]) ? ' ' . $cssID[1] : '',
            ]
        ];

        return array
        (
            $this->getParamName() => $this->prepareFrontendFilterWidget(
                $arrWidget,
                $arrFilterUrl,
                $arrJumpTo,
                $objFrontendFilterOptions
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getReferencedAttributes()
    {
        if ($attribute = $this->getFilteredAttribute()) {
            return array($attribute->getColName());
        }

        return array();
    }

    /**
     * Retrieve the attribute we are filtering on.
     *
     * @return IAttribute|null
     */
    protected function getFilteredAttribute()
    {
        if (!($attributeId = $this->get('attr_id'))) {
            return null;
        }

        if ($attribute = $this->getMetaModel()->getAttributeById($attributeId)) {
            return $attribute;
        }

        return null;
    }

    /**
     * Determine the filter value from the passed values.
     *
     * @param array  $filterValues The filter values.
     * @param string $valueName    The parameter name to obtain.
     *
     * @return mixed|null
     */
    private function determineFilterValue($filterValues, $valueName)
    {
        if (!isset($filterValues[$valueName]) && $this->get('defaultid')) {
            return $this->get('defaultid');
        }

        return $filterValues[$valueName];
    }
}
