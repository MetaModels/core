<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2021 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2021 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Test\Filter\Rules;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\Result;
use MetaModels\Filter\Rules\SimpleQuery;
use PHPUnit\Framework\TestCase;

/**
 * This tests the simple query filter rule.
 *
 * @covers \MetaModels\Filter\Rules\SimpleQuery
 */
class SimpleQueryTest extends TestCase
{
    /**
     * Test execution of a query.
     *
     * @return void
     */
    public function testExecution()
    {
        $query  = 'SELECT * FROM test';
        $params = ['param' => 'value'];
        $types  = ['param' => \PDO::PARAM_STR];

        $connection = $this
            ->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['executeQuery'])
            ->getMock();
        $result  = $this
            ->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['fetchAllAssociative'])
            ->getMock();

        $connection
            ->expects(self::once())
            ->method('executeQuery')
            ->with($query, $params, $types)
            ->willReturn($result);
        $result
            ->expects(self::once())
            ->method('fetchAllAssociative')
            ->willReturn([['idcolumn' => 'a'], ['idcolumn' => 'b'], ['idcolumn' => 'c']]);

        $rule = new SimpleQuery($query, $params, 'idcolumn', $connection, $types);
        self::assertSame(['a', 'b', 'c'], $rule->getMatchingIds());
    }
}
