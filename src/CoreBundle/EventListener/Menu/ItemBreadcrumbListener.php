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

declare(strict_types=1);

namespace MetaModels\CoreBundle\EventListener\Menu;

use Contao\CoreBundle\Event\MenuEvent;
use MetaModels\CoreBundle\Backend\ItemBreadcrumbBuilder;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Puts the trail of a child table listing into Contaos backend breadcrumb.
 *
 * Contao renders the menu itself, including the collapsed middle - an item named "ancestor_trail"
 * comes out as an ellipsis with a dropdown. Only the elements are contributed here.
 */
final class ItemBreadcrumbListener
{
    /**
     * How many elements are shown before the middle is collapsed.
     *
     * The base of the chain, the last parent and the current view stay visible; what lies between
     * them moves into the dropdown. Collapsing a single element would trade one readable entry for
     * an ellipsis, so it starts at two.
     */
    private const int KEEP_AT_START = 1;
    private const int KEEP_AT_END   = 2;

    public function __construct(
        private readonly ItemBreadcrumbBuilder $builder,
        private readonly RequestStack $requestStack,
    ) {
    }

    /**
     * Add the elements to the breadcrumb menu.
     *
     * @param MenuEvent $event The event.
     *
     * @return void
     */
    public function __invoke(MenuEvent $event): void
    {
        if ('breadcrumbMenu' !== $event->getTree()->getName()) {
            return;
        }

        if (null === ($trail = $this->trail())) {
            return;
        }

        $factory = $event->getFactory();
        $tree    = $event->getTree();

        $middle = \array_slice($trail, self::KEEP_AT_START, -self::KEEP_AT_END);
        $shown  = \count($middle) > 1
            ? \array_merge(
                \array_slice($trail, 0, self::KEEP_AT_START),
                [null],
                \array_slice($trail, -self::KEEP_AT_END)
            )
            : $trail;

        $level = 0;
        foreach ($shown as $element) {
            if (null === $element) {
                $ancestors = $factory->createItem('ancestor_trail');
                foreach ($middle as $index => $ancestor) {
                    $ancestors->addChild('ancestor_trail_' . $index, [
                        'label'  => $ancestor['label'],
                        'uri'    => $ancestor['url'],
                        'extras' => ['index' => $index],
                    ]);
                }
                $tree->addChild($ancestors);

                continue;
            }

            $tree->addChild(
                $factory
                    ->createItem('metamodels_' . $level++)
                    ->setLabel($element['label'])
                    ->setUri($element['url'])
            );
        }
    }

    /**
     * Build the trail for the current request, if it is a MetaModels item view with a chain.
     *
     * @return list<array{table: string, label: string, url: string|null}>|null
     */
    private function trail(): ?array
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request || 'metamodels.metamodel' !== $request->attributes->get('_route')) {
            return null;
        }

        $table = (string) $request->query->get('table', '');
        if ('' === $table) {
            return null;
        }

        $trail = $this->builder->build(
            $table,
            $request->query->getString('pid') ?: null,
            $request->query->getString('id') ?: null
        );

        return [] === $trail ? null : $trail;
    }
}
