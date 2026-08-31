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
use MetaModels\Item\LazyAttributeValues;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Countable/IteratorAggregate behaviour and the shared-resolver invariant, split from
 * LazyAttributeValuesTest to keep both test classes under the public-method limit.
 */
#[CoversClass(LazyAttributeValues::class)]
#[CoversClass(LazyAttributeResultResolver::class)]
class LazyAttributeValuesIterationTest extends TestCase
{
    public function testCountMatchesTheNumberOfColumnsWithAPresentValue(): void
    {
        $resolver = new LazyAttributeResultResolver(
            static fn (string $colName): array => 'hidden' === $colName ? ['raw' => null] : ['text' => $colName]
        );
        $values = new LazyAttributeValues($resolver, 'text', ['title', 'hidden', 'alias']);

        self::assertCount(2, $values);
    }

    public function testIterationYieldsOnlyColumnsWithAPresentValue(): void
    {
        $resolver = new LazyAttributeResultResolver(
            static fn (string $colName): array => 'hidden' === $colName
                ? ['raw' => null]
                : ['text' => 'text:' . $colName]
        );
        $values = new LazyAttributeValues($resolver, 'text', ['title', 'hidden', 'alias']);

        self::assertSame(
            ['title' => 'text:title', 'alias' => 'text:alias'],
            \iterator_to_array($values)
        );
    }

    public function testTwoViewsShareOneResolverSoAnAttributeRendersOnlyOnce(): void
    {
        $calls    = [];
        $resolver = new LazyAttributeResultResolver(
            static function (string $colName) use (&$calls): array {
                $calls[] = $colName;

                return ['text' => 'text:' . $colName, 'html5' => 'html5:' . $colName];
            }
        );

        $text  = new LazyAttributeValues($resolver, 'text', ['title']);
        $html5 = new LazyAttributeValues($resolver, 'html5', ['title']);

        self::assertSame('text:title', $text['title']);
        self::assertSame('html5:title', $html5['title']);
        self::assertSame(['title'], $calls, 'the shared resolver must render the attribute only once');
    }
}
