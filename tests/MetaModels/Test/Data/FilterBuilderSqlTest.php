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
use MetaModels\DcGeneral\Data\FilterBuilderSql;
use MetaModels\MetaModel;
use MetaModels\Test\Contao\Database;
use MetaModels\Test\TestCase;

/**
 * Test the filter builder.
 */
class FilterBuilderSqlTest extends TestCase
{
    /**
     * Test the build process.
     *
     * @return void
     */
    public function testBuild()
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

        /** @var \MetaModels\Attribute\Base $attribute */
        $attribute = $this
            ->getMockForAbstractClass(
                'MetaModels\Attribute\Base',
                array(
                    $metaModel,
                    array(
                        'colname'      => 'test1',
                    )
                )
            );

        $metaModel->addAttribute($attribute);

        $config = DefaultConfig::init();

        $config->setFilter(array(
            array(
                'operation' => '=',
                'property'  => 'foo',
                'value'     => 0
            )
        ));

        $dataBase = Database::getNewTestInstance();
        $builder  = new FilterBuilderSql($metaModel->getTableName(), 'AND', $dataBase);

        $dataBase
            ->getQueryCollection()
            ->theQuery('SELECT id FROM mm_test WHERE ((test = ?))')
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

        $this->assertTrue($builder->isEmpty());

        $this->assertEquals(
            $builder,
            $builder->addChild(array('operation' => '=', 'property' => 'test', 'value' => 0))
        );

        $this->assertEquals(array(0, 1, 2, 3, 4, 5), $builder->build()->getMatchingIds());
    }
}
