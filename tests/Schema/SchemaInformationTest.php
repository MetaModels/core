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

use MetaModels\Schema\SchemaInformation;
use MetaModels\Schema\SchemaInformationInterface;
use PHPUnit\Framework\TestCase;

/**
 * This tests the schema information.
 *
 * @covers \MetaModels\Schema\SchemaInformation
 */
class SchemaInformationTest extends TestCase
{
    /**
     * Test the instantiation.
     *
     * @return void
     */
    public function testInstantiation(): void
    {
        $instance = new SchemaInformation();

        $this->assertInstanceOf(SchemaInformation::class, $instance);
    }

    /**
     * Test adding.
     *
     * @return void
     */
    public function testAdd(): void
    {
        $instance = new SchemaInformation();

        $mock = $this->getMockForAbstractClass(SchemaInformationInterface::class);
        $mock->method('getName')->willReturn('test');

        $instance->add($mock);

        $this->assertTrue($instance->has('test'));
        $this->assertSame($mock, $instance->get('test'));
    }

    /**
     * Test adding.
     *
     * @return void
     */
    public function testAddThrowsForAlreadyRegistered(): void
    {
        $instance = new SchemaInformation();

        $mock = $this->getMockForAbstractClass(SchemaInformationInterface::class);
        $mock->method('getName')->willReturn('test');

        $instance->add($mock);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Information with name "test" already registered.');

        $instance->add($mock);
    }

    /**
     * Test adding.
     *
     * @return void
     */
    public function testGetThrowsForUnregistered(): void
    {
        $instance = new SchemaInformation();

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Information with name "test" not registered.');

        $instance->get('test');
    }

    /**
     * Test the has method.
     *
     * @return void
     */
    public function testHasForUnknown(): void
    {
        $instance = new SchemaInformation();

        $this->assertFalse($instance->has('unknown'));
    }

    /**
     * Test obtaining the list of registered names.
     *
     * @return void
     */
    public function testGetRegisteredNames(): void
    {
        $instance = new SchemaInformation();

        $mock = $this->getMockForAbstractClass(SchemaInformationInterface::class);
        $mock->method('getName')->willReturn('test');

        $instance->add($mock);

        $this->assertSame(['test'], $instance->getRegisteredNames());
    }
}
