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

use MetaModels\Filter\Setting\CustomSql;

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
        $setting = $this->getMock(
            'MetaModels\Filter\Setting\CustomSql',
            array('parseInsertTags'),
            array($this->mockFilterSetting($tableName), $properties)
        );

        $setting
            ->expects($this->any())
            ->method('parseInsertTags')
            ->will($this->returnCallback(function ($sql){
                return str_replace(
                    array('{{', '::', '}}'),
                    '__',
                    $sql
                );
            }));

        return $setting;
    }

    /**
     * Internal convenience method to call the protected generateSql method on the customSql instance.
     *
     * @param CustomSql $instance  The instance.
     * @param array     $params    The result parameter array.
     * @param array     $filterUrl The filter url to process.
     *
     * @return mixed
     */
    protected function generateSql($instance, &$params, $filterUrl = array())
    {
        return $instance->generateSql($params, $filterUrl);
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

        $params = array();

        $sql = $this->generateSql($setting, $params);

        $this->assertEquals('SELECT id FROM mm_mymetamodel WHERE page_id=1', $sql);
        $this->assertEquals(array(), $params);
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

        $params = array();

        $sql = $this->generateSql($setting, $params);

        $this->assertEquals('SELECT id FROM tableName WHERE page_id=1', $sql);
        $this->assertEquals(array(), $params);
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

        $params = array();

        $sql = $this->generateSql($setting, $params);

        $this->assertEquals('SELECT id FROM tableName WHERE page_id=__page__id__', $sql);
        $this->assertEquals(array(), $params);
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

        $params = array();

        $sql = $this->generateSql($setting, $params);

        $this->assertEquals('SELECT id FROM tableName WHERE page_id=?', $sql);
        $this->assertEquals(array('__page__id__'), $params);
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
        $params = array();
        $sql    = $this->generateSql($setting, $params);
        $this->assertEquals(
            'SELECT id FROM tableName WHERE catname=?',
            $sql,
            'See https://github.com/MetaModels/core/issues/376'
        );
        $this->assertEquals(
            array('defaultcat'),
            $params,
            'See https://github.com/MetaModels/core/issues/376'
        );

        $this->initializeContaoInputClass(array('category' => 'category name'));
        $params = array();
        $sql    = $this->generateSql($setting, $params);
        $this->assertEquals('SELECT id FROM tableName WHERE catname=?', $sql);
        $this->assertEquals(array('category name'), $params);
    }
}
