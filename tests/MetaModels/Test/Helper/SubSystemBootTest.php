<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
 * @package    MetaModels
 * @subpackage Tests
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Test\Helper;

use ContaoCommunityAlliance\Contao\EventDispatcher\Event\CreateEventDispatcherEvent;
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
     * Test that getDatabase() returns the correct instance.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function testServiceContainer()
    {
        $boot   = new SubSystemBoot();
        $class  = new \ReflectionClass($boot);
        $method = $class->getMethod('getServiceContainer');
        $method->setAccessible(true);

        $GLOBALS['container']['metamodels-service-container'] = new MetaModelsServiceContainer();

        /** @var SubSystemBoot $boot */
        $this->assertEquals(
            $GLOBALS['container']['metamodels-service-container'],
            $method->invoke($boot)
        );
    }
    /**
     * Test that getDatabase() returns the correct instance.
     *
     * @return void
     */
    public function testGetDatabase()
    {
        $boot   = new SubSystemBoot();
        $class  = new \ReflectionClass($boot);
        $method = $class->getMethod('getDatabase');
        $method->setAccessible(true);

        /** @var SubSystemBoot $boot */
        $this->assertEquals(
            \Database::getInstance(),
            $method->invoke($boot)
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
            ),
            1
        );

        $container = new MetaModelsServiceContainer();
        $container->setEventDispatcher($dispatcher);

        $boot = $this->getMock('MetaModels\Helper\SubSystemBoot', array('getMode', 'getServiceContainer'));
        $boot
            ->expects($this->any())
            ->method('getMode')
            ->will($this->returnValue('FE'));
        $boot
            ->expects($this->any())
            ->method('getServiceContainer')
            ->will($this->returnValue($container));

        $class   = new \ReflectionClass($boot);
        $getMode = $class->getMethod('getMode');
        $getMode->setAccessible(true);

        /** @var SubSystemBoot $boot */
        $this->assertEquals('FE', $getMode->invoke($boot));

        $boot->boot(new CreateEventDispatcherEvent($dispatcher));
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
            ),
            1
        );

        $container = new MetaModelsServiceContainer();
        $container->setEventDispatcher($dispatcher);

        $boot = $this->getMock('MetaModels\Helper\SubSystemBoot', array('getMode', 'getServiceContainer'));
        $boot
            ->expects($this->any())
            ->method('getMode')
            ->will($this->returnValue('BE'));
        $boot
            ->expects($this->any())
            ->method('getServiceContainer')
            ->will($this->returnValue($container));

        $class   = new \ReflectionClass($boot);
        $getMode = $class->getMethod('getMode');
        $getMode->setAccessible(true);

        /** @var SubSystemBoot $boot */
        $this->assertEquals('BE', $getMode->invoke($boot));

        $boot->boot(new CreateEventDispatcherEvent($dispatcher));
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
