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

use MetaModels\Attribute\IAttribute;
use MetaModels\Attribute\IComplex;
use MetaModels\Attribute\ISimple;
use MetaModels\Schema\LegacySchemaInformation;
use MetaModels\Schema\LegacySchemaManager;
use MetaModels\Schema\SchemaInformation;
use PHPUnit\Framework\TestCase;

/**
 * This tests the doctrine schema.
 *
 * @covers \MetaModels\Schema\LegacySchemaManager
 */
class LegacySchemaManagerTest extends TestCase
{
    /**
     * Test the instantiation.
     *
     * @return void
     */
    public function testInstantiation(): void
    {
        $instance = new LegacySchemaManager();

        $this->assertInstanceOf(LegacySchemaManager::class, $instance);
    }

    /**
     * Test the generate method.
     *
     * @return void
     */
    public function testIgnoresIfNotFound(): void
    {
        $instance    = new LegacySchemaManager();
        $information = $this->getMockBuilder(SchemaInformation::class)->getMock();
        $information->expects($this->once())->method('has')->with(LegacySchemaInformation::class)->willReturn(false);
        $information->expects($this->never())->method('get');

        $instance->process($information);
    }

    /**
     * Test the process method.
     *
     * @return void
     */
    public function testProcess(): void
    {
        $information = new SchemaInformation();
        $legacy      = new LegacySchemaInformation();
        $information->add($legacy);

        $legacy->addAttribute($attribute1 = $this->getMockForAbstractClass(ISimple::class));
        $legacy->addAttribute($attribute2 = $this->getMockForAbstractClass(IComplex::class));
        $legacy->addAttribute($attribute3 = $this->getMockForAbstractClass(IAttribute::class));

        $attribute1->expects($this->once())->method('initializeAUX');
        $attribute2->expects($this->once())->method('initializeAUX');
        $attribute3->expects($this->once())->method('initializeAUX')->willThrowException(new \RuntimeException());

        $instance = new LegacySchemaManager();

        $instance->process($information);
    }

    /**
     * Test the validate method.
     *
     * @return void
     */
    public function testValidate(): void
    {
        $information = new SchemaInformation();
        $legacy      = new LegacySchemaInformation();
        $information->add($legacy);

        $legacy->addAttribute($attribute1 = $this->getMockForAbstractClass(ISimple::class));
        $legacy->addAttribute($attribute2 = $this->getMockForAbstractClass(IComplex::class));
        $legacy->addAttribute($attribute3 = $this->getMockForAbstractClass(IAttribute::class));

        $attribute1->expects($this->once())->method('getColName')->willReturn('attribute1');
        $attribute1->expects($this->once())->method('get')->with('type')->willReturn('type1');
        $attribute2->expects($this->once())->method('getColName')->willReturn('attribute2');
        $attribute2->expects($this->once())->method('get')->with('type')->willReturn('type2');
        $attribute3->expects($this->once())->method('getColName')->willReturn('attribute3');
        $attribute3->expects($this->once())->method('get')->with('type')->willReturn('type3');

        $instance = new LegacySchemaManager();

        $this->assertSame([
            '(Re-)Initialize attribute "attribute1" (type: "type1") via legacy method.',
            '(Re-)Initialize attribute "attribute2" (type: "type2") via legacy method.',
            '(Re-)Initialize attribute "attribute3" (type: "type3") via legacy method.',
        ], $instance->validate($information));
    }
}
