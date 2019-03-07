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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Test\Filter\Setting;

use MetaModels\Filter\FilterUrlBuilder;
use MetaModels\Filter\Setting\ICollection;
use MetaModels\Filter\Setting\Simple;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Test simple filter settings.
 *
 * @covers \MetaModels\Filter\Setting\Simple
 */
class SimpleTest extends TestCase
{
    /**
     * Mock a Simple filter setting.
     *
     * @param array $properties The initialization data.
     *
     * @return Simple|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function mockSimpleFilterSetting($properties = [])
    {
        $filterSetting    = $this->getMockForAbstractClass(ICollection::class);
        $eventDispatcher  = $this->getMockForAbstractClass(EventDispatcherInterface::class);
        $filterUrlBuilder = $this->getMockBuilder(FilterUrlBuilder::class)->disableOriginalConstructor()->getMock();

        $setting = $this
            ->getMockBuilder('MetaModels\Filter\Setting\Simple')
            ->setConstructorArgs([$filterSetting, $properties, $eventDispatcher, $filterUrlBuilder])
            ->getMockForAbstractClass();

        return $setting;
    }

    /**
     * Add a parameter to the url, if it is auto_item, it will get prepended.
     *
     * @param Simple $instance The instance.
     *
     * @param string $url      The url built so far.
     *
     * @param string $name     The parameter name.
     *
     * @param string $value    The parameter value.
     *
     * @return string.
     */
    protected function addUrlParameter($instance, $url, $name, $value)
    {
        $reflection = new \ReflectionMethod($instance, 'addUrlParameter');
        $reflection->setAccessible(true);
        return $reflection->invoke($instance, $url, $name, $value);
    }

    /**
     * Internal convenience method to call the protected generateSql method on the customSql instance.
     *
     * @param Simple $instance  The instance.
     *
     * @param array  $params    The filter url parameter array.
     *
     * @param string $paramName The filter url parameter name.
     *
     * @return string
     */
    protected function buildFilterUrl($instance, $params, $paramName)
    {
        $reflection = new \ReflectionMethod($instance, 'buildFilterUrl');
        $reflection->setAccessible(true);
        return $reflection->invoke($instance, $params, $paramName);
    }

    /**
     * Test adding of filter url parameters.
     *
     * @return void
     */
    public function testAddUrlParameter()
    {
        $setting = $this->mockSimpleFilterSetting();

        $this->assertEquals(
            '/foo/a/A/b/B',
            $this->addUrlParameter($setting, '/a/A/b/B', 'auto_item', 'foo'),
            'auto_item'
        );
        $this->assertEquals(
            '/a/A/b/B/bar/foo',
            $this->addUrlParameter($setting, '/a/A/b/B', 'bar', 'foo'),
            'bar'
        );
        $this->assertEquals(
            '/a/A/b/B/bar/%%25foo',
            $this->addUrlParameter($setting, '/a/A/b/B', 'bar', '%foo'),
            'bar with percent'
        );
        $this->assertEquals(
            '/a/A/b/B/bar/%%24foo',
            $this->addUrlParameter($setting, '/a/A/b/B', 'bar', '$foo'),
            'bar with dollar'
        );
    }

    /**
     * Test building of filter urls.
     *
     * @return void
     */
    public function testBuildFilterUrl()
    {
        $setting = $this->mockSimpleFilterSetting();

        $this->assertEquals(
            '%s/a/A/b/B',
            $this->buildFilterUrl($setting, array('a' => 'A', 'b' => 'B', 'auto_item' => 'AUTO'), 'auto_item'),
            'auto_item'
        );
        $this->assertEquals(
            '/AUTO/a/A%s',
            $this->buildFilterUrl($setting, array('a' => 'A', 'b' => 'B', 'auto_item' => 'AUTO'), 'b'),
            'b'
        );
        $this->assertEquals(
            '/AUTO%s/b/B',
            $this->buildFilterUrl($setting, array('a' => 'A', 'b' => 'B', 'auto_item' => 'AUTO'), 'a'),
            'a'
        );
        $this->assertEquals(
            '/AUTO/a/A/b/B%s',
            $this->buildFilterUrl($setting, array('a' => 'A', 'b' => 'B', 'auto_item' => 'AUTO'), 'c'),
            'c'
        );
        $this->assertEquals(
            '%s/a/A/b/B',
            $this->buildFilterUrl($setting, array('a' => 'A', 'b' => 'B'), 'auto_item'),
            'auto_item 2'
        );
        $this->assertEquals(
            '%s',
            $this->buildFilterUrl($setting, array(), 'auto_item'),
            'auto_item 3'
        );
        $this->assertEquals(
            '%s',
            $this->buildFilterUrl($setting, array('auto_item' => 'AUTO'), 'auto_item'),
            'auto_item 4'
        );
    }
}
