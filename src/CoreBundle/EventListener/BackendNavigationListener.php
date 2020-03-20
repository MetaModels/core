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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2020 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener;

use Contao\BackendUser;
use Contao\CoreBundle\Event\MenuEvent;
use Contao\StringUtil;
use MetaModels\ViewCombination\ViewCombination;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * This registers the backend navigation of MetaModels.
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
    private $translator;

    /**
     * The translator in use.
     *
     * @var ViewCombination
     */
    private $viewCombination;

    /**
     * The token storage.
     *
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * The router.
     *
     * @var RouterInterface
     */
    private $router;

    /**
     * Create a new instance.
     *
     * @param TranslatorInterface   $translator      The translator.
     * @param RequestStack          $requestStack    The request stack.
     * @param ViewCombination       $viewCombination The view combination.
     * @param TokenStorageInterface $tokenStorage    The token storage.
     */
    public function __construct(
        TranslatorInterface $translator,
        RequestStack $requestStack,
        ViewCombination $viewCombination,
        TokenStorageInterface $tokenStorage, RouterInterface $router
    ) {
        $this->requestStack    = $requestStack;
        $this->translator      = $translator;
        $this->viewCombination = $viewCombination;
        $this->tokenStorage    = $tokenStorage;
        $this->router = $router;
    }

    /**
     * Register back end menu items.
     *
     * @param MenuEvent $event The menu event.
     */
    public function __invoke(MenuEvent $event): void
    {
        $factory = $event->getFactory();
        $tree    = $event->getTree();

        if ('mainMenu' !== $tree->getName()) {
            return;
        }

        if (null === $request = $this->requestStack->getCurrentRequest()) {
            return;
        }

        $this->addBackendCss();

        if (null !== ($user = $this->tokenStorage->getToken())) {
            $userRights = $this->extractUserRights($user);
        }

        $isAdmin = \in_array('ROLE_ADMIN', $user->getRoleNames(), true);

        $metaModelsNode = $tree->getChild('metamodels');
        if (null !== $metaModelsNode && ($isAdmin || isset($userRights['support_metamodels']))) {
            $node = $factory
                ->createItem('support_screen')
                ->setUri($this->router->generate('metamodels.support_screen'))
                ->setLabel($this->translator->trans('MOD.support_metamodels.0', [], 'contao_modules'))
                ->setLinkAttribute('title', $this->translator->trans('MOD.support_metamodels.1', [], 'contao_modules'))
                ->setLinkAttribute('class', 'support_screen')
                ->setCurrent('metamodels.support_screen' === $request->get('_route'));

            $metaModelsNode->addChild($node);
        }

        $locale = $request->getLocale();
        foreach ($this->viewCombination->getStandalone() as $metaModelName => $screen) {
            $moduleName = 'metamodel_' . $metaModelName;
            if (!$isAdmin && !isset($userRights[$moduleName])) {
                continue;
            }

            $sectionNode = $tree->getChild($screen['meta']['backendsection']);
            if (null === $sectionNode) {
                continue;
            }

            $node = $factory
                ->createItem('metamodel_' . $metaModelName)
                ->setUri($this->router->generate('contao_backend', ['do' => 'metamodel_' . $metaModelName]))
                ->setLabel($this->extractLanguageValue($screen['label'], $locale))
                ->setLinkAttribute('title', $this->extractLanguageValue($screen['description'], $locale))
                ->setLinkAttribute('class', 'metamodel_' . $metaModelName)
                ->setCurrent($request->get('_route') === 'contao_backend' && $request->query->get('do') === 'metamodel_' . $metaModelName);

            $sectionNode->addChild($node);
        }
    }

    /**
     * Extract the permissions from the Contao backend user.
     *
     * @param TokenInterface $token The token.
     *
     * @return array
     */
    private function extractUserRights(TokenInterface $token): array
    {
        $beUser = $token->getUser();
        if (!($beUser instanceof BackendUser)) {
            return [];
        }

        $allowedModules = $beUser->modules;
        switch (true) {
            case \is_string($allowedModules):
                $allowedModules = StringUtil::deserialize($allowedModules, true);
                break;
            case null === $allowedModules:
                $allowedModules = [];
                break;
            default:
        }

        return array_flip($allowedModules);
    }

    /**
     * Extract the language value.
     *
     * @param string[] $values The values.
     *
     * @param string   $locale The current locale.
     *
     * @return string
     */
    private function extractLanguageValue($values, $locale): string
    {
        return $values[$locale] ?? $values[''];
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
        $GLOBALS['TL_CSS']['metamodels'] = 'bundles/metamodelscore/css/be_logo_svg.css';
    }
}
