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

namespace MetaModels\Test\Schema;

use MetaModels\Information\MetaModelCollectionInterface;
use MetaModels\Schema\SchemaGenerator;
use MetaModels\Schema\SchemaGeneratorInterface;
use MetaModels\Schema\SchemaInformation;
use PHPUnit\Framework\TestCase;

/**
 * This tests the schema generator class.
 *
 * @covers \MetaModels\Schema\SchemaGenerator
 */
class SchemaGeneratorTest extends TestCase
{
    /**
     * Test the instantiation.
     *
     * @return void
     */
    public function testInstantiation(): void
    {
        $instance = new SchemaGenerator([]);

        $this->assertInstanceOf(SchemaGenerator::class, $instance);
    }

    /**
     * Test generating the schema.
     *
     * @return void
     */
    public function testGenerateSchema(): void
    {
        $collection  = $this->getMockForAbstractClass(MetaModelCollectionInterface::class);
        $information = new SchemaInformation();
        $generator   = $this->getMockForAbstractClass(SchemaGeneratorInterface::class);
        $generator->expects($this->once())->method('generate')->with($information, $collection);

        $instance = new SchemaGenerator([$generator]);

        $instance->generate($information, $collection);
    }
}
