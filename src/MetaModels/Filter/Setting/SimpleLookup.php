<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2015 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  2012-2015 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\Filter\Setting;

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

        $objAttribute = $this->getMetaModel()->getAttributeById($this->get('attr_id'));
        if ($objAttribute) {
            return $objAttribute->getColName();
        }

        return null;
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
    protected function getParameterFilterOptions($objAttribute, $arrIds, &$arrCount = null, $forceToUseArrIds = false)
    {
        
        $arrOptions = $objAttribute->getFilterOptions(
            ($this->get('onlypossible') || $forceToUseArrIds) ? $arrIds : null,
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

        return $arrOptions;
    }

    /**
     * Determine if this filter setting shall be available for frontend filter widget generating.
     *
     * @return bool true if available, false otherwise.
     */
    public function enableFEFilterWidget()
    {
        // TODO: better use a seperate checkbox or the like? For the moment, this has to be overridden by sub classes.
        return (bool) $this->get('predef_param');
    }

    /**
     * {@inheritdoc}
     */
    public function prepareRules(IFilter $objFilter, $arrFilterUrl)
    {
        $objMetaModel = $this->getMetaModel();
        $objAttribute = $objMetaModel->getAttributeById($this->get('attr_id'));
        $strParam     = $this->getParamName();

        if ($objAttribute && $strParam) {
            $arrFilterValue = isset($arrFilterUrl[$strParam]) ? $arrFilterUrl[$strParam] : null;

            if (!$arrFilterValue && $this->get('defaultid')) {
                $arrFilterValue = $this->get('defaultid');
            }

            if ($arrFilterValue) {
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
        $objAttribute = $this->getMetaModel()->getAttributeById($this->get('attr_id'));
        if ($objAttribute) {
            // TODO: shall we omit returning of empty values?
            $strResult = $objAttribute->getFilterUrlValue($objItem->get($objAttribute->getColName()));
            return array($this->getParamName() => $strResult);
        }

        return array();
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

        $objAttribute = $this->getMetaModel()->getAttributeById($this->get('attr_id'));
        $arrOptions   = $objAttribute->getFilterOptions(null, true);

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
        if ($strParamName = $this->getParamName()) {
            $objAttribute = $this->getMetaModel()->getAttributeById($this->get('attr_id'));

            if (!$objAttribute) {
                return array();
            }

            return array(
                $strParamName => ($this->get('label') ? $this->get('label') : $objAttribute->getName())
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
        FrontendFilterOptions $objFrontendFilterOptions,
        $arrCustom = array()
    ) {
        
        /* $arrCustom array with settings which makes the function more flexible to use in template
            the following keys can be set: objAttribute, forceToUseArrIds, strParamName, 
            [Widget > eval: includeBlankOption, onlyused, onlypossible, template] (only eval for now)
        */
        $strParam           = (!isset($arrCustom['strParamName'])) ? $this->getParamName() : $arrCustom['strParamName'];
        $forceToUseArrIds   = (!isset($arrCustom['forceToUseArrIds'])) ? false : $arrCustom['forceToUseArrIds'];
        
        // If defined as static, return nothing as not to be manipulated via editors.
        if (!$this->enableFEFilterWidget()) {
            return array();
        }

        $objAttribute = (!isset($arrCustom['objAttribute'])) ? $this->getMetaModel()->getAttributeById($this->get('attr_id')) : $arrCustom['objAttribute'];

        if (!$objAttribute) {
            return array();
        }

        $GLOBALS['MM_FILTER_PARAMS'][] = $strParam;

        $arrCount  = array();
        $arrWidget = array(
            'label'     => array(
                // TODO: make this multilingual.
                ($this->get('label') ? $this->get('label') : $objAttribute->getName()),
                'GET: ' . $strParam
            ),
            'inputType' => 'select',
            'options'   => $this->getParameterFilterOptions($objAttribute, $arrIds, $arrCount, $forceToUseArrIds),
            'count'     => $arrCount,
            'showCount' => $objFrontendFilterOptions->isShowCountValues(),
            'eval'      => array
            (
                'includeBlankOption' => (
                        $this->get('blankoption') && !$objFrontendFilterOptions->isHideClearFilter()
                        ? true
                        : false
                    ),
                'blankOptionLabel'   => &$GLOBALS['TL_LANG']['metamodels_frontendfilter']['do_not_filter'],
                'colname'            => $objAttribute->getColname(),
                'urlparam'           => $strParam,
                'onlyused'           => $this->get('onlyused'),
                'onlypossible'       => $this->get('onlypossible'),
                'template'           => $this->get('template'),
            )
        );

        if (isset($arrCustom['Widget']['eval'])&&is_array($arrCustom['Widget']['eval'])) {
            $arrWidget['eval'] = array_merge($arrWidget['eval'],$arrCustom['Widget']['eval']);
        }

        return array
        (
            $strParam => $this->prepareFrontendFilterWidget(
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
        if ($this->get('attr_id')) {
            $attribute = $this->getMetaModel()->getAttributeById($this->get('attr_id'));
            if ($attribute instanceof IAttribute) {
                return array($attribute->getColName());
            }
        }

        return array();
    }
}
