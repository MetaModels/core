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

use Doctrine\DBAL\Schema\Schema;
use MetaModels\Schema\Doctrine\DoctrineSchemaInformation;
use MetaModels\Schema\Doctrine\SchemaProcessorInterface;
use PHPUnit\Framework\TestCase;

/**
 * This tests the doctrine schema.
 *
 * @covers \MetaModels\Schema\Doctrine\DoctrineSchemaInformation
 */
class DoctrineSchemaInformationTest extends TestCase
{
    /**
     * Test the instantiation.
     *
     * @return void
     */
    public function testInstantiation(): void
    {
        $schema = new Schema();

        $instance = new DoctrineSchemaInformation($schema);

        $this->assertInstanceOf(DoctrineSchemaInformation::class, $instance);
        $this->assertSame(DoctrineSchemaInformation::class, $instance->getName());
        $this->assertSame($schema, $instance->getSchema());
        $this->assertSame([], $instance->getPreProcessors());
        $this->assertSame([], $instance->getPostProcessors());
    }

    /**
     * Test the pre processor handling.
     *
     * @return void
     */
    public function testAddPreProcessors(): void
    {
        $schema = new Schema();

        $processorNormal = $this
            ->getMockBuilder(SchemaProcessorInterface::class)
            ->setMockClassName('TestAddPreProcessorsNormal')
            ->getMockForAbstractClass();
        $processorHigh   = $this
            ->getMockBuilder(SchemaProcessorInterface::class)
            ->setMockClassName('TestAddPreProcessorsHigh')
            ->getMockForAbstractClass();
        $processorLow    = $this
            ->getMockBuilder(SchemaProcessorInterface::class)
            ->setMockClassName('TestAddPreProcessorsLow')
            ->getMockForAbstractClass();

        $instance = new DoctrineSchemaInformation($schema);

        $instance->addPreProcessor($processorNormal);
        $instance->addPreProcessor($processorHigh, 100);
        $instance->addPreProcessor($processorLow, -100);

        $this->assertSame([$processorHigh, $processorNormal, $processorLow], $instance->getPreProcessors());
    }

    /**
     * Test the post processor handling.
     *
     * @return void
     */
    public function testAddPostProcessors(): void
    {
        $schema = new Schema();

        $processorNormal = $this
            ->getMockBuilder(SchemaProcessorInterface::class)
            ->setMockClassName('TestAddPostProcessorsNormal')
            ->getMockForAbstractClass();
        $processorHigh   = $this
            ->getMockBuilder(SchemaProcessorInterface::class)
            ->setMockClassName('TestAddPostProcessorsHigh')
            ->getMockForAbstractClass();
        $processorLow    = $this
            ->getMockBuilder(SchemaProcessorInterface::class)
            ->setMockClassName('TestAddPostProcessorsLow')
            ->getMockForAbstractClass();

        $instance = new DoctrineSchemaInformation($schema);

        $instance->addPostProcessor($processorNormal);
        $instance->addPostProcessor($processorHigh, 100);
        $instance->addPostProcessor($processorLow, -100);

        $this->assertSame([$processorHigh, $processorNormal, $processorLow], $instance->getPostProcessors());
    }
}
