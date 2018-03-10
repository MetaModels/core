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

namespace MetaModels\Test;

use MetaModels\Events\CreateMetaModelEvent;
use MetaModels\Events\GetMetaModelNameFromIdEvent;
use MetaModels\Factory;
use MetaModels\Attribute\IAttributeFactory;
use MetaModels\IMetaModelsServiceContainer;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Test the attribute factory.
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
        $serviceContainer = $this
            ->getMockBuilder('MetaModels\IMetaModelsServiceContainer')
            ->getMockForAbstractClass();

        $serviceContainer
            ->expects($this->any())
            ->method('getEventDispatcher')
            ->will(
                $this->returnValue(
                    $mockEventDispatcher
                    ? $this
                        ->getMockBuilder('Symfony\Component\EventDispatcher\EventDispatcherInterface')
                        ->getMockForAbstractClass()
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
        $factory = $this->getMockBuilder('MetaModels\Attribute\IAttributeFactory')->getMockForAbstractClass();

        return $factory;
    }
}
