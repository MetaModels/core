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

declare(strict_types = 1);

namespace MetaModels\Test\Schema\Doctrine;

use MetaModels\Attribute\IAttribute;
use MetaModels\Attribute\IComplex;
use MetaModels\Attribute\IInternal;
use MetaModels\Attribute\ISchemaManagedAttribute;
use MetaModels\Attribute\ISimple;
use MetaModels\IFactory;
use MetaModels\IMetaModel;
use MetaModels\Information\AttributeInformation;
use MetaModels\Information\MetaModelCollectionInterface;
use MetaModels\Information\MetaModelInformation;
use MetaModels\Schema\LegacySchemaGenerator;
use MetaModels\Schema\LegacySchemaInformation;
use MetaModels\Schema\SchemaInformation;
use PHPUnit\Framework\TestCase;

/**
 * This tests the doctrine schema.
 *
 * @covers \MetaModels\Schema\LegacySchemaGenerator
 */
class LegacySchemaGeneratorTest extends TestCase
{
    /**
     * Test the instantiation.
     *
     * @return void
     */
    public function testInstantiation(): void
    {
        $instance = new LegacySchemaGenerator($this->getMockForAbstractClass(IFactory::class));

        $this->assertInstanceOf(LegacySchemaGenerator::class, $instance);
    }

    /**
     * Test the generate method.
     *
     * @return void
     */
    public function testGenerateAddsSchemaInformationIfNotFound(): void
    {
        $instance    = new LegacySchemaGenerator($this->getMockForAbstractClass(IFactory::class));
        $information = new SchemaInformation();
        $collection  = $this->getMockForAbstractClass(MetaModelCollectionInterface::class);

        $collection->expects($this->once())->method('getIterator')->willReturn(new \ArrayIterator([]));

        $instance->generate($information, $collection);

        $this->assertTrue($information->has(LegacySchemaInformation::class));
    }

    /**
     * Test the generate method.
     *
     * @return void
     */
    public function testGenerate(): void
    {
        $information = new SchemaInformation();
        $collection  = $this->getMockForAbstractClass(MetaModelCollectionInterface::class);

        $attribute1 = $this->getMockForAbstractClass(ISimple::class);
        $attribute2 = $this->getMockForAbstractClass(IComplex::class);
        $attribute3 = $this->getMockForAbstractClass(ISchemaManagedAttribute::class);
        $attribute4 = $this->getMockForAbstractClass(IInternal::class);
        $metaModel  = $this->mockMetaModel([$attribute1, $attribute2, $attribute3, $attribute4]);

        $factory = $this->getMockForAbstractClass(IFactory::class);
        $factory->expects($this->once())->method('getMetaModel')->with('mm_test')->willReturn($metaModel);

        $collection->expects($this->once())->method('getIterator')->willReturn(new \ArrayIterator([
            $metaModelInformation = new MetaModelInformation('mm_test')
        ]));
        $metaModelInformation->addAttribute(new AttributeInformation('test', 'test_type'));

        $instance = new LegacySchemaGenerator($factory);

        $instance->generate($information, $collection);

        $this->assertTrue($information->has(LegacySchemaInformation::class));
        $this->assertSame(
            [$attribute1, $attribute2],
            $information->get(LegacySchemaInformation::class)->getAttributes()
        );
    }

    /**
     * Mock a MetaModel with the passed attributes.
     *
     * @param IAttribute[] $attributes The attributes.
     *
     * @return IMetaModel
     */
    private function mockMetaModel(array $attributes)
    {
        $metaModel = $this->getMockForAbstractClass(IMetaModel::class);

        $metaModel->expects($this->once())->method('getAttributes')->willReturn($attributes);

        return $metaModel;
    }
}
