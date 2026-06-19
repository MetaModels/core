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

namespace MetaModels\Test\MetaModel;

use Doctrine\DBAL\Connection;
use MetaModels\Attribute\IAttribute;
use MetaModels\IItem;
use MetaModels\Item;
use MetaModels\MetaModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * @covers \MetaModels\MetaModel::shouldSkipAttributeUpdate
 */
class ShouldSkipAttributeUpdateTest extends TestCase
{
    /**
     * Attribute is not set on the item → skip.
     */
    public function testSkipsWhenAttributeNotSet(): void
    {
        $reflection = new \ReflectionMethod(MetaModel::class, 'shouldSkipAttributeUpdate');

        /** @var IItem&MockObject $item */
        $item = $this->createMock(IItem::class);
        $item->method('isAttributeSet')->willReturn(false);

        /** @var IAttribute&MockObject $attribute */
        $attribute = $this->createMock(IAttribute::class);
        $attribute->method('getColName')->willReturn('title');

        self::assertTrue($reflection->invoke($this->createMetaModel(), $item, $attribute, false));
    }

    /**
     * IDirtyTracking item with attribute not dirty → skip.
     */
    public function testSkipsWhenAttributeNotDirty(): void
    {
        $metaModel = $this->createMetaModel();

        // Constructor data is never dirty — isAttributeSet=true, isDirty=false.
        $item = new Item($metaModel, ['title' => 'text'], new EventDispatcher());

        /** @var IAttribute&MockObject $attribute */
        $attribute = $this->createMock(IAttribute::class);
        $attribute->method('getColName')->willReturn('title');

        $reflection = new \ReflectionMethod(MetaModel::class, 'shouldSkipAttributeUpdate');
        self::assertTrue($reflection->invoke($metaModel, $item, $attribute, false));
    }

    /**
     * IDirtyTracking item with dirty attribute, non-variant item → don't skip.
     */
    public function testDoesNotSkipWhenDirtyAndNonVariant(): void
    {
        $metaModel = $this->createMetaModel();

        // set() marks the attribute dirty; MetaModel without variants → isVariant()=false.
        $item = new Item($metaModel, [], new EventDispatcher());
        $item->set('title', 'text');

        /** @var IAttribute&MockObject $attribute */
        $attribute = $this->createMock(IAttribute::class);
        $attribute->method('getColName')->willReturn('title');
        $attribute->method('get')->with('isvariant')->willReturn(false);

        $reflection = new \ReflectionMethod(MetaModel::class, 'shouldSkipAttributeUpdate');
        self::assertFalse($reflection->invoke($metaModel, $item, $attribute, false));
    }

    /**
     * Non-IDirtyTracking item (plain IItem), attribute set → dirty tracking is not applied.
     */
    public function testDoesNotSkipForPlainIItemWithAttributeSet(): void
    {
        $reflection = new \ReflectionMethod(MetaModel::class, 'shouldSkipAttributeUpdate');

        /** @var IItem&MockObject $item */
        $item = $this->createMock(IItem::class);
        $item->method('isAttributeSet')->willReturn(true);
        $item->method('isVariant')->willReturn(false);

        /** @var IAttribute&MockObject $attribute */
        $attribute = $this->createMock(IAttribute::class);
        $attribute->method('getColName')->willReturn('title');
        $attribute->method('get')->with('isvariant')->willReturn(false);

        self::assertFalse($reflection->invoke($this->createMetaModel(), $item, $attribute, false));
    }

    /**
     * Variant item, non-variant attribute, baseAttributes=false → skip base attribute.
     */
    public function testSkipsBaseAttributeForVariantItem(): void
    {
        $reflection = new \ReflectionMethod(MetaModel::class, 'shouldSkipAttributeUpdate');

        /** @var IItem&MockObject $item */
        $item = $this->createMock(IItem::class);
        $item->method('isAttributeSet')->willReturn(true);
        $item->method('isVariant')->willReturn(true);

        /** @var IAttribute&MockObject $attribute */
        $attribute = $this->createMock(IAttribute::class);
        $attribute->method('getColName')->willReturn('title');
        $attribute->method('get')->with('isvariant')->willReturn(false);

        self::assertTrue($reflection->invoke($this->createMetaModel(), $item, $attribute, false));
    }

    /**
     * Variant item, non-variant attribute, baseAttributes=true → don't skip.
     */
    public function testDoesNotSkipBaseAttributeWhenBaseAttributesEnabled(): void
    {
        $reflection = new \ReflectionMethod(MetaModel::class, 'shouldSkipAttributeUpdate');

        /** @var IItem&MockObject $item */
        $item = $this->createMock(IItem::class);
        $item->method('isAttributeSet')->willReturn(true);
        $item->method('isVariant')->willReturn(true);

        /** @var IAttribute&MockObject $attribute */
        $attribute = $this->createMock(IAttribute::class);
        $attribute->method('getColName')->willReturn('title');
        $attribute->method('get')->with('isvariant')->willReturn(false);

        self::assertFalse($reflection->invoke($this->createMetaModel(), $item, $attribute, true));
    }

    private function createMetaModel(): MetaModel
    {
        /** @var Connection&MockObject $connection */
        $connection = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock();
        $connection->expects(self::never())->method('createQueryBuilder');

        return new MetaModel(
            [],
            $this->getMockForAbstractClass(EventDispatcherInterface::class),
            $connection
        );
    }
}
