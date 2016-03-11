<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2015 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2015 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\Test\Filter\Rules\Condition;

use MetaModels\Filter\IFilter;
use MetaModels\Filter\Rules\Condition\ConditionOr;
use MetaModels\Test\TestCase;

/**
 * Test OR condition filter rules.
 */
class ConditionOrTest extends TestCase
{
    /**
     * Provider function for testOrCondition.
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
                'Null values should return null',
                null,
                array(null, null),
            ),
            array(
                'Child value should be used if non empty',
                array(10, 5),
                array(array(10, 5)),
            ),
            array(
                'Merged child values should be used if non empty',
                array(10, 5, 15),
                array(array(10, 5), array(15, 5)),
            ),
            array(
                'Null should be used if some return null',
                null,
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
    public function testOrCondition($message, $expected, $filters)
    {
        $condition = new ConditionOr();
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
