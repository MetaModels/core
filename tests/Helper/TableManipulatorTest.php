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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2021 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Test\Helper;

use Doctrine\DBAL\Connection;
use MetaModels\Helper\TableManipulator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class TableManipulatorTest
 *
 * @covers \MetaModels\Helper\TableManipulator
 */
class TableManipulatorTest extends TestCase
{
    /**
     * System columns.
     *
     * @var list<string>
     */
    private array $systemColumns = [
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
     */
    private function createTableManipulator(?Connection $connection = null): TableManipulator
    {
        $connection = $connection ?: $this->mockConnection();

        return new TableManipulator($connection, $this->systemColumns);
    }

    /**
     * Mock the database connection.
     */
    private function mockConnection(): Connection&MockObject
    {
        $connection  = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->getMock();

        return $connection;
    }

    /**
     * Test the instantiation.
     */
    public function testInstantiation(): void
    {
        $manipulator = $this->createTableManipulator();

        self::assertInstanceOf(TableManipulator::class, $manipulator);
    }

    /**
     * Tests the reserved words.
     */
    public function testReservedWords(): void
    {
        $property = new \ReflectionProperty(TableManipulator::class, 'reservedWords');
        $property->setAccessible(true);

        $manipulator   = $this->createTableManipulator();
        $reservedWords = $property->getValue();

        foreach ($reservedWords as $word) {
            self::assertTrue($manipulator->isReservedWord($word));
        }

        foreach (['foo', 'bar'] as $word) {
            self::assertFalse($manipulator->isReservedWord($word));
        }
    }
}
