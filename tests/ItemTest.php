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

use MetaModels\IDirtyTracking;
use MetaModels\IItem;
use MetaModels\IMetaModel;
use MetaModels\Item;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Tests for Item dirty tracking (IDirtyTracking implementation).
 *
 * The key invariant: data loaded via the constructor ($arrData) is never dirty.
 * Only attributes explicitly set via set() are marked dirty.
 * This prevents fallback-language data from being written to the active language on save.
 *
 */
#[CoversClass(\MetaModels\Item::class)]
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
}
