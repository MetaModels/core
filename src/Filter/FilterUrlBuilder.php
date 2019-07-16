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
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Filter;

use Contao\Config;
use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Routing\UrlGenerator;
use Contao\PageModel;
use Contao\System;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * This class builds filter URLs.
 */
class FilterUrlBuilder
{
    /**
     * The URL generator.
     *
     * @var UrlGenerator
     */
    private $urlGenerator;

    /**
     * The request stack.
     *
     * @var RequestStack
     */
    private $requestStack;

    /**
     * Flag if the locale is prepended.
     *
     * @var bool
     */
    private $isLocalePrepended = true;

    /**
     * The Contao URL suffix.
     *
     * @var string
     */
    private $urlSuffix = '.html';

    /**
     * The page model adapter.
     *
     * @var Adapter|PageModel
     */
    private $pageModelAdapter;

    /**
     * Create a new instance.
     *
     * @param UrlGenerator $urlGenerator      The Contao URL generator.
     * @param RequestStack $requestStack      The request stack.
     * @param bool         $isLocalePrepended Flag if the locale is prepended to the URL.
     * @param string       $urlSuffix         The URL suffix.
     * @param Adapter      $pageModelAdapter  The page model adapter.
     */
    public function __construct(
        UrlGenerator $urlGenerator,
        RequestStack $requestStack,
        bool $isLocalePrepended,
        string $urlSuffix,
        Adapter $pageModelAdapter
    ) {
        $this->urlGenerator      = $urlGenerator;
        $this->requestStack      = $requestStack;
        $this->isLocalePrepended = $isLocalePrepended;
        $this->urlSuffix         = $urlSuffix;
        $this->pageModelAdapter  = $pageModelAdapter;
    }

    /**
     * Generate a frontend url.
     *
     * @param FilterUrl $filterUrl The filter URL.
     *
     * @return string
     */
    public function generate(FilterUrl $filterUrl)
    {
        $jumpTo = $filterUrl->getPage();

        // If no alias given, stay on current page.
        if (empty($jumpTo['alias'])) {
            $this->addFromCurrentRequest($filterUrl = $filterUrl->clone(), []);

            $jumpTo = $filterUrl->getPage();
        }
        $alias = $jumpTo['alias'];

        $parameters = $filterUrl->getGetParameters();

        $url = $alias;
        if ($filterUrl->hasSlug('auto_item')) {
            $url .= '/' . $this->encodeForAllowEncodedSlashes($filterUrl->getSlug('auto_item'));
        }

        if (!empty($jumpTo['domain'])) {
            $parameters['_domain'] = $jumpTo['domain'];
        }
        if (!empty($jumpTo['rootUseSSL'])) {
            $parameters['_ssl'] = (bool) $jumpTo['rootUseSSL'];
        }

        if ($filterUrl->hasSlug('language')) {
            $parameters['_locale'] = $filterUrl->getSlug('language');
        }
        foreach ($filterUrl->getSlugParameters() as $name => $value) {
            if (in_array($name, ['language', 'auto_item'])) {
                continue;
            }

            // Encode slashes in slugs - otherwise Symfony won't handle them correctly.
            // This mitigates for http://httpd.apache.org/docs/current/mod/core.html#allowencodedslashes
            // If not given, apache would 404 otherwise.
            $url .= '/' . $this->encodeForAllowEncodedSlashes($name) .
                '/' . $this->encodeForAllowEncodedSlashes($value);
        }

        return $this->urlGenerator->generate($url, $parameters);
    }

    /**
     * Generate a filter URL from the current request.
     *
     * @param array $options The options for updating - for details see FilterUrlBuilder::addFromCurrentRequest().
     *
     * @return FilterUrl
     */
    public function getCurrentFilterUrl($options = null): FilterUrl
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
     *   bool postAsSlug  Fields of POST data that shall be added to the slug entries.
     *                    default: []
     *   bool postAsGet   Fields of POST data that shall be added to the GET entries.
     *                    default: []
     *   bool preserveGet Flag if the GET parameters shall be added to the filter URL.
     *                    default: true
     *
     * @param FilterUrl $filterUrl The filter URL to update.
     * @param array     $options   The options for updating.
     *
     * @return void
     */
    public function addFromCurrentRequest(FilterUrl $filterUrl, $options = null): void
    {
        if (null === $options) {
            $options = [
                'postAsSlug'  => [],
                'postAsGet'   => [],
                'preserveGet' => true
            ];
        }

        $request = $this->requestStack->getMasterRequest();

        if (isset($options['preserveGet'])) {
            foreach ($request->query->all() as $name => $value) {
                $filterUrl->setGet($name, $value);
            }
        }

        if (null === $fragments = $this->determineFragments($request)) {
            return;
        }

        $filterUrl->setPageValue('alias', $fragments[0]);
        // Add the fragments to the slug array
        for ($i = 1, $c = \count($fragments); $i < $c; $i += 2) {
            // Skip key value pairs if the key is empty (see contao/core/#4702)
            if ('' === $fragments[$i]) {
                continue;
            }

            // Decode slashes in slugs - They got encoded in generate() above.
            $filterUrl->setSlug(
                $this->decodeForAllowEncodedSlashes($fragments[$i]),
                $this->decodeForAllowEncodedSlashes($fragments[($i + 1)])
            );
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
        return str_replace(['/', '\\'], ['%2F', '%5C'], $value);
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
        return str_replace(['%2F', '%5C'], ['/', '\\'], $value);
    }

    /**
     * Determine the fragments for the passed request.
     *
     * @param Request $request The request to parse.
     *
     * @return array|null
     *
     * @@SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @@SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function determineFragments(Request $request): ?array
    {
        if (null === $requestUri = $this->strippedUri($request)) {
            return null;
        }

        $fragments = null;
        // Use folder-style URLs
        if (Config::get('folderUrl') && false !== strpos($requestUri, '/')) {
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
            $fragments = explode('/', $requestUri);
        }

        // Add the second fragment as auto_item if the number of fragments is even
        if (Config::get('useAutoItem') && 0 === (\count($fragments) % 2)) {
            array_insert($fragments, 1, ['auto_item']);
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
     * @return string
     */
    private function strippedUri(Request $request): ?string
    {
        // Strip leading slash.
        if (null === $request || '' === $requestUri = rawurldecode(substr($request->getPathInfo(), 1))) {
            return null;
        }
        if ($this->isLocalePrepended) {
            $matches = [];
            // Use the matches instead of substr() (thanks to Mario Müller)
            if (preg_match('@^([a-z]{2}(-[A-Z]{2})?)/(.*)$@', $requestUri, $matches)) {
                $requestUri = $matches[3];
            }
        }

        // Remove the URL suffix if not just a language root (e.g. en/) is requested
        if ('' !== $this->urlSuffix && '' !== $requestUri && (
                !$this->isLocalePrepended || !preg_match('@^[a-z]{2}(-[A-Z]{2})?/$@', $requestUri)
            )) {
            $requestUri = substr($requestUri, 0, -\strlen($this->urlSuffix));
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
     * @return array
     */
    private function getFolderUrlFragments(string $alias, string $host, string $locale = null): ?array
    {
        // Check if there are pages with a matching alias
        $pages = $this->getPageCandidates($alias);
        if (null === $pages) {
            return null;
        }

        // Look for a root page whose domain name matches the host name
        if (isset($pages[$host])) {
            $languages = $pages[$host];
        } else {
            // empty domain
            $languages = $pages['*'] ?: [];
        }
        unset($pages);

        $pages = [];

        if (!$this->isLocalePrepended) {
            // Use the first result (see #4872)
            $pages = current($languages);
        } elseif ($locale && isset($languages[$locale])) {
            // Try to find a page matching the language parameter
            $pages = $languages[$locale];
        }

        // Return if there are no matches
        if (empty($pages)) {
            return null;
        }

        /** @var PageModel $page */
        $page = $pages[0];

        // The request consists of the alias only
        if ($alias == $page->alias) {
            $arrFragments = [$alias];
        } else {
            // Remove the alias from the request string, explode it and then re-insert it at the beginning.
            $arrFragments = explode('/', substr($alias, (\strlen($page->alias) + 1)));
            array_unshift($arrFragments, $page->alias);
        }

        return $arrFragments;
    }

    /**
     * Fetch matching page candidates.
     *
     * @param string $alias The requested alias.
     *
     * @return array|null
     */
    private function getPageCandidates(string $alias)
    {
        $aliases = [$alias];
        // Compile all possible aliases by applying dirname() to the request.
        while ('/' !== $alias && false !== strpos($alias, '/')) {
            $alias     = \dirname($alias);
            $aliases[] = $alias;
        }

        // Check if there are pages with a matching alias
        $pages = $this->pageModelAdapter->findByAliases($aliases);
        if (null === $pages) {
            return null;
        }
        $arrPages = [];
        // Order by domain and language
        while ($pages->next()) {
            /** @var PageModel $objModel */
            $objModel = $pages->current();
            $objPage  = $objModel->loadDetails();
            $domain   = $objPage->domain ?: '*';

            $arrPages[$domain][$objPage->rootLanguage][] = $objPage;
            // Also store the fallback language
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
    private function extractPostData(FilterUrl $filterUrl, $options, Request $request): void
    {
        if (empty($options['postAsSlug']) && empty($options['postAsGet'])) {
            return;
        }

        foreach ($request->request->all() as $name => $value) {
            if (is_array($value)) {
                $value = implode(',', $value);
            }
            if (in_array($name, $options['postAsSlug'])) {
                $filterUrl->setSlug($name, $value);
            }
            if (in_array($name, $options['postAsGet'])) {
                $filterUrl->setGet($name, $value);
            }
        }
    }
}
