<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2025 The MetaModels team.
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
 * @copyright  2012-2025 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Filter;

use Contao\PageModel;
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
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
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
     * Create a new instance.
     *
     * @param UrlGeneratorInterface $urlGenerator The Contao URL generator.
     * @param RequestStack          $requestStack The request stack.
     */
    public function __construct(
        UrlGeneratorInterface $urlGenerator,
        RequestStack $requestStack
    ) {
        $this->urlGenerator      = $urlGenerator;
        $this->requestStack      = $requestStack;
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
