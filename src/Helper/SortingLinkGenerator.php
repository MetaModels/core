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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Helper;

use Contao\Input;
use MetaModels\Attribute\IAttribute;
use MetaModels\Filter\FilterUrl;
use MetaModels\Filter\FilterUrlBuilder;
use RuntimeException;
use Symfony\Contracts\Translation\TranslatorInterface;

use function strtolower;

/**
 * Provide methods to generate sorting links.
 */
class SortingLinkGenerator
{
    /**
     * The filter url builder.
     *
     * @var FilterUrlBuilder
     */
    private FilterUrlBuilder $urlBuilder;

    /**
     * The translator interface.
     *
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    /**
     * The sorting type parameter.
     *
     * @var string
     */
    private string $sortParamType;

    /**
     * The sorting by parameter.
     *
     * @var string
     */
    private string $sortOrderByParam;

    /**
     * The sorting direction type.
     *
     * @var string
     */
    private string $sortOrderDirParam;

    /**
     * The URL fragment.
     *
     * @var string
     */
    private string $sortFragment;

    /**
     * The default sorting.
     *
     * @var string
     */
    private string $defaultSorting;

    /**
     * The default direction.
     *
     * @var string
     */
    private string $defaultDirection;

    /**
     * Create a new instance.
     *
     * @param FilterUrlBuilder    $urlBuilder        The filter url builder.
     * @param TranslatorInterface $translator        The translator.
     * @param string              $sortParamType     The pagination parameter url type (slug, get or slugNget).
     * @param string              $sortOrderByParam  The sorting by parameter name.
     * @param string              $sortOrderDirParam The sorting direction parameter name.
     * @param string              $sortFragment      The URL fragment.
     * @param string              $defaultSorting    The default sorting parameter name.
     * @param string              $defaultDirection  The default sorting direction parameter name.
     */
    public function __construct(
        FilterUrlBuilder $urlBuilder,
        TranslatorInterface $translator,
        string $sortParamType,
        string $sortOrderByParam,
        string $sortOrderDirParam,
        string $sortFragment,
        string $defaultSorting,
        string $defaultDirection,
    ) {
        $this->urlBuilder        = $urlBuilder;
        $this->translator        = $translator;
        $this->sortParamType     = $sortParamType;
        $this->sortOrderByParam  = $sortOrderByParam;
        $this->sortOrderDirParam = $sortOrderDirParam;
        $this->sortFragment      = $sortFragment;
        $this->defaultSorting    = $defaultSorting;
        $this->defaultDirection  = \strtolower($defaultDirection);
    }

    public function generateSortingLink(IAttribute $attribute, string $type): array
    {
        $pageFilterUrl = $this->urlBuilder->getCurrentFilterUrl();
        $sortBy        = $this->tryReadFromSlugOrGet($pageFilterUrl, $this->sortOrderByParam, $this->sortParamType)
            ?? $this->defaultSorting;
        $sortDirection = $this->tryReadFromSlugOrGet($pageFilterUrl, $this->sortOrderDirParam, $this->sortParamType)
            ?? $this->defaultDirection;
        $attributeName = $attribute->getColName();
        $active        = $sortBy === $attributeName;

        $dir = $this->determineDirection($type, $active, $sortDirection);

        if ($attributeName === $this->defaultSorting && $dir === $this->defaultDirection) {
            $attributeName = '';
        }

        $this->updateSortingInFilterUrl($pageFilterUrl, $attributeName, $dir);

        return [
            'attribute' => $attribute,
            'name'      => $attribute->getName(),
            'href'      => $this->urlBuilder->generate($pageFilterUrl) .
                           ($this->sortFragment ? '#' . $this->sortFragment : ''),
            'direction' => $dir,
            'active'    => $active,
            'class'     => 'sort' . ($active ? ' active' : '') . ' ' . $dir,
            'label'     => $this->translator->trans(
                'MSC.orderMetaModelListBy' . ($dir === 'asc' ? 'Ascending' : 'Descending'),
                [0 => $attribute->getName()],
                'contao_default'
            ),
        ];
    }

    /**
     * Determine the direction to use.
     *
     * @param string $type          Type of the link to generate a direction for.
     * @param bool   $active        Flag if the sorting is currently active.
     * @param string $sortDirection The current direction (only considered when active).
     *
     * @return string
     */
    private function determineDirection(string $type, bool $active, string $sortDirection): string
    {
        switch ($type) {
            case 'toggle':
                // In case of toggle, we override the type with the desired direction.
                $type = 'asc';
                if ($active) {
                    $type = \strtolower($sortDirection) === 'desc' ? 'asc' : 'desc';
                }
            // NO break here!
            case 'asc':
            case 'desc':
                return $type;
                break;
            default:
        }
        throw new RuntimeException('Unknown link type: ' . $type);
    }

    /**
     * Get parameter from get or slug.
     *
     * @param FilterUrl $filterUrl The filter URL to obtain parameters from.
     * @param string    $sortParam The sort parameter name to obtain.
     * @param string    $sortType  The sort URL type.
     *
     * @return string|null
     */
    private function tryReadFromSlugOrGet(FilterUrl $filterUrl, string $sortParam, string $sortType): ?string
    {
        $result = null;

        switch ($sortType) {
            case 'get':
                $result = $this->getGetParam($filterUrl, $sortParam);
                break;
            case 'slug':
                $result = $filterUrl->getSlug($sortParam);
                break;
            case 'slugNget':
                $result = ($this->getGetParam($filterUrl, $sortParam) ?? $filterUrl->getSlug($sortParam));
                break;
            default:
        }

        // Mark the parameter as used (otherwise, a 404 is thrown)
        Input::get($sortParam);

        return $result;
    }

    /**
     * Retrieve GET parameters.
     *
     * @param FilterUrl $filterUrl The filter URL.
     * @param string    $sortParam The sort parameter.
     *
     * @return string|null
     */
    private function getGetParam(FilterUrl $filterUrl, string $sortParam): ?string
    {
        $value = $filterUrl->getGet($sortParam);
        if (\is_array($value)) {
            return null;
        }

        return $value;
    }

    /**
     * Write parameter to filter url.
     *
     * @param FilterUrl $pageFilterUrl The filter url to update.
     * @param string    $attributeName The name of the attribute to update.
     * @param string    $dir           The direction.
     *
     * @return void
     */
    private function updateSortingInFilterUrl(FilterUrl $pageFilterUrl, string $attributeName, mixed $dir): void
    {
        if ('get' === $this->sortParamType) {
            $pageFilterUrl->setGet($this->sortOrderByParam, $attributeName);
            $pageFilterUrl->setGet($this->sortOrderDirParam, $attributeName ? $dir : '');
        } else {
            // Use slug or slugNget.
            $pageFilterUrl
                ->setSlug($this->sortOrderByParam, $attributeName)
                ->setGet($this->sortOrderByParam, '');
            $pageFilterUrl
                ->setSlug($this->sortOrderDirParam, $attributeName ? $dir : '')
                ->setGet($this->sortOrderDirParam, '');
        }
    }
}
