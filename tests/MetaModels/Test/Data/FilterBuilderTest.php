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

namespace MetaModels\Test\Data;

use ContaoCommunityAlliance\DcGeneral\Data\DefaultConfig;
use MetaModels\DcGeneral\Data\FilterBuilder;
use MetaModels\IMetaModel;
use MetaModels\MetaModel;
use MetaModels\MetaModelsServiceContainer;
use MetaModels\Test\Contao\Database;
use MetaModels\Test\TestCase;

/**
 * Test the filter builder.
 */
class FilterBuilderTest extends TestCase
{
    /**
     * Mock a MetaModel instance.
     *
     * @return IMetaModel
     */
    private function mockMetaModel()
    {
        $dataBase         = Database::getNewTestInstance();
        $serviceContainer = new MetaModelsServiceContainer();
        $serviceContainer->setDatabase($dataBase);

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

        $metaModel->setServiceContainer($serviceContainer);

        return $metaModel;
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

        $config->setFilter(array(
        ));

        $builder = new FilterBuilder($metaModel, $config);

        $filter = $builder->build();

        $this->assertNull($filter->getMatchingIds());
    }

    /**
     * Test the build process.
     *
     * @return void
     */
    public function testBuildSqlOnly()
    {
        $metaModel = $this->mockMetaModel();
        $dataBase  = $metaModel->getServiceContainer()->getDatabase();

        $attribute = $this
            ->getMockForAbstractClass(
                'MetaModels\Attribute\Base',
                array(
                    $metaModel,
                    array(
                        'colname'      => 'test1',
                    )
                ),
                '',
                true,
                true,
                true,
                array('searchFor')
            );
        $attribute
            ->expects($this->any())
            ->method('searchFor')
            ->with('abc')
            ->will($this->returnValue(array(0, 1, 2, 3)));

        /** @var \MetaModels\Attribute\Base $attribute */
        $metaModel->addAttribute($attribute);

        $config = DefaultConfig::init();

        $config->setFilter(array(
            array(
                'operation' => '=',
                'property'  => 'foo',
                'value'     => 0
            ),
            array(
                'operation' => '=',
                'property'  => 'test1',
                'value'     => 'abc'
            )
        ));

        /** @var Database $dataBase */
        $dataBase
            ->getQueryCollection()
            ->theQuery('SELECT id FROM mm_test WHERE ((foo = ?))')
            ->with(0)
            ->result()
            ->addRows(array(
                array('id' => 0),
                array('id' => 1),
                array('id' => 2),
                array('id' => 3),
                array('id' => 4),
                array('id' => 5),
            ));

        $builder = new FilterBuilder($metaModel, $config);

        $filter = $builder->build();

        $this->assertEquals(array(0, 1, 2, 3), $filter->getMatchingIds());
    }
}
