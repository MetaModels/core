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

use MetaModels\Information\AttributeInformation;
use PHPUnit\Framework\TestCase;

/**
 * This tests the attribute information.
 *
 * @covers \MetaModels\Information\AttributeInformation
 */
class AttributeInformationTest extends TestCase
{
    /**
     * Test that the various methods work as intended.
     *
     * @return void
     */
    public function testFunctionality(): void
    {
        $information = new AttributeInformation('attribute', 'typename', ['key1' => 'value', 'key2' => 'another']);

        $this->assertSame('attribute', $information->getName());
        $this->assertSame('typename', $information->getType());
        $this->assertSame(['key1' => 'value', 'key2' => 'another'], $information->getConfiguration());
    }
}
