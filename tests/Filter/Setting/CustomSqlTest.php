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

namespace MetaModels\Test\Filter\Setting;

use Contao\CoreBundle\Framework\Adapter;
use Contao\Input;
use Contao\InsertTags;
use Contao\Session;
use Doctrine\DBAL\Connection;
use MetaModels\Filter\Filter;
use MetaModels\Filter\Setting\CustomSql;
use MetaModels\Filter\Setting\ICollection;
use MetaModels\IMetaModel;
use MetaModels\IMetaModelsServiceContainer;
use MetaModels\Test\AutoLoadingTestCase;
use Symfony\Component\DependencyInjection\Container;
use MetaModels\Filter\Rules\SimpleQuery;

/**
 * Unit test for testing the CustomSql filter setting.
 *
 * @covers \MetaModels\Filter\Setting\CustomSql
 */
class CustomSqlTest extends AutoLoadingTestCase
{
    /**
     * Mock a CustomSql with parseInsertTags disabled.
     *
     * @param array  $properties The initialization data.
     * @param string $tableName  The table name of the MetaModel to mock.
     *
     * @return CustomSql
     */
    protected function mockCustomSql($properties = [], $tableName = 'mm_unittest', $services = [])
    {
        $metaModel = $this->getMockForAbstractClass(IMetaModel::class);
        $metaModel->method('getTableName')->willReturn($tableName);

        $filterSetting = $this->getMockForAbstractClass(ICollection::class);
        $filterSetting->method('getMetaModel')->willReturn($metaModel);

        if (!isset($services[InsertTags::class])) {
            $services[InsertTags::class] = $insertTags = $this
                ->getMockBuilder(InsertTags::class)
                ->setMethods(['replace'])
                ->disableOriginalConstructor()
                ->getMock();

            $insertTags->method('replace')->willReturnCallback(function ($buffer) {
                return str_replace(array('{{', '::', '}}'), '__', $buffer);
            });
        }
        if (!isset($services[Connection::class])) {
            $services[Connection::class] = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock();

        }
        if (!isset($services[Input::class])) {
            $services[Input::class] = $this
                ->getMockBuilder(Adapter::class)
                ->disableOriginalConstructor()
                ->setMethods(['cookie', 'get', 'post'])
                ->getMock();
        }
        if (!isset($services[Session::class])) {
        $services[Session::class] = $this
            ->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();
        }
        if (!isset($services[IMetaModelsServiceContainer::class])) {
            $services[IMetaModelsServiceContainer::class] = $oldCont = $this
                ->getMockBuilder(IMetaModelsServiceContainer::class)
                ->disableOriginalConstructor()
                ->setMethods(['get'])
                ->getMockForAbstractClass();
        }

        $container = new Container();

        foreach ([
            InsertTags::class,
            Connection::class,
            Input::class,
            Session::class,
            Connection::class,
            IMetaModelsServiceContainer::class,
        ] as $serviceId) {
            $container->set($serviceId, $services[$serviceId]);
        }

        return new CustomSql($filterSetting, $properties, $container);
    }

    /**
     * Internal convenience method to the generating method and to extract the values from the filter rule..
     *
     * @param CustomSql $instance  The instance.
     * @param array     $filterUrl The filter url to process.
     *
     * @return mixed
     */
    protected function generateSql($instance, $filterUrl = [])
    {
        $filter = new Filter($this->getMockForAbstractClass(IMetaModel::class));

        $instance->prepareRules($filter, $filterUrl);

        $reflection = new \ReflectionProperty($filter, 'arrFilterRules');
        $reflection->setAccessible(true);
        $rules = $reflection->getValue($filter);

        $reflection = new \ReflectionProperty(SimpleQuery::class, 'queryString');
        $reflection->setAccessible(true);
        $sql = $reflection->getValue($rules[0]);

        $reflection = new \ReflectionProperty(SimpleQuery::class, 'params');
        $reflection->setAccessible(true);
        $params = $reflection->getValue($rules[0]);

        return ['sql' => $sql, 'params' => $params];
    }

    /**
     * Run the test and assert that the generated values match the expected ones.
     *
     * @param CustomSql $setting            The filter setting to test.
     * @param string    $expectedSql        The expected Sql query.
     * @param array     $expectedParameters The expected parameters.
     * @param array     $filterUrl          The filter url to process.
     * @param string    $message            An optional message to display upon failure.
     *
     * @return void
     */
    protected function assertGeneratedSqlIs($setting, $expectedSql, $expectedParameters, $filterUrl, $message = '')
    {
        $sql = $this->generateSql($setting, $filterUrl);

        $this->assertEquals($expectedSql, $sql['sql'], $message);
        $this->assertEquals($expectedParameters, $sql['params'], $message);
    }

    /**
     * Test a literal query.
     *
     * @return void
     */
    public function testPlain()
    {
        $setting = $this->mockCustomSql(['customsql' => 'SELECT id FROM mm_mymetamodel WHERE page_id=1'], 'tableName');

        $this->assertGeneratedSqlIs($setting, 'SELECT id FROM mm_mymetamodel WHERE page_id=1', [], []);
    }

    /**
     * Test table name replacement.
     *
     * @return void
     */
    public function testTableName()
    {
        $setting = $this->mockCustomSql(['customsql' => 'SELECT id FROM {{table}} WHERE page_id=1'], 'tableName');

        $this->assertGeneratedSqlIs($setting, 'SELECT id FROM tableName WHERE page_id=1', [], []);
    }

    /**
     * Test insert tag replacement.
     *
     * @return void
     */
    public function testInsertTags()
    {
        $setting = $this->mockCustomSql(
            ['customsql' => 'SELECT id FROM tableName WHERE page_id={{page::id}}'],
            'tableName'
        );

        $this->assertGeneratedSqlIs(
            $setting,
            'SELECT id FROM tableName WHERE page_id=__page__id__',
            [],
            []
        );
    }

    /**
     * Test secure insert tag replacement.
     *
     * @return void
     */
    public function testSecureInsertTags()
    {
        $setting = $this->mockCustomSql(
            ['customsql' => 'SELECT id FROM tableName WHERE page_id={{secure::page::id}}'],
            'tableName'
        );

        $this->assertGeneratedSqlIs(
            $setting,
            'SELECT id FROM tableName WHERE page_id=?',
            ['__page__id__'],
            []
        );
    }

    /**
     * Test request variable replacement.
     *
     * @return void
     */
    public function testRequestGetWithEmptyParameter()
    {
        $input = $this
            ->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['cookie', 'get', 'post'])
            ->getMock();
        $input->expects($this->once())->method('get')->with('category')->willReturn(null);

        $setting = $this->mockCustomSql(
            ['customsql' => 'SELECT id FROM tableName WHERE catname={{param::get?name=category&default=defaultcat}}'],
            'tableName',
            [Input::class => $input]
        );

        $this->assertGeneratedSqlIs(
            $setting,
            'SELECT id FROM tableName WHERE catname=?',
            ['defaultcat'],
            [],
            'See https://github.com/MetaModels/core/issues/376'
        );
    }

    /**
     * Test request variable replacement.
     *
     * @return void
     */
    public function testRequestGetParameter()
    {
        $input = $this
            ->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['cookie', 'get', 'post'])
            ->getMock();
        $input
            ->expects($this->once())
            ->method('get')
            ->with('category')
            ->willReturn('category name');

        $setting = $this->mockCustomSql(
            ['customsql' => 'SELECT id FROM tableName WHERE catname={{param::get?name=category&default=defaultcat}}'],
            'tableName',
            [Input::class => $input]
        );

        $this->assertGeneratedSqlIs($setting, 'SELECT id FROM tableName WHERE catname=?', ['category name'], []);
    }

    /**
     * Test request variable replacement.
     *
     * @return void
     */
    public function testRequestPostParameter()
    {
        $input = $this
            ->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['cookie', 'get', 'post'])
            ->getMock();
        $input
            ->expects($this->once())
            ->method('post')
            ->with('category')
            ->willReturn('category name');

        $setting = $this->mockCustomSql(
            ['customsql' => 'SELECT id FROM tableName WHERE catname={{param::post?name=category}}'],
            'tableName',
            [Input::class => $input]
        );

        $this->assertGeneratedSqlIs($setting, 'SELECT id FROM tableName WHERE catname=?', ['category name'], []);
    }

    /**
     * Test request variable replacement.
     *
     * @return void
     */
    public function testRequestCookie()
    {
        $input = $this
            ->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['cookie', 'get', 'post'])
            ->getMock();
        $input
            ->expects($this->once())
            ->method('cookie')
            ->with('category')
            ->willReturn('category name');

        $setting = $this->mockCustomSql(
            ['customsql' => 'SELECT id FROM tableName WHERE catname={{param::cookie?name=category}}'],
            'tableName',
            [Input::class => $input]
        );

        $this->assertGeneratedSqlIs($setting, 'SELECT id FROM tableName WHERE catname=?', ['category name'], []);
    }

    /**
     * Test variable replacement via session value.
     *
     * @return void
     */
    public function testValueFromSession()
    {
        $session = $this
            ->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();
        $session
            ->expects($this->once())
            ->method('get')
            ->with('category')
            ->willReturn('category name');

        $setting = $this->mockCustomSql(
            ['customsql' => 'SELECT id FROM tableName WHERE catname={{param::session?name=category}}'],
            'tableName',
            [Session::class => $session]
        );

        $this->assertGeneratedSqlIs($setting, 'SELECT id FROM tableName WHERE catname=?', ['category name'], []);
    }

    /**
     * Test variable replacement via session value.
     *
     * @return void
     */
    public function testValueFromFilterUrl()
    {
        $setting = $this->mockCustomSql(
            ['customsql' => 'SELECT id FROM tableName WHERE catname={{param::filter?name=category}}'],
            'tableName'
        );

        $this->assertGeneratedSqlIs(
            $setting,
            'SELECT id FROM tableName WHERE catname=?',
            ['category name'],
            ['category' => 'category name']
        );
    }

    /**
     * Test request variable replacement.
     *
     * @return void
     */
    public function testRequestParameterAggregated()
    {
        $input = $this
            ->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['cookie', 'get', 'post'])
            ->getMock();
        $input
            ->expects($this->once())
            ->method('get')
            ->with('categories')
            ->willReturn(['first', 'second']);

        $setting = $this->mockCustomSql(
            ['customsql' => 'SELECT id FROM tableName WHERE catname IN ({{param::get?name=categories&aggregate}})'],
            'tableName',
            [Input::class => $input]
        );

        $this->assertGeneratedSqlIs(
            $setting,
            'SELECT id FROM tableName WHERE catname IN (?,?)',
            [
                'first',
                'second'
            ],
            []
        );
    }

    /**
     * Test request variable replacement.
     *
     * @return void
     */
    public function testRequestParameterAggregatedSet()
    {
        $input = $this
            ->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->setMethods(['cookie', 'get', 'post'])
            ->getMock();
        $input
            ->expects($this->once())
            ->method('get')
            ->with('ids')
            ->willReturn(['1', '2']);

        $setting = $this->mockCustomSql(
            ['customsql' => 'SELECT id FROM tableName WHERE catids IN ({{param::get?name=ids&aggregate=set}})'],
            'tableName',
            [Input::class => $input]
        );

        $this->assertGeneratedSqlIs($setting, 'SELECT id FROM tableName WHERE catids IN (?)', ['1,2'], []);
    }

    /**
     * Test with an empty string value.
     *
     * @return void
     */
    public function testWithEmptyStringValue()
    {
        $setting = $this->mockCustomSql(
            ['customsql' => 'SELECT id FROM {{table}} WHERE otherparam={{param::filter?name=otherparam}}'],
            'tableName'
        );

        $this->assertGeneratedSqlIs(
            $setting,
            'SELECT id FROM tableName WHERE otherparam=?',
            [''],
            ['otherparam' => '']
        );
    }

    /**
     * Test with a numeric zero value.
     *
     * @return void
     */
    public function testWithZeroValue()
    {
        $setting = $this->mockCustomSql(
            ['customsql' => 'SELECT id FROM {{table}} WHERE otherparam={{param::filter?name=otherparam}}'],
            'tableName'
        );

        $this->assertGeneratedSqlIs(
            $setting,
            'SELECT id FROM tableName WHERE otherparam=?',
            [0],
            ['otherparam' => 0]
        );
    }

    /**
     * Test with a null value (not passed as param value).
     *
     * @return void
     */
    public function testWithNullValue()
    {
        $setting = $this->mockCustomSql(
            ['customsql' => 'SELECT id FROM {{table}} WHERE otherparam={{param::filter?name=otherparam}}'],
            'tableName'
        );

        $this->assertGeneratedSqlIs(
            $setting,
            'SELECT id FROM tableName WHERE otherparam=NULL',
            [],
            ['otherparam' => null]
        );
    }

    /**
     * Test with a null value (not passed as param value) and a default.
     *
     * @return void
     */
    public function testWithNullValueAndDefault()
    {
        $setting = $this->mockCustomSql(
            ['customsql' => 'SELECT id FROM {{table}} WHERE otherparam={{param::filter?name=otherparam&default=xyz}}'],
            'tableName'
        );

        $this->assertGeneratedSqlIs(
            $setting,
            'SELECT id FROM tableName WHERE otherparam=?',
            ['xyz'],
            ['otherparam' => null]
        );
    }
}
