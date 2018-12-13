<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2018 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
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
        $dataBase  = $metaModel->getServiceContainer()->getDatabase();

        $attribute = $this
            ->getMockForAbstractClass(
                'MetaModels\Attribute\Base',
                array(
                    $metaModel,
                    array(
                        'colname' => 'test1',
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
            ->with('*test*')
            ->will($this->returnValue(array(0, 1, 2, 3)));

        /** @var \MetaModels\Attribute\Base $attribute */
        $metaModel->addAttribute($attribute);

        $config = DefaultConfig::init();

        $config->setFilter(
            array(
                array(
                    'operation' => 'AND',
                    'children'  => array(
                        array(
                            'operation' => 'AND',
                            'children'  => array(
                                array(
                                    'operation' => 'LIKE',
                                    'property'  => 'test1',
                                    'value'     => '*test*'
                                )
                            )
                        ),
                    )
                )
            )
        );

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
