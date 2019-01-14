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
 * @author     David Maack <david.maack@arcor.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Filter\Setting;

use Contao\Input;
use Contao\Widget;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\GenerateFrontendUrlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Widget\GetAttributesFromDcaEvent;
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
    private $data = array();

    /**
     * Constructor - initialize the object and store the parameters.
     *
     * @param ICollection $collection The parenting filter settings object.
     *
     * @param array       $data       The attributes for this filter setting.
     */
    public function __construct($collection, $data)
    {
        $this->collection = $collection;
        $this->data       = $data;
    }

    /**
     * Retrieve the service container.
     *
     * @return IMetaModelsServiceContainer
     */
    public function getServiceContainer()
    {
        return $this->getMetaModel()->getServiceContainer();
    }

    /**
     * Retrieve the event dispatcher.
     *
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        return $this->getServiceContainer()->getEventDispatcher();
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
        $blnIsActive = isset($arrFilterUrl[$arrWidget['eval']['urlparam']])
            && ($arrFilterUrl[$arrWidget['eval']['urlparam']] == $strKeyOption);
        if (!$blnIsActive && $this->get('defaultid')) {
            $blnIsActive = ($arrFilterUrl[$arrWidget['eval']['urlparam']] == $this->get('defaultid'));
        }
        return $blnIsActive;
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
     */
    protected function addUrlParameter($url, $name, $value)
    {
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
     */
    protected function buildFilterUrl($fragments, $searchKey)
    {
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
        $arrOptions = array();
        if (!isset($arrWidget['options'])) {
            return $arrOptions;
        }
        $dispatcher = $this->getEventDispatcher();

        $strFilterAction = $this->buildFilterUrl($arrFilterUrl, $arrWidget['eval']['urlparam']);

        // If no jumpTo-page has been provided, we use the current page.
        if (!$arrJumpTo) {
            $arrJumpTo = $GLOBALS['objPage']->row();
        }

        if ($arrWidget['eval']['includeBlankOption']) {
            $blnActive = $this->isActiveFrontendFilterValue($arrWidget, $arrFilterUrl, '');
            $event     = new GenerateFrontendUrlEvent($arrJumpTo, sprintf($strFilterAction, ''));
            $dispatcher->dispatch(ContaoEvents::CONTROLLER_GENERATE_FRONTEND_URL, $event);

            $arrOptions[] = array
            (
                'key'    => '',
                'value'  => (
                $arrWidget['eval']['blankOptionLabel']
                    ? $arrWidget['eval']['blankOptionLabel']
                    : $GLOBALS['TL_LANG']['metamodels_frontendfilter']['do_not_filter']
                ),
                'href'   => $event->getUrl(),
                'active' => $blnActive,
                'class'  => 'doNotFilter'.($blnActive ? ' active' : ''),
            );
        }

        foreach ($arrWidget['options'] as $strKeyOption => $strOption) {
            $strValue  = rawurlencode($this->getFrontendFilterValue($arrWidget, $arrFilterUrl, $strKeyOption));
            $blnActive = $this->isActiveFrontendFilterValue($arrWidget, $arrFilterUrl, $strKeyOption);

            if (!empty($strValue)) {
                if ($arrWidget['eval']['urlparam'] !== 'auto_item') {
                    $strValue = '/' . $arrWidget['eval']['urlparam'] . '/' . $strValue;
                } else {
                    $strValue = '/' . $strValue;
                }
            }
            $event = new GenerateFrontendUrlEvent($arrJumpTo, sprintf($strFilterAction, $strValue), null, true);
            $dispatcher->dispatch(ContaoEvents::CONTROLLER_GENERATE_FRONTEND_URL, $event);

            $arrOptions[] = array
            (
                'key'    => $strKeyOption,
                'value'  => $strOption,
                'href'   => $event->getUrl(),
                'active' => $blnActive,
                'class'  => standardize($strKeyOption) . ($blnActive ? ' active' : '')
            );
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
     *
     * @param array                 $arrFilterUrl             The filter url parameters to use.
     *
     * @param array                 $arrJumpTo                The jumpTo page to use for URL generating - if empty, the
     *                                                        current frontend page will get used.
     *
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
        $arrWidget,
        $arrFilterUrl,
        $arrJumpTo,
        FrontendFilterOptions $objFrontendFilterOptions
    ) {
        $strClass = $GLOBALS['TL_FFL'][$arrWidget['inputType']];

        // No widget? no output! that's it.
        if (!$strClass) {
            return array();
        }

        // Determine current value.
        $arrWidget['value'] = isset($arrFilterUrl[$arrWidget['eval']['urlparam']])
            ? $arrFilterUrl[$arrWidget['eval']['urlparam']]
            : null;

        $dispatcher = $this->getEventDispatcher();
        $event      = new GetAttributesFromDcaEvent(
            $arrWidget,
            $arrWidget['eval']['urlparam']
        );

        $dispatcher->dispatch(
            ContaoEvents::WIDGET_GET_ATTRIBUTES_FROM_DCA,
            $event
        );

        if ($objFrontendFilterOptions->isAutoSubmit() && TL_MODE == 'FE') {
            $GLOBALS['TL_JAVASCRIPT']['metamodels'] = 'system/modules/metamodels/assets/js/metamodels.js';
        }

        /** @var \Widget $objWidget */
        $objWidget = new $strClass($event->getResult());
        $this->validateWidget($objWidget, $arrWidget['value']);
        $strField = $objWidget->generateWithError();

        return array
        (
            'class'      => sprintf(
                'mm_%s %s%s%s',
                $arrWidget['inputType'],
                $arrWidget['eval']['urlparam'],
                (($arrWidget['value'] !== null) ? ' used' : ' unused'),
                ($objFrontendFilterOptions->isAutoSubmit() ? ' submitonchange' : '')
            ),
            'label'      => $objWidget->generateLabel(),
            'formfield'  => $strField,
            'raw'        => $arrWidget,
            'urlparam'   => $arrWidget['eval']['urlparam'],
            'options'    => $this->prepareFrontendFilterOptions(
                $arrWidget,
                $arrFilterUrl,
                $arrJumpTo,
                $objFrontendFilterOptions->isAutoSubmit()
            ),
            'count'      => isset($arrWidget['count']) ? $arrWidget['count'] : null,
            'showCount'  => $objFrontendFilterOptions->isShowCountValues(),
            'autosubmit' => $objFrontendFilterOptions->isAutoSubmit(),
            'urlvalue'   => array_key_exists('urlvalue', $arrWidget) ? $arrWidget['urlvalue'] : $arrWidget['value'],
            'errors'     => $objWidget->hasErrors() ? $objWidget->getErrors() : array()
        );
    }

    /**
     * {@inheritdoc}
     */
    public function generateFilterUrlFrom(IItem $objItem, IRenderSettings $objRenderSetting)
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function getParameterDCA()
    {
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function getParameterFilterNames()
    {
        return array();
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
        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function getReferencedAttributes()
    {
        return array();
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
        $name = $widget->name;

        // Backup $_POST value.
        $keeper = Input::post($name);
        Input::setPost($name, $value);
        $widget->validate();
        // Restore $_POST value.
        Input::setPost($name, $keeper);
    }
}
