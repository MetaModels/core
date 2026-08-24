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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * A shared (non-variant) attribute changed on a variant base does get broadcast to its
 * siblings' own rows by updateVariants() - but the siblings' own derived attributes (combined
 * values, aliases, ...) never got a chance to recompute from it until modelSaved() ran for them
 * too. See https://github.com/MetaModels/core/issues/657.
 */
#[CoversClass(MetaModel::class)]
class RefreshSiblingDerivedAttributesTest extends TestCase
{
    public function testNoSharedAttributeChangeForPlainIItem(): void
    {
        $reflection = new \ReflectionMethod(MetaModel::class, 'hasSharedAttributeChange');

        $item = $this->createStub(IItem::class);

        self::assertFalse($reflection->invoke($this->createMetaModel(), $item));
    }

    public function testNoSharedAttributeChangeWhenOnlyVariantAttributesAreDirty(): void
    {
        $metaModel = $this->createMetaModel();

        /** @var IAttribute&MockObject $variantAttribute */
        $variantAttribute = $this->createMock(IAttribute::class);
        $variantAttribute->method('getColName')->willReturn('komb');
        $variantAttribute->method('get')->with('isvariant')->willReturn(true);
        $metaModel->addAttribute($variantAttribute);

        $item = new Item($metaModel, [], new EventDispatcher());
        $item->set('komb', 'text');

        $reflection = new \ReflectionMethod(MetaModel::class, 'hasSharedAttributeChange');
        self::assertFalse($reflection->invoke($metaModel, $item));
    }

    public function testSharedAttributeChangeWhenANonVariantAttributeIsDirty(): void
    {
        $metaModel = $this->createMetaModel();

        /** @var IAttribute&MockObject $sharedAttribute */
        $sharedAttribute = $this->createMock(IAttribute::class);
        $sharedAttribute->method('getColName')->willReturn('parent_name');
        $sharedAttribute->method('get')->with('isvariant')->willReturn(false);
        $metaModel->addAttribute($sharedAttribute);

        $item = new Item($metaModel, [], new EventDispatcher());
        $item->set('parent_name', 'Parent A');

        $reflection = new \ReflectionMethod(MetaModel::class, 'hasSharedAttributeChange');
        self::assertTrue($reflection->invoke($metaModel, $item));
    }

    public function testRefreshSiblingDerivedAttributesDoesNothingWhenOnlySelfIsGiven(): void
    {
        /** @var Connection&MockObject $connection */
        $connection = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock();
        $connection->expects(self::never())->method('createQueryBuilder');

        $metaModel = new MetaModel([], $this->createStub(EventDispatcherInterface::class), $connection);

        $item = new Item($metaModel, ['id' => 5], new EventDispatcher());

        $reflection = new \ReflectionMethod(MetaModel::class, 'refreshSiblingDerivedAttributes');
        $reflection->invoke($metaModel, $item, [5]);

        // No exception, no query - the only id given is the item's own, so it must be skipped.
        $this->addToAssertionCount(1);
    }

    private function createMetaModel(): MetaModel
    {
        /** @var Connection&MockObject $connection */
        $connection = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock();
        $connection->expects(self::never())->method('createQueryBuilder');

        return new MetaModel(
            [],
            $this->createStub(EventDispatcherInterface::class),
            $connection
        );
    }
}
