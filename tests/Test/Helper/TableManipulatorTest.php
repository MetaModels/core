<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2017 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\Test\Helper;

use Doctrine\DBAL\Connection;
use MetaModels\Helper\TableManipulator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class TableManipulatorTest
 *
 * @package MetaModels\Test\Helper
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
     * @param Connection|null          $connection Optional pass a connection mock.
     * @param TranslatorInterface|null $translator Optional pass a translator.
     *
     * @return TableManipulator
     */
    private function createTableManipulator(Connection $connection = null, TranslatorInterface $translator = null)
    {
        $connection = $connection ?: $this->mockConnection();
        $translator = $translator ?: $this->mockTranslator();

        return new TableManipulator($connection, $translator, $this->systemColumns);
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
     * Mock the translator.
     *
     * @return \PHPUnit_Framework_MockObject_MockObject|TranslatorInterface
     */
    private function mockTranslator()
    {
        return $this
            ->getMockBuilder(TranslatorInterface::class)
            ->getMock();
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
