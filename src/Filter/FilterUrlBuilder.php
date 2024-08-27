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
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Andreas Fischer <anfischer@kaffee-partner.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Filter;

use Contao\ArrayUtil;
use Contao\Config;
use Contao\CoreBundle\Framework\Adapter;
use Contao\Model\Collection;
use Contao\PageModel;
use Contao\System;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Route;

/**
 * This class builds filter URLs.
 *
 * @psalm-type TFilterUrlOptions=array{
 *   postAsSlug?: list<string>,
 *   postAsGet?: list<string>,
 *   preserveGet?: bool,
 *   removeGetOnSlug?: bool
 * }
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class FilterUrlBuilder
{
    /**
     * The URL generator.
     *
     * @var UrlGeneratorInterface
     */
    private UrlGeneratorInterface $urlGenerator;

    /**
     * The request stack.
     *
     * @var RequestStack
     */
    private RequestStack $requestStack;

    /**
     * The page model adapter.
     *
     * @var Adapter<PageModel>
     */
    private Adapter $pageModelAdapter;

    /**
     * Create a new instance.
     *
     * @param UrlGeneratorInterface $urlGenerator      The Contao URL generator.
     * @param RequestStack          $requestStack      The request stack.
     * @param Adapter<PageModel>    $pageModelAdapter  The page model adapter.
     */
    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        RequestStack $requestStack,
        Adapter $pageModelAdapter
    ) {
        $this->urlGenerator      = $urlGenerator;
        $this->requestStack      = $requestStack;
        $this->pageModelAdapter  = $pageModelAdapter;
    }

    /**
     * Generate a frontend url.
     *
     * @param FilterUrl $filterUrl The filter URL.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function generate(FilterUrl $filterUrl): string
    {
        $jumpTo = $filterUrl->getPage();

        // If no alias given, stay on current page.
        if (empty($jumpTo['alias'])) {
            $this->addFromCurrentRequest($filterUrl = $filterUrl->clone(), []);

            $jumpTo = $filterUrl->getPage();
        }

        $parameters = $filterUrl->getGetParameters();

        $url = '';
        if ($filterUrl->hasSlug('auto_item') && '' !== ($slug = (string) $filterUrl->getSlug('auto_item'))) {
            $url .= '/' . $this->encodeForAllowEncodedSlashes($slug);
        }

        if (null !== ($locale = $jumpTo['language'] ?? null)) {
            $parameters['_locale'] = $locale;
        }
        foreach ($filterUrl->getSlugParameters() as $name => $value) {
            if ($name === 'auto_item') {
                continue;
            }

            // Encode slashes in slugs - otherwise Symfony won't handle them correctly.
            // This mitigates for http://httpd.apache.org/docs/current/mod/core.html#allowencodedslashes
            // If not given, apache would 404 otherwise.
            $url .= '/' . $this->encodeForAllowEncodedSlashes($name) .
                '/' . $this->encodeForAllowEncodedSlashes($value);
        }

        $parameters['parameters'] = $url;

        return $this->urlGenerator->generate('tl_page.' . $jumpTo['id'], $parameters);
    }

    /**
     * Generate a filter URL from the current request.
     *
     * @param TFilterUrlOptions|null $options The options for updating - for details
     *                                        see FilterUrlBuilder::addFromCurrentRequest().
     *
     * @return FilterUrl
     */
    public function getCurrentFilterUrl(array $options = null): FilterUrl
    {
        $this->addFromCurrentRequest($filterUrl = new FilterUrl(), $options);

        return $filterUrl;
    }

    /**
     * Split the current request into fragments, strip the URL suffix, recreate the $_GET array and return the page ID
     *
     * This is mostly based on \Contao\Frontend::getPageIdFromUrl() but stripped off of some checks.
     *
     * Options may be:
     *   bool postAsSlug      Fields of POST data that shall be added to the slug entries.
     *                        default: []
     *   bool postAsGet       Fields of POST data that shall be added to the GET entries.
     *                        default: []
     *   bool preserveGet     Flag if the GET parameters shall be added to the filter URL.
     *                        default: true
     *   bool removeGetOnSlug Flag to remove GET parameters from the filter URL when a same named slug parameter exists.
     *                        default: true
     *
     * @param FilterUrl  $filterUrl The filter URL to update.
     * @param TFilterUrlOptions|null $options   The options for updating.
     *
     * @return void
     */
    public function addFromCurrentRequest(FilterUrl $filterUrl, array $options = null): void
    {
        if (null === $options) {
            $options = [
                'postAsSlug'      => [],
                'postAsGet'       => [],
                'preserveGet'     => true,
                'removeGetOnSlug' => true
            ];
        }

        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return;
        }

        if ($options['preserveGet'] ?? true) {
            foreach ($request->query->all() as $name => $value) {
                $filterUrl->setGet($name, $value);
            }
        }

        $routeName = $this->determineRouteName($request);

        $filterUrl->setPageValue('id', \substr($routeName, 8));
        $requestUri = \rawurldecode(\substr($request->getPathInfo(), 1));

        if (null === ($route = $request->attributes->get('_route_object'))) {
            return;
        }
        assert($route instanceof Route);

        $pageModel = $route->getDefault('pageModel');
        assert($pageModel instanceof PageModel);

        $length    = $pageModel->urlSuffix ? -\strlen($pageModel->urlSuffix) : null;
        $start     = ($pageModel->urlPrefix ? \strlen($pageModel->urlPrefix . '/') : 0)
                     + \strlen($pageModel->alias . '/');
        $fragments = \explode('/', \substr($requestUri, $start, $length));

        if (1 === \count($fragments) % 2) {
            \array_unshift($fragments, 'auto_item');
        }
        \array_unshift($fragments, $pageModel->alias);

        // If alias part is empty, this means we have the 'index' page.
        if ('' === $fragments[0]) {
            $fragments[0] = 'index';
        }

        $filterUrl->setPageValue('alias', $fragments[0]);
        // Add the fragments to the slug array.
        for ($i = 1, $c = \count($fragments); $i < $c; $i += 2) {
            // Skip key value pairs if the key is empty (see contao/core/#4702).
            if ('' === $fragments[$i]) {
                continue;
            }

            // Decode slashes in slugs - They got encoded in generate() above.
            $name = $this->decodeForAllowEncodedSlashes($fragments[$i]);
            $filterUrl->setSlug(
                $name,
                $this->decodeForAllowEncodedSlashes($fragments[($i + 1)])
            );
            if (($options['removeGetOnSlug'] ?? true) && $filterUrl->hasGet($name)) {
                $filterUrl->setGet($name, '');
            }
        }

        $this->extractPostData($filterUrl, $options, $request);
    }

    /**
     * Mitigate for apache AllowEncodedSlashes directive being off.
     *
     * @param string $value The value to mitigate for.
     *
     * @return string
     *
     * @see http://httpd.apache.org/docs/current/mod/core.html#allowencodedslashes
     */
    private function encodeForAllowEncodedSlashes(string $value): string
    {
        return \str_replace(['/', '\\'], ['%2F', '%5C'], $value);
    }

    /**
     * Mitigate for apache AllowEncodedSlashes directive being off.
     *
     * @param string $value The value to mitigate for.
     *
     * @return string
     *
     * @see http://httpd.apache.org/docs/current/mod/core.html#allowencodedslashes
     */
    private function decodeForAllowEncodedSlashes(string $value): string
    {
        return \str_replace(['%2F', '%5C'], ['/', '\\'], $value);
    }

    /**
     * Determine the fragments for the passed request.
     *
     * @param Request $request The request to parse.
     *
     * @return array|null
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function determineFragments(Request $request): ?array
    {
        if (null === $requestUri = $this->strippedUri($request)) {
            return null;
        }

        $fragments = null;
        // Use folder-style URLs.
        if (Config::get('folderUrl') && \str_contains($requestUri, '/')) {
            $fragments = $this->getFolderUrlFragments(
                $requestUri,
                $request->getHttpHost(),
                $request->attributes->get('_locale')
            );
        }

        // If folderUrl is deactivated or did not find a matching page
        if (null === $fragments) {
            if ('/' === $requestUri) {
                return null;
            }
            $fragments = \explode('/', $requestUri);
        }

        // Add the second fragment as auto_item if the number of fragments is even
        if (Config::get('useAutoItem') && 0 === (\count($fragments) % 2)) {
            \array_splice($fragments, 1, 0, 'auto_item');
        }

        $fragments = $this->getPageIdFromUrlHook($fragments);

        // Return if the alias is empty (see #4702 and #4972)
        if (null === $fragments || ('' === $fragments[0] && \count($fragments) > 1)) {
            return null;
        }

        return $fragments;
    }

    /**
     * Strip the leading locale (if any).
     *
     * @param Request $request The request.
     *
     * @return string|null
     */
    private function strippedUri(Request $request): ?string
    {
        // Strip leading slash.
        if ('' === $requestUri = \rawurldecode(\substr($request->getPathInfo(), 1))) {
            return null;
        }

        return $requestUri;
    }

    /**
     * Update the fragments for folder URL aliases.
     *
     * @param string      $alias  The relative request.
     * @param string      $host   The host part of the current request.
     * @param string|null $locale The current locale or null if none requested.
     *
     * @return array|null
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function getFolderUrlFragments(string $alias, string $host, string $locale = null): ?array
    {
        // Check if there are pages with a matching alias
        $pages = $this->getPageCandidates($alias);
        if ([] === $pages) {
            return null;
        }

        // Look for a root page whose domain name matches the host name
        $languages = $pages[$host]
            // empty domain
            ?? ($pages['*'] ?: []);
        unset($pages);

        $pages = [];

        if (null !== $locale && isset($languages[$locale])) {
            // Try to find a page matching the language parameter
            $pages = $languages[$locale];
        }

        // Return if there are no matches.
        if (empty($pages)) {
            return null;
        }

        /** @var PageModel $page */
        $page = $pages[0];

        // The request consists of the alias only
        if ($alias === $page->alias) {
            $arrFragments = [$alias];
        } else {
            // Remove the alias from the request string, explode it and then re-insert it at the beginning.
            $arrFragments = \explode('/', \substr($alias, (\strlen($page->alias) + 1)));
            \array_unshift($arrFragments, $page->alias);
        }

        return $arrFragments;
    }

    /**
     * Fetch matching page candidates.
     *
     * @param string $alias The requested alias.
     *
     * @return array<non-falsy-string, array<string, non-empty-list<PageModel>>>
     */
    private function getPageCandidates(string $alias): array
    {
        $aliases = [$alias];
        // Compile all possible aliases by applying dirname() to the request.
        while ('/' !== $alias && \str_contains($alias, '/')) {
            $alias     = \dirname($alias);
            $aliases[] = $alias;
        }

        // Check if there are pages with a matching alias - sort by priority desc and alias* desc.
        // *: You can assume that if folderurl is enabled, the lower hierarchy pages will have a
        // longer alias string - hence descending sorting.
        /** @psalm-suppress InternalMethod */
        $pages = $this->pageModelAdapter->findByAliases(
            $aliases,
            ['order' => 'tl_page.routePriority DESC, tl_page.alias DESC']
        );
        assert($pages instanceof Collection);

        $arrPages = [];
        // Order by domain and language.
        while ($pages->next()) {
            /** @var PageModel $objModel */
            $objModel = $pages->current();
            $objPage  = $objModel->loadDetails();
            $domain   = $objPage->domain ?: '*';

            $arrPages[$domain][$objPage->rootLanguage][] = $objPage;
            // Also store the fallback language.
            if ($objPage->rootIsFallback) {
                $arrPages[$domain]['*'][] = $objPage;
            }
        }

        return $arrPages;
    }

    /**
     * Call the getPageIdFromUrl HOOKs.
     *
     * @param array|null $fragments The input fragments.
     *
     * @return array|null
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function getPageIdFromUrlHook(?array $fragments): ?array
    {
        if (!(isset($GLOBALS['TL_HOOKS']['getPageIdFromUrl']) && \is_array($GLOBALS['TL_HOOKS']['getPageIdFromUrl']))) {
            return $fragments;
        }
        foreach ($GLOBALS['TL_HOOKS']['getPageIdFromUrl'] as $callback) {
            $fragments = System::importStatic($callback[0])->{$callback[1]}($fragments);
        }

        return $fragments;
    }

    /**
     * Extract POST data from the passed request.
     *
     * @param FilterUrl $filterUrl The filter URL to populate.
     * @param array     $options   The options.
     * @param Request   $request   The request.
     *
     * @return void
     */
    private function extractPostData(FilterUrl $filterUrl, array $options, Request $request): void
    {
        if (!$request->isMethod('POST')) {
            return;
        }

        if (empty($options['postAsSlug']) && empty($options['postAsGet'])) {
            return;
        }

        foreach ($request->request->all() as $name => $value) {
            if (\is_array($value)) {
                $value = \implode(',', $value);
            }
            if (\in_array($name, $options['postAsSlug'])) {
                $filterUrl->setSlug($name, $value);
            }
            if (\in_array($name, $options['postAsGet'])) {
                $filterUrl->setGet($name, $value);
            }
        }
    }

    /**
     * Determine route name.
     *
     * @param Request $request
     *
     * @return string
     */
    public function determineRouteName(Request $request): string
    {
        $pageModel = $request->attributes->get('pageModel');

        return 'tl_page.' . match (true) {
            ($pageModel instanceof PageModel) => $pageModel->id,
            \is_int($pageModel) => (string) $pageModel,
            default =>
                throw new \RuntimeException('Unknown page model encountered: ' . \get_debug_type($pageModel)),
        };
    }
}
