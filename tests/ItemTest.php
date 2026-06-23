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
use MetaModels\Attribute\ITranslated;
use MetaModels\IDirtyTracking;
use MetaModels\IItem;
use MetaModels\IMetaModel;
use MetaModels\Item;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Tests for Item dirty tracking (IDirtyTracking implementation).
 *
 * The key invariant: data loaded via the constructor ($arrData) is never dirty.
 * Only attributes explicitly set via set() are marked dirty.
 * This prevents fallback-language data from being written to the active language on save.
 *
 * @covers \MetaModels\Item
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class ItemTest extends TestCase
{
    private function createItem(array $data = []): Item
    {
        $metaModel  = $this->createMock(IMetaModel::class);
        $dispatcher = $this->createMock(EventDispatcherInterface::class);

        return new Item($metaModel, $data, $dispatcher);
    }

    public function testImplementsIItem(): void
    {
        self::assertInstanceOf(IItem::class, $this->createItem());
    }

    public function testImplementsIDirtyTracking(): void
    {
        self::assertInstanceOf(IDirtyTracking::class, $this->createItem());
    }

    /**
     * Data loaded via the constructor must never be dirty — it comes from the DB
     * and may include fallback-language values that must not be re-saved.
     */
    public function testConstructorDataIsNotDirty(): void
    {
        $item = $this->createItem(['title' => 'Hello', 'alias' => 'hello']);

        self::assertFalse($item->isDirty('title'));
        self::assertFalse($item->isDirty('alias'));
    }

    public function testIsDirtyReturnsFalseForUnknownAttribute(): void
    {
        $item = $this->createItem();

        self::assertFalse($item->isDirty('nonexistent'));
    }

    public function testSetMarksAttributeAsDirty(): void
    {
        $item = $this->createItem(['title' => 'Hello']);
        self::assertFalse($item->isDirty('title'));

        $item->set('title', 'World');

        self::assertTrue($item->isDirty('title'));
    }

    public function testSetOnNewAttributeMarksDirty(): void
    {
        $item = $this->createItem([]);
        $item->set('title', 'New value');

        self::assertTrue($item->isDirty('title'));
    }

    public function testOnlySetAttributeIsDirty(): void
    {
        $item = $this->createItem(['title' => 'Hello', 'alias' => 'hello']);
        $item->set('title', 'World');

        self::assertTrue($item->isDirty('title'));
        self::assertFalse($item->isDirty('alias'));
    }

    public function testSetPreservesValue(): void
    {
        $item = $this->createItem(['title' => 'Hello']);
        $item->set('title', 'World');

        self::assertSame('World', $item->get('title'));
    }

    /**
     * A copied item is a brand-new record: carried-over non-translated attribute values must be marked dirty so that
     * saveItem() actually persists them (it skips non-dirty attributes). Otherwise non-translated attributes would not
     * be copied.
     */
    public function testCopyMarksNonTranslatedAttributesAsDirty(): void
    {
        $title = $this->createMock(IAttribute::class);
        $alias = $this->createMock(IAttribute::class);

        $metaModel = $this->createMock(IMetaModel::class);
        $metaModel->method('getAttribute')->willReturnMap([['title', $title], ['alias', $alias]]);

        $item = new Item(
            $metaModel,
            ['id' => '5', 'tstamp' => '123', 'title' => 'Hello', 'alias' => 'hello'],
            $this->createMock(EventDispatcherInterface::class)
        );
        self::assertFalse($item->isDirty('title'));

        $copy = $item->copy();

        self::assertTrue($copy->isDirty('title'));
        self::assertTrue($copy->isDirty('alias'));
        self::assertSame('Hello', $copy->get('title'));
        self::assertSame('hello', $copy->get('alias'));
    }

    /**
     * Translated attributes are copied per language by the CopyTranslatedData listener, so copy() must not mark them
     * dirty — that would let saveItem() write the active language using possibly fallback data of the source item.
     */
    public function testCopyDoesNotMarkTranslatedAttributesDirty(): void
    {
        $translated = $this->createMock(ITranslated::class);

        $metaModel = $this->createMock(IMetaModel::class);
        $metaModel->method('getAttribute')->willReturnMap([['title', $translated]]);

        $item = new Item(
            $metaModel,
            ['id' => '5', 'title' => 'Hallo'],
            $this->createMock(EventDispatcherInterface::class)
        );

        $copy = $item->copy();

        self::assertFalse($copy->isDirty('title'));
        self::assertSame('Hallo', $copy->get('title'));
    }

    /**
     * The copy must not carry over the identity columns of its source.
     */
    public function testCopyDropsIdentityColumns(): void
    {
        $title = $this->createMock(IAttribute::class);

        $metaModel = $this->createMock(IMetaModel::class);
        $metaModel->method('getAttribute')->willReturnMap([['title', $title]]);

        $item = new Item(
            $metaModel,
            ['id' => '5', 'tstamp' => '123', 'vargroup' => '2', 'title' => 'Hello'],
            $this->createMock(EventDispatcherInterface::class)
        );

        $copy = $item->copy();

        self::assertNull($copy->get('id'));
        self::assertNull($copy->get('tstamp'));
        self::assertNull($copy->get('vargroup'));
        self::assertFalse($copy->isDirty('id'));
        self::assertFalse($copy->isDirty('tstamp'));
    }
}
