<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2019 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Test\Helper;

use Doctrine\DBAL\Connection;
use MetaModels\Helper\TableManipulator;
use PHPUnit\Framework\TestCase;

/**
 * Class TableManipulatorTest
 */
class TableManipulatorTest extends TestCase
{
    /**
     * System columns.
     *
     * @var array
     */
    private $systemColumns = [
        'id',
        'pid',
        'sorting',
        'tstamp',
        'vargroup',
        'varbase'
    ];

    /**
     * Create the table manipulator.
     *
     * @param Connection|null $connection Optional pass a connection mock.
     *
     * @return TableManipulator
     */
    private function createTableManipulator(Connection $connection = null)
    {
        $connection = $connection ?: $this->mockConnection();

        return new TableManipulator($connection, $this->systemColumns);
    }

    /**
     * Mock the database connection.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|Connection
     */
    private function mockConnection()
    {
        $connection  = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $connection;
    }

    /**
     * Test the instantiation.
     *
     * @return void
     */
    public function testInstantiation()
    {
        $manipulator = $this->createTableManipulator();

        $this->assertInstanceOf(TableManipulator::class, $manipulator);
    }

    /**
     * Tests the reserved words.
     *
     * @return void
     */
    public function testReservedWords()
    {
        $property = new \ReflectionProperty(TableManipulator::class, 'reservedWords');
        $property->setAccessible(true);

        $manipulator   = $this->createTableManipulator();
        $reservedWords = $property->getValue(TableManipulator::class);

        foreach ($reservedWords as $word) {
            $this->assertTrue($manipulator->isReservedWord($word));
        }

        foreach (['foo', 'bar'] as $word) {
            $this->assertFalse($manipulator->isReservedWord($word));
        }
    }
}
