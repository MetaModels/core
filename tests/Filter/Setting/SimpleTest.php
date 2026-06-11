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
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2025 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Test\Filter\Setting;

use MetaModels\Filter\FilterUrl;
use MetaModels\Filter\FilterUrlBuilder;
use MetaModels\Filter\Setting\ICollection;
use MetaModels\Filter\Setting\Simple;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test simple filter settings.
 *
 */
#[CoversClass(\MetaModels\Filter\Setting\Simple::class)]
class SimpleTest extends TestCase
{
    /**
     * Mock a Simple filter setting.
     *
     * @param array                    $properties       The initialization data.
     * @param FilterUrlBuilder|null    $filterUrlBuilder Optional pre-configured filter URL builder mock.
     * @param TranslatorInterface|null $translator       Optional pre-configured translator mock.
     *
     * @return Simple|MockObject
     */
    protected function mockSimpleFilterSetting(
        $properties = [],
        ?FilterUrlBuilder $filterUrlBuilder = null,
        ?TranslatorInterface $translator = null
    ) {
        $filterSetting   = $this->createMock(ICollection::class);
        $eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        if (null === $filterUrlBuilder) {
            $filterUrlBuilder = $this->getMockBuilder(FilterUrlBuilder::class)
                ->disableOriginalConstructor()
                ->getMock();
        }

        if (null === $translator) {
            $translator = $this->createMock(TranslatorInterface::class);
        }

        $setting = $this
            ->getMockBuilder(Simple::class)
            ->setConstructorArgs([$filterSetting, $properties, $eventDispatcher, $filterUrlBuilder, $translator])
            ->onlyMethods(['prepareRules'])
            ->getMock();

        return $setting;
    }

    /**
     * Add a parameter to the url, if it is auto_item, it will get prepended.
     *
     * @param Simple $instance The instance.
     * @param string $url      The url built so far.
     * @param string $name     The parameter name.
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
     * @param array  $params    The filter url parameter array.
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
     * Call the protected prepareFrontendFilterOptions method via reflection.
     *
     * @param Simple $instance     The filter setting instance.
     * @param array  $arrWidget    Widget configuration.
     * @param array  $arrFilterUrl Current filter URL parameters.
     * @param array  $arrJumpTo   Jump-to page information.
     * @param bool   $blnAutoSubmit Whether to auto-submit.
     *
     * @return array
     */
    protected function callPrepareFrontendFilterOptions(
        Simple $instance,
        array $arrWidget,
        array $arrFilterUrl,
        array $arrJumpTo,
        bool $blnAutoSubmit
    ): array {
        $reflection = new \ReflectionMethod($instance, 'prepareFrontendFilterOptions');
        $reflection->setAccessible(true);
        return $reflection->invoke($instance, $arrWidget, $arrFilterUrl, $arrJumpTo, $blnAutoSubmit);
    }

    /**
     * Create a FilterUrlBuilder mock that records slug/GET parameters for each generate() call.
     *
     * Each entry in $capturedParams will be ['slug' => [...], 'get' => [...]].
     *
     * @param array $capturedParams Reference to the array that collects captured data.
     *
     * @return FilterUrlBuilder&MockObject
     */
    protected function mockCapturingFilterUrlBuilder(array &$capturedParams): FilterUrlBuilder
    {
        $filterUrlBuilder = $this->getMockBuilder(FilterUrlBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $filterUrlBuilder->method('generate')->willReturnCallback(
            static function (FilterUrl $filterUrl) use (&$capturedParams): string {
                $capturedParams[] = [
                    'slug' => $filterUrl->getSlugParameters(),
                    'get'  => $filterUrl->getGetParameters(),
                ];
                return 'http://example.com/test';
            }
        );

        return $filterUrlBuilder;
    }

    /**
     * Default widget array for param_type tests (two options, no blank option).
     *
     * @return array
     */
    protected function defaultWidget(): array
    {
        return [
            'inputType' => 'select',
            'options'   => ['A' => 'Option A', 'B' => 'Option B'],
            'eval'      => [
                'urlparam'           => 'my_param',
                'includeBlankOption' => false,
            ],
        ];
    }

    /**
     * Test adding of filter url parameters.
     *
     * @return void
     */
    public function testAddUrlParameter()
    {
        $setting = $this->mockSimpleFilterSetting();

        self::assertEquals(
            '/foo/a/A/b/B',
            $this->addUrlParameter($setting, '/a/A/b/B', 'auto_item', 'foo'),
            'auto_item'
        );
        self::assertEquals(
            '/a/A/b/B/bar/foo',
            $this->addUrlParameter($setting, '/a/A/b/B', 'bar', 'foo'),
            'bar'
        );
        self::assertEquals(
            '/a/A/b/B/bar/%%25foo',
            $this->addUrlParameter($setting, '/a/A/b/B', 'bar', '%foo'),
            'bar with percent'
        );
        self::assertEquals(
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

        self::assertEquals(
            '%s/a/A/b/B',
            $this->buildFilterUrl($setting, array('a' => 'A', 'b' => 'B', 'auto_item' => 'AUTO'), 'auto_item'),
            'auto_item'
        );
        self::assertEquals(
            '/AUTO/a/A%s',
            $this->buildFilterUrl($setting, array('a' => 'A', 'b' => 'B', 'auto_item' => 'AUTO'), 'b'),
            'b'
        );
        self::assertEquals(
            '/AUTO%s/b/B',
            $this->buildFilterUrl($setting, array('a' => 'A', 'b' => 'B', 'auto_item' => 'AUTO'), 'a'),
            'a'
        );
        self::assertEquals(
            '/AUTO/a/A/b/B%s',
            $this->buildFilterUrl($setting, array('a' => 'A', 'b' => 'B', 'auto_item' => 'AUTO'), 'c'),
            'c'
        );
        self::assertEquals(
            '%s/a/A/b/B',
            $this->buildFilterUrl($setting, array('a' => 'A', 'b' => 'B'), 'auto_item'),
            'auto_item 2'
        );
        self::assertEquals(
            '%s',
            $this->buildFilterUrl($setting, array(), 'auto_item'),
            'auto_item 3'
        );
        self::assertEquals(
            '%s',
            $this->buildFilterUrl($setting, array('auto_item' => 'AUTO'), 'auto_item'),
            'auto_item 4'
        );
    }

    /**
     * Without explicit param_type the default is 'slug': option URLs use slug, GET is cleared.
     */
    public function testPrepareFrontendFilterOptionsDefaultIsSlug(): void
    {
        $capturedParams   = [];
        $filterUrlBuilder = $this->mockCapturingFilterUrlBuilder($capturedParams);
        $setting          = $this->mockSimpleFilterSetting([], $filterUrlBuilder);

        $result = $this->callPrepareFrontendFilterOptions($setting, $this->defaultWidget(), [], [], false);

        self::assertCount(2, $result);
        self::assertSame('A', $capturedParams[0]['slug']['my_param'] ?? null, 'Option A slug');
        self::assertArrayNotHasKey('my_param', $capturedParams[0]['get'], 'Option A GET absent');
        self::assertSame('B', $capturedParams[1]['slug']['my_param'] ?? null, 'Option B slug');
        self::assertArrayNotHasKey('my_param', $capturedParams[1]['get'], 'Option B GET absent');
    }

    /**
     * param_type='slug': option URLs use slug, GET is cleared.
     */
    public function testPrepareFrontendFilterOptionsSlugType(): void
    {
        $capturedParams   = [];
        $filterUrlBuilder = $this->mockCapturingFilterUrlBuilder($capturedParams);
        $setting          = $this->mockSimpleFilterSetting(['param_type' => 'slug'], $filterUrlBuilder);

        $result = $this->callPrepareFrontendFilterOptions($setting, $this->defaultWidget(), [], [], false);

        self::assertCount(2, $result);
        self::assertSame('A', $capturedParams[0]['slug']['my_param'] ?? null, 'Option A slug');
        self::assertArrayNotHasKey('my_param', $capturedParams[0]['get'], 'Option A GET absent');
        self::assertSame('B', $capturedParams[1]['slug']['my_param'] ?? null, 'Option B slug');
        self::assertArrayNotHasKey('my_param', $capturedParams[1]['get'], 'Option B GET absent');
    }

    /**
     * param_type='get': option URLs use GET query string, slug is cleared.
     */
    public function testPrepareFrontendFilterOptionsGetType(): void
    {
        $capturedParams   = [];
        $filterUrlBuilder = $this->mockCapturingFilterUrlBuilder($capturedParams);
        $setting          = $this->mockSimpleFilterSetting(['param_type' => 'get'], $filterUrlBuilder);

        $result = $this->callPrepareFrontendFilterOptions($setting, $this->defaultWidget(), [], [], false);

        self::assertCount(2, $result);
        self::assertSame('A', $capturedParams[0]['get']['my_param'] ?? null, 'Option A GET');
        self::assertArrayNotHasKey('my_param', $capturedParams[0]['slug'], 'Option A slug absent');
        self::assertSame('B', $capturedParams[1]['get']['my_param'] ?? null, 'Option B GET');
        self::assertArrayNotHasKey('my_param', $capturedParams[1]['slug'], 'Option B slug absent');
    }

    /**
     * param_type='slugNget': option URLs use slug only; GET is cleared so slug takes priority.
     */
    public function testPrepareFrontendFilterOptionsSlugNgetType(): void
    {
        $capturedParams   = [];
        $filterUrlBuilder = $this->mockCapturingFilterUrlBuilder($capturedParams);
        $setting          = $this->mockSimpleFilterSetting(['param_type' => 'slugNget'], $filterUrlBuilder);

        $result = $this->callPrepareFrontendFilterOptions($setting, $this->defaultWidget(), [], [], false);

        self::assertCount(2, $result);
        self::assertSame('A', $capturedParams[0]['slug']['my_param'] ?? null, 'Option A slug');
        self::assertArrayNotHasKey('my_param', $capturedParams[0]['get'], 'Option A GET absent');
        self::assertSame('B', $capturedParams[1]['slug']['my_param'] ?? null, 'Option B slug');
        self::assertArrayNotHasKey('my_param', $capturedParams[1]['get'], 'Option B GET absent');
    }

    /**
     * The blank "do not filter" option always clears both slug and GET, regardless of param_type.
     */
    public function testPrepareFrontendFilterOptionsBlankOptionAlwaysClears(): void
    {
        $translator = $this->createMock(TranslatorInterface::class);
        $translator->method('trans')->willReturn('- no filter -');

        $widget                        = $this->defaultWidget();
        $widget['eval']['includeBlankOption'] = true;

        foreach (['slug', 'get', 'slugNget'] as $paramType) {
            $capturedParams   = [];
            $filterUrlBuilder = $this->mockCapturingFilterUrlBuilder($capturedParams);
            $setting          = $this->mockSimpleFilterSetting(
                ['param_type' => $paramType],
                $filterUrlBuilder,
                $translator
            );

            $result = $this->callPrepareFrontendFilterOptions($setting, $widget, [], [], false);

            self::assertCount(3, $result, "param_type={$paramType}: expected 3 options (blank + A + B)");
            // Blank option must clear the param from both slug and GET.
            self::assertArrayNotHasKey(
                'my_param',
                $capturedParams[0]['slug'],
                "param_type={$paramType}: blank option must not set slug"
            );
            self::assertArrayNotHasKey(
                'my_param',
                $capturedParams[0]['get'],
                "param_type={$paramType}: blank option must not set GET"
            );
        }
    }
}
