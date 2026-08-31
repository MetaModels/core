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

namespace MetaModels\Test;

use MetaModels\Attribute\Base as BaseAttribute;
use MetaModels\Item;
use MetaModels\Item\LazyAttributeValues;
use MetaModels\IMetaModel;
use MetaModels\Render\Setting\ICollection;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Tests for the "lazyAttributeRendering" opt-in switch in Item::parseValue() - see
 * ".claude/lazy-attribut-rendering.md" for the background.
 *
 * Attributes are mocked as BaseAttribute (not just IAttribute) throughout: the lazy path
 * dispatches to IAttribute::parseValueForFormat() only for attributes that extend Base (see
 * Item::internalParseAttributeFormat()), and a plain IAttribute mock would silently take the
 * fallback branch instead of exercising the format-granular behaviour under test.
 */
#[CoversClass(Item::class)]
class ItemParseValueTest extends TestCase
{
    /**
     * @param list<string> $colNames
     * @param list<string> $calls Filled with one "colName:format" entry per parseValueForFormat()
     *                             call, and one "colName:combined:format" entry per parseValue()
     *                             call (the eager/fallback path), in call order.
     */
    private function createItem(array $colNames, ?array &$calls = null): Item
    {
        $calls = [];

        $metaModel = $this->createMock(IMetaModel::class);
        $metaModel->method('hasVariants')->willReturn(false);
        $metaModel->method('getAttribute')->willReturnCallback(
            function (string $colName) use ($colNames, &$calls): ?BaseAttribute {
                if (!\in_array($colName, $colNames, true)) {
                    return null;
                }

                $attribute = $this->createMock(BaseAttribute::class);
                $attribute->method('getColName')->willReturn($colName);
                $attribute->method('getName')->willReturn('Label:' . $colName);
                $attribute->method('parseValueForFormat')->willReturnCallback(
                    function (array $rowData, string $format) use ($colName, &$calls): array {
                        self::assertSame([], $rowData, 'internalParseAttributeFormat() must pass the row data through');
                        $calls[] = $colName . ':' . $format;

                        return ['raw' => $colName, $format => $format . ':' . $colName];
                    }
                );
                $attribute->method('parseValue')->willReturnCallback(
                    function (array $rowData, string $format) use ($colName, &$calls): array {
                        self::assertSame([], $rowData, 'Item::internalParseAttribute() must pass the row data through');
                        $calls[] = $colName . ':combined:' . $format;

                        return ['raw' => $colName, 'text' => 'text:' . $colName, $format => $format . ':' . $colName];
                    }
                );

                return $attribute;
            }
        );

        $dispatcher = $this->createMock(EventDispatcherInterface::class);
        $dispatcher->method('dispatch')->willReturnArgument(0);

        return new Item($metaModel, [], $dispatcher);
    }

    /**
     * @param list<string> $colNames
     */
    private function createRenderSetting(array $colNames, bool $lazy): ICollection
    {
        $settings = $this->createMock(ICollection::class);
        $settings->method('getSettingNames')->willReturn($colNames);
        $settings->method('buildJumpToUrlFor')->willReturn(['url' => '', 'deep' => false, 'label' => '']);
        $settings->method('getSetting')->willReturn(null);
        $settings->method('get')->willReturnCallback(
            static fn (string $name): mixed => 'lazyAttributeRendering' === $name ? $lazy : null
        );

        return $settings;
    }

    public function testLazyAttributeRenderingReturnsLazyAttributeValuesForTextAndFormat(): void
    {
        $item     = $this->createItem(['title', 'alias']);
        $settings = $this->createRenderSetting(['title', 'alias'], lazy: true);

        $result = $item->parseValue('html5', $settings);

        self::assertInstanceOf(LazyAttributeValues::class, $result['text']);
        self::assertInstanceOf(LazyAttributeValues::class, $result['html5']);
    }

    public function testLazyAttributeRenderingDoesNotRenderAttributesThatAreNeverAccessed(): void
    {
        $item     = $this->createItem(['title', 'alias'], $calls);
        $settings = $this->createRenderSetting(['title', 'alias'], lazy: true);

        $item->parseValue('html5', $settings);

        self::assertSame([], $calls, 'nothing must render before access');
    }

    public function testAccessingOnlyOneFormatDoesNotRenderTheOther(): void
    {
        $item     = $this->createItem(['title'], $calls);
        $settings = $this->createRenderSetting(['title'], lazy: true);

        $result = $item->parseValue('html5', $settings);
        self::assertSame('html5:title', $result['html5']['title']);

        self::assertSame(
            ['title:html5'],
            $calls,
            'accessing only html5 must not also render text - the whole point of splitting the resolver'
        );
    }

    public function testAccessingBothFormatsRendersEachIndependently(): void
    {
        $item     = $this->createItem(['title'], $calls);
        $settings = $this->createRenderSetting(['title'], lazy: true);

        $result = $item->parseValue('html5', $settings);
        self::assertSame('html5:title', $result['html5']['title']);
        self::assertSame('text:title', $result['text']['title']);

        self::assertSame(['title:html5', 'title:text'], $calls, 'each format renders through its own resolver');
    }

    public function testRepeatedAccessOfTheSameFormatIsCachedPerView(): void
    {
        $item     = $this->createItem(['title'], $calls);
        $settings = $this->createRenderSetting(['title'], lazy: true);

        $result = $item->parseValue('html5', $settings);
        self::assertSame('html5:title', $result['html5']['title']);
        self::assertSame('html5:title', $result['html5']['title']);

        self::assertSame(['title:html5'], $calls, 'the second access must hit the resolver cache');
    }

    public function testLazyAttributeRenderingStillPopulatesAttributesCheaplyWithoutRendering(): void
    {
        $item     = $this->createItem(['title', 'alias'], $calls);
        $settings = $this->createRenderSetting(['title', 'alias'], lazy: true);

        $result = $item->parseValue('html5', $settings);

        self::assertSame(['title' => 'Label:title', 'alias' => 'Label:alias'], $result['attributes']);
        self::assertSame([], $calls);
    }

    public function testDefaultOffReturnsPlainArraysAndRendersEverythingUpfront(): void
    {
        $item     = $this->createItem(['title', 'alias'], $calls);
        $settings = $this->createRenderSetting(['title', 'alias'], lazy: false);

        $result = $item->parseValue('html5', $settings);

        self::assertIsArray($result['text']);
        self::assertIsArray($result['html5']);
        self::assertSame(['title' => 'text:title', 'alias' => 'text:alias'], $result['text']);
        self::assertSame(['title' => 'html5:title', 'alias' => 'html5:alias'], $result['html5']);
        self::assertSame(
            ['title:combined:html5', 'alias:combined:html5'],
            $calls,
            'default (eager) renders every attribute upfront through the combined parseValue()'
        );
    }

    public function testWithoutRenderSettingsStaysEagerRegardlessOfTheAttributeCount(): void
    {
        // Item::parseValue() with $objSettings === null iterates ALL MetaModel attributes, not just
        // a render setting's - there is no collection to read "lazyAttributeRendering" from at all.
        $item = $this->createItem(['title']);
        $item->getMetaModel()->method('getAttributes')->willReturn([]);

        $result = $item->parseValue('html5');

        self::assertIsArray($result['text']);
        self::assertIsArray($result['html5']);
    }

    public function testRequestingTheTextFormatRendersOnlyOnce(): void
    {
        // $strOutputFormat === 'text' collapses "text" and "$strOutputFormat" onto the same array
        // key (buildLazyAttributeValues() special-cases this rather than building two resolvers
        // for the same format), so there is only one view to access here in the first place.
        $item     = $this->createItem(['title'], $calls);
        $settings = $this->createRenderSetting(['title'], lazy: true);

        $result = $item->parseValue('text', $settings);

        self::assertInstanceOf(LazyAttributeValues::class, $result['text']);
        self::assertSame('text:title', $result['text']['title']);
        self::assertSame(['title:text'], $calls);
    }
}
