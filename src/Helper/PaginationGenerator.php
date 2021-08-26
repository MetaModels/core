<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2021 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2021 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Helper;

use Contao\Input;
use Contao\Template;
use InvalidArgumentException;
use MetaModels\Filter\FilterUrl;
use MetaModels\Filter\FilterUrlBuilder;

/**
 * Provide methods to render a pagination menu.
 */
class PaginationGenerator
{
    /**
     * @var FilterUrlBuilder
     */
    private FilterUrlBuilder $urlBuilder;

    /**
     * Total number of rows
     *
     * @var integer
     */
    private int $numRows;

    /**
     * Number of rows per page
     *
     * @var integer
     */
    private int $rowsPerPage;

    /**
     * Total number of pages
     *
     * @var integer
     */
    private int $totalPages;

    /**
     * Total number of links
     *
     * @var integer
     */
    private int $numberOfLinks;

    /**
     * Template object
     *
     * @var Template
     */
    private Template $template;

    /**
     * The parameter name
     *
     * @var string
     */
    private string $pageParam;

    /**
     * The parameter type
     *
     * @var string
     */
    private string $paramType;

    /**
     * Set the number of rows, the number of results per pages and the number of links
     *
     * @param integer  $numRows            The number of rows
     * @param integer  $rowsPerPage        The number of items per page
     * @param integer  $numberOfLinks      The number of links to generate
     * @param string   $pageParam          The pagination url key
     * @param string   $paramType          The pagination parameter url type
     * @param Template $paginationTemplate The pagination template
     */
    public function __construct(
        FilterUrlBuilder $urlBuilder,
        int $numRows,
        int $rowsPerPage,
        int $numberOfLinks,
        string $pageParam,
        string $paramType,
        Template $paginationTemplate
    ) {
        $this->urlBuilder    = $urlBuilder;
        $this->numRows       = $numRows;
        $this->rowsPerPage   = $rowsPerPage;
        $this->totalPages    = ceil($this->numRows / $this->rowsPerPage);
        $this->numberOfLinks = $numberOfLinks;
        $this->pageParam     = $pageParam;
        $this->paramType     = $paramType;
        $this->template      = $paginationTemplate;
    }

    /**
     * Generate the pagination menu and return it as HTML string
     *
     * @param FilterUrl $filterUrl The filter URL.
     *
     * @return string The pagination menu as HTML string
     */
    public function generateForFilterUrl(FilterUrl $filterUrl): string
    {
        if ($this->rowsPerPage < 1) {
            return '';
        }

        $page = $this->getCurrentPage($filterUrl);
        // This is needed to mark the parameter used.
        Input::get($this->pageParam);

        // Return if there is only one page
        if ($this->totalPages < 2 || $this->numRows < 1) {
            return '';
        }

        if ($page > $this->totalPages) {
            $page = $this->totalPages;
        }

        $template                = $this->template;
        $template->hasFirst      = $this->hasFirst($page);
        $template->hasPrevious   = $this->hasPrevious($page);
        $template->hasNext       = $this->hasNext($page);
        $template->hasLast       = $this->hasLast($page);
        $template->pages         = $this->getItemsAsArray($filterUrl, $page);
        $template->page          = $page;
        $template->totalPages    = $this->totalPages;
        $template->first         = $template->hasFirst ? $this->linkToPage($filterUrl, 1) : '';
        $template->previous      = $template->hasPrevious ? $this->linkToPage($filterUrl, $page - 1) : '';
        $template->next          = $template->hasNext ? $this->linkToPage($filterUrl, $page + 1) : '';
        $template->last          = $template->hasLast ? $this->linkToPage($filterUrl, $this->totalPages) : '';
        $template->class         = 'pagination-' . $this->pageParam;
        // Adding rel="prev" and rel="next" links is not possible
        // anymore with unique variable names (see #3515 and #4141)

        return $template->parse();
    }

    /**
     * Retrieve the current page (in pagination).
     *
     * @param FilterUrl $filterUrl The filter URL.
     *
     * @return int
     */
    private function getCurrentPage(FilterUrl $filterUrl): int
    {
        switch ($this->paramType) {
            case 'get':
                return (int) ($filterUrl->getGet($this->pageParam) ?? 1);
            case 'slug':
                return (int) ($filterUrl->getSlug($this->pageParam) ?? 1);
            case 'slugNget':
                return (int) ($filterUrl->getGet($this->pageParam)
                    ?? $filterUrl->getSlug($this->pageParam)
                    ?? 1);
        }

        throw new InvalidArgumentException('Invalid configured value: ' . $this->paramType);
    }

    /**
     * Return true if the pagination menu has a "<< first" link
     *
     * @return boolean True if the pagination menu has a "<< first" link
     */
    private function hasFirst(int $page): bool
    {
        return $page > 2;
    }

    /**
     * Return true if the pagination menu has a "< previous" link
     *
     * @return boolean True if the pagination menu has a "< previous" link
     */
    public function hasPrevious(int $page): bool
    {
        return $page > 1;
    }

    /**
     * Return true if the pagination menu has a "next >" link
     *
     * @return boolean True if the pagination menu has a "next >" link
     */
    private function hasNext(int $page): bool
    {
        return $page < $this->totalPages;
    }

    /**
     * Return true if the pagination menu has a "last >>" link
     *
     * @return boolean True if the pagination menu has a "last >>" link
     */
    private function hasLast(int $page): bool
    {
        return $page < ($this->totalPages - 1);
    }

    /**
     * Generate all page links and return them as array
     *
     * @return array The page links as array
     */
    private function getItemsAsArray(FilterUrl $filterUrl, int $page): array
    {
        $links = [];

        $numberOfLinks = floor($this->numberOfLinks / 2);
        $firstOffset   = $page - $numberOfLinks - 1;

        if ($firstOffset > 0) {
            $firstOffset = 0;
        }

        $lastOffset = $page + $numberOfLinks - $this->totalPages;

        if ($lastOffset < 0) {
            $lastOffset = 0;
        }

        $firstLink = $page - $numberOfLinks - $lastOffset;

        if ($firstLink < 1) {
            $firstLink = 1;
        }

        $lastLink = $page + $numberOfLinks - $firstOffset;

        if ($lastLink > $this->totalPages) {
            $lastLink = $this->totalPages;
        }

        for ($i = $firstLink; $i <= $lastLink; $i++) {
            if ($i == $page) {
                $links[] = [
                    'page' => $i,
                    'href' => null
                ];
            } else {
                $links[] = [
                    'page' => $i,
                    'href' => $this->linkToPage($filterUrl, $i)
                ];
            }
        }

        return $links;
    }

    /**
     * Generate a link and return the URL.
     *
     * @param FilterUrl $filterUrl
     *
     * @param integer   $page The page number.
     *
     * @return string The URL string
     */
    private function linkToPage(FilterUrl $filterUrl, int $page): string
    {
        // Set first without params.
        if ($page <= 1) {
            $page = '';
        }

        $pageFilterUrl = $filterUrl->clone();
        if ($this->paramType === 'get') {
            $pageFilterUrl->setGet($this->pageParam, $page);
        } else {
            $pageFilterUrl->setSlug($this->pageParam, $page);
        }

        return $this->urlBuilder->generate($pageFilterUrl);
    }
}
