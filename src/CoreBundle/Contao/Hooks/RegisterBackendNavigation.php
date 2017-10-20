<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2017 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\CoreBundle\Contao\Hooks;

use MetaModels\ViewCombination\ViewCombination;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
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
     * Create a new instance.
     *
     * @param TranslatorInterface   $translator
     * @param RequestStack          $requestStack
     * @param UrlGeneratorInterface $urlGenerator
     * @param ViewCombination       $viewCombination
     */
    public function __construct(
        TranslatorInterface $translator,
        RequestStack $requestStack,
        UrlGeneratorInterface $urlGenerator,
        ViewCombination $viewCombination
    ) {
        $this->requestStack    = $requestStack;
        $this->urlGenerator    = $urlGenerator;
        $this->translator      = $translator;
        $this->viewCombination = $viewCombination;
    }

    /**
     * Hook function
     *
     * @param array $modules The backend navigation.
     *
     * @return mixed
     */
    public function onGetUserNavigation($modules)
    {
        $this->addMenu(
            $modules,
            'metamodels',
            'support_screen',
            [
                'label' => $this->translator->trans('MOD.support_metamodels.0', [], 'contao_modules'),
                'title' => $this->translator->trans('MOD.support_metamodels.1', [], 'contao_modules'),
                'route' => 'metamodels.support_screen',
                'param' => [],
            ]
        );

        $locale = $this->requestStack->getCurrentRequest()->getLocale();
        foreach ($this->viewCombination->getStandalone() as $metaModelName => $screen) {
            $this->addMenu(
                $modules,
                $screen['meta']['backendsection'],
                'metamodel_' . $metaModelName,
                [
                    'label' => $this->extractLanguageValue($screen['label'], $locale),
                    'title' => $this->extractLanguageValue($screen['description'], $locale),
                    'route' => 'contao_backend',
                    'param' => ['do' => 'metamodel_' . $metaModelName],
                ]
            );
        }

        return $modules;
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
     * Add a module to the modules list.
     *
     * @param array  $modules The modules list.
     * @param string $section The section to add to.
     * @param string $name    The name of the module.
     * @param array  $module  The module.
     *
     * @return void
     */
    private function addMenu(&$modules, $section, $name, $module)
    {
        $active = $this->isActive($module['route'], $module['param']);
        $class  = 'navigation ' . $name;
        if (isset($module['class'])) {
            $class .= ' ' . $module['class'];
        }
        if ($active) {
            $class .= ' active';
        }

        $modules[$section]['modules'][$name] = [
            'label'    => $module['label'],
            'title'    => $module['title'],
            'class'    => $class,
            'isActive' => $active,
            'href'     => $this->urlGenerator->generate($module['route'], $module['param'])
        ];
    }

    /**
     * @param string $route  The route name.
     * @param array  $params The route parameters.
     *
     * @return bool
     */
    private function isActive($route, $params)
    {
        if (!$active = ($this->requestStack->getCurrentRequest()->attributes->get('_route') === $route)) {
            return false;
        }
        $request = $this->requestStack->getCurrentRequest();
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
