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

namespace MetaModels\Test\CoreBundle\Controller;

use MetaModels\CoreBundle\Controller\ListControllerTrait;
use MetaModels\Filter\FilterUrl;
use MetaModels\Test\CoreBundle\Controller\Fixtures\ListControllerTraitDouble;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Test the URL type handling of the list controllers.
 *
 * @covers \MetaModels\CoreBundle\Controller\ListControllerTrait
 */
#[CoversClass(ListControllerTrait::class)]
class ListControllerTraitTest extends TestCase
{
    /**
     * Data provider for parameters passed via the configured URL type.
     *
     * @return array<string, array{0: string, 1: array<string, string>, 2: array<string, string>}>
     */
    public static function providerMatchingType(): array
    {
        return [
            'slug as slug'        => ['slug', [], ['alias' => 'the-value']],
            'get as get'          => ['get', ['alias' => 'the-value'], []],
            'slugNget as slug'    => ['slugNget', [], ['alias' => 'the-value']],
            'slugNget as get'     => ['slugNget', ['alias' => 'the-value'], []],
            'slugNget as both'    => ['slugNget', ['alias' => 'the-value'], ['alias' => 'the-value']],
            'slug but not passed' => ['slug', [], []],
            'get but not passed'  => ['get', [], []],
            'other parameter'     => ['slug', ['other' => 'the-value'], []],
        ];
    }

    /**
     * Parameters passed via the configured URL type (or not at all) are no mismatch.
     *
     * @param string                $paramType      The configured URL type.
     * @param array<string, string> $getParameters  The GET parameters of the URL.
     * @param array<string, string> $slugParameters The slug parameters of the URL.
     */
    #[DataProvider('providerMatchingType')]
    public function testMatchingTypeIsNoMismatch(
        string $paramType,
        array $getParameters,
        array $slugParameters
    ): void {
        self::assertFalse(
            $this->isParameterTypeMismatch(new FilterUrl([], $getParameters, $slugParameters), 'alias', $paramType)
        );
    }

    /**
     * A parameter configured as "slug" but passed as GET is a mismatch.
     */
    public function testGetOnSlugParameterIsMismatch(): void
    {
        $filterUrl = new FilterUrl([], ['alias' => 'the-value'], []);

        self::assertTrue($this->isParameterTypeMismatch($filterUrl, 'alias', 'slug'));
    }

    /**
     * A parameter configured as "get" but passed as slug is a mismatch.
     */
    public function testSlugOnGetParameterIsMismatch(): void
    {
        $filterUrl = new FilterUrl([], [], ['alias' => 'the-value']);

        self::assertTrue($this->isParameterTypeMismatch($filterUrl, 'alias', 'get'));
    }

    /**
     * Call the private isParameterTypeMismatch method on a class using the trait.
     *
     * @param FilterUrl $filterUrl The filter URL to check.
     * @param string    $name      The parameter name to check.
     * @param string    $paramType The configured URL type of the parameter.
     *
     * @return bool
     */
    private function isParameterTypeMismatch(FilterUrl $filterUrl, string $name, string $paramType): bool
    {
        $reflection = new \ReflectionClass(ListControllerTraitDouble::class);
        // The trait constructor requires the whole service stack - not needed for this check.
        $instance = $reflection->newInstanceWithoutConstructor();
        $method   = $reflection->getMethod('isParameterTypeMismatch');
        $method->setAccessible(true);

        return $method->invoke($instance, $filterUrl, $name, $paramType);
    }
}
