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

namespace MetaModels\Test\Filter\Setting;

use Contao\InsertTags;
use Doctrine\DBAL\Connection;
use MetaModels\Filter\Filter;
use MetaModels\Filter\Setting\CustomSql;
use MetaModels\Filter\Setting\ICollection;
use MetaModels\IMetaModel;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for testing the CustomSql filter setting.
 */
class CustomSqlTest extends TestCase
{
    /**
     * Mock a CustomSql with parseInsertTags disabled.
     *
     * @param array  $properties The initialization data.
     *
     * @param string $tableName  The table name of the MetaModel to mock.
     *
     * @return CustomSql
     */
    protected function mockCustomSql($properties = array(), $tableName = 'mm_unittest')
    {
        if (!class_exists('\System')) {
            class_alias('\Contao\System', '\System');
        }

        if (!class_exists('\Controller')) {
            class_alias('\Contao\Controller', '\Controller');
        }

        $metaModel = $this->getMockBuilder(IMetaModel::class)->getMockForAbstractClass();
        $metaModel->method('getTableName')->willReturn($tableName);

        $filterSetting = $this->getMockForAbstractClass(ICollection::class);
        $filterSetting->method('getMetaModel')->willReturn($metaModel);


        $insertTags = $this
            ->getMockBuilder(InsertTags::class)
            ->setMethods(['replace'])
            ->disableOriginalConstructor()
            ->getMock();

        $insertTags->method('replace')->willReturnCallback(function ($buffer) {
            return str_replace(array('{{', '::', '}}'), '__', $buffer);
        });

        $database = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock();

        $oldContainer = function () {};

        $setting = new CustomSql($filterSetting, $properties, $database, $insertTags, $oldContainer);

        return $setting;
    }

    /**
     * Internal convenience method to the generating method and to extract the values from the filter rule..
     *
     * @param CustomSql $instance  The instance.
     *
     * @param array     $filterUrl The filter url to process.
     *
     * @return mixed
     */
    protected function generateSql($instance, $filterUrl = array())
    {
        $filter = new Filter($this->getMockBuilder(IMetaModel::class)->getMockForAbstractClass());

        $instance->prepareRules($filter, $filterUrl);

        $reflection = new \ReflectionProperty($filter, 'arrFilterRules');
        $reflection->setAccessible(true);
        $rules = $reflection->getValue($filter);

        $reflection = new \ReflectionProperty('MetaModels\Filter\Rules\SimpleQuery', 'strQueryString');
        $reflection->setAccessible(true);
        $sql = $reflection->getValue($rules[0]);

        $reflection = new \ReflectionProperty('MetaModels\Filter\Rules\SimpleQuery', 'arrParams');
        $reflection->setAccessible(true);
        $params = $reflection->getValue($rules[0]);

        return array(
            'sql'    => $sql,
            'params' => $params,
        );
    }

    /**
     * Run the test and assert that the generated values match the expected ones.
     *
     * @param string    $expectedSql        The expected Sql query.
     *
     * @param array     $expectedParameters The expected parameters.
     *
     * @param CustomSql $setting            The filter setting to test.
     *
     * @param array     $filterUrl          The filter url to process.
     *
     * @param string    $message            An optional message to display upon failure.
     *
     * @return void
     */
    protected function assertGeneratedSqlIs($expectedSql, $expectedParameters, $setting, $filterUrl, $message = '')
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
        $setting = $this->mockCustomSql(
            array(
            'customsql' => 'SELECT id FROM mm_mymetamodel WHERE page_id=1'
            ),
            'tableName'
        );

        $this->assertGeneratedSqlIs(
            'SELECT id FROM mm_mymetamodel WHERE page_id=1',
            array(),
            $setting,
            array()
        );
    }

    /**
     * Test table name replacement.
     *
     * @return void
     */
    public function testTableName()
    {
        $setting = $this->mockCustomSql(
            array(
            'customsql' => 'SELECT id FROM {{table}} WHERE page_id=1'
            ),
            'tableName'
        );

        $this->assertGeneratedSqlIs(
            'SELECT id FROM tableName WHERE page_id=1',
            array(),
            $setting,
            array()
        );
    }

    /**
     * Test insert tag replacement.
     *
     * @return void
     */
    public function testInsertTags()
    {
        $setting = $this->mockCustomSql(
            array(
            'customsql' => 'SELECT id FROM tableName WHERE page_id={{page::id}}'
            ),
            'tableName'
        );

        $this->assertGeneratedSqlIs(
            'SELECT id FROM tableName WHERE page_id=__page__id__',
            array(),
            $setting,
            array()
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
            array(
            'customsql' => 'SELECT id FROM tableName WHERE page_id={{secure::page::id}}'
            ),
            'tableName'
        );

        $this->assertGeneratedSqlIs(
            'SELECT id FROM tableName WHERE page_id=?',
            array('__page__id__'),
            $setting,
            array()
        );
    }

    /**
     * Test request variable replacement.
     *
     * @return void
     */
    public function testRequestVars()
    {
        $this->markTestSkipped('Input not mockable currently.');

        $setting = $this->mockCustomSql(
            array(
            'customsql' => 'SELECT id FROM tableName WHERE catname={{param::get?name=category&default=defaultcat}}'
            ),
            'tableName'
        );

        $this->initializeContaoInputClass();

        $this->assertGeneratedSqlIs(
            'SELECT id FROM tableName WHERE catname=?',
            array('defaultcat'),
            $setting,
            array(),
            'See https://github.com/MetaModels/core/issues/376'
        );

        $this->initializeContaoInputClass(array('category' => 'category name'));

        $this->assertGeneratedSqlIs(
            'SELECT id FROM tableName WHERE catname=?',
            array('category name'),
            $setting,
            array()
        );

        $setting = $this->mockCustomSql(
            array(
                'customsql' => 'SELECT id FROM tableName WHERE catname={{param::post?name=category}}'
            ),
            'tableName'
        );

        $this->initializeContaoInputClass(array(), array('category' => 'category name'));

        $this->assertGeneratedSqlIs(
            'SELECT id FROM tableName WHERE catname=?',
            array('category name'),
            $setting,
            array()
        );

        $setting = $this->mockCustomSql(
            array(
                'customsql' => 'SELECT id FROM tableName WHERE catname={{param::cookie?name=category}}'
            ),
            'tableName'
        );

        $this->initializeContaoInputClass(array(), array(), array('category' => 'category name'));

        $this->assertGeneratedSqlIs(
            'SELECT id FROM tableName WHERE catname=?',
            array('category name'),
            $setting,
            array()
        );
    }

    /**
     * Test variable replacement via session value.
     *
     * @return void
     */
    public function testValueFromSession()
    {
        $this->markTestSkipped('Session not mockable currently.');

        $setting = $this->mockCustomSql(
            array(
                'customsql' => 'SELECT id FROM tableName WHERE catname={{param::session?name=category}}'
            ),
            'tableName'
        );

        $this->initializeContaoInputClass();
        $this->initializeContaoSessionClass(array('category' => 'category name'));

        $this->assertGeneratedSqlIs(
            'SELECT id FROM tableName WHERE catname=?',
            array('category name'),
            $setting,
            array()
        );
    }

    /**
     * Test variable replacement via session value.
     *
     * @return void
     */
    public function testValueFromFilterUrl()
    {

        $setting = $this->mockCustomSql(
            array(
                'customsql' => 'SELECT id FROM tableName WHERE catname={{param::filter?name=category}}'
            ),
            'tableName'
        );

        $this->assertGeneratedSqlIs(
            'SELECT id FROM tableName WHERE catname=?',
            array('category name'),
            $setting,
            array('category' => 'category name')
        );
    }

    /**
     * Test request variable replacement.
     *
     * @return void
     */
    public function testRequestVarsAggregated()
    {
        $setting = $this->mockCustomSql(
            array(
            'customsql' => 'SELECT id FROM tableName WHERE catname IN ({{param::get?name=categories&aggregate}})'
            ),
            'tableName'
        );

        $this->initializeContaoInputClass(array('categories' => array('first', 'second')));

        $this->assertGeneratedSqlIs(
            'SELECT id FROM tableName WHERE catname IN (?,?)',
            array('first', 'second'),
            $setting,
            array()
        );

        $setting = $this->mockCustomSql(
            array(
            'customsql' => 'SELECT id FROM tableName WHERE catids IN ({{param::get?name=ids&aggregate=set}})'
            ),
            'tableName'
        );

        $this->initializeContaoInputClass(array('ids' => array('1', '2')));

        $this->assertGeneratedSqlIs(
            'SELECT id FROM tableName WHERE catids IN (?)',
            array('1,2'),
            $setting,
            array()
        );
    }

    /**
     * Test with an empty string value.
     *
     * @return void
     */
    public function testWithEmptyStringValue()
    {
        $setting = $this->mockCustomSql(
            array(
                'customsql' => 'SELECT id FROM {{table}} WHERE otherparam={{param::filter?name=otherparam}}'
            ),
            'tableName'
        );

        $this->assertGeneratedSqlIs(
            'SELECT id FROM tableName WHERE otherparam=?',
            array(''),
            $setting,
            array('otherparam' => '')
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
            array(
                'customsql' => 'SELECT id FROM {{table}} WHERE otherparam={{param::filter?name=otherparam}}'
            ),
            'tableName'
        );

        $this->assertGeneratedSqlIs(
            'SELECT id FROM tableName WHERE otherparam=?',
            array(0),
            $setting,
            array('otherparam' => 0)
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
            array(
                'customsql' => 'SELECT id FROM {{table}} WHERE otherparam={{param::filter?name=otherparam}}'
            ),
            'tableName'
        );

        $this->assertGeneratedSqlIs(
            'SELECT id FROM tableName WHERE otherparam=NULL',
            array(),
            $setting,
            array('otherparam' => null)
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
            array(
                'customsql' => 'SELECT id FROM {{table}} WHERE otherparam={{param::filter?name=otherparam&default=xyz}}'
            ),
            'tableName'
        );

        $this->assertGeneratedSqlIs(
            'SELECT id FROM tableName WHERE otherparam=?',
            array('xyz'),
            $setting,
            array('otherparam' => null)
        );
    }

    private function initializeContaoInputClass(array $get = [], array $post = [], array $cookie = [])
    {
        if (!class_exists('\Config')) {
            class_alias('\Contao\Config', '\Config');
        }

        $_GET    = [];
        $_POST   = [];
        $_COOKIE = [];
        \Contao\Input::resetCache();
        foreach ($get as $key => $value) {
            \Contao\Input::setGet($key, $value);
        }
        foreach ($post as $key => $value) {
            \Contao\Input::setPost($key, $value);
        }
        foreach ($cookie as $key => $value) {
            \Contao\Input::setCookie($key, $value);
        }
    }
}
