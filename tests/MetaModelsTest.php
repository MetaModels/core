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

namespace MetaModels\Test;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\Expression\ExpressionBuilder;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Statement;
use MetaModels\MetaModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Test the base attribute.
 *
 * @covers \MetaModels\MetaModel
 */
class MetaModelsTest extends TestCase
{
    /**
     * Test instantiation of a MetaModel.
     *
     * @return void
     */
    public function testCreation()
    {
        $values = array(
            'id'         => '1',
            'sorting'    => '1',
            'tstamp'     => '0',
            'name'       => 'MetaModel',
            'tableName'  => 'mm_test',
            'mode'       => '',
            'translated' => '1',
            'languages'  => array(
                'en' => array('isfallback' => '1'),
                'de' => array('isfallback' => '')
            ),
            'varsupport' => '1',
        );

        $serialized = array();
        foreach ($values as $key => $value) {
            if (is_array($value)) {
                $serialized[$key] = serialize($value);
            } else {
                $serialized[$key] = $value;
            }
        }

        $metaModel = new MetaModel($serialized);
        self::assertEmpty($metaModel->getAttributes());

        foreach ($values as $key => $value) {
            self::assertEquals($value, $metaModel->get($key), $key);
        }

        $metaModel = new MetaModel($values);

        foreach ($values as $key => $value) {
            self::assertEquals($value, $metaModel->get($key), $key);
        }
    }

    /**
     * Ensure the buildDatabaseParameterList works correctly.
     *
     * @return void
     */
    public function testBuildDatabaseParameterList()
    {
        $metaModel = new MetaModel(array());

        $reflection = new \ReflectionMethod($metaModel, 'buildDatabaseParameterList');
        $reflection->setAccessible(true);
        self::assertEquals('?', $reflection->invoke($metaModel, array(1)));
        self::assertEquals('?,?', $reflection->invoke($metaModel, array(1, 2)));
        self::assertEquals('?,?,?,?,?,?', $reflection->invoke($metaModel, array(1, 2, 'fooo', 'bar', null, 'test')));
    }

    /**
     * Ensure the system columns are present. See issue #196.
     *
     * @return void
     */
    public function testRetrieveSystemColumns()
    {
        $metaModel = new MetaModel(
            [
                'id'         => '1',
                'sorting'    => '256',
                'tstamp'     => '1367274071',
                'name'       => 'Test RetrieveSystemColumns',
                'tableName'  => 'mm_test_retrieve',
                'translated' => '1',
                'languages'  => serialize(['en' => ['isfallback' => '1'], 'de' => ['isfallback' => '0']]),
                'varsupport' => '',
            ],
            $this->getMockForAbstractClass(EventDispatcherInterface::class),
            $this->mockConnection([
                \Closure::fromCallable(function () {
                    $builder = $this
                        ->getMockBuilder(QueryBuilder::class)
                        ->disableOriginalConstructor()
                        ->getMock();
                    $builder
                        ->expects($this->once())
                        ->method('select')
                        ->with('t.*')
                        ->willReturn($builder);
                    $builder
                        ->expects($this->once())
                        ->method('from')
                        ->with('mm_test_retrieve', 't')
                        ->willReturn($builder);

                    $expr = $this
                        ->getMockBuilder(ExpressionBuilder::class)
                        ->disableOriginalConstructor()
                        ->setMethods()
                        ->getMock();

                    $builder
                        ->expects($this->once())
                        ->method('expr')
                        ->willReturn($expr);

                    $builder
                        ->expects($this->once())
                        ->method('where')
                        ->with('t.id IN (:values)')
                        ->willReturn($builder);

                    $builder
                        ->expects($this->once())
                        ->method('setParameter')
                        ->with('values', [1], Connection::PARAM_STR_ARRAY)
                        ->willReturn($builder);

                    $builder
                        ->expects($this->once())
                        ->method('orderBy')
                        ->with('FIELD(id, :values)')
                        ->willReturn($builder);

                    $statement = $this
                        ->getMockBuilder(Statement::class)
                        ->disableOriginalConstructor()
                        ->getMock();
                    $statement
                        ->expects($this->exactly(2))
                        ->method('fetch')
                        ->with(\PDO::FETCH_ASSOC)
                        ->willReturnOnConsecutiveCalls([
                            'id'      => 1,
                            'pid'     => 0,
                            'sorting' => 1,
                            'tstamp'  => 343094400,
                        ], null);
                    $builder
                        ->expects($this->once())
                        ->method('execute')
                        ->willReturn($statement);

                    return $builder;
                })->__invoke()
            ])
        );

        self::assertEquals($metaModel->getName(), 'Test RetrieveSystemColumns');

        $item = $metaModel->findById(1);

        self::assertEquals(1, $item->get('id'));
        self::assertEquals(0, $item->get('pid'));
        self::assertEquals(1, $item->get('sorting'));
        self::assertEquals(343094400, $item->get('tstamp'));
        self::assertNull($item->get('varbase'));
        self::assertNull($item->get('vargroup'));
    }

    /**
     * Ensure the getIdsFromFilter works correctly.
     *
     * @return void
     */
    public function testGetIdsFromFilterSortedById()
    {
        $metaModel = $this
            ->getMockBuilder(MetaModel::class)
            ->setMethods(['getMatchingIds'])
            ->setConstructorArgs([
                ['tableName' => 'mm_test_retrieve'],
                $this->getMockForAbstractClass(EventDispatcherInterface::class),
                $this->mockConnection([])
            ])
            ->getMock();
        $metaModel
            ->expects(self::exactly(6))
            ->method('getMatchingIds')
            ->willReturn([4, 3, 2, 1]);

        /** @var MetaModel $metaModel */
        self::assertSame([1, 2, 3, 4], $metaModel->getIdsFromFilter($metaModel->getEmptyFilter(), 'id'));
        self::assertSame([1, 2], $metaModel->getIdsFromFilter($metaModel->getEmptyFilter(), 'id', 0, 2));
        self::assertSame([3, 4], $metaModel->getIdsFromFilter($metaModel->getEmptyFilter(), 'id', 2, 2));
        self::assertSame([3], $metaModel->getIdsFromFilter($metaModel->getEmptyFilter(), 'id', 2, 1));
        self::assertSame([], $metaModel->getIdsFromFilter($metaModel->getEmptyFilter(), 'id', 20, 0));
        self::assertSame([2, 3, 4], $metaModel->getIdsFromFilter($metaModel->getEmptyFilter(), 'id', 1, 10));
    }

    /**
     * Ensure the getIdsFromFilter works correctly when sorting by pid and slicing the results.
     *
     * @return void
     */
    public function testGetIdsFromFilterSortedByPid()
    {
        $metaModel = $this
            ->getMockBuilder(MetaModel::class)
            ->setMethods(['getMatchingIds'])
            ->setConstructorArgs([
                ['tableName' => 'mm_test_retrieve'],
                $this->getMockForAbstractClass(EventDispatcherInterface::class),
                $this->mockConnection([
                    \Closure::fromCallable(function () {
                        $builder = $this
                            ->getMockBuilder(QueryBuilder::class)
                            ->disableOriginalConstructor()
                            ->getMock();
                        $builder
                            ->expects($this->once())
                            ->method('select')
                            ->with('t.id')
                            ->willReturn($builder);
                        $builder
                            ->expects($this->once())
                            ->method('from')
                            ->with('mm_test_retrieve', 't')
                            ->willReturn($builder);

                        $expr = $this
                            ->getMockBuilder(ExpressionBuilder::class)
                            ->disableOriginalConstructor()
                            ->setMethods()
                            ->getMock();

                        $builder
                            ->expects($this->once())
                            ->method('expr')
                            ->willReturn($expr);

                        $builder
                            ->expects($this->once())
                            ->method('where')
                            ->with('t.id IN (:values)')
                            ->willReturn($builder);

                        $builder
                            ->expects($this->once())
                            ->method('setParameter')
                            ->with('values', [4, 3, 2, 1], Connection::PARAM_STR_ARRAY)
                            ->willReturn($builder);

                        $builder
                            ->expects($this->once())
                            ->method('orderBy')
                            ->with('pid', 'ASC')
                            ->willReturn($builder);

                        $statement = $this
                            ->getMockBuilder(Statement::class)
                            ->disableOriginalConstructor()
                            ->getMock();
                        $statement
                            ->expects($this->once())
                            ->method('fetchAll')
                            ->with(\PDO::FETCH_COLUMN)
                            ->willReturn([1, 2, 3, 4]);
                        $builder
                            ->expects($this->once())
                            ->method('execute')
                            ->willReturn($statement);

                        return $builder;
                    })->__invoke()
                ])
            ])
            ->getMock();
        $metaModel
            ->expects(self::exactly(6))
            ->method('getMatchingIds')
            ->willReturn([4, 3, 2, 1]);

        /** @var MetaModel $metaModel */
        self::assertSame(array(1, 2, 3, 4), $metaModel->getIdsFromFilter($metaModel->getEmptyFilter(), 'pid'));
        self::assertSame(array(1, 2), $metaModel->getIdsFromFilter($metaModel->getEmptyFilter(), 'pid', 0, 2));
        self::assertSame(array(3, 4), $metaModel->getIdsFromFilter($metaModel->getEmptyFilter(), 'pid', 2, 2));
        self::assertSame(array(3), $metaModel->getIdsFromFilter($metaModel->getEmptyFilter(), 'pid', 2, 1));
        self::assertSame(array(), $metaModel->getIdsFromFilter($metaModel->getEmptyFilter(), 'pid', 20, 0));
        self::assertSame(array(2, 3, 4), $metaModel->getIdsFromFilter($metaModel->getEmptyFilter(), 'pid', 1, 10));
    }

    /**
     * Ensure the getIdsFromFilter works correctly when the results have been cached.
     *
     * @return void
     */
    public function testGetIdsFromFilterSortedByPidWithCache()
    {
        $metaModel = $this
            ->getMockBuilder(MetaModel::class)
            ->setMethods(['getMatchingIds'])
            ->setConstructorArgs([
                ['tableName' => 'mm_test_retrieve'],
                $this->getMockForAbstractClass(EventDispatcherInterface::class),
                $this->mockConnection([
                    \Closure::fromCallable(function () {
                        $builder = $this
                            ->getMockBuilder(QueryBuilder::class)
                            ->disableOriginalConstructor()
                            ->getMock();
                        $builder
                            ->expects($this->once())
                            ->method('select')
                            ->with('t.id')
                            ->willReturn($builder);
                        $builder
                            ->expects($this->once())
                            ->method('from')
                            ->with('mm_test_retrieve', 't')
                            ->willReturn($builder);

                        $expr = $this
                            ->getMockBuilder(ExpressionBuilder::class)
                            ->disableOriginalConstructor()
                            ->setMethods()
                            ->getMock();

                        $builder
                            ->expects($this->once())
                            ->method('expr')
                            ->willReturn($expr);

                        $builder
                            ->expects($this->once())
                            ->method('where')
                            ->with('t.id IN (:values)')
                            ->willReturn($builder);

                        $builder
                            ->expects($this->once())
                            ->method('setParameter')
                            ->with('values', [4, 3, 2, 1], Connection::PARAM_STR_ARRAY)
                            ->willReturn($builder);

                        $builder
                            ->expects($this->once())
                            ->method('orderBy')
                            ->with('pid', 'ASC')
                            ->willReturn($builder);

                        $statement = $this
                            ->getMockBuilder(Statement::class)
                            ->disableOriginalConstructor()
                            ->getMock();
                        $statement
                            ->expects($this->once())
                            ->method('fetchAll')
                            ->with(\PDO::FETCH_COLUMN)
                            ->willReturn([1, 2, 3, 4]);
                        $builder
                            ->expects($this->once())
                            ->method('execute')
                            ->willReturn($statement);

                        return $builder;
                    })->__invoke()
                ])
            ])
            ->getMock();
        $metaModel
            ->expects(self::exactly(2))
            ->method('getMatchingIds')
            ->willReturnOnConsecutiveCalls([4, 3, 2, 1], [3, 2]);

        /** @var MetaModel $metaModel */
        self::assertSame([1, 2, 3, 4], $metaModel->getIdsFromFilter($metaModel->getEmptyFilter(), 'pid'));
        self::assertSame([2, 3], $metaModel->getIdsFromFilter($metaModel->getEmptyFilter(), 'pid'));
    }

    /**
     * Ensure the getCount works correctly.
     *
     * @return void
     */
    public function testGetCountForEmptyList()
    {
        $metaModel = $this
            ->getMockBuilder(MetaModel::class)
            ->setMethods(['getMatchingIds'])
            ->setConstructorArgs(
                [
                    ['tableName' => 'mm_test_retrieve'],
                    $this->getMockForAbstractClass(EventDispatcherInterface::class),
                    $this->mockConnection([])
                ]
            )
            ->getMock();
        $metaModel
            ->expects(self::once())
            ->method('getMatchingIds')
            ->willReturn([]);

        /** @var MetaModel $metaModel */
        self::assertEquals(0, $metaModel->getCount($metaModel->getEmptyFilter()));
    }

    /**
     * Ensure the getCount works correctly.
     *
     * @return void
     */
    public function testGetCountForNonEmptyList()
    {
        $metaModel = $this->getMockBuilder(MetaModel::class)
            ->setMethods(['getMatchingIds'])
            ->setConstructorArgs([
                ['tableName' => 'mm_test_retrieve'],
                $this->getMockForAbstractClass(EventDispatcherInterface::class),
                $this->mockConnection([
                    \Closure::fromCallable(function () {
                        $builder = $this
                            ->getMockBuilder(QueryBuilder::class)
                            ->disableOriginalConstructor()
                            ->getMock();
                        $builder
                            ->expects($this->once())
                            ->method('select')
                            ->with('COUNT(t.id)')
                            ->willReturn($builder);
                        $builder
                            ->expects($this->once())
                            ->method('from')
                            ->with('mm_test_retrieve', 't')
                            ->willReturn($builder);

                        $expr = $this
                            ->getMockBuilder(ExpressionBuilder::class)
                            ->disableOriginalConstructor()
                            ->setMethods()
                            ->getMock();

                        $builder
                            ->expects($this->once())
                            ->method('expr')
                            ->willReturn($expr);

                        $builder
                            ->expects($this->once())
                            ->method('where')
                            ->with('t.id IN (:values)')
                            ->willReturn($builder);

                        $builder
                            ->expects($this->once())
                            ->method('setParameter')
                            ->with('values', [4, 3, 2, 1], Connection::PARAM_STR_ARRAY)
                            ->willReturn($builder);

                        $statement = $this
                            ->getMockBuilder(Statement::class)
                            ->disableOriginalConstructor()
                            ->getMock();
                        $statement
                            ->expects($this->once())
                            ->method('fetch')
                            ->with(\PDO::FETCH_COLUMN)
                            ->willReturn(4);
                        $builder
                            ->expects($this->once())
                            ->method('execute')
                            ->willReturn($statement);

                        return $builder;
                    })->__invoke()
                ])
            ])
            ->getMock();
        $metaModel
            ->expects(self::once())
            ->method('getMatchingIds')
            ->willReturn([4, 3, 2, 1]);

        /** @var MetaModel $metaModel */
        self::assertEquals(4, $metaModel->getCount($metaModel->getEmptyFilter()));
    }

    /**
     * Mock a database connection with hte passed query builders.
     *
     * @param array $queryBuilders The query builder list.
     *
     * @return MockObject|Connection
     */
    private function mockConnection(array $queryBuilders)
    {
        $connection = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock();

        if ([] !== $queryBuilders) {
            $connection
                ->expects(self::exactly(count($queryBuilders)))
                ->method('createQueryBuilder')
                ->willReturnOnConsecutiveCalls(...$queryBuilders);
        } else {
            $connection
                ->expects(self::never())
                ->method('createQueryBuilder');
        }

        return $connection;
    }
}
