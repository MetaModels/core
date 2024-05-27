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

use Contao\CoreBundle\Controller\AbstractBackendController;
use Contao\CoreBundle\Framework\ContaoFramework;
use ContaoCommunityAlliance\DcGeneral\Factory\DcGeneralFactoryService;
use ContaoCommunityAlliance\Translator\TranslatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment as TwigEnvironment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;

final class ConfigurationController extends AbstractBackendController
{
    use DcGeneralControllerTrait;

    /**
     * @param Request                  $request        The request.
     * @param TwigEnvironment          $twig           The twig environment.
     * @param DcGeneralFactoryService  $factoryFactory The DCG factory
     * @param EventDispatcherInterface $dispatcher     The event dispatcher.
     * @param TranslatorInterface      $translator     The translator.
     * @param ContaoFramework          $framework      The Contao framework
     *
     * @return Response
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function __invoke(
        Request $request,
        TwigEnvironment $twig,
        DcGeneralFactoryService $factoryFactory,
        EventDispatcherInterface $dispatcher,
        TranslatorInterface $translator,
        ContaoFramework $framework,
    ): Response {
        $containerName    = (string) $request->query->get('table', 'tl_metamodel');
        $controllerResult = $this->bootDcGeneralAndProcess(
            $request,
            $containerName,
            $factoryFactory,
            $dispatcher,
            $translator,
            $framework
        );
        $headline         = $this->determineHeadline($containerName, $translator);

        $GLOBALS['TL_CSS']['metamodels.core'] = '/bundles/metamodelscore/css/style.css';

        return $this->render(
            '@MetaModelsCore/Backend/be_config.html.twig',
            [
                'title'       => $headline,
                'headline'    => $headline,
                'body'        => $controllerResult,
            ]
        );
    }

    /**
     * Generate headline.
     *
     * @param string              $containerName The container.
     * @param TranslatorInterface $translator    The translator.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    private function determineHeadline(string $containerName, TranslatorInterface $translator): string
    {
        return $translator->translate('backend-module.headline', $containerName);
    }
}
