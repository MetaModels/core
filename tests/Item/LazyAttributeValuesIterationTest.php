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
 * Countable/IteratorAggregate behaviour and the resolver-sharing capability, split from
 * LazyAttributeValuesTest to keep both test classes under the public-method limit.
 *
 * Sharing one resolver between two views is a capability of these classes, not something
 * Item::buildLazyAttributeValues() currently uses - it deliberately gives "text" and the
 * requested format independent resolvers, see the class docblock of LazyAttributeValues.
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

    public function testTwoViewsCanShareOneResolverIfConstructedThatWay(): void
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

    public function testTwoViewsWithIndependentResolversEachRenderTheirOwnFormat(): void
    {
        // This is what Item::buildLazyAttributeValues() actually does: reading only one of the two
        // views must not trigger the other's resolver at all.
        $textCalls  = [];
        $html5Calls = [];
        $text       = new LazyAttributeValues(
            new LazyAttributeResultResolver(
                static function (string $colName) use (&$textCalls): array {
                    $textCalls[] = $colName;

                    return ['text' => 'text:' . $colName];
                }
            ),
            'text',
            ['title']
        );
        $html5 = new LazyAttributeValues(
            new LazyAttributeResultResolver(
                static function (string $colName) use (&$html5Calls): array {
                    $html5Calls[] = $colName;

                    return ['html5' => 'html5:' . $colName];
                }
            ),
            'html5',
            ['title']
        );

        self::assertSame('html5:title', $html5['title']);

        self::assertInstanceOf(LazyAttributeValues::class, $text, 'sanity: the unread view was built correctly too');
        self::assertSame(['title'], $html5Calls);
        self::assertSame([], $textCalls, 'reading only the html5 view must not touch the text resolver');
    }
}
