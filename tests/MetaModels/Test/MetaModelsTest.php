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
use MetaModels\Test\Contao\Database;

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
        $this->assertEmpty($metaModel->getAttributes());

        foreach ($values as $key => $value) {
            $this->assertEquals($value, $metaModel->get($key), $key);
        }

        $metaModel = new MetaModel($values);

        foreach ($values as $key => $value) {
            $this->assertEquals($value, $metaModel->get($key), $key);
        }
    }

    /**
     * Test method MetaModel::fetchRows.
     *
     * @return void
     */
    public function testFetchRows()
    {
        $metaModel = new MetaModel(array(
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
        ));

        $rows = array(
            1 => array(
                'id'     => 1,
                'tstamp' => 343094400,
            ),
            2 => array(
                'id'     => 2,
                'tstamp' => 343094400,
            ),
        );

        $database = Database::getNewTestInstance();
        $metaModel->setDatabase($database);

        $database
            ->getQueryCollection()
            ->theQuery('SELECT * FROM mm_test WHERE id IN (?,?) ORDER BY FIELD(id,?,?)')
            ->with(1, 2, 1, 2)
            ->result()
                ->addRows($rows);

        $reflection = new \ReflectionMethod($metaModel, 'fetchRows');
        $reflection->setAccessible(true);

        $result = $reflection->invoke($metaModel, array(1,2));

        $this->assertEquals($result, $rows);
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

    /**
     * Ensure the system columns are present. See issue #196.
     *
     * @return void
     */
    public function testRetrieveSystemColumns()
    {
        $metaModel = new MetaModel(array(
            'id'         => '1',
            'sorting'    => '256',
            'tstamp'     => '1367274071',
            'name'       => 'Test RetrieveSystemColumns',
            'tableName'  => 'mm_test_retrieve',
            'translated' => '1',
            'languages'  => 'a:2:{s:2:"en";a:1:{s:10:"isfallback"; s:1:"1";}s:2:"de"; a:1:{s:10:"isfallback";s:0:"";}}',
            'varsupport' => '',
        ));

        $rows = array(
            1 => array(
                'id'      => 1,
                'pid'     => 0,
                'sorting' => 1,
                'tstamp'  => 343094400,
            ),
        );

        $database = Database::getNewTestInstance();
        $metaModel->setDatabase($database);

        $database
            ->getQueryCollection()
            ->theQuery('SELECT * FROM mm_test_retrieve WHERE id IN (?) ORDER BY FIELD(id,?)')
            ->with(1, 1)
            ->result()
            ->addRows($rows);

        $this->assertEquals($metaModel->getName(), 'Test RetrieveSystemColumns');

        $item = $metaModel->findById(1);

        $this->assertEquals(1, $item->get('id'));
        $this->assertEquals(0, $item->get('pid'));
        $this->assertEquals(1, $item->get('sorting'));
        $this->assertEquals(343094400, $item->get('tstamp'));
        $this->assertNull($item->get('varbase'));
        $this->assertNull($item->get('vargroup'));
    }

    /**
     * Ensure the buildDatabaseParameterList works correctly.
     *
     * @return void
     */
    public function testGetIdsFromFilter()
    {
        $metaModel = $this->getMock(
            'MetaModels\MetaModel',
            array('getMatchingIds'),
            array(array('tableName'  => 'mm_test_retrieve'))
        );
        $metaModel
            ->expects($this->any())
            ->method('getMatchingIds')
            ->will($this->returnValue(array(4, 3, 2, 1)));

        /** @var MetaModel $metaModel */
        $database = Database::getNewTestInstance();
        $metaModel->setDatabase($database);

        $database
            ->getQueryCollection()
            ->theQuery('SELECT id FROM mm_test_retrieve WHERE id IN(?,?,?,?) ORDER BY id ASC')
            ->with(4, 3, 2, 1)
            ->result()
            ->addRow(array('id' => 1))
            ->addRow(array('id' => 2))
            ->addRow(array('id' => 3))
            ->addRow(array('id' => 4));

        $this->assertEquals(array(1,2,3,4), $metaModel->getIdsFromFilter($metaModel->getEmptyFilter(), 'id'));
        $this->assertEquals(array(1,2), $metaModel->getIdsFromFilter($metaModel->getEmptyFilter(), 'id', 0, 2));
        $this->assertEquals(array(3,4), $metaModel->getIdsFromFilter($metaModel->getEmptyFilter(), 'id', 2, 2));
        $this->assertEquals(array(3), $metaModel->getIdsFromFilter($metaModel->getEmptyFilter(), 'id', 2, 1));
        $this->assertEquals(array(), $metaModel->getIdsFromFilter($metaModel->getEmptyFilter(), 'id', 20, 0));
        $this->assertEquals(array(2,3,4), $metaModel->getIdsFromFilter($metaModel->getEmptyFilter(), 'id', 1, 10));
    }

    /**
     * Ensure the getCount works correctly.
     *
     * @return void
     */
    public function testGetCount()
    {
        $metaModel = $this->getMock(
            'MetaModels\MetaModel',
            array('getMatchingIds'),
            array(array('tableName'  => 'mm_test_retrieve'))
        );
        $metaModel
            ->expects($this->any())
            ->method('getMatchingIds')
            ->will($this->returnValue(array()));
        $this->assertEquals(0, $metaModel->getCount($metaModel->getEmptyFilter()));

        $metaModel = $this->getMock(
            'MetaModels\MetaModel',
            array('getMatchingIds'),
            array(array('tableName'  => 'mm_test_retrieve'))
        );
        $metaModel
            ->expects($this->any())
            ->method('getMatchingIds')
            ->will($this->returnValue(array(4, 3, 2, 1)));

        /** @var MetaModel $metaModel */
        $database = Database::getNewTestInstance();
        $metaModel->setDatabase($database);

        $database
            ->getQueryCollection()
            ->theQuery('SELECT COUNT(id) AS count FROM mm_test_retrieve WHERE id IN(?,?,?,?)')
            ->with(4, 3, 2, 1)
            ->result()
            ->addRow(array('count' => 4));

        $this->assertEquals(4, $metaModel->getCount($metaModel->getEmptyFilter()));
    }
}
