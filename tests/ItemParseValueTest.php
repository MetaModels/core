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

use MetaModels\Attribute\IAttribute;
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
 */
#[CoversClass(Item::class)]
class ItemParseValueTest extends TestCase
{
    /**
     * @param list<string> $colNames
     * @param array<string, int> $renderCalls Filled with colName => number of times it was rendered.
     */
    private function createItem(array $colNames, ?array &$renderCalls = null): Item
    {
        $renderCalls = \array_fill_keys($colNames, 0);

        $metaModel = $this->createMock(IMetaModel::class);
        $metaModel->method('hasVariants')->willReturn(false);
        $metaModel->method('getAttribute')->willReturnCallback(
            function (string $colName) use (&$renderCalls): ?IAttribute {
                if (!\array_key_exists($colName, $renderCalls)) {
                    return null;
                }

                $attribute = $this->createMock(IAttribute::class);
                $attribute->method('getColName')->willReturn($colName);
                $attribute->method('getName')->willReturn('Label:' . $colName);
                $attribute->method('parseValue')->willReturnCallback(
                    function () use ($colName, &$renderCalls): array {
                        $renderCalls[$colName]++;

                        return ['raw' => $colName, 'text' => 'text:' . $colName, 'html5' => 'html5:' . $colName];
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
        $item     = $this->createItem(['title', 'alias'], $renderCalls);
        $settings = $this->createRenderSetting(['title', 'alias'], lazy: true);

        $item->parseValue('html5', $settings);

        self::assertSame(['title' => 0, 'alias' => 0], $renderCalls, 'nothing must render before access');
    }

    public function testLazyAttributeRenderingRendersOnlyTheAccessedAttributeExactlyOnce(): void
    {
        $item     = $this->createItem(['title', 'alias'], $renderCalls);
        $settings = $this->createRenderSetting(['title', 'alias'], lazy: true);

        $result = $item->parseValue('html5', $settings);
        self::assertSame('html5:title', $result['html5']['title']);
        // Access twice - must still render only once (shared cache).
        self::assertSame('text:title', $result['text']['title']);

        self::assertSame(['title' => 1, 'alias' => 0], $renderCalls);
    }

    public function testLazyAttributeRenderingStillPopulatesAttributesCheaplyWithoutRendering(): void
    {
        $item     = $this->createItem(['title', 'alias'], $renderCalls);
        $settings = $this->createRenderSetting(['title', 'alias'], lazy: true);

        $result = $item->parseValue('html5', $settings);

        self::assertSame(['title' => 'Label:title', 'alias' => 'Label:alias'], $result['attributes']);
        self::assertSame(['title' => 0, 'alias' => 0], $renderCalls);
    }

    public function testDefaultOffReturnsPlainArraysAndRendersEverythingUpfront(): void
    {
        $item     = $this->createItem(['title', 'alias'], $renderCalls);
        $settings = $this->createRenderSetting(['title', 'alias'], lazy: false);

        $result = $item->parseValue('html5', $settings);

        self::assertIsArray($result['text']);
        self::assertIsArray($result['html5']);
        self::assertSame(['title' => 'text:title', 'alias' => 'text:alias'], $result['text']);
        self::assertSame(['title' => 'html5:title', 'alias' => 'html5:alias'], $result['html5']);
        self::assertSame(['title' => 1, 'alias' => 1], $renderCalls, 'default renders every attribute upfront');
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

    public function testATextOutputFormatSharesOneLazyViewInsteadOfTwo(): void
    {
        $item     = $this->createItem(['title']);
        $settings = $this->createRenderSetting(['title'], lazy: true);

        $result = $item->parseValue('text', $settings);

        self::assertSame($result['text'], $result['text'], 'sanity: same key compares equal to itself');
        self::assertInstanceOf(LazyAttributeValues::class, $result['text']);
    }
}
