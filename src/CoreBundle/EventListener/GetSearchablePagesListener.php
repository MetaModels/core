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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\GenerateFrontendUrlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\GetPageDetailsEvent;
use ContaoCommunityAlliance\UrlBuilder\UrlBuilder;
use Contao\CoreBundle\Event\SitemapEvent;
use Contao\Environment;
use Contao\StringUtil;
use DOMElement;
use DOMException;
use DOMNode;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Generator;
use MetaModels\Filter\IFilter;
use MetaModels\Filter\Setting\IFilterSettingFactory;
use MetaModels\IFactory;
use MetaModels\IItems;
use MetaModels\IMetaModel;
use MetaModels\Item;
use MetaModels\ITranslatedMetaModel;
use MetaModels\Render\Setting\ICollection as IRenderSettingCollection;
use MetaModels\Render\Setting\IRenderSettingFactory;
use RuntimeException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

use function array_merge;
use function array_values;
use function in_array;
use function trim;

/**
 * Class SearchablePages.
 *
 * @psalm-type TSearchablePageConfig=array{
 *   id: int,
 *   pid: int,
 *   tstamp: int,
 *   name: string,
 *   filter: int,
 *   filterparams: string,
 *   rendersetting: int,
 *   published: string
 * }
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
class GetSearchablePagesListener
{
    /**
     * Database connection.
     *
     * @var Connection
     */
    private Connection $connection;

    /**
     * Factory.
     *
     * @var IFactory
     */
    private IFactory $factory;

    /**
     * Event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private EventDispatcherInterface $dispatcher;

    /**
     * Filter setting factory.
     *
     * @var IFilterSettingFactory
     */
    private IFilterSettingFactory $filterSettingFactory;

    /**
     * Render setting factory.
     *
     * @var IRenderSettingFactory
     */
    private IRenderSettingFactory $renderSettingFactory;

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
     * Start point for the contao sitemap event.
     *
     * @throws DOMException|Exception
     */
    public function __invoke(SitemapEvent $event): void
    {
        $rootPageIds = $event->getRootPageIds();
        $sitemap     = $event->getDocument();
        $urlSet      = $sitemap->childNodes[0];
        assert($urlSet instanceof DOMElement);

        // Run each entry in the published config array and search detail pages.
        foreach ($this->getPublishedConfigs() as $config) {
            $metaModelId = (string) $config['pid'];
            $metaModel   = $this->getMetaModel($metaModelId);
            assert($metaModel instanceof IMetaModel);
            $filterParams  = StringUtil::deserialize($config['filterparams'], true);
            $renderSetting =
                $this->renderSettingFactory->createCollection($metaModel, (string) $config['rendersetting']);

            // Now loop over all detail pages...
            foreach ((array) $renderSetting->get('jumpTo') as $jumpTo) {
                if (empty($jumpTo['langcode']) || empty($jumpTo['value']) || empty($jumpTo['filter'])) {
                    continue;
                }

                $event = new GetPageDetailsEvent((int) $jumpTo['value']);
                $this->dispatcher->dispatch($event, ContaoEvents::CONTROLLER_GET_PAGE_DETAILS);
                $pageDetails = $event->getPageDetails();

                // If details page not found or root page is not within passed list, continue.
                if (empty($pageDetails) || !in_array($pageDetails['rootId'], $rootPageIds, true)) {
                    continue;
                }

                $this->removePlainDetailsUrl($pageDetails, $urlSet);

                $langCode         = $jumpTo['langcode'];
                $filterSetting    = $this->filterSettingFactory->createCollection($jumpTo['filter']);
                $filterAttributes = $filterSetting->getReferencedAttributes();

                foreach (
                    $this->generateUrlsFor(
                        $metaModel,
                        $langCode,
                        $renderSetting,
                        $pageDetails,
                        (string) $config['filter'],
                        $filterAttributes,
                        $filterParams,
                    ) as $url
                ) {
                    $loc   = $sitemap->createElement('loc', $url);
                    $urlEl = $sitemap->createElement('url');
                    $urlEl->appendChild($loc);
                    $urlSet->appendChild($urlEl);
                }
            }
        }
    }

    /**
     * Generate URLs for detail page.
     *
     * @param IMetaModel               $metaModel        The MetaModel.
     * @param string                   $language         The language.
     * @param IRenderSettingCollection $renderSetting    The render settings for the detail page.
     * @param array                    $pageDetails      The page information for the detail page.
     * @param array                    $filterAttributes The filter attributes.
     * @param IFilter                  $listFilter       The list filter.
     *
     * @return Generator
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function generateUrlsFor(
        IMetaModel $metaModel,
        string $language,
        IRenderSettingCollection $renderSetting,
        array $pageDetails,
        string $filterId,
        array $filterAttributes,
        array $filterParams,
    ): Generator {
        // Save language.
        $currentLanguage = $GLOBALS['TL_LANGUAGE'];

        // Try to generate URLs.
        try {
            if ($metaModel instanceof ITranslatedMetaModel) {
                $prevLanguage = $metaModel->selectLanguage($language);
            }
            $GLOBALS['TL_LANGUAGE'] = $language;

            $listFilter = $this->getListFilter($metaModel, $filterId, $filterParams);

            $items = $metaModel->findByFilter(
                $listFilter,
                '',
                0,
                0,
                'ASC',
                array_values(array_merge($renderSetting->getSettingNames(), $filterAttributes))
            );

            foreach ($this->buildUrlsForItems($items, $renderSetting, $pageDetails) as $url) {
                yield $url;
            }
        } finally {
            // Reset language.
            $GLOBALS['TL_LANGUAGE'] = $currentLanguage;
            if (isset($prevLanguage) && $metaModel instanceof ITranslatedMetaModel) {
                $metaModel->selectLanguage($prevLanguage);
            }
        }
    }

    /**
     * Get all published index configs.
     *
     * @return Generator<int, TSearchablePageConfig>
     *
     * @throws Exception When a database error occur.
     */
    private function getPublishedConfigs(): Generator
    {
        $statement = $this->connection
            ->createQueryBuilder()
            ->select('t.*')
            ->from('tl_metamodel_searchable_pages', 't')
            ->where('t.published=1')
            ->executeQuery();

        /** @var TSearchablePageConfig $config */
        foreach ($statement->fetchAllAssociative() as $config) {
            yield $config;
        }
    }

    /**
     * Calculate list filter.
     *
     * @param IMetaModel                          $metaModel The MetaModel.
     * @param string                              $filterId  The id of the filter.
     * @param array<string, array{value: string}> $presets   The parameter preset values to use.
     *
     * @return IFilter
     */
    private function getListFilter(IMetaModel $metaModel, string $filterId, array $presets): IFilter
    {
        $filterSettings = $this->filterSettingFactory->createCollection($filterId);
        $presetNames    = $filterSettings->getParameters();
        $processed      = [];

        // We have to use all the preset values we want first.
        foreach ($presets as $presetName => $arrPreset) {
            if (in_array($presetName, $presetNames, true)) {
                $processed[$presetName] = $arrPreset['value'];
            }
        }

        // Create a new filter for the search.
        $filter        = $metaModel->getEmptyFilter();
        $filterSettings->addRules($filter, $processed);

        return $filter;
    }

    /**
     * Get a MetaModels by name or id.
     *
     * @param string $identifier The Name/ID of a MetaModels.
     *
     * @return IMetaModel|null
     *
     * @throws RuntimeException When the MetaModels is missing.
     */
    private function getMetaModel(string $identifier): ?IMetaModel
    {
        // Translate id to name.
        $identifier = $this->factory->translateIdToMetaModelName($identifier);

        return $this->factory->getMetaModel($identifier);
    }

    /**
     * Build URL for every item to detail page.
     *
     * @param IItems                   $items         The items to process.
     * @param IRenderSettingCollection $renderSetting The render settings for the detail page.
     * @param array                    $pageDetails   The page information for the detail page.
     *
     * @return Generator<int, string>
     */
    private function buildUrlsForItems(
        IItems $items,
        IRenderSettingCollection $renderSetting,
        array $pageDetails
    ): Generator {
        foreach ($items as $item) {
            assert($item instanceof Item);
            $jumpTo = $item->buildJumpToLink($renderSetting);
            $url    = UrlBuilder::fromUrl($jumpTo['url']);
            if (null === $url->getScheme()) {
                // Build the absolute url.
                $url = $this->getBaseUrl($pageDetails, $jumpTo['url']);
            }

            yield $url->getUrl();
        }
    }

    /**
     * Get the base URL.
     *
     * @param array<string, string> $pageDetails The page details.
     * @param string|null           $path        Additional path settings.
     *
     * @return UrlBuilder
     */
    private function getBaseUrl(array $pageDetails, string $path = null): UrlBuilder
    {
        $url = new UrlBuilder();
        // Set the domain (see contao/core#6421)
        $url->setHost($pageDetails['domain'] ?: Environment::get('host'));
        $url->setScheme($pageDetails['rootUseSSL'] ? 'https' : 'http');

        // Get the path.
        if ($path === null) {
            // Add dummy parameter, because non legacy mode parameter must not be null.
            $event = new GenerateFrontendUrlEvent(
                $pageDetails,
                ((bool) ($pageDetails['requireItem'] ?? false)) ? '/foo/bar' : null,
                $pageDetails['language'],
                true
            );
            $this->dispatcher->dispatch($event, ContaoEvents::CONTROLLER_GENERATE_FRONTEND_URL);
            $url->setPath($event->getUrl());
        } else {
            $url->setPath($path);
        }

        return $url;
    }

    /**
     * Remove the plain details URL from the sitemap to omit 404 without filter url.
     *
     * @param array      $pageDetails The page details information.
     * @param DOMElement $urlSet      The list of URLs.
     *
     * @return void
     */
    public function removePlainDetailsUrl(array $pageDetails, DOMElement $urlSet): void
    {
        $removeUrl = $this->getBaseUrl($pageDetails)->getUrl();
        foreach ($urlSet->childNodes as $childNode) {
            if (!$this->isDomElement($childNode, 'url')) {
                continue;
            }

            foreach ($childNode->childNodes as $childNode2) {
                if (!$this->isDomElement($childNode2, 'loc') || trim((string) $childNode2->nodeValue) !== $removeUrl) {
                    continue;
                }
                assert($childNode->parentNode instanceof \DOMNode);
                $childNode->parentNode->removeChild($childNode);

                return;
            }
        }
    }

    /**
     * Check if right DOM element.
     *
     * @param DOMNode $node     The node.
     * @param string  $nodeName The node name.
     *
     * @return bool
     */
    private function isDomElement(DOMNode $node, string $nodeName): bool
    {
        return ($node instanceof DOMElement) && $node->nodeName === $nodeName;
    }
}
