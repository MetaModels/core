<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2017 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\BackendIntegration;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\GenerateFrontendUrlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\GetPageDetailsEvent;
use ContaoCommunityAlliance\UrlBuilder\UrlBuilder;
use MetaModels\Attribute\IAttributeFactory;
use MetaModels\Filter\IFilter;
use MetaModels\IFactory;
use MetaModels\IMetaModel;
use MetaModels\IMetaModelsServiceContainer;
use MetaModels\Item;

/**
 * Class SearchablePages.
 */
class SearchablePages
{
    /**
     * A list with all pages found by Contao.
     *
     * @var array
     */
    protected $foundPages = array();

    /**
     * A list with all settings from the database.
     *
     * @var array
     */
    protected $configs = array();

    /**
     * Construct.
     */
    public function __construct()
    {
        // Init the config from database.
        $this->configs = $this
            ->getServiceContainer()
            ->getDatabase()
            ->prepare('SELECT * FROM tl_metamodel_searchable_pages')
            ->execute()
            ->fetchAllAssoc();
    }

    /**
     * Retrieve the service container.
     *
     * @return IMetaModelsServiceContainer
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function getServiceContainer()
    {
        return $GLOBALS['container']['metamodels-service-container'];
    }

    /**
     * Get the event Dispatcher.
     *
     * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected function getEventDispatcher()
    {
        return $this->getServiceContainer()->getEventDispatcher();
    }

    /**
     * Get the attribute Factory.
     *
     * @return IAttributeFactory
     */
    protected function getAttributeFactory()
    {
        return $this->getServiceContainer()->getAttributeFactory();
    }

    /**
     * Get the MetaModels Factory.
     *
     * @return IFactory
     */
    protected function getMetaModelsFactory()
    {
        return $this->getServiceContainer()->getFactory();
    }

    /**
     * Get a MetaModels by name or id.
     *
     * @param string|int $identifier  The Name or ID of a MetaModels.
     *
     * @param boolean    $ignoreError If true ignore errors like the MetaModels was not found.
     *
     * @return IMetaModel|null
     *
     * @throws \RuntimeException When the MetaModels is missing.
     */
    protected function getMetaModel($identifier, $ignoreError)
    {
        // Get the factory.
        $factory = $this->getMetaModelsFactory();

        // Id to name.
        if (is_numeric($identifier)) {
            $identifier = $factory->translateIdToMetaModelName($identifier);
        }

        // Create mm, if yowl is true check if we have really a mm .
        $metaModels = $factory->getMetaModel($identifier);

        // If $ignoreError is off and we have no mm throw a new exception.
        if (!$ignoreError && $metaModels == null) {
            throw new \RuntimeException('Could not find the MetaModels with the name ' . $identifier);
        }

        return $metaModels;
    }

    /**
     * Get a filter based on the id.
     *
     * @param mixed $identifier Id of the filter.
     *
     * @return \MetaModels\Filter\Setting\ICollection The filter
     */
    protected function getFilterSettings($identifier)
    {
        $filterFactory = $this->getServiceContainer()->getFilterFactory();

        return $filterFactory->createCollection($identifier);
    }

    /**
     * Get the view for a MetaModels.
     *
     * @param string|int $identifier ID/Name of the MetaModels.
     *
     * @param int        $view       ID of the view.
     *
     * @return \MetaModels\Render\Setting\ICollection
     */
    protected function getView($identifier, $view)
    {
        $metaModels = $this->getMetaModel($identifier, false);

        return $metaModels->getView($view);
    }

    /**
     * Get the language.
     *
     * First check the overwrite language. Then check if the MetaModels is translated and get all languages from it.
     * Use the current language as fallback.
     *
     * @param string     $singleLanguage The language with the overwrite.
     *
     * @param IMetaModel $metaModels     The MetaModels for the check.
     *
     * @return string[] A list with all languages or null.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function getLanguage($singleLanguage, $metaModels)
    {
        if (!empty($singleLanguage)) {
            return array($singleLanguage);
        } elseif ($metaModels->isTranslated() && $metaModels->getAvailableLanguages()) {
            return $metaModels->getAvailableLanguages();
        }

        return array($GLOBALS['TL_LANGUAGE']);
    }

    /**
     * Get the list of jumpTos based on the items.
     *
     * @param IMetaModel                             $metaModels The MetaModels to be used.
     *
     * @param IFilter                                $filter     The filter to be used.
     *
     * @param \MetaModels\Render\Setting\ICollection $view       The view to be used.
     *
     * @param string|null                            $rootPage   The root page id or null if there is no root page.
     *
     * @return array A list of urls for the jumpTos
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function getJumpTosFor($metaModels, $filter, $view, $rootPage = null)
    {
        $entries = array();

        // Get the object.
        $items = $metaModels->findByFilter($filter);

        /** @var Item $item */
        foreach ($items as $item) {
            $jumpTo = $item->buildJumpToLink($view);
            $event  = new GetPageDetailsEvent($jumpTo['page']);
            $this->getEventDispatcher()->dispatch(ContaoEvents::CONTROLLER_GET_PAGE_DETAILS, $event);
            $pageDetails = $event->getPageDetails();

            // If there is a root page check the context or if we have no page continue.
            if ($pageDetails === null || ($rootPage !== null && $pageDetails['rootId'] != $rootPage)) {
                continue;
            }

            // Build the url.
            $url = $this->getBaseUrl(
                $pageDetails,
                $jumpTo['url']
            );

            $entries[] = $url->getUrl();
        }

        return $entries;
    }

    /**
     * Get the base URL.
     *
     * @param string[]    $pageDetails The page details.
     *
     * @param null|string $path        Additional path settings.
     *
     * @param bool        $ignoreSSL   If active the system will ignore the 'rootUseSSL' flag.
     *
     * @return UrlBuilder
     */
    private function getBaseUrl($pageDetails, $path = null, $ignoreSSL = false)
    {
        $url = new UrlBuilder();

        // Set the domain (see contao/core#6421)
        if ($pageDetails['domain']) {
            $url->setHost($pageDetails['domain']);
        } else {
            $url->setHost(\Environment::get('host'));
        }

        if ($pageDetails['rootUseSSL'] && !$ignoreSSL) {
            $url->setScheme('https');
        } else {
            $url->setScheme('http');
        }

        // Make a array for the parts.
        $fullPath   = array();
        $fullPath[] = TL_PATH;

        // Get the path.
        if ($path === null) {
            $event = new GenerateFrontendUrlEvent($pageDetails, null, $pageDetails['language'], true);
            $this->getEventDispatcher()->dispatch(ContaoEvents::CONTROLLER_GENERATE_FRONTEND_URL, $event);
            $fullPath[] = $event->getUrl();
        } else {
            $fullPath[] = $path;
        }

        $url->setPath(implode('/', $fullPath));

        return $url;
    }

    /**
     * Remove all empty detail pages.
     *
     * @param array $jumpTos A list with the jumpTo pages.
     *
     * @return void
     */
    protected function removeEmptyDetailPages($jumpTos)
    {
        // Remove the detail pages.
        foreach ($jumpTos as $jumpTo) {
            // Get the page from the url.
            $event = new GetPageDetailsEvent($jumpTo['value']);
            $this->getEventDispatcher()->dispatch(ContaoEvents::CONTROLLER_GET_PAGE_DETAILS, $event);

            $pageDetails = $event->getPageDetails();

            // Check if we have a page If not go to the next one.
            if ($pageDetails === null) {
                continue;
            }

            // Make a full url from it.
            $baseUrl = $this->getBaseUrl($pageDetails);

            if (($strKey = array_search($baseUrl->getUrl(), $this->foundPages)) !== false) {
                unset($this->foundPages[$strKey]);
            }

            // Make a full url from it without the https.
            $baseUrl = $this->getBaseUrl($pageDetails, null, true);

            if (($strKey = array_search($baseUrl->getUrl(), $this->foundPages)) !== false) {
                unset($this->foundPages[$strKey]);
            }
        }
    }

    /**
     * Set parameters.
     *
     * @param string   $filterId The id of the filter.
     *
     * @param string[] $presets  The parameter preset values to use.
     *
     * @param string[] $values   The dynamic parameter values that may be used.
     *
     * @return array
     */
    public function setFilterParameters($filterId, $presets, $values)
    {
        $filterSettings = $this->getFilterSettings($filterId);
        $presetNames    = $filterSettings->getParameters();
        $feFilterParams = array_keys($filterSettings->getParameterFilterNames());
        $processed      = array();

        // We have to use all the preset values we want first.
        foreach ($presets as $strPresetName => $arrPreset) {
            if (in_array($strPresetName, $presetNames)) {
                $processed[$strPresetName] = $arrPreset['value'];
            }
        }

        // Now we have to use all FE filter params, that are either:
        // * not contained within the presets
        // * or are overridable.
        foreach ($feFilterParams as $strParameter) {
            // Unknown parameter? - next please.
            if (!array_key_exists($strParameter, $values)) {
                continue;
            }
            // Not a preset or allowed to override? - use value.
            if ((!array_key_exists($strParameter, $presets)) || $presets[$strParameter]['use_get']) {
                $processed[$strParameter] = $values[$strParameter];
            }
        }

        return $processed;
    }

    /**
     * Start point for the hook getSearchablePages.
     *
     * @param array       $pages       List with all pages.
     *
     * @param int|null    $rootPage    ID of the root page.
     *
     * @param bool|null   $fromSiteMap True when called from sitemap generator, null otherwise.
     *
     * @param string|null $language    The current language.
     *
     * @return array
     *
     * @see \RebuildIndex::run()
     * @see \Automator::generateSitemap()
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function addPages($pages, $rootPage = null, $fromSiteMap = false, $language = null)
    {
        // Save the pages.
        $this->foundPages = $pages;
        unset($pages);

        // Run each entry in the config array.
        foreach ($this->configs as $config) {
            $this->getMetaModelsPages(
                $config,
                $rootPage,
                $language
            );
        }

        asort($this->foundPages);

        // Return the new list.
        return $this->foundPages;
    }

    /**
     * Get a MetaModels, a filter and a renderSetting. Get all items based on the filter and build the jumpTo urls.
     *
     * @param array       $config   ID of the MetaModels.
     *
     * @param string|null $rootPage The root page id or null if there is no root page.
     *
     * @param string|null $language The current language.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function getMetaModelsPages(
        $config,
        $rootPage = null,
        $language = null
    ) {
        $metaModelsIdentifier = $config['pid'];
        $filterIdentifier     = $config['filter'];
        $presetParams         = deserialize($config['filterparams'], true);
        $renderSettingId      = $config['rendersetting'];

        // Get the MetaModels.
        $metaModels         = $this->getMetaModel($metaModelsIdentifier, false);
        $availableLanguages = $this->getLanguage($language, $metaModels);
        $currentLanguage    = $GLOBALS['TL_LANGUAGE'];

        foreach ($availableLanguages as $newLanguage) {
            // Change language.
            $GLOBALS['TL_LANGUAGE'] = $newLanguage;

            // Get the view.
            $view    = $this->getView($metaModelsIdentifier, $renderSettingId);
            $jumpTos = $view->get('jumpTo');

            // Set the filter.
            $processed = $this->setFilterParameters($filterIdentifier, $presetParams, array());

            // Create a new filter for the search.
            $filter        = $metaModels->getEmptyFilter();
            $filterSetting = $this->getFilterSettings($filterIdentifier);
            $filterSetting->addRules($filter, $processed);

            // Get all jumpTos.
            $newEntries = $this->getJumpTosFor($metaModels, $filter, $view, $rootPage);

            // Remove all empty page details.
            $this->removeEmptyDetailPages($jumpTos);

            // Reset language.
            $GLOBALS['TL_LANGUAGE'] = $currentLanguage;

            // Merge all results.
            $this->foundPages = array_merge($this->foundPages, $newEntries);
        }

        // Reset the language.
        $GLOBALS['TL_LANGUAGE'] = $currentLanguage;
    }
}
