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

use MetaModels\Information\MetaModelCollectionInterface;
use MetaModels\Schema\Doctrine\DoctrineSchemaGenerator;
use MetaModels\Schema\Doctrine\DoctrineSchemaInformation;
use MetaModels\Schema\Doctrine\DoctrineSchemaGeneratorInterface;
use MetaModels\Schema\SchemaInformation;
use PHPUnit\Framework\TestCase;

/**
 * This tests the doctrine schema.
 *
 * @covers \MetaModels\Schema\Doctrine\DoctrineSchemaGenerator
 */
class DoctrineSchemaGeneratorTest extends TestCase
{
    /**
     * Test the instantiation.
     *
     * @return void
     */
    public function testInstantiation(): void
    {
        $instance = new DoctrineSchemaGenerator([]);

        $this->assertInstanceOf(DoctrineSchemaGenerator::class, $instance);
    }

    /**
     * Test the generate method.
     *
     * @return void
     */
    public function testGenerateAddsSchemaInformationIfNotFound(): void
    {
        $instance = new DoctrineSchemaGenerator([]);

        $information = new SchemaInformation();

        $collection = $this->getMockForAbstractClass(MetaModelCollectionInterface::class);

        $instance->generate($information, $collection);

        $this->assertTrue($information->has(DoctrineSchemaInformation::class));
    }

    /**
     * Test the generate method.
     *
     * @return void
     */
    public function testGenerate(): void
    {
        $doctrineSchema = new DoctrineSchemaInformation();
        $collection     = $this->getMockForAbstractClass(MetaModelCollectionInterface::class);
        $generator1     = $this->getMockForAbstractClass(DoctrineSchemaGeneratorInterface::class);
        $generator2     = $this->getMockForAbstractClass(DoctrineSchemaGeneratorInterface::class);
        $generator1->expects($this->once())->method('generate')->with($doctrineSchema, $collection);
        $generator2->expects($this->once())->method('generate')->with($doctrineSchema, $collection);


        $instance = new DoctrineSchemaGenerator([$generator1, $generator2]);

        $information = new SchemaInformation();

        $instance->generate($information, $collection);
    }
}
