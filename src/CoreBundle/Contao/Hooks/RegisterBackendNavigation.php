<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2019 The MetaModels team.
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
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\Contao\Hooks;

use Contao\BackendUser;
use Contao\StringUtil;
use MetaModels\ViewCombination\ViewCombination;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * This registers the backend navigation of MetaModels.
 */
class RegisterBackendNavigation
{
    /**
     * The request stack.
     *
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * The URL generator.
     *
     * @var UrlGeneratorInterface
     */
    private $urlGenerator;

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
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * Create a new instance.
     *
     * @param TranslatorInterface   $translator      The translator.
     * @param RequestStack          $requestStack    The request stack.
     * @param UrlGeneratorInterface $urlGenerator    The url generator.
     * @param ViewCombination       $viewCombination The view combination.
     * @param TokenStorage          $tokenStorage    The token storage.
     */
    public function __construct(
        TranslatorInterface $translator,
        RequestStack $requestStack,
        UrlGeneratorInterface $urlGenerator,
        ViewCombination $viewCombination,
        TokenStorage $tokenStorage
    ) {
        $this->requestStack    = $requestStack;
        $this->urlGenerator    = $urlGenerator;
        $this->translator      = $translator;
        $this->viewCombination = $viewCombination;
        $this->tokenStorage    = $tokenStorage;
    }

    /**
     * Hook function
     *
     * @param array $modules The backend navigation.
     *
     * @return array
     */
    public function onGetUserNavigation($modules)
    {
        if (null === $request = $this->requestStack->getCurrentRequest()) {
            return $modules;
        }

        if (null !== ($user = $this->tokenStorage->getToken())) {
            $userRights = $this->extractUserRights($user);
        }
        $isAdmin = \in_array('ROLE_ADMIN', array_map(function (Role $role) {
            return $role->getRole();
        }, $user->getRoles()), true);

        if ($isAdmin || isset($userRights['support_metamodels'])) {
            $this->addMenu(
                $modules,
                'metamodels',
                'support_screen',
                [
                    'label' => $this->translator->trans('MOD.support_metamodels.0', [], 'contao_modules'),
                    'title' => $this->translator->trans('MOD.support_metamodels.1', [], 'contao_modules'),
                    'route' => 'metamodels.support_screen',
                    'param' => [],
                ],
                $request
            );
        }

        $locale = $request->getLocale();
        foreach ($this->viewCombination->getStandalone() as $metaModelName => $screen) {
            $moduleName = 'metamodel_' . $metaModelName;
            if (!$isAdmin && !isset($userRights[$moduleName])) {
                continue;
            }
            $this->addMenu(
                $modules,
                $screen['meta']['backendsection'],
                $moduleName,
                [
                    'label' => $this->extractLanguageValue($screen['label'], $locale),
                    'title' => $this->extractLanguageValue($screen['description'], $locale),
                    'route' => 'contao_backend',
                    'param' => ['do' => 'metamodel_' . $metaModelName],
                ],
                $request
            );
        }

        return $modules;
    }

    /**
     * Extract the permissions from the Contao backend user.
     *
     * @param TokenInterface $token The token.
     *
     * @return array
     */
    private function extractUserRights(TokenInterface $token)
    {
        $beUser = $token->getUser();
        if (!($beUser instanceof BackendUser)) {
            return [];
        }

        $allowedModules = $beUser->modules;
        switch (true) {
            case \is_string($allowedModules):
                $allowedModules = unserialize($allowedModules, ['allowed_classes' => false]);
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
    private function extractLanguageValue($values, $locale)
    {
        if (isset($values[$locale])) {
            return $values[$locale];
        }

        return $values[''];
    }

    /**
     * Build a menu section.
     *
     * @param string  $groupName The group name.
     * @param Request $request   The current request.
     *
     * @return array
     */
    private function buildBackendMenuSection($groupName, Request $request)
    {
        $strRefererId = $request->attributes->get('_contao_referer_id');

        $label = $this->translator->trans('MOD.' . $groupName, [], 'contao_modules');

        if (\is_array($label)) {
            $label = $label[0];
        }

        return [
            'class'   => ' node-expanded',
            'title'   => StringUtil::specialchars($this->translator->trans('MSC.collapseNode', [], 'contao_modules')),
            'label'   => $label,
            'href'    => $this->urlGenerator->generate(
                'contao_backend',
                ['do' => $request->get('do'), 'mtg' => $groupName, 'ref' => $strRefererId]
            ),
            'ajaxUrl' => $this->urlGenerator->generate('contao_backend'),
            // backwards compatibility with e.g. EasyThemes
            'icon'    => 'modPlus.gif',
            'modules'  => [],
        ];
    }

    /**
     * Add a module to the modules list.
     *
     * @param array   $modules The modules list.
     * @param string  $section The section to add to.
     * @param string  $name    The name of the module.
     * @param array   $module  The module.
     * @param Request $request The current request.
     *
     * @return void
     */
    private function addMenu(&$modules, $section, $name, $module, Request $request)
    {
        if (!isset($modules[$section])) {
            $modules[$section] = $this->buildBackendMenuSection($section, $request);
        }

        $active = $this->isActive($module['route'], $module['param'], $request);
        $class  = 'navigation ' . $name;
        if (isset($module['class'])) {
            $class .= ' ' . $module['class'];
        }
        if ($active) {
            $class .= ' active';
        }
        if ($request->query->has('ref')) {
            $module['param']['ref'] = $request->query->get('ref');
        }

        $modules[$section]['modules'][$name] = [
            'label'    => $module['label'],
            'title'    => $module['title'],
            'class'    => $class,
            'isActive' => $active,
            'href'     => $this->urlGenerator->generate($module['route'], $module['param']),
        ];
    }

    /**
     * Determine if is active.
     *
     * @param string  $route   The route name.
     * @param array   $params  The route parameters.
     * @param Request $request The current request.
     *
     * @return bool
     */
    private function isActive($route, $params, Request $request)
    {
        if ('/contao' === $request->getPathInfo()
            || !($request->attributes->get('_route') === $route)
        ) {
            return false;
        }

        $attributes = $request->attributes->get('_route_params');
        $query      = $request->query;
        foreach ($params as $param => $value) {
            if (isset($attributes[$param]) && ($value !== $request->attributes['_route_params'][$param])) {
                return false;
            }
            if ($query->has($param) && ($value !== $query->get($param))) {
                return false;
            }
        }

        return true;
    }
}
