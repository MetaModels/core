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
 * @author     Christian de la Haye <service@delahaye.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\FrontendIntegration;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\GenerateFrontendUrlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\RedirectEvent;
use MetaModels\Filter\Setting\Factory as FilterFactory;
use MetaModels\FrontendIntegration\Content\FilterClearAll as ContentElementFilterClearAll;
use MetaModels\FrontendIntegration\Module\FilterClearAll as ModuleFilterClearAll;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * FE-filtering for Contao MetaModels.
 *
 * @package    MetaModels
 * @subpackage FrontendFilter
 * @author     Christian de la Haye <service@delahaye.de>
 */
class FrontendFilter
{
    /**
     * Filter config.
     *
     * @var \ContentElement|\Module
     */
    protected $objFilterConfig;

    /**
     * The form id to use.
     *
     * @var string
     */
    protected $formId = 'mm_filter_';

    /**
     * Retrieve the event dispatcher.
     *
     * @return EventDispatcherInterface
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function getDispatcher()
    {
        return $GLOBALS['container']['event-dispatcher'];
    }

    /**
     * Configure the filter module.
     *
     * @param \ContentElement|\Module $objFilterConfig The content element or module using this filter.
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function getMetaModelFrontendFilter($objFilterConfig)
    {
        $this->objFilterConfig = $objFilterConfig;

        $this->objFilterConfig->arrJumpTo = $GLOBALS['objPage']->row();

        if ($this->objFilterConfig->metamodel_jumpTo) {
            // Page to jump to when filter submit.
            $objPage = \Database::getInstance()->prepare('SELECT id, alias FROM tl_page WHERE id=?')
                ->limit(1)
                ->execute($this->objFilterConfig->metamodel_jumpTo);
            if ($objPage->numRows) {
                $this->objFilterConfig->arrJumpTo = $objPage->row();
            }
        }

        $this->formId .= $this->objFilterConfig->id;
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
     */
    protected function getJumpToUrl($arrParams)
    {
        $strFilterAction = '';
        foreach ($arrParams as $strName => $varParam) {
            // Skip the magic "language" parameter.
            if (($strName == 'language') && $GLOBALS['TL_CONFIG']['addLanguageToUrl']) {
                continue;
            }

            $strValue = $varParam;

            if (is_array($varParam)) {
                $strValue = implode(',', array_filter($varParam));
            }

            $strValue = str_replace(array('/', '\''), array('-slash-', '-apos-'), $strValue);

            if (strlen($strValue)) {
                // Shift auto_item to the front.
                if ($strName == 'auto_item') {
                    $strFilterAction = '/' . $strValue . $strFilterAction;
                    continue;
                }

                $strFilterAction .= sprintf(
                    $GLOBALS['TL_CONFIG']['disableAlias'] ? '&amp;%s=%s' : '/%s/%s',
                    $strName,
                    rawurlencode($strValue)
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
     */
    protected function redirectPost($arrParams)
    {
        $dispatcher = $this->getDispatcher();
        $event      = new GenerateFrontendUrlEvent(
            $this->objFilterConfig->arrJumpTo,
            $this->getJumpToUrl($arrParams)
        );
        $dispatcher->dispatch(ContaoEvents::CONTROLLER_GENERATE_FRONTEND_URL, $event);

        $dispatcher->dispatch(
            ContaoEvents::CONTROLLER_REDIRECT,
            new RedirectEvent(
                \Environment::getInstance()->base . $event->getUrl()
            )
        );
    }

    /**
     * Retrieve the list of parameter names that shall be evaluated.
     *
     * @return array
     */
    protected function getWantedNames()
    {
        return (array) unserialize($this->objFilterConfig->metamodel_fef_params);
    }

    /**
     * Retrieve the parameter values.
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function getParams()
    {
        $input          = \Input::getInstance();
        $arrWantedParam = $this->getWantedNames();
        $arrMyParams    = $arrOtherParams = array();

        if ($_GET) {
            foreach (array_keys($_GET) as $strParam) {
                if (in_array($strParam, $arrWantedParam)) {
                    $arrMyParams[$strParam] = $input->get($strParam);
                } elseif ($strParam != 'page') {
                    // Add only to the array if param is not page.
                    $arrOtherParams[$strParam] = $input->get($strParam);
                }
            }
        }

        // if POST, translate to proper GET url
        if ($_POST && ($input->post('FORM_SUBMIT') == $this->formId)) {
            foreach (array_keys($_POST) as $strParam) {
                if (in_array($strParam, $arrWantedParam)) {
                    $arrMyParams[$strParam] = $input->post($strParam);
                }
            }
        }

        return array
        (
            'filter' => $arrMyParams,
            'other' => $arrOtherParams,
            'all' => array_merge($arrOtherParams, $arrMyParams)
        );
    }

    /**
     * Parse a single filter widget.
     *
     * @param array                 $widget        The widget configuration.
     *
     * @param FrontendFilterOptions $filterOptions The filter options to apply.
     *
     * @return array
     */
    protected function renderWidget($widget, $filterOptions)
    {
        $filter       = $widget;
        $templateName = $filter['raw']['eval']['template'];
        $template     = new \FrontendTemplate($templateName ? $templateName : 'mm_filteritem_default');

        $template->setData($filter);

        $template->submit = $filterOptions->isAutoSubmit();
        $filter['value']  = $template->parse();

        return $filter;
    }

    /**
     * Check if we want to redirect to another url.
     *
     * @param array $widgets         The widgets.
     *
     * @param array $wantedParameter The wanted parameters.
     *
     * @param array $allParameter    The current parameters.
     *
     * @return void
     */
    protected function checkRedirect($widgets, $wantedParameter, $allParameter)
    {
        // If we have POST data, we need to redirect now.
        if (\Input::getInstance()->post('FORM_SUBMIT') != $this->formId) {
            return;
        }
        $redirectParameters = $allParameter['other'];
        foreach ($wantedParameter as $widgetName) {
            $filter = $widgets[$widgetName];
            if (!empty($filter['urlvalue'])) {
                $redirectParameters[$widgetName] = $filter['urlvalue'];
            }
        }
        $this->redirectPost($redirectParameters);
    }

    /**
     * Get the frontend filter options to use.
     *
     * @return FrontendFilterOptions
     */
    protected function getFrontendFilterOptions()
    {
        $objFrontendFilterOptions = new FrontendFilterOptions();
        $objFrontendFilterOptions->setAutoSubmit($this->objFilterConfig->metamodel_fef_autosubmit ? true : false);
        $objFrontendFilterOptions->setHideClearFilter(
            $this->objFilterConfig->metamodel_fef_hideclearfilter ? true : false
        );
        $objFrontendFilterOptions->setShowCountValues(
            $this->objFilterConfig->metamodel_available_values ? true : false
        );

        return $objFrontendFilterOptions;
    }

    /**
     * Get the filters.
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function getFilters()
    {
        $filterSetting     = FilterFactory::byId($this->objFilterConfig->metamodel_filtering);
        $filterOptions     = $this->getFrontendFilterOptions();
        $jumpToInformation = $this->objFilterConfig->arrJumpTo;
        $filterParameters  = $this->getParams();

        $arrWidgets = $filterSetting->getParameterFilterWidgets(
            $filterParameters['all'],
            $jumpToInformation,
            $filterOptions
        );

        // Filter the widgets we do not want to show.
        $wantedNames = $this->getWantedNames();

        $this->checkRedirect($arrWidgets, $wantedNames, $filterParameters);

        $renderedWidgets = array();

        // Render the widgets through the filter templates.
        foreach ($wantedNames as $strWidget) {
            $renderedWidgets[$strWidget] = $this->renderWidget($arrWidgets[$strWidget], $filterOptions);
        }

        $event = new GenerateFrontendUrlEvent($jumpToInformation, $this->getJumpToUrl($filterParameters['other']));
        $this->getDispatcher()->dispatch(ContaoEvents::CONTROLLER_GENERATE_FRONTEND_URL, $event);

        // Return filter data.
        return array(
            'action'     => $event->getUrl(),
            'formid'     => $this->formId,
            'filters'    => $renderedWidgets,
            'submit'     => (
                $filterOptions->isAutoSubmit()
                    ? ''
                    : $GLOBALS['TL_LANG']['metamodels_frontendfilter']['submit']
                )
        );
    }

    /**
     * Render a content element.
     *
     * @param string $content   The html content in which to replace.
     *
     * @param string $replace   The string within the html to be replaced.
     *
     * @param int    $contentId The id of the content element to be inserted for the replace string.
     *
     * @return string
     */
    protected function generateContentElement($content, $replace, $contentId)
    {
        $objDbResult = \Database::getInstance()
            ->prepare('SELECT * FROM tl_content WHERE id=? AND type="metamodels_frontendclearall"')
            ->execute($contentId);

        // Check if we have a ce element.
        if ($objDbResult->numRows == 0) {
            return str_replace($replace, '', $content);
        }

        // Get instance and call generate function.
        $objCE = new ContentElementFilterClearAll($objDbResult);
        return str_replace($replace, $objCE->generateReal(), $content);
    }

    /**
     * Render a module.
     *
     * @param string $content  The html content in which to replace.
     *
     * @param string $replace  The string within the html to be replaced.
     *
     * @param int    $moduleId The id of the module to be inserted for the replace string.
     *
     * @return string
     */
    protected function generateModule($content, $replace, $moduleId)
    {
        $objDbResult = \Database::getInstance()
            ->prepare('SELECT * FROM tl_module WHERE id=? AND type="metamodels_frontendclearall"')
            ->execute($moduleId);

        // Check if we have a ce element.
        if ($objDbResult->numRows == 0) {
            return str_replace($replace, '', $content);
        }

        // Get instance and call generate function.
        $objModule = new ModuleFilterClearAll($objDbResult);
        return str_replace($replace, $objModule->generateReal(), $content);
    }



    /**
     * Add the "clear all Filter".
     *
     * This is called via parseTemplate HOOK to inject the "clear all" filter into fe_page.
     *
     * @param string $strContent  The whole page content.
     *
     * @param string $strTemplate The name of the template being parsed.
     *
     * @return string
     *
     * @throws \RuntimeException When an invalid selector has been used (different than "ce" or "mod").
     */
    public function generateClearAll($strContent, $strTemplate)
    {
        if (substr($strTemplate, 0, 7) === 'fe_page') {
            if (preg_match_all(
                '#\[\[\[metamodelfrontendfilterclearall::(ce|mod)::([^\]]*)\]\]\]#',
                $strContent,
                $arrMatches,
                PREG_SET_ORDER
            )) {
                foreach ($arrMatches as $arrMatch) {
                    switch ($arrMatch[1])
                    {
                        case 'ce':
                            $strContent = $this->generateContentElement($strContent, $arrMatch[0], $arrMatch[2]);
                            break;

                        case 'mod':
                            $strContent = $this->generateModule($strContent, $arrMatch[0], $arrMatch[2]);
                            break;

                        default:
                            throw new \RuntimeException('Unexpected element determinator encountered: ' . $arrMatch[1]);
                    }
                }
            }
        }

        return $strContent;
    }
}
