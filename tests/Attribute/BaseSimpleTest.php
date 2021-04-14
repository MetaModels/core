<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2021 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2021 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Test\Attribute;

use Doctrine\DBAL\Connection;
use MetaModels\Attribute\BaseSimple;
use MetaModels\Attribute\IAttribute;
use MetaModels\Attribute\ISimple;
use MetaModels\Helper\TableManipulator;
use MetaModels\IMetaModel;
use PHPUnit\Framework\TestCase;

/**
 * Class BaseSimpleTest
 *
 * @covers \MetaModels\Attribute\BaseSimple
 */
class BaseSimpleTest extends TestCase
{
    /**
     * Test the instantiation of the base simple attribute.
     *
     * @return void
     */
    public function testInstantiation()
    {
        $metaModel   = $this->getMockBuilder(IMetaModel::class)->getMock();
        $connection  = $this->getMockBuilder(Connection::class)->disableOriginalConstructor()->getMock();
        $manipulator = $this->getMockBuilder(TableManipulator::class)->disableOriginalConstructor()->getMock();

        $attribute = new BaseSimple($metaModel, [], $connection, $manipulator);

        self::assertInstanceOf(BaseSimple::class, $attribute);
        self::assertInstanceOf(IAttribute::class, $attribute);
        self::assertInstanceOf(ISimple::class, $attribute);
    }
}
