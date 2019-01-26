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

namespace MetaModels\Test\Schema;

use MetaModels\Schema\SchemaInformation;
use MetaModels\Schema\SchemaManager;
use MetaModels\Schema\SchemaManagerInterface;
use PHPUnit\Framework\TestCase;

/**
 * This tests the schema manager class.
 *
 * @covers \MetaModels\Schema\SchemaManager
 */
class SchemaManagerTest extends TestCase
{
    /**
     * Test the instantiation.
     *
     * @return void
     */
    public function testInstantiation(): void
    {
        $instance = new SchemaManager([]);

        $this->assertInstanceOf(SchemaManager::class, $instance);
    }

    /**
     * Test processing.
     *
     * @return void
     */
    public function testPreprocess(): void
    {
        $manager1    = $this->getMockForAbstractClass(SchemaManagerInterface::class);
        $manager2    = $this->getMockForAbstractClass(SchemaManagerInterface::class);
        $information = new SchemaInformation();
        $manager1->expects($this->once())->method('preprocess')->with($information);
        $manager2->expects($this->once())->method('preprocess')->with($information);

        $instance = new SchemaManager([$manager1, $manager2]);

        $instance->preprocess($information);
    }

    /**
     * Test processing.
     *
     * @return void
     */
    public function testProcess(): void
    {
        $manager1    = $this->getMockForAbstractClass(SchemaManagerInterface::class);
        $manager2    = $this->getMockForAbstractClass(SchemaManagerInterface::class);
        $information = new SchemaInformation();
        $manager1->expects($this->once())->method('process')->with($information);
        $manager2->expects($this->once())->method('process')->with($information);

        $instance = new SchemaManager([$manager1, $manager2]);

        $instance->process($information);
    }

    /**
     * Test processing.
     *
     * @return void
     */
    public function testPostcess(): void
    {
        $manager1    = $this->getMockForAbstractClass(SchemaManagerInterface::class);
        $manager2    = $this->getMockForAbstractClass(SchemaManagerInterface::class);
        $information = new SchemaInformation();
        $manager1->expects($this->once())->method('postprocess')->with($information);
        $manager2->expects($this->once())->method('postprocess')->with($information);

        $instance = new SchemaManager([$manager1, $manager2]);

        $instance->postprocess($information);
    }
}
