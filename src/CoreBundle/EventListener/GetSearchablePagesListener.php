<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2023 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\StringUtil;
use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\GenerateFrontendUrlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\GetPageDetailsEvent;
use ContaoCommunityAlliance\UrlBuilder\UrlBuilder;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use MetaModels\Filter\IFilter;
use MetaModels\Filter\Setting\ICollection as IFilterSettingCollection;
use MetaModels\Filter\Setting\IFilterSettingFactory;
use MetaModels\IFactory;
use MetaModels\IMetaModel;
use MetaModels\Item;
use MetaModels\ITranslatedMetaModel;
use MetaModels\Render\Setting\ICollection as IRenderSettingCollection;
use MetaModels\Render\Setting\IRenderSettingFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Terminal42\ServiceAnnotationBundle\ServiceAnnotationInterface;

/**
 * Class SearchablePages.
 */
class GetSearchablePagesListener implements ServiceAnnotationInterface
{
    /**
     * A list with all pages found by Contao.
     *
     * @var array
     */
    protected $foundPages = [];

    /**
     * A list with all settings from the database.
     *
     * @var array
     */
    private $configs = [];

    /**
     * Database connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * Factory.
     *
     * @var IFactory
     */
    private $factory;

    /**
     * Event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * Filter setting factory.
     *
     * @var IFilterSettingFactory
     */
    private $filterSettingFactory;

    /**
     * Render setting factory.
     *
     * @var IRenderSettingFactory
     */
    private $renderSettingFactory;

    /**
     * Construct.
     *
     * @param Connection               $connection           Database connection.
     * @param IFactory                 $factory              Factory.
     * @param EventDispatcherInterface $dispatcher           Event dispatcher.
     * @param IFilterSettingFactory    $filterSettingFactory Filter setting factory.
     * @param IRenderSettingFactory    $renderSettingFactory Render setting factory.
     */
    public function __construct(
        Connection $connection,
        IFactory $factory,
        EventDispatcherInterface $dispatcher,
        IFilterSettingFactory $filterSettingFactory,
        IRenderSettingFactory $renderSettingFactory
    ) {
        $this->connection           = $connection;
        $this->factory              = $factory;
        $this->dispatcher           = $dispatcher;
        $this->filterSettingFactory = $filterSettingFactory;
        $this->renderSettingFactory = $renderSettingFactory;
    }

    /**
     * Start point for the hook getSearchablePages.
     *
     * @param array       $pages       List with all pages.
     * @param int|null    $rootPage    ID of the root page.
     * @param bool|null   $fromSiteMap True when called from sitemap generator, null otherwise.
     * @param string|null $language    The current language.
     *
     * @return array
     *
     * @throws \Doctrine\DBAL\DBALException When an database error occur.
     *
     * @see \RebuildIndex::run()
     * @see \Automator::generateSitemap()
     *
     * @Hook("getSearchablePages")
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __invoke($pages, $rootPage = null, $fromSiteMap = false, $language = null)
    {
        // Save the pages.
        $this->foundPages = $pages;
        // Run each entry in the published config array.
        foreach ($this->getConfigs() as $config) {
            if (!$config['published']) {
                continue;
            }
            $this->getMetaModelsPages(
                $config,
                $rootPage,
                $language
            );
        }

        asort($this->foundPages);

        return $this->foundPages;
    }

    /**
     * Get all configs.
     *
     * @return array
     *
     * @throws \Doctrine\DBAL\DBALException When a database error occur.
     */
    protected function getConfigs(): array
    {
        if (!count($this->configs)) {
            // Init the config from database.
            $statement = $this->connection
                ->createQueryBuilder()
                ->select('t.*')
                ->from('tl_metamodel_searchable_pages', 't')
                ->execute();

            $this->configs = $statement->fetchAll(FetchMode::ASSOCIATIVE);
        }

        return $this->configs;
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
    protected function getMetaModel($identifier, $ignoreError): ?IMetaModel
    {
        // Id to name.
        if (is_numeric($identifier)) {
            $identifier = $this->factory->translateIdToMetaModelName($identifier);
        }

        // Create mm, if yowl is true check if we have really a mm .
        $metaModels = $this->factory->getMetaModel($identifier);

        // If $ignoreError is off and we have no mm throw a new exception.
        if (!$ignoreError && null === $metaModels) {
            throw new \RuntimeException('Could not find the MetaModels with the name ' . $identifier);
        }

        return $metaModels;
    }

    /**
     * Get a filter based on the id.
     *
     * @param mixed $identifier Id of the filter.
     *
     * @return IFilterSettingCollection The filter
     */
    protected function getFilterSettings($identifier): IFilterSettingCollection
    {
        return $this->filterSettingFactory->createCollection($identifier);
    }

    /**
     * Get the view for a MetaModels.
     *
     * @param string|int $identifier ID/Name of the MetaModels.
     * @param int        $view       ID of the view.
     *
     * @return IRenderSettingCollection
     */
    protected function getView($identifier, $view): ?IRenderSettingCollection
    {
        $metaModel = $this->getMetaModel($identifier, false);
        if (null === $metaModel) {
            return null;
        }

        return $this->renderSettingFactory->createCollection($metaModel, $view);
    }

    /**
     * Get the language.
     *
     * First check the overwrite language. Then check if the MetaModels is translated and get all languages from it.
     * Use the current language as fallback.
     *
     * @param string     $singleLanguage The language with the overwrite.
     * @param IMetaModel $metaModels     The MetaModels for the check.
     *
     * @return string[] A list with all languages or null.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function getLanguage($singleLanguage, $metaModels): array
    {
        if (!empty($singleLanguage)) {
            return array($singleLanguage);
        }

        if ($metaModels instanceof ITranslatedMetaModel) {
            return $metaModels->getLanguages();
        }

        if ($metaModels->isTranslated() && $metaModels->getAvailableLanguages()) {
            return $metaModels->getAvailableLanguages();
        }

        return array(\str_replace('-', '_', $GLOBALS['TL_LANGUAGE']));
    }

    /**
     * Get the list of jumpTos based on the items.
     *
     * @param IMetaModel               $metaModels The MetaModels to be used.
     * @param IFilter                  $filter     The filter to be used.
     * @param IRenderSettingCollection $view       The view to be used.
     * @param string|null              $rootPage   The root page id or null if there is no root page.
     *
     * @return array A list of urls for the jumpTos
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function getJumpTosFor($metaModels, $filter, $view, $rootPage = null): array
    {
        $entries          = [];
        $filterAttributes = [];
        $translated       = ($metaModels instanceof ITranslatedMetaModel) || $metaModels->isTranslated(false);
        $desired          = \str_replace('-', '_', $GLOBALS['TL_LANGUAGE']);
        $fallback         = $translated
            ? (($metaModels instanceof ITranslatedMetaModel) ? $metaModels->getMainLanguage()
            : $metaModels->getFallbackLanguage()) : null;

        foreach ((array) $view->get('jumpTo') as $jumpTo) {
            $langCode = $jumpTo['langcode'];
            // If either desired language or fallback, keep the result.
            if (!$translated || ($langCode === $desired) || ($langCode === $fallback)) {
                $jumpToFilterSetting = $this->filterSettingFactory->createCollection($jumpTo['filter']);
                $filterAttributes    = $jumpToFilterSetting->getReferencedAttributes();
                // If the desired language, break.
                // Otherwise try to get the desired one until all have been evaluated.
                if (!$translated || ($desired === $jumpTo['langcode'])) {
                    break;
                }
            }
        }

        // Get the object.
        /** @var Item $item */
        foreach ($metaModels->findByFilter(
            $filter,
            '',
            0,
            0,
            'ASC',
            array_merge($view->getSettingNames(), $filterAttributes)
        ) as $item) {
            $jumpTo = $item->buildJumpToLink($view);
            $event  = new GetPageDetailsEvent((int) $jumpTo['page']);
            $this->dispatcher->dispatch($event, ContaoEvents::CONTROLLER_GET_PAGE_DETAILS);
            $pageDetails = $event->getPageDetails();

            // If there is a root page check the context or if we have no page continue.
            if (empty($pageDetails) || ($rootPage !== null && $pageDetails['rootId'] != $rootPage)) {
                continue;
            }

            $url = UrlBuilder::fromUrl($jumpTo['url']);
            if (null === $url->getScheme()) {
                // Build the absolute url.
                $url = $this->getBaseUrl($pageDetails, $jumpTo['url']);
            }

            $entries[] = $url->getUrl();
        }

        return $entries;
    }

    /**
     * Get the base URL.
     *
     * @param string[]    $pageDetails The page details.
     * @param null|string $path        Additional path settings.
     * @param bool        $ignoreSSL   If active the system will ignore the 'rootUseSSL' flag.
     *
     * @return UrlBuilder
     */
    private function getBaseUrl($pageDetails, $path = null, $ignoreSSL = false): UrlBuilder
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

        // Make an array for the parts.
        $fullPath   = [];
        $fullPath[] = TL_PATH;

        // Get the path.
        if ($path === null) {
            $event = new GenerateFrontendUrlEvent($pageDetails, null, $pageDetails['language'], true);
            $this->dispatcher->dispatch($event, ContaoEvents::CONTROLLER_GENERATE_FRONTEND_URL);
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
    protected function removeEmptyDetailPages($jumpTos): void
    {
        // Remove the detail pages.
        foreach ($jumpTos as $jumpTo) {
            // Get the page from the url.
            $event = new GetPageDetailsEvent((int) $jumpTo['value']);
            $this->dispatcher->dispatch($event, ContaoEvents::CONTROLLER_GET_PAGE_DETAILS);

            $pageDetails = $event->getPageDetails();

            // Check if we have a page - if not go to the next one.
            if (empty($pageDetails)) {
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
     * @param string[] $presets  The parameter preset values to use.
     * @param string[] $values   The dynamic parameter values that may be used.
     *
     * @return array
     */
    public function setFilterParameters($filterId, $presets, $values)
    {
        $filterSettings = $this->getFilterSettings($filterId);
        $presetNames    = $filterSettings->getParameters();
        $feFilterParams = array_keys($filterSettings->getParameterFilterNames());
        $processed      = [];

        // We have to use all the preset values we want first.
        foreach ($presets as $strPresetName => $arrPreset) {
            if (in_array($strPresetName, $presetNames, true)) {
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
     * @param int|null    $rootPage    ID of the root page.
     * @param bool|null   $fromSiteMap True when called from sitemap generator, null otherwise.
     * @param string|null $language    The current language.
     *
     * @return array
     *
     * @throws \Doctrine\DBAL\DBALException When an database error occur.
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

        // Run each entry in the published config array.
        foreach ($this->getConfigs() as $config) {
            if (!$config['published']) {
                continue;
            }
            $this->getMetaModelsPages(
                $config,
                $rootPage,
                $language
            );
        }

        asort($this->foundPages);

        return $this->foundPages;
    }

    /**
     * Get a MetaModels, a filter and a renderSetting. Get all items based on the filter and build the jumpTo urls.
     *
     * @param array       $config   ID of the MetaModels.
     * @param string|null $rootPage The root page id or null if there is no root page.
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
    ): void {
        $metaModelsIdentifier = $config['pid'];
        $filterIdentifier     = $config['filter'];
        $presetParams         = StringUtil::deserialize($config['filterparams'], true);
        $renderSettingId      = $config['rendersetting'];

        // Get the MetaModels.
        $metaModels         = $this->getMetaModel($metaModelsIdentifier, false);
        $availableLanguages = $this->getLanguage($language, $metaModels);
        $currentLanguage    = $GLOBALS['TL_LANGUAGE'];

        $foundPages = [$this->foundPages];

        foreach ($availableLanguages as $newLanguage) {
            // Change language.
            $GLOBALS['TL_LANGUAGE'] = \str_replace('_', '-', $newLanguage);
            if ($metaModels instanceof ITranslatedMetaModel) {
                $prevLanguage = $metaModels->selectLanguage($newLanguage);
            }

            // Get the view.
            $view    = $this->getView($metaModelsIdentifier, $renderSettingId);
            $jumpTos = $view->get('jumpTo');

            // Set the filter.
            $processed = $this->setFilterParameters($filterIdentifier, $presetParams, []);

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
            if ($metaModels instanceof ITranslatedMetaModel) {
                $metaModels->selectLanguage($prevLanguage);
            }

            // Merge all results.
            $foundPages[] = $newEntries;
        }

        $this->foundPages = array_merge(...$foundPages);

        // Reset the language.
        $GLOBALS['TL_LANGUAGE'] = $currentLanguage;
    }
}
