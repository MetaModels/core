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

use MetaModels\Information\ConfigurationTrait;
use PHPUnit\Framework\TestCase;

/**
 * This tests the configuration trait.
 *
 * @covers \MetaModels\Information\ConfigurationTrait
 */
class ConfigurationTraitTest extends TestCase
{
    /**
     * Test that the various methods work as intended.
     *
     * @return void
     */
    public function testFunctionality(): void
    {
        $trait = $this->getMockForTrait(ConfigurationTrait::class);

        $this->assertSame([], $trait->getConfiguration());
        $trait->addConfiguration(['string' => 'string', 'int' => 1, 'null' => null]);
        $this->assertSame(['string' => 'string', 'int' => 1, 'null' => null], $trait->getConfiguration());
        $this->assertTrue($trait->hasConfigurationValue('string'));
        $this->assertTrue($trait->hasConfigurationValue('int'));
        $this->assertTrue($trait->hasConfigurationValue('null'));
        $this->assertSame('string', $trait->getConfigurationValue('string'));
        $this->assertSame(1, $trait->getConfigurationValue('int'));
        $this->assertNull($trait->getConfigurationValue('null'));
    }

    /**
     * Test.
     *
     * @return void
     */
    public function testGetConfigurationValueThrowsForUnknown(): void
    {
        $trait = $this->getMockForTrait(ConfigurationTrait::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Configuration key "unknown" does not exist');

        $trait->getConfigurationValue('unknown');
    }
}
