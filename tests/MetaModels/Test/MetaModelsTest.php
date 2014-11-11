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

namespace MetaModels\Test;

use MetaModels\MetaModel;

/**
 * Test the base attribute.
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

        foreach ($values as $key => $value) {
            $this->assertEquals($value, $metaModel->get($key), $key);
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
        $this->assertEquals('?', $reflection->invoke($metaModel, array(1)));
        $this->assertEquals('?,?', $reflection->invoke($metaModel, array(1,2)));
        $this->assertEquals('?,?,?,?,?,?', $reflection->invoke($metaModel, array(1, 2, 'fooo', 'bar', null, 'test')));
    }

    }
}


