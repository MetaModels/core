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
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
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
