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

use MetaModels\Schema\Doctrine\DoctrineSchemaInformation;
use MetaModels\Schema\Doctrine\DoctrineSchemaManager;
use MetaModels\Schema\Doctrine\DoctrineSchemaManipulator;
use MetaModels\Schema\Doctrine\SchemaProcessorInterface;
use MetaModels\Schema\SchemaInformation;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * This tests the doctrine schema.
 *
 * @covers \MetaModels\Schema\Doctrine\DoctrineSchemaManager
 */
class DoctrineSchemaManagerTest extends TestCase
{
    /**
     * Test the instantiation.
     *
     * @return void
     */
    public function testInstantiation(): void
    {
        /** @var MockObject|DoctrineSchemaManipulator $manipulator */
        $manipulator = $this->getMockBuilder(DoctrineSchemaManipulator::class)->disableOriginalConstructor()->getMock();
        $instance    = new DoctrineSchemaManager($manipulator);

        $this->assertInstanceOf(DoctrineSchemaManager::class, $instance);
    }

    /**
     * Test the preprocess method.
     *
     * @return void
     */
    public function testPreprocess(): void
    {
        $processor1 = $this->getMockForAbstractClass(SchemaProcessorInterface::class);
        $processor2 = $this->getMockForAbstractClass(SchemaProcessorInterface::class);

        $processor1->expects($this->once())->method('process');
        $processor2->expects($this->once())->method('process');

        /** @var MockObject|DoctrineSchemaInformation $information */
        $information = $this
            ->getMockBuilder(DoctrineSchemaInformation::class)
            ->disableOriginalConstructor()
            ->getMock();
        $information
            ->expects($this->once())
            ->method('getPreProcessors')
            ->willReturn([$processor1, $processor2]);

        $schemaInformation = $this->mockSchemaInformation($information);

        $instance = $this->createSchemaManager();
        $instance->preprocess($schemaInformation);
    }

    /**
     * Test the preprocess method.
     *
     * @return void
     */
    public function testPreprocessSkipsIfNothingToDo(): void
    {
        $schemaInformation = $this->mockSchemaInformation();

        $instance = $this->createSchemaManager();
        $instance->preprocess($schemaInformation);
    }

    /**
     * Test the process method.
     *
     * @return void
     */
    public function testProcess(): void
    {
        $manipulator = $this
            ->getMockBuilder(DoctrineSchemaManipulator::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var MockObject|DoctrineSchemaInformation $information */
        $information = $this
            ->getMockBuilder(DoctrineSchemaInformation::class)
            ->disableOriginalConstructor()
            ->getMock();

        $manipulator->expects($this->once())->method('updateDatabase')->with($information);

        $instance = $this->createSchemaManager($manipulator);

        $schemaInformation = $this->mockSchemaInformation($information);
        $instance->process($schemaInformation);
    }

    /**
     * Test the postprocess method.
     *
     * @return void
     */
    public function testProcessSkipsIfNothingToDo(): void
    {
        $schemaInformation = $this->mockSchemaInformation();

        $instance = $this->createSchemaManager();
        $instance->process($schemaInformation);
    }

    /**
     * Test the postprocess method.
     *
     * @return void
     */
    public function testPostProcess(): void
    {
        $processor1 = $this->getMockForAbstractClass(SchemaProcessorInterface::class);
        $processor2 = $this->getMockForAbstractClass(SchemaProcessorInterface::class);

        $processor1->expects($this->once())->method('process');
        $processor2->expects($this->once())->method('process');

        /** @var MockObject|DoctrineSchemaInformation $information */
        $information = $this
            ->getMockBuilder(DoctrineSchemaInformation::class)
            ->disableOriginalConstructor()
            ->getMock();
        $information
            ->expects($this->once())
            ->method('getPostProcessors')
            ->willReturn([$processor1, $processor2]);

        $schemaInformation = $this->mockSchemaInformation($information);

        $instance = $this->createSchemaManager();
        $instance->postprocess($schemaInformation);
    }

    /**
     * Test the postprocess method.
     *
     * @return void
     */
    public function testPostprocessSkipsIfNothingToDo(): void
    {
        $schemaInformation = $this->mockSchemaInformation();

        $instance = $this->createSchemaManager();
        $instance->postprocess($schemaInformation);
    }

    /**
     * Test the validation
     *
     * @return void
     */
    public function testValidate(): void
    {
        $manipulator = $this
            ->getMockBuilder(DoctrineSchemaManipulator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $manipulator->expects($this->once())->method('getScript')->willReturn(['query1', 'query2']);

        $processor1 = $this->getMockForAbstractClass(SchemaProcessorInterface::class);
        $processor2 = $this->getMockForAbstractClass(SchemaProcessorInterface::class);
        $processor1->expects($this->once())->method('__toString')->willReturn('pre1');
        $processor2->expects($this->once())->method('__toString')->willReturn('pre2');
        $processor3 = $this->getMockForAbstractClass(SchemaProcessorInterface::class);
        $processor4 = $this->getMockForAbstractClass(SchemaProcessorInterface::class);
        $processor3->expects($this->once())->method('__toString')->willReturn('post1');
        $processor4->expects($this->once())->method('__toString')->willReturn('post2');

        /** @var MockObject|DoctrineSchemaInformation $information */
        $information = $this
            ->getMockBuilder(DoctrineSchemaInformation::class)
            ->disableOriginalConstructor()
            ->getMock();
        $information
            ->expects($this->once())
            ->method('getPreProcessors')
            ->willReturn([$processor1, $processor2]);
        $information
            ->expects($this->once())
            ->method('getPostProcessors')
            ->willReturn([$processor3, $processor4]);

        $schemaInformation = $this->mockSchemaInformation($information);

        $instance = $this->createSchemaManager($manipulator);

        $this->assertSame([
            'pre1',
            'pre2',
            'Execute SQL: query1',
            'Execute SQL: query2',
            'post1',
            'post2',
        ], $instance->validate($schemaInformation));
    }

    /**
     * Create a schema manager instance.
     *
     * @param MockObject|DoctrineSchemaManipulator|null $manipulator The manipulator.
     *
     * @return DoctrineSchemaManager
     */
    private function createSchemaManager(
        DoctrineSchemaManipulator $manipulator = null
    ): DoctrineSchemaManager {
        if (null === $manipulator) {
            $manipulator = $this
                ->getMockBuilder(DoctrineSchemaManipulator::class)
                ->disableOriginalConstructor()
                ->getMock();
        }

        return new DoctrineSchemaManager($manipulator);
    }

    /**
     * Mock a schema information for the passed doctrine schema information.
     *
     * @param DoctrineSchemaInformation|null $information The doctrine information if any.
     *
     * @return SchemaInformation
     */
    private function mockSchemaInformation(DoctrineSchemaInformation $information = null): SchemaInformation
    {
        /** @var MockObject|SchemaInformation $schemaInformation */
        $schemaInformation = $this->getMockBuilder(SchemaInformation::class)->getMock();

        if (null === $information) {
            $schemaInformation
                ->expects($this->once())
                ->method('has')
                ->with(DoctrineSchemaInformation::class)
                ->willReturn(false);
            $schemaInformation
                ->expects($this->never())
                ->method('get');

            return $schemaInformation;
        }

        $schemaInformation
            ->expects($this->once())
            ->method('has')
            ->with(DoctrineSchemaInformation::class)
            ->willReturn(true);
        $schemaInformation
            ->expects($this->once())
            ->method('get')
            ->with(DoctrineSchemaInformation::class)
            ->willReturn($information);

        return $schemaInformation;
    }
}
