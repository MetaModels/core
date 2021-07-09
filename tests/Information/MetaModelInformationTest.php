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

namespace MetaModels\Test\Information;

use MetaModels\Information\AttributeInformationInterface;
use MetaModels\Information\MetaModelInformation;
use PHPUnit\Framework\TestCase;

/**
 * This tests the attribute information.
 *
 * @covers \MetaModels\Information\MetaModelInformation
 */
class MetaModelInformationTest extends TestCase
{
    /**
     * Test that the various methods work as intended.
     *
     * @return void
     */
    public function testFunctionality(): void
    {
        $information = new MetaModelInformation('mm_test', ['key1' => 'value', 'key2' => 'another']);

        $this->assertSame('mm_test', $information->getName());
        $this->assertSame(['key1' => 'value', 'key2' => 'another'], $information->getConfiguration());
        $this->assertSame([], $information->getAttributeNames());
        $this->assertSame([], $information->getAttributes());
    }

    /**
     * Test adding of an attribute.
     *
     * @return void
     */
    public function testAddAttribute(): void
    {
        $information = new MetaModelInformation('mm_test');

        $attribute = $this->getMockForAbstractClass(AttributeInformationInterface::class);
        $attribute->expects($this->once())->method('getName')->willReturn('test');

        $information->addAttribute($attribute);

        $this->assertTrue($information->hasAttribute('test'));
        $this->assertSame($attribute, $information->getAttribute('test'));
        $this->assertSame([$attribute], $information->getAttributes());
    }

    /**
     * Test adding twice.
     *
     * @return void
     */
    public function testAddThrowsForRegisteredName(): void
    {
        $information = new MetaModelInformation('mm_test');

        $attribute = $this->getMockForAbstractClass(AttributeInformationInterface::class);
        $attribute->expects($this->once())->method('getName')->willReturn('test');
        $information->addAttribute($attribute);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Attribute "test" already registered');

        $second = $this->getMockForAbstractClass(AttributeInformationInterface::class);
        $second->expects($this->once())->method('getName')->willReturn('test');

        $information->addAttribute($second);
    }

    /**
     * Test fetching unknown attribute.
     *
     * @return void
     */
    public function testGetAttributeThrowsForUnknown(): void
    {
        $information = new MetaModelInformation('mm_test');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown attribute "unknown"');

        $information->getAttribute('unknown');
    }

    /**
     * Test retrieving attributes of a given type.
     *
     * @return void
     */
    public function testGetAttributesOfType(): void
    {
        $information = new MetaModelInformation('mm_test');

        $attribute1 = $this->getMockForAbstractClass(AttributeInformationInterface::class);
        $attribute1->expects($this->once())->method('getName')->willReturn('test1');
        $attribute1->expects($this->once())->method('getType')->willReturn('searched');
        $information->addAttribute($attribute1);
        $attribute2 = $this->getMockForAbstractClass(AttributeInformationInterface::class);
        $attribute2->expects($this->once())->method('getName')->willReturn('test2');
        $attribute2->expects($this->once())->method('getType')->willReturn('other');
        $information->addAttribute($attribute2);
        $attribute3 = $this->getMockForAbstractClass(AttributeInformationInterface::class);
        $attribute3->expects($this->once())->method('getName')->willReturn('test3');
        $attribute3->expects($this->once())->method('getType')->willReturn('searched');

        $information->addAttribute($attribute3);

        $this->assertSame(
            [$attribute1, $attribute3],
            iterator_to_array($information->getAttributesOfType('searched'))
        );
    }
}
