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

use Contao\Ajax;
use Contao\Controller;
use Contao\CoreBundle\Framework\ContaoFramework;
use ContaoCommunityAlliance\DcGeneral\Action;
use ContaoCommunityAlliance\DcGeneral\Clipboard\ClipboardInterface;
use ContaoCommunityAlliance\DcGeneral\Controller\ControllerInterface;
use ContaoCommunityAlliance\DcGeneral\Factory\DcGeneralFactoryService;
use ContaoCommunityAlliance\Translator\TranslatorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;

trait DcGeneralControllerTrait
{
    /**
     * @param Request                  $request        The request.
     * @param string                   $tableName      The table name
     * @param DcGeneralFactoryService  $factoryFactory The DCG factory
     * @param EventDispatcherInterface $dispatcher     The event dispatcher.
     * @param TranslatorInterface      $translator     The translator.
     * @param ContaoFramework          $framework      The Contao framework
     *
     * @return string
     * @throws \Exception
     */
    public function bootDcGeneralAndProcess(
        Request $request,
        string $tableName,
        DcGeneralFactoryService $factoryFactory,
        EventDispatcherInterface $dispatcher,
        TranslatorInterface $translator,
        ContaoFramework $framework,
    ): string {
        $act = (string) $request->query->get('act', 'showAll');

        // Work around legacy Contao code.
        /** @psalm-suppress InternalMethod - Class ContaoFramework is internal, not the getAdapter() method. */
        $contaoController = $framework->getAdapter(Controller::class);
        // Need to load the language file due to Widget class using hardcoded lang array offsets.
        $contaoController->loadLanguageFile('default');

        // Handle Ajax calls.
        $action    = null;
        if ($request->isXmlHttpRequest() && '' !== ($action = (string) $request->request->get('action', ''))) {
            $ajaxClass = new Ajax($action);
            $ajaxClass->executePreActions();
        }

        // Build data container.
        $factory = $factoryFactory->createFactory();
        $general = $factory
            ->setContainerName($tableName)
            ->setTranslator($translator)
            ->setEventDispatcher($dispatcher)
            ->createDcGeneral();

        $environment = $general->getEnvironment();
        $clipboard = $environment->getClipboard();
        assert($clipboard instanceof ClipboardInterface);

        // Load the clipboard.
        $clipboard->loadFrom($environment);

        $controller = $environment->getController();
        assert($controller instanceof ControllerInterface);

        if (null !== $action) {
            $environment->getView()?->handleAjaxCall();
        }

        return $controller->handle(new Action($act));
    }
}
