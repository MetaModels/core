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

namespace MetaModels\Test;

use MetaModels\Events\CreateMetaModelEvent;
use MetaModels\Events\GetMetaModelNameFromIdEvent;
use MetaModels\Factory;
use MetaModels\Attribute\IFactory as IAttributeFactory;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Test the attribute factory.
 *
 * @package MetaModels\Test\Filter\Setting
 */
class FactoryTest extends TestCase
{
    /**
     * Test to add an attribute factory to a factory and retrieve it again.
     *
     * @return void
     */
    public function testCreateMetaModelFiresEvent()
    {
        $eventDispatcher = $this->mockEventDispatcher(CreateMetaModelEvent::NAME, 1);
        $factory         = new Factory($eventDispatcher, $this->mockAttributeFactory());

        $this->assertSame($eventDispatcher, $factory->getEventDispatcher());

        $factory->getMetaModel('mm_test');

    }

    /**
     * Test to add an attribute factory to a factory and retrieve it again.
     *
     * @return void
     */
    public function testGetMetaModelNameFromId()
    {
        $eventDispatcher = new EventDispatcher();
        $factory         = new Factory($eventDispatcher, $this->mockAttributeFactory());

        $eventDispatcher->addListener(
            GetMetaModelNameFromIdEvent::NAME,
            function (GetMetaModelNameFromIdEvent $event) {
                $event->setMetaModelName('mm_with_id_' . $event->getMetaModelId());
            }
        );

        $this->assertSame($eventDispatcher, $factory->getEventDispatcher());
        $this->assertSame('mm_with_id_10', $factory->translateIdToMetaModelName(10));

        $factory->getMetaModel('mm_test');

    }


    /**
     * Mock an event dispatcher.
     *
     * @param string $expectedEvent The name of the expected event.
     *
     * @param int    $expectedCount The amount how often this event shall get dispatched.
     *
     * @return EventDispatcherInterface
     */
    protected function mockEventDispatcher($expectedEvent = '', $expectedCount = 0)
    {
        $eventDispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcher');

        if ($expectedEvent) {
            $eventDispatcher
                ->expects($this->exactly($expectedCount))
                ->method('dispatch')
                ->with($this->equalTo($expectedEvent));
        }

        return $eventDispatcher;
    }

    /**
     * Mock an attribute factory.
     *
     * @return IAttributeFactory
     */
    protected function mockAttributeFactory()
    {
        $factory = $this->getMock('MetaModels\Attribute\IFactory');

        return $factory;
    }
}
