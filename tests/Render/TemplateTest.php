<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2020 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2012-2020 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\Test\Render;

use Contao\CoreBundle\Framework\Adapter;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use MetaModels\Render\Template;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;

/**
 * This tests the empty value check helper.
 *
 * @covers \MetaModels\Render\Template
 */
final class TemplateTest extends TestCase
{
    /**
     * Test instantiation of a template instance.
     *
     * @return void
     */
    public function testInstantiation(): void
    {
        $templateLoader    = $this->createMock(Adapter::class);
        $scopeDeterminator = $this->createMock(RequestScopeDeterminator::class);
        $template          = new Template('example', $templateLoader, $scopeDeterminator);

        $this->assertInstanceOf(Template::class, $template);
    }

    /**
     * Test cache of multiple calls.
     *
     * @return void
     */
    public function testCacheFoundTemplatePaths(): void
    {
        $templateLoader = $this->createMock(Adapter::class);
        $templateLoader
            ->expects($this->once())
            ->method('__call')
            ->with('getPath', ['example1', 'html5', 'templates'])
            ->willReturn('templates/example1.html5');

        $scopeDeterminator = $this->createMock(RequestScopeDeterminator::class);
        $template          = new Template('example1', $templateLoader, $scopeDeterminator);
        $method            = $this->reflectGetTemplateMethod();

        $this->assertEquals('templates/example1.html5', $method->invoke($template, 'example1'));
        $this->assertEquals('templates/example1.html5', $method->invoke($template, 'example1'));
    }

    /**
     * Test different formats creates different cache entries.
     *
     * @return void
     */
    public function testCacheForEachFormat(): void
    {
        $templateLoader = $this->createMock(Adapter::class);
        $templateLoader
            ->expects($this->exactly(2))
            ->method('__call')
            ->willReturnMap([
                ['getPath', ['example2', 'html5', 'templates'], 'templates/example2.html5'],
                ['getPath', ['example2', 'text', 'templates'], 'templates/example2.text'],
            ]);

        $scopeDeterminator = $this->createMock(RequestScopeDeterminator::class);
        $template          = new Template('example2', $templateLoader, $scopeDeterminator);
        $method            = $this->reflectGetTemplateMethod();

        $this->assertEquals('templates/example2.html5', $method->invoke($template, 'example2', 'html5'));
        $this->assertEquals('templates/example2.html5', $method->invoke($template, 'example2', 'html5'));
        $this->assertEquals('templates/example2.text', $method->invoke($template, 'example2', 'text'));
        $this->assertEquals('templates/example2.text', $method->invoke($template, 'example2', 'text'));
    }

    /**
     * Test cache is shared between multiple instances.
     *
     * @return void
     */
    public function testCacheOverMultipleInstances(): void
    {
        $templateLoader = $this->createMock(Adapter::class);
        $templateLoader
            ->expects($this->exactly(2))
            ->method('__call')
            ->willReturnMap([
                ['getPath', ['example3', 'html5', 'templates'], 'templates/example3.html5'],
                ['getPath', ['example3', 'text', 'templates'], 'templates/example3.text'],
            ]);

        $scopeDeterminator = $this->createMock(RequestScopeDeterminator::class);
        $template          = new Template('example3', $templateLoader, $scopeDeterminator);
        $method            = $this->reflectGetTemplateMethod();

        $this->assertEquals('templates/example3.html5', $method->invoke($template, 'example3', 'html5'));
        $this->assertEquals('templates/example3.text', $method->invoke($template, 'example3', 'text'));

        $template2 = new Template('example3', $templateLoader, $scopeDeterminator);
        $this->assertEquals('templates/example3.html5', $method->invoke($template2, 'example3', 'html5'));
        $this->assertEquals('templates/example3.text', $method->invoke($template2, 'example3', 'text'));
    }

    /**
     * Test different caches for custom paths.
     *
     * @return void
     */
    public function testCacheForEachCustomPathInstances(): void
    {
        $templateLoader = $this->createMock(Adapter::class);
        $templateLoader
            ->expects($this->exactly(4))
            ->method('__call')
            ->willReturnMap([
                ['getPath', ['example4', 'html5', 'templates'], 'templates/example4.html5'],
                ['getPath', ['example4', 'text', 'templates'], 'templates/example4.text'],
                ['getPath', ['example4', 'html5', 'templates/theme'], 'templates/theme/example4.html5'],
                ['getPath', ['example4', 'text', 'templates/theme'], 'templates/theme/example4.text'],
            ]);

        $scopeDeterminator = $this->createMock(RequestScopeDeterminator::class);
        $template          = new Template('example4', $templateLoader, $scopeDeterminator);
        $method            = $this->reflectGetTemplateMethod();

        $this->assertEquals('templates/example4.html5', $method->invoke($template, 'example4', 'html5'));
        $this->assertEquals('templates/example4.text', $method->invoke($template, 'example4', 'text'));
        $this->assertEquals('templates/example4.html5', $method->invoke($template, 'example4', 'html5'));
        $this->assertEquals('templates/example4.text', $method->invoke($template, 'example4', 'text'));

        $scopeDeterminator = $this->createMock(RequestScopeDeterminator::class);
        $scopeDeterminator
            ->expects($this->exactly(4))
            ->method('currentScopeIsFrontend')
            ->willReturn(true);

        $GLOBALS['objPage'] = (object) ['templateGroup' => 'templates/theme'];

        $template2 = new Template('example4', $templateLoader, $scopeDeterminator);
        $this->assertEquals('templates/theme/example4.html5', $method->invoke($template2, 'example4', 'html5'));
        $this->assertEquals('templates/theme/example4.text', $method->invoke($template2, 'example4', 'text'));
        $this->assertEquals('templates/theme/example4.html5', $method->invoke($template2, 'example4', 'html5'));
        $this->assertEquals('templates/theme/example4.text', $method->invoke($template2, 'example4', 'text'));
    }

    /**
     * Test not found templates.
     *
     * @return void
     */
    public function testCacheNotFoundTemplatePaths(): void
    {
        $templateLoader = $this->createMock(Adapter::class);
        $templateLoader
            ->expects($this->once())
            ->method('__call')
            ->with('getPath', ['example5', 'html5', 'templates'])
            ->willReturn(null);

        $scopeDeterminator = $this->createMock(RequestScopeDeterminator::class);
        $template          = new Template('example5', $templateLoader, $scopeDeterminator);
        $method            = $this->reflectGetTemplateMethod();

        $this->assertEquals(null, $method->invoke($template, 'example5'));
        $this->assertEquals(null, $method->invoke($template, 'example5'));
    }

    /**
     * Create method reflection for protected getTemplate() method.
     *
     * @return ReflectionMethod
     */
    private function reflectGetTemplateMethod(): ReflectionMethod
    {
        $reflection = new ReflectionClass(Template::class);
        $method     = $reflection->getMethod('getTemplate');
        $method->setAccessible(true);

        return $method;
    }
}
