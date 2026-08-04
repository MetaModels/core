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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2026 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\Test\Filter\Setting;

use MetaModels\Filter\FilterUrlBuilder;
use MetaModels\Filter\Setting\ICollection;
use MetaModels\Filter\Setting\Simple;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Test the URL parameter types of simple filter settings.
 *
 * @covers \MetaModels\Filter\Setting\Simple
 */
#[CoversClass(Simple::class)]
class SimpleParameterTypesTest extends TestCase
{
    /**
     * Data provider for the configurable URL types.
     *
     * @return array<string, array{0: string}>
     */
    public static function providerParamType(): array
    {
        return [
            'slug'     => ['slug'],
            'get'      => ['get'],
            'slugNget' => ['slugNget'],
        ];
    }

    /**
     * The configured param_type is reported for every parameter of the setting.
     *
     * This has to work for settings without frontend filter widget as well (the usual detail page filter rules),
     * as the type is otherwise unknown and both slug and GET would be accepted.
     *
     * @param string $paramType The configured URL type.
     */
    #[DataProvider('providerParamType')]
    public function testReportsConfiguredType(string $paramType): void
    {
        $setting = $this->mockSimpleFilterSetting(['my_param'], ['param_type' => $paramType]);

        self::assertSame(['my_param' => $paramType], $setting->getParameterTypes());
    }

    /**
     * Legacy settings without stored param_type keep the lenient behaviour of accepting slug and GET.
     */
    public function testFallsBackToSlugNget(): void
    {
        $setting = $this->mockSimpleFilterSetting(['my_param'], []);

        self::assertSame(['my_param' => 'slugNget'], $setting->getParameterTypes());
    }

    /**
     * All parameters of a setting share the configured type.
     */
    public function testCoversAllParameters(): void
    {
        $setting = $this->mockSimpleFilterSetting(['from', 'to'], ['param_type' => 'get']);

        self::assertSame(['from' => 'get', 'to' => 'get'], $setting->getParameterTypes());
    }

    /**
     * A setting without any parameter reports no types.
     */
    public function testIsEmptyWithoutParameters(): void
    {
        $setting = $this->mockSimpleFilterSetting([], ['param_type' => 'get']);

        self::assertSame([], $setting->getParameterTypes());
    }

    /**
     * Mock a Simple filter setting returning the passed parameter names.
     *
     * @param list<string> $parameters The parameter names the setting shall report.
     * @param array        $properties The initialization data.
     *
     * @return Simple|MockObject
     */
    private function mockSimpleFilterSetting(array $parameters, array $properties)
    {
        $filterUrlBuilder = $this->getMockBuilder(FilterUrlBuilder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $setting = $this
            ->getMockBuilder(Simple::class)
            ->setConstructorArgs(
                [
                    $this->getMockForAbstractClass(ICollection::class),
                    $properties,
                    $this->getMockForAbstractClass(EventDispatcherInterface::class),
                    $filterUrlBuilder,
                    $this->getMockForAbstractClass(TranslatorInterface::class)
                ]
            )
            ->onlyMethods(['getParameters'])
            ->getMockForAbstractClass();
        $setting->method('getParameters')->willReturn($parameters);

        return $setting;
    }
}
