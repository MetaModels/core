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

namespace MetaModels\Test\Filter\Setting;

use MetaModels\Filter\Setting\Collection;
use MetaModels\Filter\Setting\ISimple;
use MetaModels\Filter\Setting\Simple;
use MetaModels\FrontendIntegration\FrontendFilterOptions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Collection::class)]
class CollectionTest extends TestCase
{
    /**
     * When no MetaModel is set (e.g. FE list module with no filter configured),
     * getParameterFilterWidgets() must return an empty array instead of throwing a RuntimeException.
     */
    public function testGetParameterFilterWidgetsReturnsEmptyArrayWhenNoMetaModelSet(): void
    {
        $collection = new Collection([]);

        $result = $collection->getParameterFilterWidgets([], [], new FrontendFilterOptions());

        self::assertSame([], $result);
    }

    /**
     * getParameters() returns an empty array when the collection has no settings.
     */
    public function testGetParametersReturnsEmptyArrayWhenNoSettings(): void
    {
        $collection = new Collection([]);

        self::assertSame([], $collection->getParameters());
    }

    /**
     * getParameterTypes() returns an empty array when the collection has no settings.
     */
    public function testGetParameterTypesReturnsEmptyArrayWhenNoSettings(): void
    {
        $collection = new Collection([]);

        self::assertSame([], $collection->getParameterTypes());
    }

    /**
     * getParameterTypes() collects the types of all contained settings - also for settings not rendering a
     * frontend filter widget (i.e. the usual detail page filter rules).
     */
    public function testGetParameterTypesCollectsTypesFromAllSettings(): void
    {
        $collection = new Collection([]);
        $collection->addSetting($this->mockSetting(['alias' => 'get']));
        $collection->addSetting($this->mockSetting(['category' => 'slug', 'legacy' => 'slugNget']));

        self::assertSame(
            ['alias' => 'get', 'category' => 'slug', 'legacy' => 'slugNget'],
            $collection->getParameterTypes()
        );
    }

    /**
     * Settings not implementing getParameterTypes() (BC layer) are treated as "slugNget".
     */
    public function testGetParameterTypesFallsBackToSlugNgetForLegacySettings(): void
    {
        $legacySetting = $this->getMockForAbstractClass(ISimple::class);
        $legacySetting->method('getParameters')->willReturn(['legacy_param']);

        $collection = new Collection([]);
        $collection->addSetting($legacySetting);

        $previous = set_error_handler(
            static function (int $severity, string $message) use (&$deprecation): bool {
                unset($severity);
                $deprecation = $message;

                return true;
            },
            E_USER_DEPRECATED
        );

        try {
            $types = $collection->getParameterTypes();
        } finally {
            set_error_handler($previous);
        }

        self::assertSame(['legacy_param' => 'slugNget'], $types);
        self::assertStringContainsString('getParameterTypes()', (string) $deprecation);
    }

    /**
     * Mock a filter setting providing the passed parameter types.
     *
     * @param array<string, string> $types The parameter types (parametername => type).
     *
     * @return ISimple
     */
    private function mockSetting(array $types): ISimple
    {
        $setting = $this
            ->getMockBuilder(Simple::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getParameters', 'getParameterTypes'])
            ->getMockForAbstractClass();
        $setting->method('getParameters')->willReturn(array_keys($types));
        $setting->method('getParameterTypes')->willReturn($types);

        return $setting;
    }
}
