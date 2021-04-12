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

use ContaoCommunityAlliance\DcGeneral\Data\DefaultConfig;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Driver\ResultStatement;
use MetaModels\DcGeneral\Data\FilterBuilder;
use MetaModels\IMetaModel;
use MetaModels\MetaModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use MetaModels\Attribute\Base;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Test the filter builder.
 *
 * @covers \MetaModels\DcGeneral\Data\FilterBuilder
 */
class FilterBuilderTest extends TestCase
{
    /**
     * Mock a MetaModel instance.
     *
     * @return IMetaModel
     */
    private function mockMetaModel(Connection $connection = null)
    {
        if (null === $connection) {
            $connection = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock();
        }

        $metaModel = new MetaModel(
            [
                'id'         => '1',
                'sorting'    => '1',
                'tstamp'     => '0',
                'name'       => 'MetaModel',
                'tableName'  => 'mm_test',
                'mode'       => '',
                'translated' => '1',
                'languages'  => [
                    'en' => ['isfallback' => '1'],
                    'de' => ['isfallback' => '']
                ],
                'varsupport' => '1',
            ],
            $this->getMockBuilder(EventDispatcherInterface::class)->disableOriginalConstructor()->getMock(),
            $connection
        );

        return $metaModel;
    }

    /**
     * Mock a database connection with the passed query.
     *
     * @param string $queryString The expected SQL query.
     * @param array  $params      The expected parameters.
     * @param array  $result      The query result.
     *
     * @return MockObject|Connection
     */
    private function mockConnection($queryString, $params, $result)
    {
        $connection = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock();

        $statement = $this
            ->getMockBuilder(ResultStatement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statement
            ->expects(self::once())
            ->method('fetchAll')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn($result);
        $connection
            ->expects(self::once())
            ->method('executeQuery')
            ->with($queryString, $params)
            ->willReturn($statement);

        return $connection;
    }

    /**
     * Test the build process.
     *
     * @return void
     */
    public function testBuildEmpty()
    {
        $metaModel = $this->mockMetaModel();

        $config = DefaultConfig::init();

        $config->setFilter([]);

        $builder = new FilterBuilder(
            $metaModel,
            $config,
            $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock()
        );

        $filter = $builder->build();

        self::assertNull($filter->getMatchingIds());
    }

    /**
     * Test the build process.
     *
     * @return void
     */
    public function testBuildSqlOnly()
    {
        $metaModel = $this->mockMetaModel();

        $attribute = $this
            ->getMockBuilder(Base::class)
            ->setConstructorArgs([$metaModel, ['colname' => 'test1']])
            ->setMethods(['searchFor'])
            ->getMockForAbstractClass();

        $attribute
            ->expects(self::once())
            ->method('searchFor')
            ->with('abc')
            ->willReturn([0, 1, 2, 3]);

        /** @var \MetaModels\Attribute\Base $attribute */
        $metaModel->addAttribute($attribute);

        $config = DefaultConfig::init();

        $config->setFilter([
            [
                'operation' => '=',
                'property'  => 'foo',
                'value'     => 0
            ],
            [
                'operation' => '=',
                'property'  => 'test1',
                'value'     => 'abc'
            ]
        ]);

        $connection = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock();

        $statement = $this
            ->getMockBuilder(ResultStatement::class)
            ->disableOriginalConstructor()
            ->getMock();
        $statement
            ->expects(self::once())
            ->method('fetchAll')
            ->with(\PDO::FETCH_ASSOC)
            ->willReturn([
                ['id' => 0],
                ['id' => 1],
                ['id' => 2],
                ['id' => 3],
                ['id' => 4],
                ['id' => 5],
            ]);
        $connection
            ->expects(self::once())
            ->method('executeQuery')
            ->with('SELECT t.id FROM mm_test AS t WHERE ((t.foo = ?))', [0])
            ->willReturn($statement);

        $builder = new FilterBuilder($metaModel, $config, $connection);

        $filter = $builder->build();

        self::assertSame([0, 1, 2, 3], $filter->getMatchingIds());
    }

    /**
     * Test the build process.
     *
     * @link https://github.com/MetaModels/core/issues/700
     *
     * @return void
     */
    public function testIssue700()
    {
        $metaModel = $this->mockMetaModel();

        $attribute = $this
            ->getMockBuilder(Base::class)
            ->setConstructorArgs([$metaModel, ['colname' => 'test1']])
            ->setMethods(['searchFor'])
            ->getMockForAbstractClass();

        $attribute
            ->expects(self::once())
            ->method('searchFor')
            ->with('*test*')
            ->willReturn([0, 1, 2, 3]);

        /** @var \MetaModels\Attribute\Base $attribute */
        $metaModel->addAttribute($attribute);

        $config = DefaultConfig::init();

        $config->setFilter(
            [
                [
                    'operation' => 'AND',
                    'children'  => [
                        [
                            'operation' => 'AND',
                            'children'  => [
                                [
                                    'operation' => 'LIKE',
                                    'property'  => 'test1',
                                    'value'     => '*test*'
                                ]
                            ]
                        ],
                    ]
                ]
            ]
        );

        $connection = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock();
        $connection
            ->expects(self::never())
            ->method('executeQuery');

        $builder = new FilterBuilder($metaModel, $config, $connection);

        $filter = $builder->build();

        self::assertSame([0, 1, 2, 3], $filter->getMatchingIds());
    }
}
