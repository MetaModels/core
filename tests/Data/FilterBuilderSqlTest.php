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

namespace MetaModels\Test\Data;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Result;
use MetaModels\DcGeneral\Data\FilterBuilderSql;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test the filter builder.
 *
 * @covers \MetaModels\DcGeneral\Data\FilterBuilderSql
 */
class FilterBuilderSqlTest extends TestCase
{
    /**
     * Test that a new builder is empty.
     */
    public function testBuilderIsInitiallyEmpty(): void
    {
        $connection = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock();
        $builder    = new FilterBuilderSql('mm_test', 'AND', $connection);

        self::assertTrue($builder->isEmpty());
        self::assertNull($builder->build());
    }

    /**
     * Data provider for testBuild()
     */
    public function buildTestProvider(): array
    {
        return [
            'equality compare' => [
                'expectedSql'    => 'SELECT t.id FROM mm_test AS t WHERE ((t.test = ?))',
                'expectedParams' => [0],
                'filter'         => ['operation' => '=', 'property' => 'test', 'value' => 0]
            ],
            'greater than compare' => [
                'expectedSql'    => 'SELECT t.id FROM mm_test AS t WHERE ((t.test > ?))',
                'expectedParams' => [0],
                'filter'         => ['operation' => '>', 'property' => 'test', 'value' => 0]
            ],
            'less than compare' => [
                'expectedSql'    => 'SELECT t.id FROM mm_test AS t WHERE ((t.test < ?))',
                'expectedParams' => [0],
                'filter'         => ['operation' => '<', 'property' => 'test', 'value' => 0]
            ],
            'IN list' => [
                'expectedSql'    => 'SELECT t.id FROM mm_test AS t WHERE ((t.test IN (?,?,?)))',
                'expectedParams' => [1, 2, 3],
                'filter'         => ['operation' => 'IN', 'property' => 'test', 'values' => [1, 2, 3]]
            ],
            'LIKE' => [
                'expectedSql'    => 'SELECT t.id FROM mm_test AS t WHERE ((t.test LIKE ?))',
                'expectedParams' => ['any_thing%'],
                'filter'         => ['operation' => 'LIKE', 'property' => 'test', 'value' => 'any?thing*']
            ],
        ];
    }

    /**
     * Test the build process.
     *
     * @param string $expectedSql    The expected SQL query.
     * @param array  $expectedParams The expected parameters.
     * @param array  $filter         The filter input array.
     *
     * @dataProvider buildTestProvider
     */
    public function testBuild(string $expectedSql, array $expectedParams, array $filter): void
    {
        $connection = $this->mockConnection($expectedSql, $expectedParams, [['id' => 'succ'], ['id' => 'ess']]);
        $builder    = new FilterBuilderSql('mm_test', 'AND', $connection);

        self::assertSame($builder, $builder->addChild($filter));
        self::assertSame(['succ', 'ess'], $builder->build()->getMatchingIds());
    }

    /**
     * Test the build process.
     */
    public function testBuildMultiple(): void
    {
        $connection = $this->mockConnection(
            'SELECT t.id FROM mm_test AS t WHERE ((t.foo = ?) AND (t.bar = ?))',
            ['fooz', 'barz'],
            [['id' => 'succ'], ['id' => 'ess']]
        );
        $builder    = new FilterBuilderSql('mm_test', 'AND', $connection);

        self::assertSame($builder, $builder->addChild(['operation' => '=', 'property' => 'foo', 'value' => 'fooz']));
        self::assertSame($builder, $builder->addChild(['operation' => '=', 'property' => 'bar', 'value' => 'barz']));

        self::assertSame(['succ', 'ess'], $builder->build()->getMatchingIds());
    }

    /**
     * Test the build process.
     */
    public function testAddSubProcedure(): void
    {
        $child = new FilterBuilderSql(
            'mm_test',
            'OR',
            $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock()
        );
        $child->addChild(['operation' => '=', 'property' => 'foo', 'value' => 'fooz']);
        $child->addChild(['operation' => '=', 'property' => 'bar', 'value' => 'barz']);

        $connection = $this->mockConnection(
            'SELECT t.id FROM mm_test AS t WHERE (((t.foo = ?) OR (t.bar = ?)) AND (t.moo = ?))',
            ['fooz', 'barz', 'mooz'],
            [['id' => 'succ'], ['id' => 'ess']]
        );
        $builder    = new FilterBuilderSql('mm_test', 'AND', $connection);

        self::assertSame($builder, $builder->addSubProcedure($child));
        self::assertSame($builder, $builder->addChild(['operation' => '=', 'property' => 'moo', 'value' => 'mooz']));

        self::assertSame(['succ', 'ess'], $builder->build()->getMatchingIds());
    }

    /**
     * Mock a database connection with the passed query.
     *
     * @param string $queryString The expected SQL query.
     * @param array  $params      The expected parameters.
     * @param array  $result      The query result.
     */
    private function mockConnection(string $queryString, array $params, array $result): Connection&MockObject
    {
        $connection = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock();

        $resultSet = $this
            ->getMockBuilder(Result::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultSet
            ->expects(self::once())
            ->method('fetchAllAssociative')
            ->willReturn($result);
        $connection
            ->expects(self::once())
            ->method('executeQuery')
            ->with($queryString, $params)
            ->willReturn($resultSet);

        return $connection;
    }
}
