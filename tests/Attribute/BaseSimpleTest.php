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
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2018 The MetaModels team.
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
 * @package MetaModels\Test\Attribute
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

        $this->assertInstanceOf(BaseSimple::class, $attribute);
        $this->assertInstanceOf(IAttribute::class, $attribute);
        $this->assertInstanceOf(ISimple::class, $attribute);
    }
}
