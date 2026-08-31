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

namespace MetaModels\Test\Item;

use MetaModels\Item\LazyAttributeResultResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(LazyAttributeResultResolver::class)]
class LazyAttributeResultResolverTest extends TestCase
{
    public function testResolvesThroughTheGivenClosure(): void
    {
        $resolver = new LazyAttributeResultResolver(
            static fn (string $colName): array => ['text' => 'rendered:' . $colName]
        );

        self::assertSame(['text' => 'rendered:title'], $resolver->resolve('title'));
    }

    public function testCallsTheClosureOnlyOnceForRepeatedAccess(): void
    {
        $calls = [];

        $resolver = new LazyAttributeResultResolver(
            static function (string $colName) use (&$calls): array {
                $calls[] = $colName;

                return ['text' => $colName];
            }
        );

        $resolver->resolve('title');
        $resolver->resolve('title');
        $resolver->resolve('title');

        self::assertSame(['title'], $calls);
    }

    public function testDifferentColumnsAreResolvedIndependently(): void
    {
        $calls = [];

        $resolver = new LazyAttributeResultResolver(
            static function (string $colName) use (&$calls): array {
                $calls[] = $colName;

                return ['text' => $colName];
            }
        );

        $resolver->resolve('alias');
        $resolver->resolve('title');
        $resolver->resolve('alias');

        self::assertSame(['alias', 'title'], $calls);
    }
}
