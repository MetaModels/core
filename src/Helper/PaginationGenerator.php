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

use Contao\FrontendTemplate;
use Contao\StringUtil;
use Contao\Template;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provide methods to render a pagination menu.
 */
class PaginationGenerator
{
    /**
     * Current page number
     *
     * @var integer
     */
    protected int $intPage;

    /**
     * Total number of rows
     *
     * @var integer
     */
    protected int $intRows;

    /**
     * Number of rows per page
     *
     * @var integer
     */
    protected int $intRowsPerPage;

    /**
     * Total number of pages
     *
     * @var integer
     */
    protected int $intTotalPages;

    /**
     * Total number of links
     *
     * @var integer
     */
    protected int $intNumberOfLinks;

    /**
     * Label for the "<< first" link
     *
     * @var string
     */
    protected string $lblFirst;

    /**
     * Label for the "< previous" link
     *
     * @var string
     */
    protected string $lblPrevious;

    /**
     * Label for the "next >" link
     *
     * @var string
     */
    protected string $lblNext;

    /**
     * Label for the "last >>" link
     *
     * @var string
     */
    protected string $lblLast;

    /**
     * Label for "total pages"
     *
     * @var string
     */
    protected string $lblTotal;

    /**
     * Template object
     *
     * @var Template
     */
    protected Template $objTemplate;

    /**
     * Show "<< first" and "last >>" links
     *
     * @var boolean
     */
    protected bool $blnShowFirstLast = true;

    /**
     * Request url
     *
     * @var string
     */
    protected string $strUrl = '';

    /**
     * Page paramenter
     *
     * @var string
     */
    protected string $pageKey = 'page';

    /**
     * Variable connector
     *
     * @var string
     */
    protected string $strVarConnector = '?';

    /**
     * Data array
     *
     * @var array
     */
    protected array $arrData = [];

    /**
     * Force URL parameter
     *
     * @var boolean
     */
    protected bool $blnForceParam = false;

    /**
     * Set the number of rows, the number of results per pages and the number of links
     *
     * @param integer       $intRows       The number of rows
     * @param integer       $intPerPage    The number of items per page
     * @param integer       $numberOfLinks The number of links to generate
     * @param string        $pageKey       The parameter name
     * @param Template|null $objTemplate   The template object
     * @param string        $paramType     The parameter type
     */
    public function __construct(
        int $intRows,
        int $intPerPage,
        int $numberOfLinks = 7,
        string $pageKey = 'page',
        Template $objTemplate = null,
        string $paramType = 'get'
    ) {
        $this->intPage          = 1;
        $this->intRows          = (int) $intRows;
        $this->intRowsPerPage   = (int) $intPerPage;
        $this->intNumberOfLinks = (int) $numberOfLinks;

        if (\Input::get($pageKey) > 0) {
            $this->intPage = \Input::get($pageKey);
        }

        $this->pageKey = $pageKey;

        if (null === $objTemplate) {
            $objTemplate = new FrontendTemplate('mm_pagination');
        }

        $this->objTemplate = $objTemplate;

        $this->paramType = $paramType;
    }


    /**
     * Return true if the pagination menu has a "<< first" link
     *
     * @return boolean True if the pagination menu has a "<< first" link
     */
    public function hasFirst(): bool
    {
        return $this->blnShowFirstLast && $this->intPage > 2;
    }

    /**
     * Return true if the pagination menu has a "< previous" link
     *
     * @return boolean True if the pagination menu has a "< previous" link
     */
    public function hasPrevious(): bool
    {
        return $this->intPage > 1;
    }

    /**
     * Return true if the pagination menu has a "next >" link
     *
     * @return boolean True if the pagination menu has a "next >" link
     */
    public function hasNext(): bool
    {
        return $this->intPage < $this->intTotalPages;
    }

    /**
     * Return true if the pagination menu has a "last >>" link
     *
     * @return boolean True if the pagination menu has a "last >>" link
     */
    public function hasLast(): bool
    {
        return $this->blnShowFirstLast && $this->intPage < ($this->intTotalPages - 1);
    }

    public function generateForRequest(Request $request): string
    {
        $this->strUrl = $request->getRequestUri();

        return '';
    }

    /**
     * Generate the pagination menu and return it as HTML string
     *
     * @return string The pagination menu as HTML string
     */
    public function generate(): string
    {
        if ($this->intRowsPerPage < 1) {
            return '';
        }

        $blnQuery = false;
        [$this->strUrl] = explode('?', \Environment::get('request'), 2);

        // Prepare the URL
        foreach (preg_split('/&(amp;)?/', \Environment::get('queryString'), -1, PREG_SPLIT_NO_EMPTY) as $fragment) {
            if (strpos($fragment, $this->pageKey . '=') === false) {
                $this->strUrl .= (!$blnQuery ? '?' : '&amp;') . $fragment;
                $blnQuery     = true;
            }
        }

        $this->strVarConnector = $blnQuery ? '&amp;' : '?';
        $this->intTotalPages   = ceil($this->intRows / $this->intRowsPerPage);

        $this->strUrl = \Environment::get('request');

        // Return if there is only one page
        if ($this->intTotalPages < 2 || $this->intRows < 1) {
            return '';
        }

        if ($this->intPage > $this->intTotalPages) {
            $this->intPage = $this->intTotalPages;
        }

        $objTemplate = $this->objTemplate;

        $objTemplate->hasFirst      = $this->hasFirst();
        $objTemplate->hasPrevious   = $this->hasPrevious();
        $objTemplate->hasNext       = $this->hasNext();
        $objTemplate->hasLast       = $this->hasLast();
        $objTemplate->pages         = $this->getItemsAsArray();
        $objTemplate->intPage       = $this->intPage;
        $objTemplate->intTotalPages = $this->intTotalPages;

        $objTemplate->first = [
            'href' => $this->linkToPage(1),
        ];

        $objTemplate->previous = [
            'href' => $this->linkToPage($this->intPage - 1),
        ];

        $objTemplate->next = [
            'href' => $this->linkToPage($this->intPage + 1),
        ];

        $objTemplate->last = [
            'href' => $this->linkToPage($this->intTotalPages),
        ];

        $objTemplate->class = 'pagination-' . $this->pageKey;

        // Adding rel="prev" and rel="next" links is not possible
        // anymore with unique variable names (see #3515 and #4141)

        return $objTemplate->parse();
    }

    /**
     * Generate all page links and return them as array
     *
     * @return array The page links as array
     */
    public function getItemsAsArray()
    {
        $arrLinks = [];

        $intNumberOfLinks = floor($this->intNumberOfLinks / 2);
        $intFirstOffset   = $this->intPage - $intNumberOfLinks - 1;

        if ($intFirstOffset > 0) {
            $intFirstOffset = 0;
        }

        $intLastOffset = $this->intPage + $intNumberOfLinks - $this->intTotalPages;

        if ($intLastOffset < 0) {
            $intLastOffset = 0;
        }

        $intFirstLink = $this->intPage - $intNumberOfLinks - $intLastOffset;

        if ($intFirstLink < 1) {
            $intFirstLink = 1;
        }

        $intLastLink = $this->intPage + $intNumberOfLinks - $intFirstOffset;

        if ($intLastLink > $this->intTotalPages) {
            $intLastLink = $this->intTotalPages;
        }

        for ($i = $intFirstLink; $i <= $intLastLink; $i++) {
            if ($i == $this->intPage) {
                $arrLinks[] = [
                    'page' => $i,
                    'href' => null
                ];
            } else {
                $arrLinks[] = [
                    'page' => $i,
                    'href' => $this->linkToPage($i)
                ];
            }
        }

        return $arrLinks;
    }

    /**
     * Generate a link and return the URL
     *
     * @param integer $intPage The page ID
     *
     * @return string The URL string
     */
    protected function linkToPage($intPage)
    {
        if ($intPage <= 1) {
            return ampersand($this->strUrl);
        }

        return ampersand($this->strUrl) . $this->strVarConnector . $this->pageKey . '=' . $intPage;
    }
}
