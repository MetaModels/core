<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2026 The MetaModels team.
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
 * @copyright  2012-2026 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Filter\Setting;

use Contao\StringUtil;
use MetaModels\Attribute\IAliasConverter;
use MetaModels\Attribute\IAttribute;
use MetaModels\Filter\IFilter;
use MetaModels\Filter\Rules\SearchAttribute as FilterRuleSimpleLookup;
use MetaModels\Filter\Rules\StaticIdList as FilterRuleStaticIdList;
use MetaModels\Filter\Rules\StaticIdList;
use MetaModels\FrontendIntegration\FrontendFilterOptions;
use MetaModels\IItem;
use MetaModels\IMetaModel;
use MetaModels\ITranslatedMetaModel;
use MetaModels\Render\Setting\ICollection as IRenderSettings;

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
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function getParameterFilterOptions($objAttribute, $arrIds, &$arrCount = null)
    {
        $labelAttribute = null;
        if ((!$objAttribute instanceof IAliasConverter) && $this->get('label_attr_id')) {
            $labelAttribute = $this->getMetaModel()->getAttributeById((int) $this->get('label_attr_id'));
        }

        if ($labelAttribute) {
            $arrOptions = $this->getLabelAttributeValuesByOptionsList(
                $objAttribute,
                $labelAttribute,
                ((bool) $this->get('onlypossible')) ? $arrIds : null
            );
        } else {
            $arrOptions = $objAttribute->getFilterOptions(
                $this->get('onlypossible') ? $arrIds : null,
                (bool) $this->get('onlyused'),
                $arrCount
            );
        }

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
    #[\Override]
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
    #[\Override]
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
    #[\Override]
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
    #[\Override]
    public function getParameterDCA()
    {
        // If defined as static, return nothing as not to be manipulated via editors.
        if (!$this->get('predef_param')) {
            return [];
        }

        $objAttribute = $this->getFilteredAttribute();
        assert($objAttribute instanceof IAttribute);

        $arrOptions = $objAttribute->getFilterOptions(null, (bool) $this->get('onlyused'));

        return [
            (string) $this->getParamName() => [
                'label'     => $this->translator->trans(
                    'simplelookup.label',
                    ['%id%' => $objAttribute->getName()],
                    'metamodels_filter'
                ),
                'inputType' => 'select',
                'options'   => $arrOptions,
                // Maps a value stored in any language onto the one of the language currently shown - see below.
                'aliasMap'  => $this->buildAliasMap($objAttribute, $arrOptions),
                'eval'      => [
                    'includeBlankOption' => true,
                    'style'              => 'min-width:450px;width:450px;margin-bottom:16px;margin-right:10px;'
                ]
            ]
        ];
    }

    /**
     * Build a map from the value of every language onto the value of the language currently shown.
     *
     * The stored value of a predefined parameter is whatever the option list held when it was picked. With a
     * translated attribute behind the filter that value is language dependent: an editor working in German stores
     * "abteilung-b", the same option reads "division-b" in English. The stored value then matches no option and the
     * select falls back to showing the raw string as an unknown option - the editor cannot tell what is configured,
     * and touching the field loses the setting.
     *
     * The map lets the caller translate the stored value before the widget looks for a matching option, so the
     * equivalent option of the current language gets selected instead. Both values point at the same record, so
     * filtering is unaffected either way.
     *
     * @param IAttribute            $attribute The attribute behind this filter setting.
     * @param array<string, string> $options   The options as built for the language currently shown.
     *
     * @return array<string, string>
     */
    private function buildAliasMap(IAttribute $attribute, array $options): array
    {
        if (!$attribute instanceof IAliasConverter || [] === $options) {
            return [];
        }

        $languages = $this->determineAllLanguages($this->getMetaModel());
        if ([] === $languages) {
            return [];
        }

        $current = $languages[0];
        if (($metaModel = $this->getMetaModel()) instanceof ITranslatedMetaModel) {
            $current = $metaModel->getLanguage();
        }

        $map = [];
        foreach (\array_keys($options) as $alias) {
            $map += $this->aliasesOfOption($attribute, (string) $alias, (string) $current, $languages);
        }

        return $map;
    }

    /**
     * Collect the values of every other language pointing at the same record as the passed one.
     *
     * @param IAliasConverter $attribute The attribute behind this filter setting.
     * @param string          $alias     The value as held by the option list of the language currently shown.
     * @param string          $current   The language currently shown.
     * @param list<string>    $languages Every language of the MetaModel.
     *
     * @return array<string, string>
     */
    private function aliasesOfOption(
        IAliasConverter $attribute,
        string $alias,
        string $current,
        array $languages
    ): array {
        if (null === ($id = $attribute->getIdForAlias($alias, $current))) {
            return [];
        }

        $map = [];
        foreach ($languages as $language) {
            $foreign = $attribute->getAliasForId($id, $language);
            if (null !== $foreign && '' !== $foreign && $foreign !== $alias) {
                $map[$foreign] = $alias;
            }
        }

        return $map;
    }

    /**
     * Retrieve every language of the MetaModel, regardless of the "all_langs" setting.
     *
     * @param IMetaModel $metaModel The MetaModel.
     *
     * @return list<string>
     */
    private function determineAllLanguages(IMetaModel $metaModel): array
    {
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

    /**
     * {@inheritdoc}
     */
    #[\Override]
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
    #[\Override]
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
                    : $this->translator->trans('do_not_filter', [], 'metamodels_filter'),
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
    #[\Override]
    public function getReferencedAttributes()
    {
        $result = [];
        if ($attribute = $this->getFilteredAttribute()) {
            $result[] = $attribute->getColName();
        }

        if ((!$attribute instanceof IAliasConverter) && $this->get('label_attr_id')) {
            if ($labelAttribute = $this->getMetaModel()->getAttributeById((int) $this->get('label_attr_id'))) {
                $result[] = $labelAttribute->getColName();
            }
        }

        return $result;
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

    /**
     * @param null|list<string> $idList
     *
     * @return array<string, string>
     */
    private function getLabelAttributeValuesByOptionsList(
        IAttribute $keyAttribute,
        IAttribute $labelAttribute,
        ?array $idList
    ): array {
        $model        = $this->getMetaModel();
        $filter       = $model->getEmptyFilter();
        $keyColName   = $keyAttribute->getColName();
        $labelColName = $labelAttribute->getColName();

        if (null !== $idList) {
            $filter->addFilterRule(new StaticIdList($idList));
        }

        $items = $model->findByFilter(
            $filter,
            arrAttrOnly: [$keyColName, $labelColName]
        );

        $parsed = $items->parseAll('text');
        $result = [];
        foreach ($parsed as $item) {
            $result[$item['text'][$keyColName]] = $item['text'][$labelColName];
        }

        return $result;
    }
}
