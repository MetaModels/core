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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2023 The MetaModels team.
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
            ?: $this->defaultSorting;
        $sortDirection = $this->tryReadFromSlugOrGet($pageFilterUrl, $this->sortOrderDirParam, $this->sortParamType)
            ?: $this->defaultDirection;
        $attributeName = $attribute->getColName();
        $active        = $sortBy === $attributeName;

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
                $dir = $type;
                break;
            default:
                throw new RuntimeException('Unknown link type: ' . $type);
        }

        if ($attributeName === $this->defaultSorting && $dir === $this->defaultDirection) {
            $attributeName = '';
        }

        if ('get' === $this->sortParamType) {
            $pageFilterUrl->setGet($this->sortOrderByParam, $attributeName);
            $pageFilterUrl->setGet($this->sortOrderDirParam, $attributeName ? $dir : '');
        } else {
            // Use slug or slugNget.
            $pageFilterUrl->setSlug($this->sortOrderByParam, $attributeName);
            $pageFilterUrl->setSlug($this->sortOrderDirParam, $attributeName ? $dir : '');
        }

        return [
            'attribute' => $attribute,
            'name'      => $attribute->getName(),
            'href'      => $this->urlBuilder->generate($pageFilterUrl) .
                           ($this->sortFragment ? '#' . $this->sortFragment : ''),
            'direction' => $dir,
            'active'    => (bool) $active,
            'class'     => 'sort' . ($active ? ' active' : '') . ' ' . $dir,
            'label'     => $this->translator->trans(
                'MSC.orderMetaModelListBy' . ($dir === 'asc' ? 'Ascending' : 'Descending'),
                [0 => $attribute->getName()],
                'contao_default'
            ),
        ];
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
                $result = $filterUrl->getGet($sortParam);
                break;
            case 'slug':
                $result = $filterUrl->getSlug($sortParam);
                break;
            case 'slugNget':
                $result = ($filterUrl->getGet($sortParam) ?? $filterUrl->getSlug($sortParam));
                break;
            default:
        }

        // Mark the parameter as used (otherwise, a 404 is thrown)
        Input::get($sortParam);

        return $result;
    }
}
