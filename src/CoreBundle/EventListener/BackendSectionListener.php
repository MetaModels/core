<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2026 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2026 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener;

use Contao\CoreBundle\Event\MenuEvent;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Knp\Menu\Util\MenuManipulator;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;

use function array_keys;
use function array_search;
use function count;
use function ltrim;
use function preg_match;
use function sprintf;
use function str_starts_with;

/**
 * This registers user defined backend navigation sections configured via "metamodels.be_sections".
 *
 * @psalm-type TBackendSectionConfig = array{
 *   name: array<string, string>,
 *   tooltip: array<string, string>,
 *   icon: string|null,
 *   add: array{before: string|null, after: string|null},
 *   collapsed: bool
 * }
 */
class BackendSectionListener
{
    /**
     * The configured sections, keyed by their alias.
     *
     * @var array<string, TBackendSectionConfig>
     */
    private array $sections;

    /**
     * The request stack.
     *
     * @var RequestStack
     */
    private RequestStack $requestStack;

    /**
     * Create a new instance.
     *
     * @param array<string, TBackendSectionConfig> $sections     The configured sections.
     * @param RequestStack                         $requestStack The request stack.
     */
    public function __construct(array $sections, RequestStack $requestStack)
    {
        $this->sections     = $sections;
        $this->requestStack = $requestStack;
    }

    /**
     * Register the configured backend sections.
     *
     * @param MenuEvent $event The menu event.
     *
     * @return void
     */
    public function __invoke(MenuEvent $event): void
    {
        if ([] === $this->sections) {
            return;
        }

        $factory = $event->getFactory();
        $tree    = $event->getTree();

        if ('mainMenu' !== $tree->getName()) {
            return;
        }

        if (null === ($request = $this->requestStack->getCurrentRequest())) {
            return;
        }

        $locale      = $request->getLocale();
        $manipulator = new MenuManipulator();

        foreach ($this->sections as $alias => $config) {
            if (null !== $tree->getChild($alias)) {
                // Someone else (or a previous request) already built this node - leave it alone.
                continue;
            }

            $node = $this->buildSectionNode($factory, $alias, $config, $locale);
            $tree->addChild($node);

            $targetNode     = $config['add']['before'] ?? $config['add']['after'];
            $targetPosition = array_search($targetNode, array_keys($tree->getChildren()), true);
            $targetPosition = false === $targetPosition
                ? count($tree->getChildren()) - 1
                : $targetPosition + (null !== $config['add']['after'] ? 1 : 0);

            $manipulator->moveToPosition($node, $targetPosition);
        }
    }

    /**
     * Build a single section node.
     *
     * @param FactoryInterface      $factory The factory.
     * @param string                $alias   The section alias.
     * @param TBackendSectionConfig $config  The section configuration.
     * @param string                $locale  The current locale.
     *
     * @return ItemInterface
     */
    private function buildSectionNode(
        FactoryInterface $factory,
        string $alias,
        array $config,
        string $locale
    ): ItemInterface {
        $sessionBag  = $this->requestStack->getSession()->getBag('contao_backend');
        $status      = ($sessionBag instanceof AttributeBagInterface) ? $sessionBag->get('backend_modules') : [];
        $default     = $config['collapsed'] ? 0 : 1;
        $isCollapsed = ($status[$alias] ?? $default) < 1;

        $label   = $this->resolveTranslation($config['name'], $locale, $alias);
        $tooltip = [] !== $config['tooltip']
            ? $this->resolveTranslation($config['tooltip'], $locale, $label)
            : $label;

        $node = $factory
            ->createItem($alias)
            ->setUri('/contao?mtg=' . $alias)
            ->setLabel($label)
            ->setExtra('translation_domain', false)
            ->setLinkAttribute('class', 'group-' . $alias)
            ->setLinkAttribute('title', $tooltip)
            ->setLinkAttribute('data-action', 'contao--toggle-navigation#toggle:prevent')
            ->setLinkAttribute('data-contao--toggle-navigation-category-param', $alias)
            ->setLinkAttribute('aria-controls', $alias)
            ->setLinkAttribute('aria-expanded', $isCollapsed ? 'false' : 'true')
            ->setChildrenAttribute('id', $alias);

        if (null !== $config['icon']) {
            $node->setLinkAttribute(
                'style',
                sprintf('background: url(%s) 3px 2px no-repeat;', $this->resolveIconPath($config['icon']))
            );
        }

        if ($isCollapsed) {
            $node->setAttribute('class', 'collapsed');
        }

        return $node;
    }

    /**
     * Resolve a translation from a locale map.
     *
     * @param array<string, string> $translations The locale => text map.
     * @param string                $locale       The current locale.
     * @param string                $fallback     The fallback value if nothing could be resolved.
     *
     * @return string
     */
    private function resolveTranslation(array $translations, string $locale, string $fallback): string
    {
        if (isset($translations[$locale])) {
            return $translations[$locale];
        }

        if (isset($translations['en'])) {
            return $translations['en'];
        }

        foreach ($translations as $text) {
            return $text;
        }

        return $fallback;
    }

    /**
     * Resolve the web accessible path for an icon.
     *
     * @param string $icon The icon path as configured.
     *
     * @return string
     */
    private function resolveIconPath(string $icon): string
    {
        if (str_starts_with($icon, '/') || 1 === preg_match('#^https?://#', $icon)) {
            return $icon;
        }

        return '/' . ltrim($icon, '/');
    }
}
