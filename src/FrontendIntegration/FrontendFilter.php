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
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Christopher Boelter <christopher@boelter.eu>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Marc Reimann <reimann@mediendepot-ruhr.de>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\FrontendIntegration;

use Contao\CoreBundle\Exception\RedirectResponseException;
use Contao\FrontendTemplate;
use Contao\Input;
use Contao\System;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\RedirectEvent;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception;
use MetaModels\Filter\FilterUrl;
use MetaModels\Filter\FilterUrlBuilder;
use MetaModels\FrontendIntegration\Content\FilterClearAll as ContentElementFilterClearAll;
use MetaModels\FrontendIntegration\Module\FilterClearAll as ModuleFilterClearAll;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * FE-filtering for Contao MetaModels.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class FrontendFilter
{
    /**
     * Filter config.
     *
     * @var HybridFilterBlock
     */
    protected $objFilterConfig;

    /**
     * The form id to use.
     *
     * @var string
     */
    protected $formId = 'mm_filter_';

    /**
     * Database connection.
     *
     * @var Connection
     */
    private Connection $connection;

    /**
     * The filter URL builder.
     *
     * @var FilterUrlBuilder
     */
    private FilterUrlBuilder $filterUrlBuilder;

    /**
     * FrontendFilter constructor.
     *
     * @param Connection|null       $connection       Database connection.
     * @param FilterUrlBuilder|null $filterUrlBuilder The filter URL builder.
     */
    public function __construct(Connection $connection = null, FilterUrlBuilder $filterUrlBuilder = null)
    {
        if (null === $connection) {
            // @codingStandardsIgnoreStart
            @trigger_error(
                'Connection is missing. It has to be passed in the constructor. Fallback will be dropped.',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
            $connection = System::getContainer()->get('database_connection');
            assert($connection instanceof Connection);
        }
        $this->connection = $connection;

        if (null === $filterUrlBuilder) {
            // @codingStandardsIgnoreStart
            @trigger_error(
                'FilterUrlBuilder is missing. It has to be passed in the constructor. Fallback will be dropped.',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
            $filterUrlBuilder = System::getContainer()->get('metamodels.filter_url');
            assert($filterUrlBuilder instanceof FilterUrlBuilder);
        }
        $this->filterUrlBuilder = $filterUrlBuilder;
    }

    /**
     * Retrieve the event dispatcher.
     *
     * @return EventDispatcherInterface
     */
    protected function getDispatcher()
    {
        $dispatcher = System::getContainer()->get('event_dispatcher');
        assert($dispatcher instanceof EventDispatcherInterface);

        return $dispatcher;
    }

    /**
     * Configure the filter module.
     *
     * @param HybridFilterBlock $objFilterConfig The content element or module using this filter.
     *
     * @return array
     */
    public function getMetaModelFrontendFilter(HybridFilterBlock $objFilterConfig)
    {
        $this->objFilterConfig = $objFilterConfig;

        /** @psalm-suppress UndefinedMagicPropertyFetch */
        $this->formId .= $this->objFilterConfig->metamodel_fef_id ?: $this->objFilterConfig->id;
        return $this->getFilters();
    }

    /**
     * Generate an url determined by the given params and configured jumpTo page.
     *
     * @param array $arrParams The URL parameters to use.
     *
     * @return string the generated URL.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     *
     * @deprecated Do not use.
     */
    protected function getJumpToUrl($arrParams)
    {
        // @codingStandardsIgnoreStart
        @trigger_error(
            \sprintf('"%1$s" has been deprecated in favor of the new "FilterUrlBuilder"', __METHOD__),
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd

        $strFilterAction = '';
        foreach ($arrParams as $strName => $varParam) {
            // Skip the magic "language" parameter.
            if (($strName === 'language') && $GLOBALS['TL_CONFIG']['addLanguageToUrl']) {
                continue;
            }

            $strValue = $varParam;

            if (\is_array($varParam)) {
                $strValue = \implode(',', \array_filter($varParam));
            }

            if (\strlen($strValue)) {
                // Shift auto_item to the front.
                if ($strName === 'auto_item') {
                    $strFilterAction = '/' . \rawurlencode(\rawurlencode($strValue)) . $strFilterAction;
                    continue;
                }

                $strFilterAction .= \sprintf(
                    $GLOBALS['TL_CONFIG']['disableAlias'] ? '&amp;%s=%s' : '/%s/%s',
                    \rawurlencode($strName),
                    // Double rawurlencode to encode all special characters.
                    // Look at http://php.net/manual/en/function.rawurlencode.php .
                    \rawurlencode(\rawurlencode($strValue))
                );
            }
        }
        return $strFilterAction;
    }

    /**
     * Redirect the browser to the url determined by the given params (configured jumpTo page will get used).
     *
     * This will exit the script!
     *
     * @param array $arrParams The URL parameters to use.
     *
     * @return void
     *
     * @deprecated Do not use.
     */
    protected function redirectPost($arrParams)
    {
        // @codingStandardsIgnoreStart
        @trigger_error(
            \sprintf('"%1$s" has been deprecated in favor of the new "FilterUrlBuilder"', __METHOD__),
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd
        $jumpTo     = $this->objFilterConfig->getJumpTo();
        $dispatcher = $this->getDispatcher();
        $filterUrl  = new FilterUrl($jumpTo, [], $arrParams);
        $dispatcher->dispatch(
            new RedirectEvent($this->filterUrlBuilder->generate($filterUrl)),
            ContaoEvents::CONTROLLER_REDIRECT
        );
    }

    /**
     * Retrieve the list of parameter names that shall be evaluated.
     *
     * @return list<string>
     */
    protected function getWantedNames()
    {
        return \array_values(
            \array_map(
                static fn (mixed $value): string => (string) $value,
                (array) \unserialize($this->objFilterConfig->metamodel_fef_params, ['allowed_classes' => false])
            )
        );
    }

    /**
     * Retrieve the parameter values.
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     *
     * @deprecated Do not use.
     */
    protected function getParams()
    {
        // @codingStandardsIgnoreStart
        @trigger_error(
            \sprintf('"%1$s" has been deprecated in favor of the new "FilterUrlBuilder"', __METHOD__),
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd
        $arrWantedParam = $this->getWantedNames();
        $arrMyParams    = $arrOtherParams = [];

        if ($_GET) {
            foreach (\array_keys($_GET) as $strParam) {
                if (\in_array($strParam, $arrWantedParam)) {
                    $arrMyParams[$strParam] = Input::get((string) $strParam);
                } elseif ($strParam !== 'page') {
                    // Add only to the array if param is not page.
                    $arrOtherParams[$strParam] = Input::get((string) $strParam);
                }
            }
        }

        // if POST, translate to proper GET url
        if ($_POST && (Input::post('FORM_SUBMIT') === $this->formId)) {
            foreach (\array_keys($_POST) as $strParam) {
                if (\in_array($strParam, $arrWantedParam)) {
                    $arrMyParams[$strParam] = Input::post((string) $strParam);
                }
            }
        }

        return [
            'filter' => $arrMyParams,
            'other'  => $arrOtherParams,
            'all'    => \array_merge($arrOtherParams, $arrMyParams)
        ];
    }

    /**
     * Parse a single filter widget.
     *
     * @param array                 $widget        The widget configuration.
     * @param FrontendFilterOptions $filterOptions The filter options to apply.
     *
     * @return array
     */
    protected function renderWidget($widget, $filterOptions)
    {
        $filter       = $widget;
        $templateName = ($filter['raw']['eval']['template'] ?? 'mm_filteritem_default');
        $template     = new FrontendTemplate($templateName);

        $template->setData($filter);

        /** @psalm-suppress UndefinedMagicPropertyAssignment */
        $template->submit = $filterOptions->isAutoSubmit();
        $filter['value']  = $template->parse();

        return $filter;
    }

    /**
     * Check if we want to redirect to another url.
     *
     * @param array $widgets         The widgets.
     * @param array $wantedParameter The wanted parameters.
     * @param array $allParameter    The current parameters.
     *
     * @return void
     *
     * @deprecated Do not use.
     */
    protected function checkRedirect($widgets, $wantedParameter, $allParameter)
    {
        // @codingStandardsIgnoreStart
        @trigger_error(
            \sprintf('"%1$s" has been deprecated in favor of the new "FilterUrlBuilder"', __METHOD__),
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd

        // If we have POST data, we need to redirect now.
        if (Input::post('FORM_SUBMIT') != $this->formId) {
            return;
        }
        $redirectParameters = $allParameter['other'];
        foreach ($wantedParameter as $widgetName) {
            $filter = $widgets[$widgetName];
            if ($filter['urlvalue'] !== null) {
                $redirectParameters[$widgetName] = $filter['urlvalue'];
            }
        }

        $filterUrl  = new FilterUrl($this->objFilterConfig->getJumpTo(), [], $redirectParameters);
        $dispatcher = $this->getDispatcher();
        $dispatcher->dispatch(
            new RedirectEvent($this->filterUrlBuilder->generate($filterUrl)),
            ContaoEvents::CONTROLLER_REDIRECT
        );
    }

    /**
     * Get the frontend filter options to use.
     *
     * @return FrontendFilterOptions
     */
    protected function getFrontendFilterOptions()
    {
        $filterOptions = new FrontendFilterOptions();
        $filterOptions->setAutoSubmit($this->objFilterConfig->metamodel_fef_autosubmit ? true : false);
        $filterOptions->setHideClearFilter(
            $this->objFilterConfig->metamodel_fef_hideclearfilter ? true : false
        );
        $filterOptions->setShowCountValues(
            $this->objFilterConfig->metamodel_available_values ? true : false
        );
        $filterOptions->setUrlFragment($this->objFilterConfig->metamodel_fef_urlfragment);

        return $filterOptions;
    }

    /**
     * Get the filters.
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @throws RedirectResponseException When there was a POST request and we have to reload the page.
     */
    protected function getFilters()
    {
        $filterOptions     = $this->getFrontendFilterOptions();
        $jumpToInformation = $this->objFilterConfig->getJumpTo();
        $filterSetting     = $this->objFilterConfig->getFilterCollection();
        $wantedNames       = $this->getWantedNames();

        $this->buildParameters(
            $other = new FilterUrl($jumpToInformation),
            $all   = new FilterUrl($jumpToInformation),
            $wantedNames
        );

        // DAMN Contao - we have to "mark" the keys in the Input class as used as we get an 404 otherwise.
        foreach ($wantedNames as $name) {
            if ($all->hasSlug($name)) {
                Input::get($name);
            }
        }

        $arrWidgets = $filterSetting->getParameterFilterWidgets(
            \array_merge($all->getSlugParameters(), $all->getGetParameters()),
            $jumpToInformation,
            $filterOptions
        );

        // If we have POST data, we need to redirect now.
        if (Input::post('FORM_SUBMIT') === $this->formId) {
            foreach ($wantedNames as $widgetName) {
                if (empty($arrWidgets[$widgetName])) {
                    continue;
                }
                $filter = $arrWidgets[$widgetName];
                if (null !== $filter['urlvalue']) {
                    $other->setSlug($widgetName, $filter['urlvalue']);
                }
            }

            throw new RedirectResponseException($this->filterUrlBuilder->generate($other));
        }

        $renderedWidgets = [];

        // Render the widgets through the filter templates.
        foreach ($wantedNames as $strWidget) {
            if (!empty($arrWidgets[$strWidget])) {
                $renderedWidgets[$strWidget] = $this->renderWidget($arrWidgets[$strWidget], $filterOptions);
            }
        }

        $tokenManager = System::getContainer()->get('contao.csrf.token_manager');
        assert($tokenManager instanceof CsrfTokenManagerInterface);

        // Return filter data.
        /** @psalm-suppress UndefinedMagicPropertyFetch */
        return [
            'requestToken' => $tokenManager->getDefaultTokenValue(),
            'action'       => $this->filterUrlBuilder->generate($other)
                              . ($this->objFilterConfig->metamodel_fef_urlfragment
                    ? '#' . $this->objFilterConfig->metamodel_fef_urlfragment
                    : ''),
            'formid'       => $this->formId,
            'filters'      => $renderedWidgets,
            'submit'       => ($filterOptions->isAutoSubmit()
                ? ''
                : $GLOBALS['TL_LANG']['metamodels_frontendfilter']['submit'] ?? ''
            )
        ];
    }

    /**
     * Retrieve the parameter values.
     *
     * @param FilterUrl    $other       Destination for "other" parameters (not originating from current filter module).
     * @param FilterUrl    $all         Destination for "all" parameters.
     * @param list<string> $wantedNames The wanted parameter names.
     *
     * @return void
     */
    protected function buildParameters(FilterUrl $other, FilterUrl $all, array $wantedNames): void
    {
        $current = $this->filterUrlBuilder->getCurrentFilterUrl(
            [
                'postAsSlug'  => $wantedNames,
                'postAsGet'   => [],
                'preserveGet' => true
            ]
        );
        foreach ($current->getSlugParameters() as $name => $value) {
            $all->setSlug($name, $value);
            if (!\in_array($name, $wantedNames)) {
                $other->setSlug($name, $value);
            }
        }
        foreach ($current->getGetParameters() as $name => $value) {
            $all->setGet($name, $value);
            if (!\in_array($name, $wantedNames)) {
                $other->setGet($name, $value);
            }
        }
    }

    /**
     * Render a content element.
     *
     * @param string $content   The html content in which to replace.
     * @param string $replace   The string within the html to be replaced.
     * @param int    $contentId The id of the content element to be inserted for the replace string.
     *
     * @return string
     */
    protected function generateContentElement($content, $replace, $contentId)
    {
        return $this->generateElement('tl_content', $content, $replace, $contentId);
    }

    /**
     * Render a module.
     *
     * @param string $content  The html content in which to replace.
     * @param string $replace  The string within the html to be replaced.
     * @param int    $moduleId The id of the module to be inserted for the replace string.
     *
     * @return string
     */
    protected function generateModule($content, $replace, $moduleId)
    {
        return $this->generateElement('tl_module', $content, $replace, $moduleId);
    }

    /**
     * Render a module or content element.
     *
     * @param string $table     The name of the table.
     * @param string $content   The html content in which to replace.
     * @param string $replace   The string within the html to be replaced.
     * @param int    $elementId The id of the module/ce-element to be inserted for the replace string.
     *
     * @return string
     *
     * @throws Exception When a database error occur.
     */
    protected function generateElement($table, $content, $replace, $elementId)
    {
        $result = $this->connection
            ->createQueryBuilder()
            ->select('t.*')
            ->from($table, 't')
            ->where('t.id=:id')
            ->andWhere('t.type=:type')
            ->setParameter('id', $elementId)
            ->setParameter('type', 'metamodels_frontendclearall')
            ->executeQuery()
            ->fetchAssociative();

        // Check if we have an existing module or ce element.
        if ($result === false) {
            return \str_replace($replace, '', $content);
        }

        // Get instance and call generate function.
        if ($table === 'tl_module') {
            /** @psalm-suppress ArgumentTypeCoercion */
            $objElement = new ModuleFilterClearAll((object) $result);
        } elseif ($table === 'tl_content') {
            /** @psalm-suppress ArgumentTypeCoercion */
            $objElement = new ContentElementFilterClearAll((object) $result);
        } else {
            return \str_replace($replace, '', $content);
        }

        return \str_replace($replace, $objElement->generateReal(), $content);
    }

    /**
     * Add the "clear all Filter".
     *
     * This is called via parseTemplate HOOK to inject the "clear all" filter into fe_page.
     *
     * @param string $strContent  The whole page content.
     * @param string $strTemplate The name of the template being parsed.
     *
     * @return string
     *
     * @throws RuntimeException When an invalid selector has been used (different than "ce" or "mod").
     */
    public function generateClearAll($strContent, $strTemplate)
    {
        if (\str_starts_with($strTemplate, 'fe_')) {
            if (
                (bool) \preg_match_all(
                    '#\[\[\[metamodelfrontendfilterclearall::(ce|mod)::([^\]]*)\]\]\]#',
                    $strContent,
                    $arrMatches,
                    PREG_SET_ORDER
                )
            ) {
                foreach ($arrMatches as $arrMatch) {
                    switch ($arrMatch[1]) {
                        case 'ce':
                            $strContent = $this->generateContentElement($strContent, $arrMatch[0], (int) $arrMatch[2]);
                            break;

                        case 'mod':
                            $strContent = $this->generateModule($strContent, $arrMatch[0], (int) $arrMatch[2]);
                            break;

                        default:
                            throw new RuntimeException('Unexpected element determinator encountered: ' . $arrMatch[1]);
                    }
                }
            }
        }

        return $strContent;
    }
}
