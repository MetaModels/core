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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2020 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\Controller;

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
     */
    private function getResponseInternal(Template $template, Model $model, Request $request): ?Response
    {
        $itemRenderer = new ItemList(
            $this->factory,
            $this->filterFactory,
            $this->renderSettingFactory,
            $this->eventDispatcher
        );

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

        return $response;
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
