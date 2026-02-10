<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2025 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2025 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Test\Filter;

use MetaModels\Filter\FilterUrl;
use MetaModels\Filter\FilterUrlBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/** This tests the filter url class. */
#[CoversClass(FilterUrlBuilder::class)]
class FilterUrlBuilderTest extends TestCase
{
    /**
     * Data provider for the URL generate test.
     *
     * @return array
     */
    public static function generateProvider(): array
    {
        return [
            'test generating'   => [
                'expectedUrl'        => 'tl_page.42',
                'expectedParameters' => [
                    'get2'       => 'value',
                    'parameters' => '/auto/slug/sluggy',
                ],
                'page'               => [
                    'alias' => 'page-alias',
                    'id'    => 42,
                ],
                'get'                => [
                    'get2' => 'value',
                ],
                'slug'               => [
                    'slug'      => 'sluggy',
                    'auto_item' => 'auto',
                ],
                'requestGet'         => [
                    'get-param' => 'get-value',
                ],
                'requestUrl'         => 'https://example.org/alias.html',
            ],
            'test stay on page' => [
                'expectedUrl'        => 'tl_page.42',
                'expectedParameters' => [
                    'get2'       => 'value',
                    'parameters' => '/auto/slug/sluggy',
                    'get-param'  => 'get-value',
                ],
                'page'               => [
                    'id' => 42,
                ],
                'get'                => [
                    'get2' => 'value',
                ],
                'slug'               => [
                    'slug'      => 'sluggy',
                    'auto_item' => 'auto',
                ],
                'requestGet'         => [
                    'get-param' => 'get-value',
                ],
                'requestUrl'         => 'https://example.org/alias.html',
            ],
        ];
    }

    /**
     * Test initialization.
     *
     * @param string $expectedUrl        The expected URL.
     * @param array  $expectedParameters The expected parameters.
     * @param array  $page               The page array.
     * @param array  $get                The GET parameters.
     * @param array  $slug               The slug parameters.
     * @param array  $requestGet         The GET parameters of the current request.
     * @param string $requestUrl         The current URL.
     *
     * @return void
     */
    #[DataProvider('generateProvider')]
    public function testGenerate(
        string $expectedUrl,
        array $expectedParameters,
        array $page,
        array $get,
        array $slug,
        array $requestGet,
        string $requestUrl
    ): void {
        $filterUrl = new FilterUrl(
            $page,
            $get,
            $slug
        );

        $generator = $this
            ->getMockBuilder(UrlGeneratorInterface::class)
            ->getMock();

        $generator
            ->expects($this->once())
            ->method('generate')
            ->with($expectedUrl, $expectedParameters)
            ->willReturn('success');

        $requestStack = $this->mockRequestStack($requestGet, $requestUrl, $page['id']);

        $builder = new FilterUrlBuilder($generator, $requestStack);

        self::assertSame('success', $builder->generate($filterUrl));
    }

    public function testGeneratesNonStandardPorts(): void
    {
        $filterUrl = new FilterUrl(
            [],
            ['get2' => 'value'],
            ['slug' => 'sluggy', 'auto_item' => 'auto']
        );

        $generator = $this
            ->getMockBuilder(UrlGeneratorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $generator
            ->expects($this->once())
            ->method('generate')
            ->with('tl_page.42', ['get2' => 'value', 'get-param' => 'get-value', 'parameters' => '/auto/slug/sluggy'])
            ->willReturn('success');

        $requestStack = $this->getMockBuilder(RequestStack::class)->getMock();
        $requestStack->method('getCurrentRequest')->willReturn(
            new Request(
                ['get-param' => 'get-value'],
                [],
                ['pageModel' => 42],
                [],
                [],
                [
                    'REQUEST_URI' => 'https://example.org:8080/folder/page.html',
                    'HTTP_HOST'   => 'example.org:8080',
                ]
            )
        );

        $builder = new FilterUrlBuilder($generator, $requestStack);

        self::assertSame('success', $builder->generate($filterUrl));
    }

    /**
     * Mock the request stack.
     *
     * @param array  $requestGet The current GET parameters.
     * @param string $requestUrl The request URL.
     */
    private function mockRequestStack(array $requestGet, string $requestUrl, int $pageModel): RequestStack
    {
        $requestStack = $this->getMockBuilder(RequestStack::class)->getMock();
        $requestStack->method('getCurrentRequest')->willReturn(
            new Request($requestGet, [], ['pageModel' => $pageModel], [], [], ['REQUEST_URI' => $requestUrl])
        );

        return $requestStack;
    }
}
