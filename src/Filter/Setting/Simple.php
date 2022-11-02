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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2022 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Filter\Setting;

use Contao\StringUtil;
use Contao\System;
use Contao\Widget;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Widget\GetAttributesFromDcaEvent;
use MetaModels\Filter\FilterUrl;
use MetaModels\Filter\FilterUrlBuilder;
use MetaModels\FrontendIntegration\FrontendFilterOptions;
use MetaModels\IItem;
use MetaModels\IMetaModelsServiceContainer;
use MetaModels\Render\Setting\ICollection as IRenderSettings;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Base class for filter setting implementation.
 */
abstract class Simple implements ISimple
{
    /**
     * The parenting filter setting container this setting belongs to.
     *
     * @var ICollection
     */
    private $collection = null;

    /**
     * The attributes of this filter setting.
     *
     * @var array
     */
    private $data = [];

    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * The filter URL builder.
     *
     * @var FilterUrlBuilder
     */
    private $filterUrlBuilder;

    /**
     * Constructor - initialize the object and store the parameters.
     *
     * @param ICollection                   $collection       The parenting filter settings object.
     * @param array                         $data             The attributes for this filter setting.
     * @param EventDispatcherInterface|null $eventDispatcher  The event dispatcher.
     * @param FilterUrlBuilder|null         $filterUrlBuilder The filter URL builder.
     */
    public function __construct(
        $collection,
        $data,
        EventDispatcherInterface $eventDispatcher = null,
        FilterUrlBuilder $filterUrlBuilder = null
    ) {
        $this->collection = $collection;
        $this->data       = $data;

        if (null === $eventDispatcher) {
            // @codingStandardsIgnoreStart
            @trigger_error(
                'Event dispatcher is not passed as constructor argument.',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd

            $eventDispatcher = System::getContainer()->get('event_dispatcher');
        }

        if (null === $filterUrlBuilder) {
            // @codingStandardsIgnoreStart
            @trigger_error(
                'FilterUrlBuilder is missing. It has to be passed in the constructor. Fallback will be dropped.',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
            $filterUrlBuilder = System::getContainer()->get('metamodels.filter_url');
        }

        $this->eventDispatcher  = $eventDispatcher;
        $this->filterUrlBuilder = $filterUrlBuilder;
    }

    /**
     * Retrieve the service container.
     *
     * @return IMetaModelsServiceContainer
     *
     * @deprecated Inject needed services via constructor or setter.
     */
    public function getServiceContainer()
    {
        // @codingStandardsIgnoreStart
        @trigger_error(
            '"' . __METHOD__ . '" is deprecated and will get removed.',
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd
        return $this->getMetaModel()->getServiceContainer();
    }

    /**
     * Retrieve the event dispatcher.
     *
     * @return EventDispatcherInterface
     *
     * @deprecated Inject the event dispatcher via constructor or setter.
     */
    public function getEventDispatcher()
    {
        // @codingStandardsIgnoreStart
        @trigger_error(
            '"' . __METHOD__ . '" is deprecated and will get removed.',
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd

        return $this->eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function get($strKey)
    {
        return isset($this->data[$strKey]) ? $this->data[$strKey] : null;
    }

    /**
     * Get the parenting collection instance.
     *
     * @return ICollection The parent.
     */
    public function getFilterSettings()
    {
        return $this->collection;
    }

    /**
     * Return the MetaModel instance this filter setting relates to.
     *
     * @return \MetaModels\IMetaModel
     */
    public function getMetaModel()
    {
        return $this->getFilterSettings()->getMetaModel();
    }

    /**
     * Returns if the given value is currently active in the given filter settings.
     *
     * @param array  $arrWidget    The widget information.
     *
     * @param array  $arrFilterUrl The filter url parameters to use.
     *
     * @param string $strKeyOption The option value to determine.
     *
     * @return bool  true If the given value is mentioned in the given filter parameters, false otherwise.
     */
    protected function isActiveFrontendFilterValue($arrWidget, $arrFilterUrl, $strKeyOption)
    {
        // Special case, the "empty" value first.
        if (empty($strKeyOption) && !isset($arrFilterUrl[$arrWidget['eval']['urlparam']])) {
            return true;
        }
        if (isset($arrFilterUrl[$arrWidget['eval']['urlparam']])) {
            return ($arrFilterUrl[$arrWidget['eval']['urlparam']] == $strKeyOption);
        }

        if ($defaultValue = $this->get('defaultid')) {
            return $strKeyOption == $defaultValue;
        }

        return false;
    }

    /**
     * Translate an option to a proper url value to be used in the filter url.
     *
     * Overriding this method allows to toggle the value in the url in addition to extract
     * or inject a value into an "combined" filter url parameter (like tags i.e.)
     *
     * @param array  $arrWidget    The widget information.
     *
     * @param array  $arrFilterUrl The filter url parameters to use.
     *
     * @param string $strKeyOption The option value to determine.
     *
     * @return string The filter url value to use for link gererating.
     */
    protected function getFrontendFilterValue($arrWidget, $arrFilterUrl, $strKeyOption)
    {
        // Toggle if active.
        if ($this->isActiveFrontendFilterValue($arrWidget, $arrFilterUrl, $strKeyOption)) {
            return '';
        }
        return $strKeyOption;
    }

    /**
     * Add a parameter to the url, if it is auto_item, it will get prepended.
     *
     * @param string $url   The url built so far.
     *
     * @param string $name  The parameter name.
     *
     * @param mixed  $value The parameter value.
     *
     * @return string.
     *
     * @deprecated Not in use anymore, use the FilterUrlBuilder.
     */
    protected function addUrlParameter($url, $name, $value)
    {
        // @codingStandardsIgnoreStart
        @trigger_error(
            sprintf('"%1$s" has been deprecated in favor of the "FilterUrlBuilder"', __METHOD__),
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd

        if (is_array($value)) {
            $value = implode(',', array_filter($value));
        }

        $value = str_replace('%', '%%', urlencode($value));

        if (empty($value)) {
            return $url;
        }

        if ($name !== 'auto_item') {
            $url .= '/' . $name . '/' . $value;
        } else {
            $url = '/' . $value . $url;
        }

        return $url;
    }

    /**
     * Build the filter url based upon the fragments.
     *
     * @param array  $fragments The parameters to be used in the Url.
     *
     * @param string $searchKey The param key to handle for "this".
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     *
     * @deprecated Not in use anymore, use the FilterUrlBuilder.
     */
    protected function buildFilterUrl($fragments, $searchKey)
    {
        // @codingStandardsIgnoreStart
        @trigger_error(
            sprintf('"%1$s" has been deprecated in favor of the "FilterUrlBuilder"', __METHOD__),
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd

        $url   = '';
        $found = false;

        // Create base url containing for preserving the current filter on unrelated widgets and modules.
        // The URL parameter concerning us will be masked via %s to be used later on in a sprintf().
        foreach ($fragments as $key => $value) {
            // Skip the magic "language" parameter.
            if (($key == 'language') && $GLOBALS['TL_CONFIG']['addLanguageToUrl']) {
                continue;
            }

            if ($key == $searchKey) {
                if ($key !== 'auto_item') {
                    $url .= '%s';
                } else {
                    $url = '%s' . $url;
                }
                $found = true;
            } else {
                $url = $this->addUrlParameter($url, $key, $value);
            }
        }

        // If we have not found our parameter in the URL, we add it as %s now to be able to populate it via sprintf()
        // below.
        if (!$found) {
            if ($searchKey !== 'auto_item') {
                $url .= '%s';
            } else {
                $url = '%s' . $url;
            }
        }

        return $url;
    }

    /**
     * Generate the options for the frontend widget as the frontend templates expect them.
     *
     * The returning array will be made of option arrays containing the following fields:
     * * key    The option value as raw key from the options array in the given widget information.
     * * value  The value to show as option label.
     * * href   The URL to use to activate this value in the filter.
     * * active Boolean determining if this value is the current active option in the widget.
     * * class  The CSS class to use. Contains active if the option is active or is empty otherwise.
     *
     * @param array $arrWidget     The widget information to use for value generating.
     *
     * @param array $arrFilterUrl  The filter url parameters to use.
     *
     * @param array $arrJumpTo     The jumpTo page to use for URL generating - if empty, the current
     *                             frontend page will get used.
     *
     * @param bool  $blnAutoSubmit Determines if the generated options/widgets shall perform auto submitting
     *                             or not.
     *
     * @return array The filter option values to use in the mm_filteritem_* templates.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function prepareFrontendFilterOptions($arrWidget, $arrFilterUrl, $arrJumpTo, $blnAutoSubmit)
    {
        $arrOptions = [];
        if (!isset($arrWidget['options'])) {
            return $arrOptions;
        }

        $filterUrl = new FilterUrl($arrJumpTo);
        foreach ($arrFilterUrl as $name => $value) {
            if (is_array($value)) {
                $value = implode(',', array_filter($value));
            }

            $filterUrl->setSlug($name, (string) $value);
        }
        $parameterName = $arrWidget['eval']['urlparam'];

        if ($arrWidget['eval']['includeBlankOption']) {
            $blnActive = $this->isActiveFrontendFilterValue($arrWidget, $arrFilterUrl, '');

            $arrOptions[] = [
                'key'    => '',
                'value'  => (
                $arrWidget['eval']['blankOptionLabel']
                    ? $arrWidget['eval']['blankOptionLabel']
                    : $GLOBALS['TL_LANG']['metamodels_frontendfilter']['do_not_filter']
                ),
                'href'   => $this->filterUrlBuilder->generate($filterUrl->clone()->setSlug($parameterName, '')),
                'active' => $blnActive,
                'class'  => 'doNotFilter' . ($blnActive ? ' active' : ''),
            ];
        }

        foreach ($arrWidget['options'] as $strKeyOption => $strOption) {
            $strValue  = $this->getFrontendFilterValue($arrWidget, $arrFilterUrl, $strKeyOption);
            $blnActive = $this->isActiveFrontendFilterValue($arrWidget, $arrFilterUrl, $strKeyOption);

            $arrOptions[] = [
                'key'    => $strKeyOption,
                'value'  => $strOption,
                'href'   => $this->filterUrlBuilder->generate($filterUrl->clone()->setSlug($parameterName, $strValue)),
                'active' => $blnActive,
                'class'  => StringUtil::standardize($strKeyOption) . ($blnActive ? ' active' : '')
            ];
        }
        return $arrOptions;
    }

    /**
     * Returns the frontend filter widget information for the filter setting.
     *
     * The returning array will hold the following keys:
     * * class      - The CSS classes for the widget.
     * * label      - The label text for the widget.
     * * formfield  - The parsed default widget object for this filter setting.
     * * raw        - The widget information that was used for rendering "formfield" as raw array (this means
     *                prior calling prepareForWidget()).
     * * urlparam   - The URL parameter used for this widget.
     * * options    - The filter options available to be used in selects etc. see prepareFrontendFilterOptions
     *                for details on the contained array.
     * * autosubmit - True if the frontend filter shall perform auto form submitting, false otherwise.
     * * urlvalue   - The current value selected in the filtersetting. Will use "urlvalue" from $arrWidget with
     *                fallback to the value of the url param in the filter url.
     *
     * @param array                 $arrWidget                The widget information to use for generating.
     * @param array                 $arrFilterUrl             The filter url parameters to use.
     * @param array                 $arrJumpTo                The jumpTo page to use for URL generating - if empty, the
     *                                                        current frontend page will get used.
     * @param FrontendFilterOptions $objFrontendFilterOptions The options to use.
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function prepareFrontendFilterWidget(
        array $arrWidget,
        array $arrFilterUrl,
        array $arrJumpTo,
        FrontendFilterOptions $objFrontendFilterOptions
    ): array {
        $strClass = $GLOBALS['TL_FFL'][$arrWidget['inputType']];

        // No widget? no output! that's it.
        if (!$strClass) {
            return [];
        }

        // Determine current value.
        $arrWidget['value'] = (
            $arrFilterUrl[$arrWidget['eval']['urlparam']]
            ?? ($arrWidget['eval']['default']
            ?? null)
        );

        $event = new GetAttributesFromDcaEvent(
            $arrWidget,
            $arrWidget['eval']['urlparam']
        );

        $this->eventDispatcher->dispatch($event, ContaoEvents::WIDGET_GET_ATTRIBUTES_FROM_DCA);

        if ($objFrontendFilterOptions->isAutoSubmit() && TL_MODE == 'FE') {
            $min                                    = System::getContainer()->get('kernel')->isDebug() ? '' : '.min';
            $GLOBALS['TL_JAVASCRIPT']['metamodels'] = sprintf('bundles/metamodelscore/js/metamodels%s.js', $min);
        }

        /** @var \Widget $objWidget */
        $objWidget = new $strClass($event->getResult());
        $this->validateWidget($objWidget, $arrWidget['value']);
        $strField = $objWidget->generate();

        return [
            'cssID'       => $arrWidget['eval']['cssID'],
            'class'       => sprintf(
                'mm_%s %s%s%s%s',
                $arrWidget['inputType'],
                $arrWidget['eval']['urlparam'],
                (($arrWidget['value'] !== null) ? ' used' : ' unused'),
                ($objFrontendFilterOptions->isAutoSubmit() ? ' submitonchange' : ''),
                $arrWidget['eval']['class']
            ),
            'label'       => $objWidget->generateLabel(),
            'legend'      => $this->generateLegend($arrWidget),
            'hide_label'  => $arrWidget['eval']['hide_label'],
            'formfield'   => $strField,
            'raw'         => $arrWidget,
            'urlparam'    => $arrWidget['eval']['urlparam'],
            'options'     => $this->prepareFrontendFilterOptions(
                $arrWidget,
                $arrFilterUrl,
                $arrJumpTo,
                $objFrontendFilterOptions->isAutoSubmit()
            ),
            'count'       => isset($arrWidget['count']) ? $arrWidget['count'] : null,
            'showCount'   => $objFrontendFilterOptions->isShowCountValues(),
            'autosubmit'  => $objFrontendFilterOptions->isAutoSubmit(),
            'urlvalue'    => array_key_exists('urlvalue', $arrWidget) ? $arrWidget['urlvalue'] : $arrWidget['value'],
            'errors'      => $objWidget->hasErrors() ? $objWidget->getErrors() : [],
            'used'        => $arrWidget['value'] !== null ? true : false,
            'urlfragment' => $objFrontendFilterOptions->getUrlFragment()
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function generateFilterUrlFrom(IItem $objItem, IRenderSettings $objRenderSetting)
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getParameterDCA()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getParameterFilterNames()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getParameterFilterWidgets(
        $arrIds,
        $arrFilterUrl,
        $arrJumpTo,
        FrontendFilterOptions $objFrontendFilterOptions
    ) {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getReferencedAttributes()
    {
        return [];
    }

    /**
     * Validate the widget using the value.
     *
     * @param Widget      $widget The widget to validate.
     *
     * @param string|null $value  The value to validate.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function validateWidget($widget, $value)
    {
        if (null === $value) {
            return;
        }
        $widget->setInputCallback(function () use ($value) {
            return $value;
        });
        $widget->validate();
    }

    /**
     * Generate legend.
     *
     * @param array<string, array{label: list<string>}> $arrWidget The widget information array.
     *
     * @return string
     */
    protected function generateLegend($arrWidget): string
    {
        return '<legend>' . $arrWidget['label'][0] . '</legend>';
    }
}
