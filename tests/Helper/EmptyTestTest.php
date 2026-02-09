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

namespace MetaModels\Test\Helper;

use MetaModels\Helper\EmptyTest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/** This tests the empty value check helper. */
#[CoversClass(EmptyTest::class)]
class EmptyTestTest extends TestCase
{
    /**
     * Data provider for test.
     *
     * @return array
     */
    public static function emptyValueTestProvider(): array
    {
        return [
            [
                'expected' => true,
                'value'    => null,
            ],
            [
                'expected' => true,
                'value'    => '',
            ],
            [
                'expected' => true,
                'value'    => [],
            ],
            [
                'expected' => true,
                'value'    => ['value' => []],
            ],
            [
                'expected' => true,
                'value'    => ['value' => [[]]],
            ],
            [
                'expected' => true,
                'value'    => [[]],
            ],
            [
                'expected' => true,
                'value'    => [[], []],
            ],
            [
                'expected' => true,
                'value'    => [['', null]],
            ],
            [
                'expected' => false,
                'value'    => 'a',
            ],
            [
                'expected' => false,
                'value'    => false,
            ],
            [
                'expected' => false,
                'value'    => true,
            ],
            [
                'expected' => false,
                'value'    => ['a'],
            ],
            [
                'expected' => false,
                'value'    => [0],
            ],
        ];
    }

    /**
     * Test the empty value results.
     *
     * @param bool  $expected The expected value.
     * @param mixed $value    The value to test.
     *
     * @return void
     */
    #[DataProvider('emptyValueTestProvider')]
    public function testEmptyValue(bool $expected, mixed $value): void
    {
        $message = sprintf(
            ' %s === %s::isEmptyValue(%s)',
            var_export($expected, true),
            EmptyTest::class,
            var_export($value, true)
        );
        if (true === $expected) {
            self::assertTrue(EmptyTest::isEmptyValue($value), $message);
            return;
        }
        self::assertFalse(EmptyTest::isEmptyValue($value), $message);
    }
}
