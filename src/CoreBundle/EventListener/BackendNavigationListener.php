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
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener;

use Contao\CoreBundle\Event\MenuEvent;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use MetaModels\ViewCombination\ViewCombination;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

use function array_key_exists;
use function array_map;
use function array_unshift;
use function in_array;
use function is_array;

/**
 * This registers the backend navigation of MetaModels.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class BackendNavigationListener
{
    /**
     * The request stack.
     *
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * The translator in use.
     *
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    /**
     * The translator in use.
     *
     * @var ViewCombination
     */
    private ViewCombination $viewCombination;

    /**
     * The token storage.
     *
     * @var TokenStorageInterface
     */
    private TokenStorageInterface $tokenStorage;

    /**
     * The router.
     *
     * @var RouterInterface
     */
    private RouterInterface $router;

    /**
     * The session.
     *
     * @var Session
     */
    private Session $session;

    /**
     * Create a new instance.
     *
     * @param TranslatorInterface   $translator      The translator.
     * @param RequestStack          $requestStack    The request stack.
     * @param ViewCombination       $viewCombination The view combination.
     * @param TokenStorageInterface $tokenStorage    The token storage.
     * @param RouterInterface       $router          The router.
     */
    public function __construct(
        TranslatorInterface $translator,
        RequestStack $requestStack,
        ViewCombination $viewCombination,
        TokenStorageInterface $tokenStorage,
        RouterInterface $router,
    ) {
        $this->requestStack    = $requestStack;
        $this->translator      = $translator;
        $this->viewCombination = $viewCombination;
        $this->tokenStorage    = $tokenStorage;
        $this->router          = $router;
    }

    /**
     * Register back end menu items.
     *
     * @param MenuEvent $event The menu event.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function __invoke(MenuEvent $event): void
    {
        $factory = $event->getFactory();
        $tree    = $event->getTree();

        if ('mainMenu' !== $tree->getName()) {
            return;
        }

        if (null === ($request = $this->requestStack->getCurrentRequest())) {
            return;
        }

        if (null === ($user = $this->tokenStorage->getToken())) {
            return;
        }

        $this->addBackendCss();

        $isAdmin = in_array('ROLE_ADMIN', $user->getRoleNames(), true);

        $metaModelsNode = $this->getRootNode($tree, $factory);

        $names = array_map(static fn(ItemInterface $item): string => $item->getName(), $metaModelsNode->getChildren());

        // Show MetaModels config only for Admins.
        if ($isAdmin) {
            // Add or override child - might have been introduced by parsing of legacy BE_MOD.
            $metaModelsNode->addChild($configNode = $this->buildConfigNode($factory, $request));
            if (!in_array($configNode->getName(), $names, true)) {
                array_unshift($names, $configNode->getName());
            }
        }

        $metaModelsNode->reorderChildren($names);

        $currentMetaModel = '';
        if ($request->attributes->get('_route') === 'metamodels.metamodel') {
            $currentMetaModel = (string) (($request->attributes->get('_route_params', []))['tableName'] ?? '');
        }

        foreach ($this->viewCombination->getStandalone() as $metaModelName => $screen) {
            if (null === $sectionNode = $this->getSectionNode($factory, $tree, $screen['meta']['backendsection'])) {
                // Rien ne vas plus.
                continue;
            }

            $item = 'metamodel_' . $metaModelName;

            $node = $factory
                ->createItem($item)
                ->setUri($this->router->generate('metamodels.metamodel', ['tableName' => $metaModelName]))
                ->setLabel('inputscreen.' . $screen['meta']['id'] . '.menu.label')
                ->setExtra('translation_domain', $metaModelName)
                ->setLinkAttribute(
                    'title',
                    $this->translator->trans(
                        'inputscreen.' . $screen['meta']['id'] . '.menu.description',
                        [],
                        $metaModelName
                    )
                )
                ->setLinkAttribute('class', $item)
                ->setCurrent($currentMetaModel === $metaModelName);

            $sectionNode->addChild($node);
        }

        if ($isAdmin) {
            $node = $factory
                ->createItem('support_screen')
                ->setUri($this->router->generate('metamodels.support_screen'))
                ->setLabel('menu.label')
                ->setExtra('translation_domain', 'metamodels_support')
                ->setLinkAttribute('title', $this->translator->trans('menu.description', [], 'metamodels_support'))
                ->setLinkAttribute('class', 'support_screen')
                ->setCurrent('metamodels.support_screen' === $request->attributes->get('_route'));

            $metaModelsNode->addChild($node);
        }
    }

    /**
     * Add the CSS files for the backend.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function addBackendCss(): void
    {
        // BE group icon.
        $GLOBALS['TL_CSS']['metamodels'] = '/bundles/metamodelscore/css/be_logo_svg.css';
    }

    /**
     * Get root node.
     *
     * @param ItemInterface    $tree    The tree.
     * @param FactoryInterface $factory The factory.
     *
     * @return ItemInterface
     */
    private function getRootNode(ItemInterface $tree, FactoryInterface $factory): ItemInterface
    {
        $names          =
            $this->getChildNamesFromTree($tree);
        $insertPos      = (int) array_search('accounts', $names, true) + 1;
        $metaModelsNode = $tree->getChild('metamodels');
        if (null === $metaModelsNode) {
            $metaModelsNode = $factory->createItem('metamodels');
            $tree->addChild($metaModelsNode);
        }

        $metaModelsNode
            ->setLabel('menuGroup.metamodels.label')
            ->setExtra('translation_domain', 'metamodels_navigation');

        $this->updateCollapsedState($metaModelsNode);

        // Resort if already existing.
        $names = array_values(array_filter($names, static fn(string $name): bool => 'metamodels' !== $name));

        array_splice($names, $insertPos, 0, 'metamodels');
        $tree->reorderChildren($names);

        return $metaModelsNode;
    }

    /**
     * Generate build config.
     *
     * @param FactoryInterface $factory The factory.
     * @param Request          $request The request.
     *
     * @return ItemInterface
     */
    private function buildConfigNode(FactoryInterface $factory, Request $request): ItemInterface
    {
        $configNode = $factory->createItem('metamodels');

        $configNode
            ->setUri($this->router->generate('metamodels.configuration'))
            ->setLabel('menu.metamodels.label')
            ->setExtra('translation_domain', 'metamodels_navigation')
            ->setLinkAttribute('title', $this->translator->trans('menu.metamodels.title', [], 'metamodels_navigation'))
            ->setLinkAttribute('class', 'metamodel_config')
            ->setCurrent('metamodels.configuration' === $request->attributes->get('_route'));

        return $configNode;
    }

    /**
     * Update collapsed state of navigation groups.
     *
     * @param ItemInterface $metaModelsNode
     *
     * @return void
     */
    private function updateCollapsedState(ItemInterface $metaModelsNode): void
    {
        $nodeName    = $metaModelsNode->getName();
        $sessionBag  = $this->requestStack->getSession()->getBag('contao_backend');
        $status      = ($sessionBag instanceof AttributeBagInterface) ? $sessionBag->get('backend_modules') : [];
        $isCollapsed = ($status[$nodeName] ?? 1) < 1;
        $path        = $this->router->generate('contao_backend');

        $metaModelsNode
            ->setLinkAttribute('class', 'group-' . $nodeName)
            ->setLinkAttribute(
                'onclick',
                "return AjaxRequest.toggleNavigation(this, '" . $nodeName . "', '" . $path . "')"
            )
            ->setLinkAttribute('aria-controls', $nodeName)
            ->setChildrenAttribute('id', $nodeName)
            ->setLinkAttribute(
                'title',
                $this->translator->trans('MSC.' . ($isCollapsed ? 'expand' : 'collapse') . 'Node', [], 'contao_default')
            )
            ->setLinkAttribute('aria-expanded', $isCollapsed ? 'false' : 'true');

        if ($isCollapsed) {
            $metaModelsNode->setAttribute('class', 'collapsed');
        }

        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return;
        }

        $uri = $this->router->generate(
            'contao_backend',
            [
                'do'  => $request->query->get('do'),
                'mtg' => $nodeName,
                'ref' => $request->attributes->get('_contao_referer_id')
            ]
        );

        $metaModelsNode->setUri($uri);
    }

    /**
     * Get section node.
     *
     * @param FactoryInterface $factory        The factory.
     * @param ItemInterface    $tree           The item interface.
     * @param string           $backendSection The backend section.
     *
     * @return ItemInterface|null
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function getSectionNode(
        FactoryInterface $factory,
        ItemInterface $tree,
        string $backendSection
    ): ?ItemInterface {
        if (null !== $sectionNode = $tree->getChild($backendSection)) {
            return $sectionNode;
        }

        // Somehow it disappeared - try to generate it via BE_MOD.
        if (!array_key_exists($backendSection, $GLOBALS['BE_MOD'])) {
            return null;
        }
        $navigation = array_keys($GLOBALS['BE_MOD']);
        // Keep child names before adding the new child.
        $namesInMenu   = $this->getChildNamesFromTree($tree);
        $sectionNode   = $factory->createItem($backendSection);
        $tree->addChild($sectionNode);
        $sectionNode
            ->setLabel($this->getLabelForSection($backendSection))
            ->setExtra('translation_domain', false);

        $this->updateCollapsedState($sectionNode);

        // Search the position in the already existing menu by starting at offset in BE_MOD and walking up to the start.
        $start = (int) array_search($backendSection, $navigation, true);
        while (0 <= --$start) {
            /** @psalm-suppress InvalidArrayOffset */
            if (in_array($navigation[$start], $namesInMenu, true)) {
                array_splice($namesInMenu, $start + 1, 0, [$backendSection]);
                break;
            }
        }
        // If we did not find a position in existing menu, append at the end.
        if (!in_array($backendSection, $namesInMenu)) {
            $namesInMenu[] = $backendSection;
        }
        $tree->reorderChildren($namesInMenu);

        return $sectionNode;
    }

    /**
     * @param ItemInterface $tree
     *
     * @return list<string>
     */
    private function getChildNamesFromTree(ItemInterface $tree): array
    {
        return array_values(
            array_map(static fn(ItemInterface $item): string => $item->getName(), $tree->getChildren())
        );
    }

    /** @SuppressWarnings(PHPMD.Superglobals) */
    public function getLabelForSection(string $backendSection): string
    {
        /** @var null|list<string>|string $langValue */
        $langValue = $GLOBALS['TL_LANG']['MOD'][$backendSection] ?? null;
        if (is_array($langValue)) {
            return $langValue[0] ?? $backendSection;
        }

        return $langValue ?? $backendSection;
    }
}
