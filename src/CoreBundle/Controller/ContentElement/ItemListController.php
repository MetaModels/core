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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2020 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\Controller\ContentElement;

use Contao\BackendTemplate;
use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\ServiceAnnotation\ContentElement;
use Contao\Input;
use Contao\StringUtil;
use Contao\Template;
use MetaModels\Filter\FilterUrl;
use MetaModels\Filter\FilterUrlBuilder;
use MetaModels\IItem;
use MetaModels\ItemList;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The item list content element.
 *
 * @ContentElement("metamodel_content", category="metamodels", template="ce_metamodel_content")
 */
final class ItemListController extends AbstractContentElementController
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

    /**
     * Override the template and return the response.
     *
     * @param Request      $request The request.
     * @param ContentModel $model   The content model.
     * @param string       $section The layout section, e.g. "main".
     * @param array|null   $classes The css classes.
     *
     * @return Response The response.
     */
    public function __invoke(Request $request, ContentModel $model, string $section, array $classes = null): Response
    {
        if ($this->get('contao.routing.scope_matcher')->isBackendRequest($request)) {
            return $this->getBackendWildcard($model);
        }

        if (!empty($model->metamodel_layout)) {
            $model->customTpl = $model->metamodel_layout;
        }

        return parent::__invoke($request, $model, $section, $classes);
    }

    /**
     * Generate the response.
     *
     * @param Template     $template The template.
     * @param ContentModel $model    The content model.
     * @param Request      $request  The request.
     *
     * @return Response The response.
     */
    protected function getResponse(Template $template, ContentModel $model, Request $request): ?Response
    {
        $itemRenderer = new ItemList();

        $template->searchable = !$model->metamodel_donotindex;

        $sorting   = $model->metamodel_sortby;
        $direction = $model->metamodel_sortby_direction;

        // FIXME: filter URL should be created from local request and not from master request.
        $filterUrl = $this->filterUrlBuilder->getCurrentFilterUrl();
        if ($model->metamodel_sort_override) {
            if (null !== $value = $this->tryReadFromSlugOrGet($filterUrl, 'orderBy')) {
                $sorting = $value;
            }
            if (null !== $value = $this->tryReadFromSlugOrGet($filterUrl, 'orderDir')) {
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
        $this->addSharedMaxAgeToResponse($response, $model);

        return $response;
    }

    /**
     * Return a back end wildcard response.
     *
     * @return Response The repsonse.
     */
    private function getBackendWildcard(): Response
    {
        $name = $this->get('translator')->trans('CTE.' . $this->getType() . '.0', [], 'contao_modules');

        $template = new BackendTemplate('be_wildcard');

        $template->wildcard = '### ' . strtoupper($name) . ' ###';

        return new Response($template->parse());
    }

    /**
     * Retrieve all filter parameters from the input class for the specified filter setting.
     *
     * @param ItemList $itemRenderer The list renderer instance to be used.
     *
     * @return string[]
     */
    private function getFilterParameters(FilterUrl $filterUrl, $itemRenderer): array
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
     * @param FilterUrl $filterUrl
     * @param string    $name
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
}
