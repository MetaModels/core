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
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Test\Filter\Rules\Condition;

use MetaModels\Filter\IFilter;
use MetaModels\Filter\Rules\Condition\ConditionAnd;
use MetaModels\Test\TestCase;

/**
 * Test AND condition filter rules.
 *
 * @covers \MetaModels\Filter\Rules\Condition\ConditionAnd
 */
class ConditionAndTest extends TestCase
{
    /**
     * Provider function for testAndCondition.
     *
     * @return array
     */
    public function provider()
    {
        return array(
            array(
                'Empty list of childs should return empty array',
                array(),
                array(),
            ),
            array(
                'Null values should return null (See https://github.com/MetaModels/core/issues/961)',
                null,
                array(null, null),
            ),
            array(
                'Child value should be used if non empty',
                array(10, 5),
                array(array(10, 5)),
            ),
            array(
                'Child value intersection should be used if multiple non empty',
                array(5),
                array(array(10, 5), array(15, 5)),
            ),
            array(
                'Child value intersection of non null should be used if some return null',
                array(5),
                array(array(10, 5), null, array(15, 5), null),
            ),
        );
    }

    /**
     * Test that the result equals the expected value.
     *
     * @param string     $message  The message to show on failure.
     *
     * @param array|null $expected The expected result.
     *
     * @param array      $filters  The filter result values to add to the AND condition.
     *
     * @return void
     *
     * @dataProvider provider
     */
    public function testAndCondition($message, $expected, $filters)
    {
        $condition = new ConditionAnd();
        foreach ($filters as $filter) {
            $condition->addChild($this->mockFilter($filter));
        }

        $this->assertEquals($expected, $condition->getMatchingIds(), $message);
    }

    /**
     * Mock a filter returning the ids from the passed rule.
     *
     * @param array|null $result The filter result to use.
     *
     * @return IFilter
     */
    private function mockFilter($result)
    {
        $filter = $this->getMockForAbstractClass('MetaModels\\Filter\\IFilter');
        $filter->method('getMatchingIds')->willReturn($result);

        return $filter;
    }
}
