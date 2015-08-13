<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2015 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2015 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\Test\Helper;

use MetaModels\Helper\SubSystemBoot;
use MetaModels\MetaModelsEvents;
use MetaModels\MetaModelsServiceContainer;
use MetaModels\Test\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Test the SubSystemBoot class.
 */
class SubSystemBootTest extends TestCase
{
    /**
     * Test that getMode() returns empty string when no mode has been defined.
     *
     * @return void
     */
    public function testGetModeUndefined()
    {
        if (defined('TL_MODE')) {
            $this->markTestSkipped('TL_MODE is defined');
            return;
        }

        $boot    = new SubSystemBoot();
        $class   = new \ReflectionClass($boot);
        $getMode = $class->getMethod('getMode');
        $getMode->setAccessible(true);

        /** @var SubSystemBoot $boot */
        $this->assertEquals(
            '',
            $getMode->invoke($boot)
        );
    }

    /**
     * Test that getMode() returns the correct constant.
     *
     * @return void
     */
    public function testGetMode()
    {
        if (defined('TL_MODE')) {
            $this->markTestSkipped('TL_MODE is defined');
            return;
        }

        define('TL_MODE', 'TESTS');

        $boot    = new SubSystemBoot();
        $class   = new \ReflectionClass($boot);
        $getMode = $class->getMethod('getMode');
        $getMode->setAccessible(true);

        /** @var SubSystemBoot $boot */
        $this->assertEquals(
            'TESTS',
            $getMode->invoke($boot)
        );
    }

    /**
     * Test the booting in the frontend.
     *
     * @return void
     */
    public function testBootFrontend()
    {
        $dispatcher = $this->mockEventDispatcher(
            array(
                MetaModelsEvents::SUBSYSTEM_BOOT,
                MetaModelsEvents::SUBSYSTEM_BOOT_FRONTEND
            )
        );

        $environment = \Contao\Environment::getInstance();
        $container   = new MetaModelsServiceContainer();
        $container->setEventDispatcher($dispatcher);
        $environment::set('script', 'index.php');

        $boot = $this->getMock('MetaModels\Helper\SubSystemBoot', array('getMode', 'metaModelsTablesPresent'));
        $boot
            ->expects($this->any())
            ->method('getMode')
            ->will($this->returnValue('FE'));
        $boot
            ->expects($this->any())
            ->method('metaModelsTablesPresent')
            ->will($this->returnValue(true));

        $class   = new \ReflectionClass($boot);
        $getMode = $class->getMethod('getMode');
        $getMode->setAccessible(true);

        /** @var SubSystemBoot $boot */
        $this->assertEquals('FE', $getMode->invoke($boot));

        $boot->boot(
            new \Pimple(
                array(
                    'environment' => $environment,
                    'metamodels-service-container' => $container
                )
            )
        );
    }

    /**
     * Test the booting in the frontend.
     *
     * @return void
     */
    public function testBootBackend()
    {
        $dispatcher = $this->mockEventDispatcher(
            array(
                MetaModelsEvents::SUBSYSTEM_BOOT,
                MetaModelsEvents::SUBSYSTEM_BOOT_BACKEND
            )
        );

        $environment = \Contao\Environment::getInstance();
        $container   = new MetaModelsServiceContainer();
        $container->setEventDispatcher($dispatcher);
        $environment::set('script', 'contao/main.php');

        $boot = $this->getMock('MetaModels\Helper\SubSystemBoot', array('getMode', 'metaModelsTablesPresent'));
        $boot
            ->expects($this->any())
            ->method('getMode')
            ->will($this->returnValue('BE'));
        $boot
            ->expects($this->any())
            ->method('metaModelsTablesPresent')
            ->will($this->returnValue(true));

        $class   = new \ReflectionClass($boot);
        $getMode = $class->getMethod('getMode');
        $getMode->setAccessible(true);

        /** @var SubSystemBoot $boot */
        $this->assertEquals('BE', $getMode->invoke($boot));

        $boot->boot(
            new \Pimple(
                array(
                    'environment' => $environment,
                    'metamodels-service-container' => $container
                )
            )
        );
    }

    /**
     * Mock an event dispatcher.
     *
     * @param array $expectedEvents The names of the expected events.
     *
     * @return EventDispatcherInterface
     */
    protected function mockEventDispatcher($expectedEvents = array())
    {
        $eventDispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcher');

        if ($expectedEvents) {
            foreach ($expectedEvents as $index => $eventName) {
                $eventDispatcher
                    ->expects($this->at($index))
                    ->method('dispatch')
                    ->with($this->equalTo($eventName));
            }
        }

        return $eventDispatcher;
    }
}
