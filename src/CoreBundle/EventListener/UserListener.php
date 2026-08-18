<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2023 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener;

use Contao\CoreBundle\Routing\ScopeMatcher;
use MetaModels\ViewCombination\ViewCombination;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Security\Core\Authentication\AuthenticationTrustResolverInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * This listens on the user and initializes the backend then.
 */
class UserListener
{
    /**
     * The authentication resolver.
     *
     * @var TokenStorageInterface
     */
    private TokenStorageInterface $tokenStorage;

    /**
     * The authentication resolver.
     *
     * @var AuthenticationTrustResolverInterface
     */
    private AuthenticationTrustResolverInterface $trustResolver;

    /**
     * The scope matcher.
     *
     * @var ScopeMatcher
     */
    private ScopeMatcher $scopeMatcher;

    /**
     * The view combination.
     *
     * @var ViewCombination
     */
    private ViewCombination $viewCombination;

    /**
     * Constructor.
     *
     * @param TokenStorageInterface                $tokenStorage    The token storage.
     * @param AuthenticationTrustResolverInterface $trustResolver   The authentication resolver.
     * @param ScopeMatcher                         $scopeMatcher    The scope matche.
     * @param ViewCombination                      $viewCombination The view combination.
     */
    public function __construct(
        TokenStorageInterface $tokenStorage,
        AuthenticationTrustResolverInterface $trustResolver,
        ScopeMatcher $scopeMatcher,
        ViewCombination $viewCombination
    ) {
        $this->tokenStorage    = $tokenStorage;
        $this->trustResolver   = $trustResolver;
        $this->scopeMatcher    = $scopeMatcher;
        $this->viewCombination = $viewCombination;
    }

    /**
     * Replaces the current session data with the stored session data.
     *
     * @param RequestEvent $event The event.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     *
     * @return void
     */
    public function onKernelRequest(RequestEvent $event)
    {
        if (!$this->scopeMatcher->isBackendMainRequest($event)) {
            return;
        }

        $token = $this->tokenStorage->getToken();

        if (null === $token || !$this->trustResolver->isAuthenticated($token)) {
            return;
        }

        $localMenu = &$GLOBALS['BE_MOD'];
        // The MetaModels themselves are deliberately not registered as back end modules here.
        // Doing so would keep the permissions in two places at once: as a module right on the
        // user group, and as the assignment of input screen and render setting to that group
        // in "tl_metamodel_dca_combine". The latter decides what a user gets to see anyway,
        // and the controller of the route turns away anyone the combination does not cover.
        // One place to maintain was preferred over two that can contradict each other.
        //
        // Child tables are a different matter - those still need their entry, see below.
        $this->injectChildTables($localMenu);
    }

    /**
     * Inject all child tables.
     *
     * @param array $localMenu Reference to the global array.
     *
     * @return void
     */
    private function injectChildTables(&$localMenu)
    {
        $parented  = $this->viewCombination->getParented();
        $lastCount = \count($parented);
        while ($parented) {
            foreach ($parented as $metaModelName => $child) {
                foreach ($localMenu as $groupName => $modules) {
                    foreach ($modules as $moduleName => $module) {
                        if (isset($module['tables']) && \in_array($child['meta']['ptable'], $module['tables'])) {
                            $localMenu[$groupName][$moduleName]['tables'][] = $metaModelName;
                            unset($parented[$metaModelName]);
                            break;
                        }
                    }
                }
            }
            // If the dependencies can not be resolved any further, we give up here to prevent an endless loop.
            if (\count($parented) === $lastCount) {
                break;
            }
            $lastCount = \count($parented);
        }
    }
}
