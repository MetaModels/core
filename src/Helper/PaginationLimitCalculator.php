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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2021 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Helper;

use Contao\Config;
use Contao\FrontendTemplate;
use Contao\System;
use InvalidArgumentException;
use MetaModels\Filter\FilterUrl;
use MetaModels\Filter\FilterUrlBuilder;

/**
 * Helper class to calculate limit and offset and pagination.
 */
class PaginationLimitCalculator
{
    /**
     * Use limit.
     *
     * @var bool
     */
    private bool $applyLimitAndOffset = false;

    /**
     * Offset.
     *
     * @var int
     */
    private int $offset = 0;

    /**
     * Limit.
     *
     * @var int
     */
    private int $limit = 0;

    /**
     * The current page.
     *
     * @var int
     */
    private int $currentPage;

    /**
     * Pagination page break.
     *
     * @var int
     */
    private int $perPage = 0;

    /**
     * The total amount of items.
     *
     * @var int
     */
    private int $totalAmount = 0;

    /**
     * The maximum number of pagination links.
     *
     * @var int|null
     */
    private ?int $maxPaginationLinks = null;

    /**
     * The calculated offset.
     *
     * @var int|null
     */
    private ?int $calculatedOffset = null;

    /**
     * The calculated limit.
     *
     * @var int|null
     */
    private ?int $calculatedLimit = null;

    /**
     * The calculated total amount.
     *
     * @var int
     */
    private int $calculatedTotal;

    /**
     * Flag if the data needs to be recalculated.
     *
     * @var bool
     */
    private bool $isDirty = true;

    /**
     * The filter url builder.
     *
     * @var FilterUrlBuilder|object|null
     */
    private FilterUrlBuilder $filterUrlBuilder;

    /**
     * The pagination URL key.
     *
     * @var string
     */
    private string $pageParam;

    /**
     * The pagination parameter URL type.
     *
     * @var string
     */
    private string $paramType;

    /**
     * The pagination template.
     *
     * @var string
     */
    private string $paginationTemplate;

    /**
     * Create a new instance.
     *
     * @param FilterUrlBuilder|null $filterUrlBuilder   The filter url builder.
     *
     * @param string                $pageParam          The pagination url key
     *
     * @param string                $paramType          The pagination parameter url type
     *
     * @param string                $paginationTemplate The pagination template
     */
    public function __construct(
        ?FilterUrlBuilder $filterUrlBuilder = null,
        string $pageParam = 'page',
        string $paramType = 'get',
        string $paginationTemplate = 'mm_pagination'
    ) {
        $this->pageParam          = $pageParam;
        $this->paramType          = $paramType;
        $this->paginationTemplate = $paginationTemplate;
        if (null === $filterUrlBuilder) {
            $filterUrlBuilder = System::getContainer()->get('metamodels.filter_url');
            @trigger_deprecation(
                'metamodels/core',
                '2.2.0',
                __CLASS__ . ' parameter FilterUrlBuilder is null, but the parameter should be set'
            );
        }
        $this->filterUrlBuilder = $filterUrlBuilder;

        $this->filterUrl = new FilterUrl();
        $this->filterUrlBuilder->addFromCurrentRequest($this->filterUrl);
    }

    /**
     * Check if the object needs to be recalculated.
     *
     * @return boolean
     */
    public function isDirty(): bool
    {
        return $this->isDirty;
    }

    /**
     * Mark the object dirty.
     *
     * @return PaginationLimitCalculator
     */
    public function markDirty(): self
    {
        $this->isDirty = true;

        return $this;
    }

    /**
     * Check if a limit shall be applied.
     *
     * @return boolean
     */
    public function isLimited(): bool
    {
        return $this->applyLimitAndOffset && ($this->getLimit() || $this->getOffset());
    }

    /**
     * Set if a limit shall be applied.
     *
     * @param boolean $applyLimitAndOffset The flag.
     *
     * @return PaginationLimitCalculator
     */
    public function setApplyLimitAndOffset(bool $applyLimitAndOffset): self
    {
        $this->applyLimitAndOffset = $applyLimitAndOffset;

        return $this->markDirty();
    }

    /**
     * Retrieve the offset to use.
     *
     * @return int
     */
    public function getOffset(): int
    {
        return $this->offset;
    }

    /**
     * Set the offset to use.
     *
     * @param int $offset The new offset.
     *
     * @return PaginationLimitCalculator
     */
    public function setOffset(int $offset): self
    {
        $this->offset = $offset;

        return $this->markDirty();
    }

    /**
     * Retrieve the limit.
     *
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }

    /**
     * Set the limit.
     *
     * @param int $limit The limit.
     *
     * @return PaginationLimitCalculator
     */
    public function setLimit(int $limit): self
    {
        $this->limit = $limit;

        return $this->markDirty();
    }

    /**
     * Retrieve the current page (in pagination).
     *
     * @return int
     */
    public function getCurrentPage(): int
    {
        if (isset($this->currentPage)) {
            return $this->currentPage;
        }

        switch ($this->paramType) {
            case 'get':
                return (int) ($this->filterUrl->getGet($this->pageParam) ?? 1);
            case 'slug':
                return (int) ($this->filterUrl->getSlug($this->pageParam) ?? 1);
            case 'slugNget':
                return (int) ($this->filterUrl->getGet($this->pageParam)
                              ?? $this->filterUrl->getSlug($this->pageParam)
                                 ?? 1);
        }

        throw new InvalidArgumentException('Invalid configured value: ' . $this->paramType);
    }

    /**
     * Set the current page (for pagination).
     *
     * @param int $currentPage The current page.
     *
     * @return PaginationLimitCalculator
     *
     * @deprecated The page is determined automatically from the current request.
     */
    public function setCurrentPage(int $currentPage): self
    {
        $this->currentPage = $currentPage;

        @trigger_deprecation(
            'metamodels/core',
            '2.2.0',
            __METHOD__ . ' is deprecated - the page is determined automatically from the current request'
        );

        return $this->markDirty();
    }

    /**
     * Set the pagination limit.
     *
     * @return int
     */
    public function getPerPage(): int
    {
        return $this->perPage;
    }

    /**
     * Set the pagination limit.
     *
     * @param int $perPage The pagination limit.
     *
     * @return PaginationLimitCalculator
     */
    public function setPerPage(int $perPage): self
    {
        $this->perPage = $perPage;

        return $this->markDirty();
    }

    /**
     * Retrieve the total amount of items.
     *
     * @return int
     */
    public function getTotalAmount(): int
    {
        return $this->totalAmount;
    }

    /**
     * Set the total amount of items.
     *
     * @param int $totalAmount The total amount.
     *
     * @return PaginationLimitCalculator
     */
    public function setTotalAmount(int $totalAmount): self
    {
        $this->totalAmount = $totalAmount;

        return $this->markDirty();
    }

    /**
     * Set the number of maximum pagination links.
     *
     * @return int
     */
    public function getMaxPaginationLinks(): int
    {
        if (null === $this->maxPaginationLinks) {
            $this->setMaxPaginationLinks(Config::get('maxPaginationLinks'));
        }

        return $this->maxPaginationLinks;
    }

    /**
     * Get the number of maximum pagination links.
     *
     * @param int $maxPaginationLinks The maximum number of links.
     *
     * @return PaginationLimitCalculator
     */
    public function setMaxPaginationLinks(int $maxPaginationLinks): self
    {
        $this->maxPaginationLinks = $maxPaginationLinks;

        return $this->markDirty();
    }

    /**
     * Render the pagination string.
     *
     * @return string
     */
    public function getPaginationString(): string
    {
        $this->calculate();

        if ($this->getPerPage() == 0) {
            return '';
        }

        $paginationTemplate = new FrontendTemplate($this->paginationTemplate);

        // Add pagination menu get parameter.
        $objPagination = new PaginationGenerator(
            $this->filterUrlBuilder,
            $this->calculatedTotal,
            $this->getPerPage(),
            $this->getMaxPaginationLinks(),
            $this->pageParam,
            $this->paramType,
            $paginationTemplate,
        );

        return $objPagination->generateForFilterUrl($this->filterUrl);
    }

    /**
     * Retrieve the calculated offset.
     *
     * @return int
     */
    public function getCalculatedOffset(): ?int
    {
        $this->calculate();

        return $this->calculatedOffset;
    }

    /**
     * Retrieve the calculated limit.
     *
     * @return int
     */
    public function getCalculatedLimit(): ?int
    {
        $this->calculate();

        return $this->calculatedLimit;
    }

    /**
     * Calculate the limit and offset with pagination.
     *
     * @return void
     */
    private function calculatePaginated()
    {
        $this->calculatedTotal = $this->getTotalAmount();

        // If a total limit has been defined, we need to honor that.
        if (($this->calculatedLimit !== null) && ($this->calculatedTotal > $this->calculatedLimit)) {
            $this->calculatedTotal -= $this->calculatedLimit;
        }
        $this->calculatedTotal -= $this->calculatedOffset;

        // Get the current page.
        $page = $this->getCurrentPage();

        if ($page > ($this->calculatedTotal / $this->getPerPage())) {
            $page = (int) ceil($this->calculatedTotal / $this->getPerPage());
        }

        // Set limit and offset.
        $pageOffset              = ((max($page, 1) - 1) * $this->getPerPage());
        $this->calculatedOffset += $pageOffset;
        if ($this->calculatedLimit === null) {
            $this->calculatedLimit = $this->getPerPage();
        } else {
            $this->calculatedLimit = min(($this->calculatedLimit - $this->calculatedOffset), $this->getPerPage());
        }
    }

    /**
     * Calculate the pagination based upon the offset, limit and total amount of items.
     *
     * @return void
     */
    protected function calculate()
    {
        if (!$this->isDirty()) {
            return;
        }

        $this->isDirty = false;

        $this->calculatedOffset = null;
        $this->calculatedLimit  = null;

        // If defined, we override the pagination here.
        if ($this->isLimited()) {
            if ($this->getLimit()) {
                $this->calculatedLimit = $this->getLimit();
            }
            if ($this->getOffset()) {
                $this->calculatedOffset = $this->getOffset();
            }
        }

        if ($this->getPerPage() > 0) {
            $this->calculatePaginated();

            return;
        }

        if ($this->calculatedLimit === null) {
            $this->calculatedLimit = 0;
        }
        if ($this->calculatedOffset === null) {
            $this->calculatedOffset = 0;
        }
    }
}
