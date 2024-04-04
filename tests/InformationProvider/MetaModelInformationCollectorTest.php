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

namespace MetaModels\Test\InformationProvider;

use MetaModels\Information\MetaModelInformation;
use MetaModels\InformationProvider\MetaModelInformationCollector;
use MetaModels\InformationProvider\InformationProviderInterface;
use PHPUnit\Framework\TestCase;

/**
 * This tests the information collector.
 *
 * @covers \MetaModels\InformationProvider\MetaModelInformationCollector
 */
class MetaModelInformationCollectorTest extends TestCase
{
    /**
     * Test the class can be instantiated.
     *
     * @return void
     */
    public function testInstantiation(): void
    {
        $collector = new MetaModelInformationCollector([]);
        $this->assertInstanceOf(MetaModelInformationCollector::class, $collector);
    }

    /**
     * Test that all providers are queried.
     *
     * @return void
     */
    public function testCollectsNamesFromAllProviders(): void
    {
        $provider1 = $this->getMockForAbstractClass(InformationProviderInterface::class);
        $provider2 = $this->getMockForAbstractClass(InformationProviderInterface::class);

        $provider1->expects($this->once())->method('getNames')->willReturn(['name1', 'name2']);
        $provider2->expects($this->once())->method('getNames')->willReturn(['name2', 'name3']);

        $collector = new MetaModelInformationCollector([$provider1, $provider2]);

        $this->assertSame(['name1', 'name2', 'name3'], $collector->getNames());
    }

    /**
     * Test that all providers are queried.
     *
     * @return void
     */
    public function testCollectsInformationFromAllProviders(): void
    {
        $information = new MetaModelInformation('mm_test');

        $provider1 = $this->getMockForAbstractClass(InformationProviderInterface::class);
        $provider2 = $this->getMockForAbstractClass(InformationProviderInterface::class);

        $provider1->expects($this->once())->method('getInformationFor')->with($information);
        $provider2->expects($this->once())->method('getInformationFor')->with($information);

        $collector = new MetaModelInformationCollector([$provider1, $provider2]);

        $collector->getInformationFor($information);
    }

    /**
     * Test obtaining of the collection.
     *
     * @return void
     */
    public function testGetCollection(): void
    {
        $collector = $this
            ->getMockBuilder(MetaModelInformationCollector::class)
            ->setMethods(['getNames', 'getInformationFor'])
            ->disableOriginalConstructor()
            ->getMock();

        $collector->expects($this->once())->method('getNames')->willReturn(['name1', 'name2', 'name3']);
        $collector->expects($this->exactly(3))->method('getInformationFor');
        /** @var MetaModelInformationCollector $collector */
        $collection = $collector->getCollection();

        $this->assertSame(['name1', 'name2', 'name3'], $collection->getNames());
    }
}
