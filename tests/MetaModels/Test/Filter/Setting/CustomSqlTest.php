<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package    MetaModels
 * @subpackage Tests
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Test\Filter\Setting;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\ReplaceInsertTagsEvent;
use MetaModels\Filter\Filter;
use MetaModels\Filter\Setting\CustomSql;

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

        $this->initializeContaoInputClass();
        $this->initializeContaoSessionClass();

        $filterSetting = $this->mockFilterSetting($tableName);
        $filterSetting->getMetaModel()->getServiceContainer()->getEventDispatcher()->addListener(
            ContaoEvents::CONTROLLER_REPLACE_INSERT_TAGS,
            function (ReplaceInsertTagsEvent $event) {
                $event->setBuffer(str_replace(array('{{', '::', '}}'), '__', $event->getBuffer()));
            }
        );

        $setting = new CustomSql($filterSetting, $properties);

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
        $filter = new Filter($instance->getMetaModel());

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
     * Test variable replacement via session value.
     *
     * @return void
     */
    public function testValueFromServiceContainer()
    {

        $setting = $this->mockCustomSql(
            array(
                'customsql' => 'SELECT id FROM tableName WHERE catname={{param::container?name=category&foo=bar}}'
            ),
            'tableName'
        );

        $this->initializeContaoInputClass();
        $this->initializeContaoSessionClass();
        $setting->getMetaModel()->getServiceContainer()->setService(
            function ($name, $arguments) {
                return $name . ' ' . $arguments['foo'];
            },
            'category'
        );

        $this->assertGeneratedSqlIs(
            'SELECT id FROM tableName WHERE catname=?',
            array('category bar'),
            $setting,
            array()
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
}
