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
use PHPUnit\Framework\TestCase;

/**
 * This tests the legacy schema.
 *
 * @covers \MetaModels\Schema\LegacySchemaInformation
 */
class LegacySchemaInformationTest extends TestCase
{
    /**
     * Test the instantiation.
     *
     * @return void
     */
    public function testInstantiation(): void
    {
        $instance = new LegacySchemaInformation();

        $this->assertInstanceOf(LegacySchemaInformation::class, $instance);
        $this->assertSame(LegacySchemaInformation::class, $instance->getName());
        $this->assertSame([], $instance->getAttributes());
    }

    /**
     * Test adding of attributes.
     *
     * @return void
     */
    public function testAddAttributes(): void
    {
        $instance = new LegacySchemaInformation();

        $instance->addAttribute($attribute1 = $this->getMockForAbstractClass(ISimple::class));
        $instance->addAttribute($attribute2 = $this->getMockForAbstractClass(IComplex::class));
        $instance->addAttribute($attribute3 = $this->getMockForAbstractClass(IAttribute::class));

        $this->assertSame([$attribute1, $attribute2, $attribute3], $instance->getAttributes());
    }
}
