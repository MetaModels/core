<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2019 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\Test\Information;

use MetaModels\Information\MetaModelInformationInterface;
use MetaModels\Information\MetaModelCollection;
use PHPUnit\Framework\TestCase;

/**
 * This tests the attribute information.
 *
 * @covers \MetaModels\Information\MetaModelCollection
 */
class MetaModelCollectionTest extends TestCase
{
    /**
     * Test that the various methods work as intended.
     *
     * @return void
     */
    public function testInstantiation(): void
    {
        $information = new MetaModelCollection();
        $this->assertSame([], $information->getNames());
        $this->assertSame([], $information->all());
        $this->assertSame([], iterator_to_array($information));
    }

    /**
     * Test adding of a MetaModel.
     *
     * @return void
     */
    public function testAddMetaModel(): void
    {
        $information = new MetaModelCollection();

        $metamodel = $this->getMockForAbstractClass(MetaModelInformationInterface::class);
        $metamodel->expects($this->once())->method('getName')->willReturn('mm_test');

        $information->add($metamodel);

        $this->assertTrue($information->has('mm_test'));
        $this->assertSame($metamodel, $information->get('mm_test'));
        $this->assertSame([$metamodel], $information->all());
        $this->assertSame([$metamodel], iterator_to_array($information));
    }

    /**
     * Test adding twice.
     *
     * @return void
     */
    public function testAddThrowsForRegisteredName(): void
    {
        $information = new MetaModelCollection();

        $metamodel = $this->getMockForAbstractClass(MetaModelInformationInterface::class);
        $metamodel->expects($this->once())->method('getName')->willReturn('mm_test');
        $information->add($metamodel);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('MetaModel "mm_test" already registered');

        $second = $this->getMockForAbstractClass(MetaModelInformationInterface::class);
        $second->expects($this->once())->method('getName')->willReturn('mm_test');

        $information->add($second);
    }

    /**
     * Test fetching unknown attribute.
     *
     * @return void
     */
    public function testGetThrowsForUnknown(): void
    {
        $information = new MetaModelCollection();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown MetaModel "unknown"');

        $information->get('unknown');
    }
}
