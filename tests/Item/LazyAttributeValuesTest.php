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

#[CoversClass(LazyAttributeValues::class)]
#[CoversClass(LazyAttributeResultResolver::class)]
class LazyAttributeValuesTest extends TestCase
{
    /**
     * @param list<string> $colNames
     */
    private function createValues(
        array $colNames,
        ?\Closure $resolverFn = null,
        ?array &$calls = null
    ): LazyAttributeValues {
        $calls ??= [];

        $resolverFn ??= static function (string $colName) use (&$calls): array {
            $calls[] = $colName;

            return ['text' => 'text:' . $colName, 'html5' => '<span>' . $colName . '</span>'];
        };

        return new LazyAttributeValues(new LazyAttributeResultResolver($resolverFn), 'text', $colNames);
    }

    public function testOffsetGetReturnsTheSubKeyOfTheResolvedResult(): void
    {
        $values = $this->createValues(['title', 'alias']);

        self::assertSame('text:title', $values['title']);
        self::assertSame('text:alias', $values['alias']);
    }

    public function testOffsetGetDoesNotRenderColumnsThatAreNeverAccessed(): void
    {
        $calls  = [];
        $values = $this->createValues(['title', 'alias', 'description'], null, $calls);

        $value = $values['title'];

        self::assertSame('text:title', $value);
        self::assertSame(['title'], $calls, 'only the accessed column must have been rendered');
    }

    public function testOffsetGetCachesAcrossRepeatedAccess(): void
    {
        $calls  = [];
        $values = $this->createValues(['title'], null, $calls);

        $values['title'];
        $values['title'];

        self::assertSame(['title'], $calls);
    }

    public function testOffsetExistsIsTrueForAKnownColumnWithAValue(): void
    {
        $values = $this->createValues(['title']);

        self::assertTrue(isset($values['title']));
    }

    public function testOffsetExistsIsFalseForAColumnNotInTheRenderSetting(): void
    {
        $values = $this->createValues(['title']);

        self::assertFalse(isset($values['unknown']));
    }

    /**
     * Mirrors internalParseAttribute() dropping the format key when "hideEmptyValues" applies -
     * the column is known, but the specific sub-key ("text" here) is absent from its result.
     */
    public function testOffsetExistsIsFalseWhenTheSubKeyWasRemovedByHideEmptyValues(): void
    {
        $values = $this->createValues(
            ['title'],
            static fn (): array => ['raw' => null]
        );

        self::assertFalse(isset($values['title']));
        self::assertNull($values['title']);
    }

    public function testOffsetSetThrows(): void
    {
        $values = $this->createValues(['title']);

        $this->expectException(\LogicException::class);

        $values['title'] = 'new value';
    }

    public function testOffsetUnsetThrows(): void
    {
        $values = $this->createValues(['title']);

        $this->expectException(\LogicException::class);

        unset($values['title']);
    }
}
