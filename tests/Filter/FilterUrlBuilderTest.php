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

use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Routing\UrlGenerator;
use MetaModels\Filter\FilterUrl;
use MetaModels\Filter\FilterUrlBuilder;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * This tests the filter url class.
 *
 * @covers \MetaModels\Filter\FilterUrlBuilder
 */
class FilterUrlBuilderTest extends TestCase
{
    /**
     * Data provider for the URL generate test.
     *
     * @return array
     */
    public function generateProvider(): array
    {
        return [
            'test generating' => [
                'expectedUrl' => 'page-alias/auto/slug/sluggy',
                'expectedParameters' => [
                    'get-param' => 'get-value',
                    'get2' => 'value',
                ],
                'page' => [
                    'alias' => 'page-alias',
                ],
                'get' => [
                    'get2' => 'value',
                ],
                'slug' => [
                    'slug' => 'sluggy',
                    'auto_item' => 'auto',
                ],
                'requestGet' => [
                    'get-param' => 'get-value',
                ],
                'requestUrl' => 'https://example.org/alias.html',
            ],
            'test stay on page' => [
                'expectedUrl' => 'alias/auto/slug/sluggy',
                'expectedParameters' => [
                    'get-param' => 'get-value',
                    'get2' => 'value',
                ],
                'page' => [
                ],
                'get' => [
                    'get2' => 'value',
                ],
                'slug' => [
                    'slug' => 'sluggy',
                    'auto_item' => 'auto',
                ],
                'requestGet' => [
                    'get-param' => 'get-value',
                ],
                'requestUrl' => 'https://example.org/alias.html',
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
     *
     * @dataProvider generateProvider
     */
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
            ->getMockBuilder(UrlGenerator::class)
            ->disableOriginalConstructor()
            ->getMock();

        $generator
            ->expects($this->once())
            ->method('generate')
            ->with($expectedUrl, $expectedParameters)
            ->willReturn('success');

        $adapter      = $this->getMockBuilder(Adapter::class)->getMock();
        $requestStack = $this->mockRequestStack($requestGet, $requestUrl);

        $builder = new FilterUrlBuilder($generator, $requestStack, true, '.html', $adapter);

        $this->assertSame('success', $builder->generate($filterUrl));
    }

    /**
     * Mock the request stack.
     *
     * @param array $requestGet The current GET parameters.
     *
     * @return RequestStack
     */
    private function mockRequestStack(array $requestGet, string $requestUrl): RequestStack
    {
        $requestStack = $this->getMockBuilder(RequestStack::class)->getMock();
        $requestStack->method('getMasterRequest')->willReturn(
            new Request($requestGet, [], [], [], [], ['REQUEST_URI' => $requestUrl])
        );

        return $requestStack;
    }
}
