<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2026 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2026 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\Test\Render;

use Contao\CoreBundle\Config\ResourceFinderInterface;
use Contao\CoreBundle\Framework\Adapter;
use Contao\System;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use MetaModels\Render\Template;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;

/**
 * This tests the empty value check helper.
 *
 * Template::getTemplate() delegates to the private resolveTemplatePath(), which mirrors the
 * algorithm the (Contao 6 removed) TemplateLoader::getPath() used - the custom (theme) folder
 * first, then the project's global "templates" folder, then every installed bundle's
 * "Resources/contao/templates" folder via the "contao.resource_finder" service. These tests
 * exercise that real file-lookup, using an actual temporary project directory, rather than mocking
 * a template loader adapter that resolveTemplatePath() no longer calls.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) Exercising the real file-lookup (rather than
 *     mocking a single adapter) needs the resource finder, container, and Finder types directly.
 */
#[CoversClass(\MetaModels\Render\Template::class)]
final class TemplateTest extends TestCase
{
    /**
     * The temporary project directory created for the current test.
     *
     * @var string
     */
    private string $projectDir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->projectDir = \sys_get_temp_dir() . '/metamodels-core-template-test-' . \uniqid();
        \mkdir($this->projectDir . '/templates', 0777, true);
    }

    /**
     * Resets everything a test may have touched that outlives the test itself: the shared static
     * template path cache, the temporary project directory, Contao's process-wide container
     * static, and the frontend "objPage" superglobal.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    protected function tearDown(): void
    {
        $cacheProperty = new \ReflectionProperty(Template::class, 'templatePathCache');
        $cacheProperty->setValue(null, []);

        $containerProperty = new \ReflectionProperty(System::class, 'objContainer');
        $containerProperty->setValue(null, null);

        unset($GLOBALS['objPage']);

        $this->removeDirectory($this->projectDir);

        parent::tearDown();
    }

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

        self::assertInstanceOf(Template::class, $template);
    }

    /**
     * Test cache of multiple calls.
     *
     * @return void
     */
    public function testCacheFoundTemplatePaths(): void
    {
        $this->createTemplateFile('templates/example1.html5');

        $template = $this->createTemplate('example1');
        $method   = $this->reflectGetTemplateMethod();

        $expected = $this->projectDir . '/templates/example1.html5';
        self::assertEquals($expected, $method->invoke($template, 'example1'));

        // Remove the file - a second, still-successful lookup can only have been served by the
        // cache, not the filesystem.
        \unlink($expected);
        self::assertEquals($expected, $method->invoke($template, 'example1'));
    }

    /**
     * Test different formats creates different cache entries.
     *
     * @return void
     */
    public function testCacheForEachFormat(): void
    {
        $this->createTemplateFile('templates/example2.html5');
        $this->createTemplateFile('templates/example2.text');

        $template = $this->createTemplate('example2');
        $method   = $this->reflectGetTemplateMethod();

        $expectedHtml5 = $this->projectDir . '/templates/example2.html5';
        $expectedText  = $this->projectDir . '/templates/example2.text';

        self::assertEquals($expectedHtml5, $method->invoke($template, 'example2', 'html5'));
        self::assertEquals($expectedText, $method->invoke($template, 'example2', 'text'));

        \unlink($expectedHtml5);
        \unlink($expectedText);

        self::assertEquals($expectedHtml5, $method->invoke($template, 'example2', 'html5'));
        self::assertEquals($expectedText, $method->invoke($template, 'example2', 'text'));
    }

    /**
     * Test cache is shared between multiple instances.
     *
     * @return void
     */
    public function testCacheOverMultipleInstances(): void
    {
        $this->createTemplateFile('templates/example3.html5');
        $this->createTemplateFile('templates/example3.text');

        $template = $this->createTemplate('example3');
        $method   = $this->reflectGetTemplateMethod();

        $expectedHtml5 = $this->projectDir . '/templates/example3.html5';
        $expectedText  = $this->projectDir . '/templates/example3.text';

        self::assertEquals($expectedHtml5, $method->invoke($template, 'example3', 'html5'));
        self::assertEquals($expectedText, $method->invoke($template, 'example3', 'text'));

        \unlink($expectedHtml5);
        \unlink($expectedText);

        $template2 = $this->createTemplate('example3');
        self::assertEquals($expectedHtml5, $method->invoke($template2, 'example3', 'html5'));
        self::assertEquals($expectedText, $method->invoke($template2, 'example3', 'text'));
    }

    /**
     * Test different caches for custom paths.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function testCacheForEachCustomPathInstances(): void
    {
        $this->createTemplateFile('templates/example4.html5');
        $this->createTemplateFile('templates/example4.text');
        $this->createTemplateFile('templates/theme/example4.html5');
        $this->createTemplateFile('templates/theme/example4.text');

        $template = $this->createTemplate('example4');
        $method   = $this->reflectGetTemplateMethod();

        self::assertEquals(
            $this->projectDir . '/templates/example4.html5',
            $method->invoke($template, 'example4', 'html5')
        );
        self::assertEquals(
            $this->projectDir . '/templates/example4.text',
            $method->invoke($template, 'example4', 'text')
        );

        $scopeDeterminator = $this->createMock(RequestScopeDeterminator::class);
        $scopeDeterminator->method('currentScopeIsFrontend')->willReturn(true);

        $GLOBALS['objPage'] = (object) ['templateGroup' => 'templates/theme'];

        $template2 = $this->createTemplate('example4', $scopeDeterminator);
        self::assertEquals(
            $this->projectDir . '/templates/theme/example4.html5',
            $method->invoke($template2, 'example4', 'html5')
        );
        self::assertEquals(
            $this->projectDir . '/templates/theme/example4.text',
            $method->invoke($template2, 'example4', 'text')
        );
    }

    /**
     * Test not found templates.
     *
     * @return void
     */
    public function testCacheNotFoundTemplatePaths(): void
    {
        // Neither the project's "templates" folder nor any bundle has this template - the last
        // resort, the "contao.resource_finder" service, needs a container. Its Finder is pointed
        // at the (empty) project directory itself so it genuinely finds nothing.
        $resourceFinder = $this->createMock(ResourceFinderInterface::class);
        $resourceFinder
            ->expects(self::once())
            ->method('findIn')
            ->with('templates')
            ->willReturn((new Finder())->in($this->projectDir)->name('example5.html5'));

        $container = $this->createMock(ContainerInterface::class);
        $container->method('get')->with('contao.resource_finder')->willReturn($resourceFinder);
        System::setContainer($container);

        $template = $this->createTemplate('example5');
        $method   = $this->reflectGetTemplateMethod();

        self::assertNull($method->invoke($template, 'example5'));
        // A second call must be served by the cache - the resource finder mock's "once" above
        // would otherwise fail the test.
        self::assertNull($method->invoke($template, 'example5'));
    }

    /**
     * Create a Template instance pointed at the temporary project directory.
     *
     * @param string                          $strTemplate       The template name.
     * @param RequestScopeDeterminator|null   $scopeDeterminator The scope determinator (a fresh mock if omitted).
     *
     * @return Template
     */
    private function createTemplate(string $strTemplate, ?RequestScopeDeterminator $scopeDeterminator = null): Template
    {
        $templateLoader    = $this->createMock(Adapter::class);
        $scopeDeterminator = $scopeDeterminator ?? $this->createMock(RequestScopeDeterminator::class);

        return new Template($strTemplate, $templateLoader, $scopeDeterminator, null, '', $this->projectDir);
    }

    /**
     * Create an (empty) template fixture file below the temporary project directory.
     *
     * @param string $relativePath The path, relative to the project directory.
     *
     * @return void
     */
    private function createTemplateFile(string $relativePath): void
    {
        $path = $this->projectDir . '/' . $relativePath;
        if (!\is_dir(\dirname($path))) {
            \mkdir(\dirname($path), 0777, true);
        }
        \file_put_contents($path, '');
    }

    /**
     * Recursively remove a directory tree.
     *
     * @param string $directory The directory to remove.
     *
     * @return void
     */
    private function removeDirectory(string $directory): void
    {
        if (!\is_dir($directory)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            $item->isDir() ? \rmdir($item->getPathname()) : \unlink($item->getPathname());
        }

        \rmdir($directory);
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
