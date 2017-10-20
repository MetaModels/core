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
     * Create a new instance.
     *
     * @param TranslatorInterface   $translator
     * @param RequestStack          $requestStack
     * @param UrlGeneratorInterface $urlGenerator
     */
    public function __construct(
        TranslatorInterface $translator,
        RequestStack $requestStack,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->requestStack = $requestStack;
        $this->urlGenerator = $urlGenerator;
        $this->translator = $translator;
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

        return $modules;
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
        $module['href'] = $this->urlGenerator->generate($module['route']);

        $active = ($this->requestStack->getCurrentRequest()->attributes->get('_route') === $module['route']);

        $class = 'navigation ' . $name;

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
}
