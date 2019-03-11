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
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\Contao\Hooks;

use Contao\DataContainer;
use MetaModels\ViewCombination\InputScreenInformationBuilder;
use MetaModels\ViewCombination\ViewCombinationBuilder;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * This is called as a HOOK from tl_user_group.
 */
class FixupUserGroupModules
{
    /**
     * The view combination builder.
     *
     * @var ViewCombinationBuilder
     */
    private $combinationBuilder;

    /**
     * The input screen information builder.
     *
     * @var InputScreenInformationBuilder
     */
    private $inputScreens;

    /**
     * The request stack.
     *
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * Create a new instance.
     *
     * @param ViewCombinationBuilder        $combinationBuilder The view combination builder.
     * @param InputScreenInformationBuilder $inputScreens       The input screen information builder.
     * @param RequestStack                  $requestStack       The request stack.
     */
    public function __construct(
        ViewCombinationBuilder $combinationBuilder,
        InputScreenInformationBuilder $inputScreens,
        RequestStack $requestStack
    ) {
        $this->combinationBuilder = $combinationBuilder;
        $this->inputScreens       = $inputScreens;
        $this->requestStack       = $requestStack;
    }

    /**
     * Fix up the modules in the backend.
     *
     * @param DataContainer $dataContainer The current data container.
     *
     * @return array
     *
     * @throws \RuntimeException When the "parenting" class can not be found.
     */
    public function fixupModules(DataContainer $dataContainer)
    {
        if (!class_exists('tl_user_group', false)) {
            throw new \RuntimeException('data container is not loaded!');
        }

        $original = new \tl_user_group();
        $modules  = $original->getModules();

        // 1. remove all MetaModels
        foreach (array_keys($modules) as $group) {
            foreach ($modules[$group] as $key => $module) {
                if (strpos($module, 'metamodel_') === 0) {
                    unset($modules[$group][$key]);
                }
            }
            // Otherwise we end up with an associative array.
            $modules[$group] = array_values($modules[$group]);
        }

        // 2. Add our "custom" modules and remove the main module.
        $modules['metamodels'][] = 'support_metamodels';
        if (false !== $index = array_search('metamodels', $modules['metamodels'], true)) {
            unset($modules['metamodels'][$index]);
            $modules['metamodels'] = array_values($modules['metamodels']);
        }

        // 3. Add back all MetaModels for the current group.
        $combinations = $this->combinationBuilder->getCombinationsForUser([$dataContainer->activeRecord->id], 'be');

        $screenIds = array_map(function ($combination) {
            return $combination['dca_id'];
        }, $combinations['byName']);

        $screens = $this->inputScreens->fetchInputScreens($screenIds);

        $locale = $this->requestStack->getCurrentRequest()->getLocale();
        foreach ($screens as $metaModel => $screen) {
            if ('standalone' === $screen['meta']['rendertype']) {
                $modules[$screen['meta']['backendsection']][] = 'metamodel_' . $metaModel;
                $this->buildLanguageString('metamodel_' . $metaModel, $screen, $locale);
            }
        }

        return $modules;
    }

    /**
     * Build the language string for the passed backend module.
     *
     * @param string $name   The module name.
     * @param array  $screen The input screen information.
     * @param string $locale The locale.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function buildLanguageString($name, $screen, $locale)
    {
        if (isset($screen['label'][$locale])) {
            $GLOBALS['TL_LANG']['MOD'][$name] = $screen['label'][$locale];
            return;
        }

        $GLOBALS['TL_LANG']['MOD'][$name] = $screen['label'][''];
    }
}
