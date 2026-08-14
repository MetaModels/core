<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2025 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2025 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\Controller\Backend;

use Contao\CoreBundle\Controller\Backend\AbstractBackendController;
use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Menu\BackendMenuBuilder;
use ContaoCommunityAlliance\DcGeneral\Factory\DcGeneralFactoryService;
use ContaoCommunityAlliance\Translator\TranslatorInterface;
use MetaModels\CoreBundle\Backend\ItemBreadcrumbBuilder;
use MetaModels\ViewCombination\ViewCombination;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class MetaModelController extends AbstractBackendController
{
    use DcGeneralControllerTrait;

    /**
     * The constructor.
     *
     * @param BackendMenuBuilder $builder The menu builder.
     */
    public function __construct(
        private readonly BackendMenuBuilder $builder
    ) {
    }

    /**
     * @param Request                  $request        The request.
     * @param DcGeneralFactoryService  $factoryFactory The DCG factory
     * @param EventDispatcherInterface $dispatcher     The event dispatcher.
     * @param TranslatorInterface      $translator     The translator.
     * @param ContaoFramework          $framework      The Contao framework
     *
     * @return Response
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function __invoke(
        Request $request,
        DcGeneralFactoryService $factoryFactory,
        EventDispatcherInterface $dispatcher,
        TranslatorInterface $translator,
        ContaoFramework $framework,
        ViewCombination $viewCombination,
        ItemBreadcrumbBuilder $breadcrumbBuilder,
        Environment $twig,
    ): Response {
        $containerName = $request->query->get('table', '');
        if ('' === $containerName) {
            $containerName = (string) ($request->attributes->get('_route_params', [])['tableName'] ?? '');
        }
        $combination = $viewCombination->getCombination($containerName);
        if (null === $combination) {
            throw new AccessDeniedException('Permission denied to access back end module "' . $containerName . '".');
        }
        $inputScreenId    = $combination['dca_id'] ?? '';
        $controllerResult = $this->bootDcGeneralAndProcess(
            $request,
            $containerName,
            $factoryFactory,
            $dispatcher,
            $translator,
            $framework
        );
        $headline         = $this->determineHeadline($containerName, $inputScreenId, $translator);

        $GLOBALS['TL_CSS']['metamodels.core'] = '/bundles/metamodelscore/css/style.css';

        return $this->render(
            '@MetaModelsCore/Backend/be_config.html.twig',
            [
                'title'       => $headline,
                'headline'    => $headline,
                'breadcrumb'  => $this->determineBreadcrumb($request, $containerName, $breadcrumbBuilder, $twig),
                'body'        => $controllerResult,
            ]
        );
    }

    /**
     * Generate headline.
     *
     * @param string              $containerName The container.
     * @param string              $inputScreenId The input screen id.
     * @param TranslatorInterface $translator    The translator.
     *
     * @return string
     */
    private function determineHeadline(
        string $containerName,
        string $inputScreenId,
        TranslatorInterface $translator
    ): string {
        return $translator->translate('backend-module.' . $inputScreenId . '.headline', $containerName);
    }

    /**
     * Render the breadcrumb of a child table listing.
     *
     * Contaos template shows either the breadcrumb or the headline, never both - so this stays
     * empty wherever there is no chain to show, and the headline keeps its place. The menu itself
     * is filled by ItemBreadcrumbListener; asking the builder here only answers whether there is
     * anything to show at all.
     *
     * @param Request               $request       The request.
     * @param string                $containerName The table being shown.
     * @param ItemBreadcrumbBuilder $builder       The breadcrumb builder.
     * @param Environment           $twig          The template engine.
     *
     * @return string
     */
    private function determineBreadcrumb(
        Request $request,
        string $containerName,
        ItemBreadcrumbBuilder $builder,
        Environment $twig
    ): string {
        if ('' === (string) $request->query->get('table', '')) {
            return '';
        }

        if ([] === $builder->build($containerName, $request->query->getString('pid') ?: null)) {
            return '';
        }

        return $twig->render('@Contao/backend/data_container/breadcrumb.html.twig');
    }
}
