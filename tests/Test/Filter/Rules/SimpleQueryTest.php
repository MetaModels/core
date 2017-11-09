<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2017 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\Test\Filter\Rules;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\Statement;
use MetaModels\Filter\Rules\SimpleQuery;
use PHPUnit\Framework\TestCase;

/**
 * This tests the simple query filter rule.
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
            ->setMethods(['executeQuery'])
            ->getMock();
        $statement  = $this->getMockForAbstractClass(Statement::class);

        $connection
            ->expects($this->once())
            ->method('executeQuery')
            ->with($query, $params, $types)
            ->willReturn($statement);
        $statement
            ->expects($this->once())
            ->method('fetchAll')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn([['idcolumn' => 'a'], ['idcolumn' => 'b'], ['idcolumn' => 'c']]);

        $rule = new SimpleQuery($query, $params, 'idcolumn', $connection, $types);
        $this->assertSame(['a', 'b', 'c'], $rule->getMatchingIds());
    }
}
