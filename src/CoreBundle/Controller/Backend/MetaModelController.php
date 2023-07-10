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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\Controller\Backend;

use Contao\CoreBundle\Exception\AccessDeniedException;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\CoreBundle\Menu\BackendMenuBuilder;
use ContaoCommunityAlliance\DcGeneral\Factory\DcGeneralFactoryService;
use ContaoCommunityAlliance\Translator\TranslatorInterface;
use MetaModels\ViewCombination\ViewCombination;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as TwigEnvironment;

final class MetaModelController
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
     * @param TwigEnvironment          $twig           The twig environment.
     * @param DcGeneralFactoryService  $factoryFactory The DCG factory
     * @param EventDispatcherInterface $dispatcher     The event dispatcher.
     * @param TranslatorInterface      $translator     The translator.
     * @param ContaoFramework          $framework      The Contao framework
     *
     * @return Response
     */
    public function __invoke(
        Request $request,
        TwigEnvironment $twig,
        DcGeneralFactoryService $factoryFactory,
        EventDispatcherInterface $dispatcher,
        TranslatorInterface $translator,
        ContaoFramework $framework,
        ViewCombination $viewCombination,
    ): Response {
        $containerName = (string) $request->query->get('table', '');
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

        return new Response(
            $twig->render(
                '@MetaModelsCore/Backend/be_config.html.twig',
                [
                    'headline'    => $headline,
                    'body'        => $controllerResult,
                    'stylesheets' => ['bundles/metamodelscore/css/style.css']
                ]
            )
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
}
