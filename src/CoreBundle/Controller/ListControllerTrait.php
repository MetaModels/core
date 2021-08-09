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
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2021 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\Controller;

use Contao\BackendTemplate;
use Contao\Input;
use Contao\Model;
use Contao\StringUtil;
use Contao\Template;
use MetaModels\Filter\FilterUrl;
use MetaModels\Filter\FilterUrlBuilder;
use MetaModels\Filter\Setting\IFilterSettingFactory;
use MetaModels\IFactory;
use MetaModels\IItem;
use MetaModels\ItemList;
use MetaModels\Render\Setting\IRenderSettingFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
    private $filterFactory;

    /**
     * The MetaModels factory.
     *
     * @var IFactory
     */
    private $factory;

    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * The render setting factory.
     *
     * @var IRenderSettingFactory
     */
    private $renderSettingFactory;

    /**
     * The filter url builder.
     *
     * @var FilterUrlBuilder
     */
    private $filterUrlBuilder;

    /**
     * ItemListController constructor.
     *
     * @param IFactory                 $factory              The MetaModels factory (required in MetaModels 3.0).
     * @param IFilterSettingFactory    $filterFactory        The filter setting factory (required in MetaModels 3.0).
     * @param IRenderSettingFactory    $renderSettingFactory The render setting factory (required in MetaModels 3.0).
     * @param EventDispatcherInterface $eventDispatcher      The event dispatcher (required in MetaModels 3.0).
     * @param FilterUrlBuilder         $filterUrlBuilder     The filter url builder.
     */
    public function __construct(
        IFactory $factory,
        IFilterSettingFactory $filterFactory,
        IRenderSettingFactory $renderSettingFactory,
        EventDispatcherInterface $eventDispatcher,
        FilterUrlBuilder $filterUrlBuilder
    ) {
        $this->factory              = $factory;
        $this->filterFactory        = $filterFactory;
        $this->renderSettingFactory = $renderSettingFactory;
        $this->eventDispatcher      = $eventDispatcher;
        $this->filterUrlBuilder     = $filterUrlBuilder;
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
     */
    private function getResponseInternal(Template $template, Model $model, Request $request): ?Response
    {
        if (empty($key = $model->metamodel_page_param)) {
            switch ($model->type) {
                case 'metamodel_content':
                    $key = '_mmce' . $model->id;
                    break;
                case 'metamodel_list':
                    $key = '_mmfm' . $model->id;
                    break;
                default:
            }
        }

        $itemRenderer = new ItemList(
            $this->factory,
            $this->filterFactory,
            $this->renderSettingFactory,
            $this->eventDispatcher,
            $key,
            $model->metamodel_pagination
        );

        $template->searchable = !$model->metamodel_donotindex;

        $sorting   = $model->metamodel_sortby;
        $direction = $model->metamodel_sortby_direction;

        // FIXME: filter URL should be created from local request and not from master request.
        $filterUrl = $this->filterUrlBuilder->getCurrentFilterUrl();
        if ($model->metamodel_sort_override) {
            if (null !== $value = $this->tryReadFromSlugOrGet($filterUrl, $model->metamodel_order_by_param ?: 'orderBy')) {
                $sorting = $value;
            }
            if (null !== $value = $this->tryReadFromSlugOrGet($filterUrl, $model->metamodel_order_dir_param ?: 'orderDir')) {
                $direction = $value;
            }
        }

        $filterParams = StringUtil::deserialize($model->metamodel_filterparams, true);
        $itemRenderer
            ->setMetaModel($model->metamodel, $model->metamodel_rendersettings)
            ->setListTemplate($template)
            ->setLimit($model->metamodel_use_limit, $model->metamodel_offset, $model->metamodel_limit)
            ->setPageBreak($model->perPage)
            ->setSorting($sorting, $direction)
            ->setFilterSettings($model->metamodel_filtering)
            ->setFilterParameters($filterParams, $this->getFilterParameters($filterUrl, $itemRenderer))
            ->setMetaTags($model->metamodel_meta_title, $model->metamodel_meta_description);

        $template->items         = StringUtil::encodeEmail($itemRenderer->render($model->metamodel_noparsing, $model));
        $template->numberOfItems = $itemRenderer->getItems()->getCount();
        $template->pagination    = $itemRenderer->getPagination();

        $responseTags = array_map(
            static function (IItem $item) {
                return sprintf('contao.db.%s.%d', $item->getMetaModel()->getTableName(), $item->get('id'));
            },
            iterator_to_array($itemRenderer->getItems(), false)
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
            if (null !== $value = $this->tryReadFromSlugOrGet($filterUrl, $name)) {
                $result[$name] = $value;
            }
        }

        return $result;
    }

    /**
     * Get parameter from get or slug.
     *
     * @param FilterUrl $filterUrl The filter URL to obtain parameters from.
     * @param string    $name      The parameter name to obtain.
     *
     * @return string|null
     */
    private function tryReadFromSlugOrGet(FilterUrl $filterUrl, string $name): ?string
    {
        $result = null;
        if ($filterUrl->hasSlug($name)) {
            $result = $filterUrl->getSlug($name);
        } elseif ($filterUrl->hasGet($name)) {
            $result = $filterUrl->getGet($name);
        }

        // Mark the parameter as used (otherwise, a 404 is thrown)
        Input::get($name);

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

        $headline = StringUtil::deserialize($model->headline);

        $template->wildcard = $this->getWildcardInfoText($model, $href, $name);
        $template->title    = (\is_array($headline) ? $headline['value'] : $headline);
        $template->id       = $model->id;

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
     */
    private function getWildcardInfoText(Model $model, string $href, string $name): string
    {
        if (empty($model->metamodel)) {
            return 'MetaModel not configured.';
        }

        if (null === $metaModelName = $this->factory->translateIdToMetaModelName($model->metamodel)) {
            return 'Unknown MetaModel: ' . $model->metamodel;
        }
        // Add CSS file.
        $GLOBALS['TL_CSS'][] = 'bundles/metamodelscore/css/style.css';

        // Retrieve name of MetaModels.
        $infoTemplate =
            '<div class="wc_info tl_gray"><span class="wc_label"><abbr title="%s">%s:</abbr></span> %s</div>';

        $metaModel = $this->factory->getMetaModel($metaModelName);
        $header    = $name . ': ' . $metaModel->getName();
        if ($href) {
            $header .= sprintf(
                ' (<a href="%1$s&amp;rt=%2$s" class="tl_gray">ID: %3$s</a>)',
                $href,
                REQUEST_TOKEN,
                $model->id
            );
        }
        $infoText = sprintf(
            $infoTemplate,
            $this->get('translator')->trans('MSC.mm_be_info_name.1', [], 'contao_default'),
            $this->get('translator')->trans('MSC.mm_be_info_name.0', [], 'contao_default'),
            $header
        );

        // Retrieve name of filter.
        if ($model->metamodel_filtering) {
            $infoFi = $this->filterFactory->createCollection($model->metamodel_filtering)->get('name');
            if ($infoFi) {
                $infoText .= sprintf(
                    $infoTemplate,
                    $this->get('translator')->trans('MSC.mm_be_info_filter.1', [], 'contao_default'),
                    $this->get('translator')->trans('MSC.mm_be_info_filter.0', [], 'contao_default'),
                    $infoFi
                );
            }
        }

        // Retrieve name of rendersetting.
        if ($model->metamodel_rendersettings) {
            $infoRs = $this->renderSettingFactory
                ->createCollection($metaModel, $model->metamodel_rendersettings)
                ->get('name');
            if ($infoRs) {
                $infoText .= sprintf(
                    $infoTemplate,
                    $this->get('translator')->trans('MSC.mm_be_info_render_setting.1', [], 'contao_default'),
                    $this->get('translator')->trans('MSC.mm_be_info_render_setting.0', [], 'contao_default'),
                    $infoRs
                );
            }
        }

        return $infoText . '<br>';
    }
}
