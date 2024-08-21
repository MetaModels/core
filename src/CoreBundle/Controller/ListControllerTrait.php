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
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\Controller;

use Contao\BackendTemplate;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\Input;
use Contao\Model;
use Contao\StringUtil;
use Contao\System;
use Contao\Template;
use MetaModels\Filter\FilterUrl;
use MetaModels\Filter\FilterUrlBuilder;
use MetaModels\Filter\Setting\IFilterSettingFactory;
use MetaModels\Helper\SortingLinkGenerator;
use MetaModels\IFactory;
use MetaModels\IItem;
use MetaModels\IMetaModel;
use MetaModels\ItemList;
use MetaModels\Render\Setting\IRenderSettingFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Helper trait for lists (CE and MOD).
 */
trait ListControllerTrait
{
    /**
     *  The filter setting factory.
     *
     * @var IFilterSettingFactory
     */
    private IFilterSettingFactory $filterFactory;

    /**
     * The MetaModels factory.
     *
     * @var IFactory
     */
    private IFactory $factory;

    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private EventDispatcherInterface $eventDispatcher;

    /**
     * The render setting factory.
     *
     * @var IRenderSettingFactory
     */
    private IRenderSettingFactory $renderSettingFactory;

    /**
     * The filter url builder.
     *
     * @var FilterUrlBuilder
     */
    private FilterUrlBuilder $filterUrlBuilder;

    /**
     * The translator.
     *
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    /**
     * The router.
     *
     * @var RouterInterface
     */
    private RouterInterface $router;

    /**
     * The scope matcher.
     *
     * @var ScopeMatcher
     */
    private ScopeMatcher $scopeMatcher;

    /**
     * ItemListController constructor.
     *
     * @param IFactory                 $factory              The MetaModels factory (required in MetaModels 3.0).
     * @param IFilterSettingFactory    $filterFactory        The filter setting factory (required in MetaModels 3.0).
     * @param IRenderSettingFactory    $renderSettingFactory The render setting factory (required in MetaModels 3.0).
     * @param EventDispatcherInterface $eventDispatcher      The event dispatcher (required in MetaModels 3.0).
     * @param FilterUrlBuilder         $filterUrlBuilder     The filter url builder.
     * @param TranslatorInterface|null $translator           The translator.
     * @param RouterInterface|null     $router               The router.
     * @param ScopeMatcher|null        $scopeMatcher         The scope matcher.
     */
    public function __construct(
        IFactory $factory,
        IFilterSettingFactory $filterFactory,
        IRenderSettingFactory $renderSettingFactory,
        EventDispatcherInterface $eventDispatcher,
        FilterUrlBuilder $filterUrlBuilder,
        TranslatorInterface $translator = null,
        RouterInterface $router = null,
        ScopeMatcher $scopeMatcher = null
    ) {
        $this->factory              = $factory;
        $this->filterFactory        = $filterFactory;
        $this->renderSettingFactory = $renderSettingFactory;
        $this->eventDispatcher      = $eventDispatcher;
        $this->filterUrlBuilder     = $filterUrlBuilder;

        if (null === $translator) {
            // @codingStandardsIgnoreStart
            @trigger_error(
                'Translator is missing. It has to be passed in the constructor. Fallback will be dropped.',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd

            $translator = System::getContainer()->get('translator');
            assert($translator instanceof TranslatorInterface);
        }
        $this->translator = $translator;

        if (null === $router) {
            // @codingStandardsIgnoreStart
            @trigger_error(
                'Router is missing. It has to be passed in the constructor. Fallback will be dropped.',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd

            $router = System::getContainer()->get('router');
            assert($router instanceof RouterInterface);
        }
        $this->router = $router;

        if (null === $scopeMatcher) {
            // @codingStandardsIgnoreStart
            @trigger_error(
                'ScopeMatcher is missing. It has to be passed in the constructor. Fallback will be dropped.',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd

            $scopeMatcher = System::getContainer()->get('contao.routing.scope_matcher');
            assert($scopeMatcher instanceof ScopeMatcher);
        }
        $this->scopeMatcher = $scopeMatcher;
    }

    /**
     * Generate the response.
     *
     * @param Template $template The template.
     * @param Model    $model    The content model.
     * @param Request  $request  The request.
     *
     * @return Response The response.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     *
     * @psalm-suppress DeprecatedClass
     */
    private function getResponseInternal(Template $template, Model $model, Request $request): Response
    {
        /** @psalm-suppress UndefinedMagicPropertyFetch */
        if ('' === ($pageParam = $model->metamodel_page_param)) {
            /** @psalm-suppress UndefinedMagicPropertyFetch */
            switch ($model->type) {
                case 'metamodel_content':
                    $pageParam = 'page_mmce' . $model->id;
                    break;
                case 'metamodel_list':
                    $pageParam = 'page_mmfm' . $model->id;
                    break;
                default:
                    $pageParam = 'page_mm' . $model->id;
            }
        }

        /** @psalm-suppress UndefinedMagicPropertyFetch */
        $itemRenderer = new ItemList(
            $this->factory,
            $this->filterFactory,
            $this->renderSettingFactory,
            $this->eventDispatcher,
            $this->filterUrlBuilder,
            $pageParam,
            $model->metamodel_page_param_type,
            $model->metamodel_maxpaginationlinks,
            $model->metamodel_pagination,
            $model->metamodel_pagination_urlfragment
        );

        /**
         * @psalm-suppress UndefinedMagicPropertyAssignment
         * @psalm-suppress UndefinedMagicPropertyFetch
         */
        $template->searchable = !$model->metamodel_donotindex;

        /** @psalm-suppress UndefinedMagicPropertyFetch */
        $sorting = $model->metamodel_sortby;
        /** @psalm-suppress UndefinedMagicPropertyFetch */
        $direction = $model->metamodel_sortby_direction;
        /** @psalm-suppress UndefinedMagicPropertyFetch */
        $sortParamType     = $model->metamodel_sort_param_type;
        $sortOrderByParam  = 'orderBy';
        $sortOrderDirParam = 'orderDir';
        /** @psalm-suppress UndefinedMagicPropertyFetch */
        $sortOverride = $model->metamodel_sort_override;
        /** @psalm-suppress UndefinedMagicPropertyFetch */
        $sortFragment = $model->metamodel_sort_urlfragment;

        // @codingStandardsIgnoreStart
        // FIXME: filter URL should be created from local request and not from master request.
        // @codingStandardsIgnoreEnd
        $filterUrl = $this->filterUrlBuilder->getCurrentFilterUrl();
        if ($sortOverride) {
            /** @psalm-suppress UndefinedMagicPropertyFetch */
            $sortOrderByParam = $model->metamodel_order_by_param ?: $sortOrderByParam;
            /** @psalm-suppress UndefinedMagicPropertyFetch */
            $sortOrderDirParam = $model->metamodel_order_dir_param ?: $sortOrderDirParam;
            if (
                null !==
                $value = $this->tryReadFromSlugOrGet(
                    $filterUrl,
                    $sortOrderByParam,
                    $sortParamType
                )
            ) {
                $sorting = $value;
            }
            if (
                null !==
                $value = $this->tryReadFromSlugOrGet(
                    $filterUrl,
                    $sortOrderDirParam,
                    $sortParamType
                )
            ) {
                $direction = $value;
            }
        }

        /** @psalm-suppress UndefinedMagicPropertyFetch */
        $filterParams = StringUtil::deserialize($model->metamodel_filterparams, true);
        /** @psalm-suppress UndefinedMagicPropertyFetch */
        $itemRenderer
            ->setMetaModel($model->metamodel, $model->metamodel_rendersettings)
            ->setListTemplate($template)
            ->setLimit($model->metamodel_use_limit, $model->metamodel_offset, $model->metamodel_limit)
            ->setPageBreak($model->perPage)
            ->setSorting($sorting, $direction)
            ->setFilterSettings($model->metamodel_filtering)
            ->setFilterParameters($filterParams, $this->getFilterParameters($filterUrl, $itemRenderer))
            ->setMetaTags($model->metamodel_meta_title, $model->metamodel_meta_description);
        if ($sortOverride) {
            /** @psalm-suppress UndefinedMagicPropertyFetch */
            $itemRenderer->setSortingLinkGenerator(
                new SortingLinkGenerator(
                    $this->filterUrlBuilder,
                    $this->translator,
                    $sortParamType,
                    $sortOrderByParam,
                    $sortOrderDirParam,
                    $sortFragment,
                    $model->metamodel_sortby,
                    $model->metamodel_sortby_direction
                )
            );
        }

        /** @psalm-suppress UndefinedMagicPropertyFetch */
        if ($model->metamodel_use_parameters) {
            /** @psalm-suppress UndefinedMagicPropertyFetch */
            foreach (StringUtil::deserialize(($model->metamodel_parameters ?? null), true) as $key => $value) {
                $itemRenderer->setTemplateParameter($key, $value);
            }
        }

        /**
         * @psalm-suppress UndefinedMagicPropertyAssignment
         * @psalm-suppress UndefinedMagicPropertyFetch
         */
        $template->items = StringUtil::encodeEmail($itemRenderer->render($model->metamodel_noparsing, $model));
        /** @psalm-suppress UndefinedMagicPropertyAssignment */
        $template->numberOfItems = $itemRenderer->getItems()->getCount();
        /** @psalm-suppress UndefinedMagicPropertyAssignment */
        $template->pagination = $itemRenderer->getPagination();

        $responseTags = \array_map(
            static function (IItem $item) {
                return \sprintf('contao.db.%s.%d', $item->getMetaModel()->getTableName(), $item->get('id'));
            },
            \iterator_to_array($itemRenderer->getItems(), false)
        );

        $response = $template->getResponse();

        $this->tagResponse($responseTags);

        return $response;
    }

    /**
     * Retrieve all filter parameters from the input class for the specified filter setting.
     *
     * @param FilterUrl $filterUrl    The filter URL to obtain parameters from.
     * @param ItemList  $itemRenderer The list renderer instance to be used.
     *
     * @return string[]
     */
    private function getFilterParameters(FilterUrl $filterUrl, ItemList $itemRenderer): array
    {
        $result = [];
        foreach ($itemRenderer->getFilterSettings()->getParameters() as $name) {
            if (null !== $value = $this->tryReadFromSlugOrGet($filterUrl, $name, 'slugNget')) {
                $result[$name] = $value;
            }
        }

        return $result;
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

    /**
     * Return a back end wildcard response.
     *
     * @param string $href  The edit href.
     * @param string $name  The name of the element.
     * @param Model  $model The database list model.
     *
     * @return Response The response.
     */
    private function renderBackendWildcard(string $href, string $name, Model $model): Response
    {
        $template = new BackendTemplate('be_wildcard');

        /** @psalm-suppress UndefinedMagicPropertyFetch */
        $headline = StringUtil::deserialize($model->headline);
        /** @psalm-suppress UndefinedMagicPropertyAssignment */
        $template->wildcard = $this->getWildcardInfoText($model, $href, $name);
        /** @psalm-suppress UndefinedMagicPropertyAssignment */
        $template->title = (\is_array($headline) ? $headline['value'] : $headline);
        /** @psalm-suppress UndefinedMagicPropertyAssignment */
        $template->id = $model->id;

        return new Response($template->parse());
    }

    /**
     * Obtain the text for the wildcard.
     *
     * @param Model  $model The database list model.
     * @param string $href  The edit href.
     * @param string $name  The name of the element.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function getWildcardInfoText(Model $model, string $href, string $name): string
    {
        /** @psalm-suppress UndefinedMagicPropertyFetch */
        if (null === $model->metamodel) {
            return 'MetaModel not configured.';
        }

        if ('' === ($metaModelName = $this->factory->translateIdToMetaModelName($model->metamodel))) {
            return 'Unknown MetaModel: ' . $model->metamodel;
        }
        // Add CSS file.
        $GLOBALS['TL_CSS'][] = '/bundles/metamodelscore/css/style.css';

        // Retrieve name of MetaModel.
        $infoTemplate =
            '<div class="wc_info tl_gray"><span class="wc_label"><abbr title="%s">%s:</abbr></span> %s</div>';

        $metaModel = $this->factory->getMetaModel($metaModelName);
        assert($metaModel instanceof IMetaModel);

        $tokenManager = System::getContainer()->get('contao.csrf.token_manager');
        assert($tokenManager instanceof CsrfTokenManagerInterface);

        $header = $metaModel->getName();
        if ($href) {
            $header .= \sprintf(
                ' (<a href="%1$s&amp;rt=%2$s" class="tl_gray">ID: %3$s</a>)',
                $href,
                $tokenManager->getDefaultTokenValue(),
                (string) $model->id
            );
        }
        $infoText = '### ' . $name . ' ###' . \sprintf(
            $infoTemplate,
            $this->translator->trans('mm_be_info_name.description', [], 'metamodels_wildcard'),
            $this->translator->trans('mm_be_info_name.label', [], 'metamodels_wildcard'),
            $header,
        );

        // Retrieve name of filter.
        /** @psalm-suppress UndefinedMagicPropertyFetch */
        if ($model->metamodel_filtering) {
            $filterparams = [];
            /** @psalm-suppress UndefinedMagicPropertyFetch */
            foreach (StringUtil::deserialize($model->metamodel_filterparams, true) as $filterparam) {
                if ($filterparam['value']) {
                    $filterparams[] = $filterparam['value'];
                }
            }
            $infoFiPa = \count($filterparams) ? ': ' . \implode(', ', $filterparams) : '';
            $infoFi   = $this->filterFactory->createCollection($model->metamodel_filtering)->get('name');
            if (null !== $infoFi) {
                $infoText .= \sprintf(
                    $infoTemplate,
                    $this->translator->trans('mm_be_info_filter.description', [], 'metamodels_wildcard'),
                    $this->translator->trans('mm_be_info_filter.label', [], 'metamodels_wildcard'),
                    $infoFi . $infoFiPa
                );
            }
        }

        // Retrieve name of render setting.
        /** @psalm-suppress UndefinedMagicPropertyFetch */
        if ($model->metamodel_rendersettings) {
            $infoRs = $this->renderSettingFactory
                ->createCollection($metaModel, $model->metamodel_rendersettings)
                ->get('name');
            if (null !== $infoRs) {
                $infoText .= \sprintf(
                    $infoTemplate,
                    $this->translator->trans('mm_be_info_render_setting.description', [], 'metamodels_wildcard'),
                    $this->translator->trans('mm_be_info_render_setting.label', [], 'metamodels_wildcard'),
                    $infoRs
                );
            }
        }

        return $infoText . '<br>';
    }
}
