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

namespace MetaModels\Test\Filter;

use MetaModels\Filter\FilterUrl;
use PHPUnit\Framework\TestCase;

/**
 * This tests the filter url class.
 *
 * @covers \MetaModels\Filter\FilterUrl
 */
class FilterUrlTest extends TestCase
{
    /**
     * Test initialization.
     *
     * @return void
     */
    public function testInitializesValues(): void
    {
        $filterUrl = new FilterUrl(
            ['alias' => 'page-alias'],
            ['get' => 'get-value', 'empty' => ''],
            ['slug' => 'slug-value', 'auto_item' => 'auto_item-value', 'empty' => '']
        );

        $this->assertSame(['alias' => 'page-alias'], $filterUrl->getPage());

        $this->assertTrue($filterUrl->hasGet('get'));
        $this->assertSame('get-value', $filterUrl->getGet('get'));
        $this->assertFalse($filterUrl->hasGet('empty'));
        $this->assertSame(['get' => 'get-value'], $filterUrl->getGetParameters());

        $this->assertTrue($filterUrl->hasSlug('slug'));
        $this->assertSame('slug-value', $filterUrl->getSlug('slug'));
        $this->assertSame('auto_item-value', $filterUrl->getSlug('auto_item'));
        $this->assertFalse($filterUrl->hasSlug('empty'));
        $this->assertSame(['slug' => 'slug-value', 'auto_item' => 'auto_item-value'], $filterUrl->getSlugParameters());
    }
}
