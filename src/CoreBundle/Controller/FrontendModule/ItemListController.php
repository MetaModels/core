<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2020 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @copyright  2012-2020 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\ServiceAnnotation\FrontendModule;
use Contao\ModuleModel;
use Contao\StringUtil;
use Contao\Template;
use MetaModels\Filter\FilterUrlBuilder;
use MetaModels\IItem;
use MetaModels\ItemList;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @FrontendModule("metamodel_content", category="metamodels", template="ce_metamodel_content")
 */
final class ItemListController extends AbstractFrontendModuleController
{

    /**
     * The filter url builder.
     *
     * @var FilterUrlBuilder
     */
    private $filterUrlBuilder;

    /**
     * ItemListController constructor.
     *
     * @param FilterUrlBuilder $filterUrlBuilder The filter url builder.
     */
    public function __construct(FilterUrlBuilder $filterUrlBuilder)
    {
        $this->filterUrlBuilder = $filterUrlBuilder;
    }

    public function __invoke(Request $request, ModuleModel $model, string $section, array $classes = null): Response
    {
        if (!empty($model->metamodel_layout)) {
            $model->customTpl = $model->metamodel_layout;
        }

        return parent::__invoke($request, $model, $section, $classes);
    }

    protected function getResponse(Template $template, ModuleModel $model, Request $request): ?Response
    {
        $itemRenderer = new ItemList();

        $template->searchable = !$model->metamodel_donotindex;

        $sorting   = $model->metamodel_sortby;
        $direction = $model->metamodel_sortby_direction;
        if ($model->metamodel_sort_override) {
            if ($request->query->has('orderBy')) {
                $sorting = $request->query->get('orderBy');
            }
            if ($request->query->has('orderDir')) {
                $direction = $request->query->get('orderDir');
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
            ->setFilterParameters($filterParams, $this->getFilterParameters($itemRenderer))
            ->setMetaTags($model->metamodel_meta_title, $model->metamodel_meta_description);

        $template->items         = StringUtil::encodeEmail($itemRenderer->render($model->metamodel_noparsing, $this));
        $template->numberOfItems = $itemRenderer->getItems()->getCount();
        $template->pagination    = $itemRenderer->getPagination();

        $responseTags = array_map(
            static function (IItem $item) {
                return sprintf('contao.db.%s.%d', $item->getMetaModel()->getTableName(), $item->get('id'));
            },
            iterator_to_array($itemRenderer->getItems(), false)
        );

        $this->tagResponse($responseTags);

        return $template->getResponse();
    }

    /**
     * Retrieve all filter parameters from the input class for the specified filter setting.
     *
     * @param ItemList $itemRenderer The list renderer instance to be used.
     *
     * @return string[]
     */
    private function getFilterParameters($itemRenderer): array
    {
        $filterUrl = $this->filterUrlBuilder->getCurrentFilterUrl();

        $result = [];
        foreach ($itemRenderer->getFilterSettings()->getParameters() as $name) {
            if ($filterUrl->hasSlug($name)) {
                $result[$name] = $filterUrl->getSlug($name);
            } elseif ($filterUrl->hasGet($name)) {
                $result[$name] = $filterUrl->getGet($name);
            }
        }

        return $result;
    }
}
