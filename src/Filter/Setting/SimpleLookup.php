<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2024 The MetaModels team.
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
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Filter\Setting;

use Contao\StringUtil;
use Contao\System;
use MetaModels\Attribute\IAttribute;
use MetaModels\FrontendIntegration\FrontendFilterOptions;
use MetaModels\IItem;
use MetaModels\Filter\IFilter;
use MetaModels\Filter\Rules\StaticIdList as FilterRuleStaticIdList;
use MetaModels\Filter\Rules\SearchAttribute as FilterRuleSimpleLookup;
use MetaModels\IMetaModel;
use MetaModels\ITranslatedMetaModel;
use MetaModels\Render\Setting\ICollection as IRenderSettings;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Filter setting implementation performing a search for a value on a configured attribute.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity) - revisit after removing bc layer.
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
            $paramName = $objAttribute->getColName();
            // Work around #1505.
            if (\in_array($paramName, ['language', 'items'], true)) {
                $paramName .= '__';
            }

            return $paramName;
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
     * @param IAttribute        $objAttribute The attribute to search.
     * @param list<string>|null $arrIds       The Id list of items for which to retrieve the options.
     * @param array             $arrCount     If non-null, the amount of matches will get returned.
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
            $mixOptions = \strip_tags($mixOptions);
            $mixOptions = \trim($mixOptions);

            if ($mixOptions === '') {
                unset($arrOptions[$mixOptionKey]);
            }
        }

        // Sorting option for filter items.
        switch ($this->get('apply_sorting')) {
            case 'natsort_asc':
                \natcasesort($arrOptions);
                break;
            case 'natsort_desc':
                \natcasesort($arrOptions);
                $arrOptions = \array_reverse($arrOptions, true);
                break;
            default:
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

        if ($objAttribute && null !== $strParam) {
            if (null !== ($arrFilterValue = $this->determineFilterValue($arrFilterUrl, $strParam))) {
                $objFilterRule = new FilterRuleSimpleLookup(
                    $objAttribute,
                    $arrFilterValue,
                    $this->determineLanguages($objMetaModel)
                );
                $objFilter->addFilterRule($objFilterRule);

                return;
            }

            // We found an attribute but no match in URL. So ignore this filter setting if allow_empty is set.
            if ($this->allowEmpty()) {
                return;
            }
        }
        // Either no attribute found or no match in url, do not return anything.
        $objFilter->addFilterRule(new FilterRuleStaticIdList([]));
    }

    /**
     * {@inheritdoc}
     */
    public function generateFilterUrlFrom(IItem $objItem, IRenderSettings $objRenderSetting)
    {
        if ($attribute = $this->getFilteredAttribute()) {
            return [
                (string) $this->getParamName() => $attribute->getFilterUrlValue($objItem->get($attribute->getColName()))
            ];
        }

        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return (null !== ($strParamName = $this->getParamName())) ? [$strParamName] : [];
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
            return [];
        }

        $objAttribute = $this->getFilteredAttribute();
        assert($objAttribute instanceof IAttribute);

        $arrOptions = $objAttribute->getFilterOptions(null, (bool) $this->get('onlyused'));

        $translator = System::getContainer()->get('translator');
        assert($translator instanceof TranslatorInterface);

        return [
            (string) $this->getParamName() => [
                'label'     => $translator->trans(
                    'simplelookup.label',
                    ['%id%' => $objAttribute->getName()],
                    'metamodels_filter'
                ),
                'inputType' => 'select',
                'options'   => $arrOptions,
                'eval'      => [
                    'includeBlankOption' => true,
                    'style'              => 'min-width:450px;width:450px;margin-bottom:16px;margin-right:10px;'
                ]
            ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getParameterFilterNames()
    {
        if (null !== ($label = $this->getLabel()) && null !== ($paramName = $this->getParamName())) {
            return [
                $paramName => $label
            ];
        }

        return [];
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function getParameterFilterWidgets(
        $arrIds,
        $arrFilterUrl,
        $arrJumpTo,
        FrontendFilterOptions $objFrontendFilterOptions
    ) {
        // If defined as static, return nothing as not to be manipulated via editors.
        if (!$this->enableFEFilterWidget()) {
            return [];
        }

        if (!($attribute = $this->getFilteredAttribute())) {
            return [];
        }

        $paramName = $this->getParamName();
        assert(\is_string($paramName));

        $GLOBALS['MM_FILTER_PARAMS'][] = $paramName;

        $cssID = StringUtil::deserialize($this->get('cssID'), true);

        $arrCount  = array();
        $arrWidget = [
            'label'     => [
                $this->getLabel(),
                'GET: ' . $paramName
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
                    : $GLOBALS['TL_LANG']['metamodels_frontendfilter']['do_not_filter'] ?? '',
                'colname'            => $attribute->getColname(),
                'urlparam'           => $paramName,
                'onlyused'           => $this->get('onlyused'),
                'onlypossible'       => $this->get('onlypossible'),
                'template'           => $this->get('template'),
                'hide_label'         => $this->get('hide_label'),
                'cssID'              => !empty($cssID[0]) ? ' id="' . $cssID[0] . '"' : '',
                'class'              => !empty($cssID[1]) ? ' ' . $cssID[1] : '',
            ]
        ];

        return [
            $paramName => $this->prepareFrontendFilterWidget(
                $arrWidget,
                $arrFilterUrl,
                $arrJumpTo,
                $objFrontendFilterOptions
            )
        ];
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

        if ($attribute = $this->getMetaModel()->getAttributeById((int) $attributeId)) {
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

        return $filterValues[$valueName] ?? null;
    }

    /**
     * @return list<non-empty-string>
     */
    private function determineLanguages(IMetaModel $metaModel): array
    {
        if ((bool) $this->get('all_langs')) {
            if ($metaModel instanceof ITranslatedMetaModel) {
                return \array_values(\array_filter($metaModel->getLanguages()));
            }
            /**
             * @psalm-suppress DeprecatedMethod
             * @psalm-suppress TooManyArguments
             */
            if ($metaModel->isTranslated(false)) {
                /** @psalm-suppress DeprecatedMethod */
                return \array_values(\array_filter($metaModel->getAvailableLanguages() ?? []));
            }

            return [];
        }

        if ($metaModel instanceof ITranslatedMetaModel) {
            return \array_filter([$metaModel->getLanguage()]);
        }

        /**
         * @psalm-suppress DeprecatedMethod
         * @psalm-suppress TooManyArguments
         */
        if ($metaModel->isTranslated(false)) {
            /** @psalm-suppress DeprecatedMethod */
            return \array_filter([$metaModel->getActiveLanguage()]);
        }

        return [];
    }
}
