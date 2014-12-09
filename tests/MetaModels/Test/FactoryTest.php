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
use MetaModels\Attribute\IAttributeFactory;
use MetaModels\IMetaModelsServiceContainer;
use Symfony\Component\EventDispatcher\EventDispatcher;

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
        $serviceContainer = $this->mockServiceContainer(true);
        $factory          = new Factory();
        $factory->setServiceContainer($serviceContainer);

        $serviceContainer->getEventDispatcher()
            ->expects($this->exactly(1))
            ->method('dispatch')
            ->with($this->equalTo(CreateMetaModelEvent::NAME));

        $this->assertSame($serviceContainer, $factory->getServiceContainer());

        $factory->getMetaModel('mm_test');
    }

    /**
     * Test to add an attribute factory to a factory and retrieve it again.
     *
     * @return void
     */
    public function testGetMetaModelNameFromId()
    {
        $serviceContainer = $this->mockServiceContainer();
        $factory          = new Factory();
        $factory->setServiceContainer($serviceContainer);

        $serviceContainer->getEventDispatcher()->addListener(
            GetMetaModelNameFromIdEvent::NAME,
            function (GetMetaModelNameFromIdEvent $event) {
                $event->setMetaModelName('mm_with_id_' . $event->getMetaModelId());
            }
        );

        $this->assertSame('mm_with_id_10', $factory->translateIdToMetaModelName(10));

        $factory->getMetaModel('mm_test');
    }

    /**
     * Mock a service container.
     *
     * @param bool $mockEventDispatcher If true, a mock of the event dispatcher will be used, if false, a real instance.
     *
     * @return IMetaModelsServiceContainer
     */
    protected function mockServiceContainer($mockEventDispatcher = false)
    {
        $serviceContainer = $this->getMock('MetaModels\IMetaModelsServiceContainer');

        $serviceContainer
            ->expects($this->any())
            ->method('getEventDispatcher')
            ->will(
                $this->returnValue(
                    $mockEventDispatcher
                    ? $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface')
                    : new EventDispatcher()
                )
            );

        return $serviceContainer;
    }

    /**
     * Mock an attribute factory.
     *
     * @return IAttributeFactory
     */
    protected function mockAttributeFactory()
    {
        $factory = $this->getMock('MetaModels\Attribute\IAttributeFactory');

        return $factory;
    }
}
