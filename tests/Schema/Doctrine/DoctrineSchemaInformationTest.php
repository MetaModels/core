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

namespace MetaModels\Test\Schema\Doctrine;

use Doctrine\DBAL\Schema\Schema;
use MetaModels\Schema\Doctrine\DoctrineSchemaInformation;
use MetaModels\Schema\Doctrine\SchemaProcessorInterface;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * This tests the doctrine schema.
 *
 */
#[CoversClass(\MetaModels\Schema\Doctrine\DoctrineSchemaInformation::class)]
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
            ->getMock();
        $processorHigh   = $this
            ->getMockBuilder(SchemaProcessorInterface::class)
            ->setMockClassName('TestAddPreProcessorsHigh')
            ->getMock();
        $processorLow    = $this
            ->getMockBuilder(SchemaProcessorInterface::class)
            ->setMockClassName('TestAddPreProcessorsLow')
            ->getMock();

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
            ->getMock();
        $processorHigh   = $this
            ->getMockBuilder(SchemaProcessorInterface::class)
            ->setMockClassName('TestAddPostProcessorsHigh')
            ->getMock();
        $processorLow    = $this
            ->getMockBuilder(SchemaProcessorInterface::class)
            ->setMockClassName('TestAddPostProcessorsLow')
            ->getMock();

        $instance = new DoctrineSchemaInformation($schema);

        $instance->addPostProcessor($processorNormal);
        $instance->addPostProcessor($processorHigh, 100);
        $instance->addPostProcessor($processorLow, -100);

        $this->assertSame([$processorHigh, $processorNormal, $processorLow], $instance->getPostProcessors());
    }
}
