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
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Test;

use MetaModels\IMetaModelsServiceContainer;
use MetaModels\Test\Contao\Database;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Test the services.php.
 */
class ServicesTest extends TestCase
{
    /**
     * Test the services file.
     *
     * @return void
     */
    public function testServices()
    {
        $container = new \Pimple();

        $container['event-dispatcher']    = new EventDispatcher();
        $container['database.connection'] = \Database::getInstance();
        $container['config']              = $this
            ->getMockBuilder('stdClass')
            ->setMethods(array('get'))
            ->getMock();
        $container['config']
            ->expects($this->any())
            ->method('get')
            ->with('bypassCache')
            ->will($this->returnValue(true));

        include_once __DIR__ . '/../../../contao/config/services.php';

        /** @var IMetaModelsServiceContainer $metaModelsContainer */
        $metaModelsContainer = $container['metamodels-service-container'];

        $this->assertInstanceOf('\Contao\Database', $metaModelsContainer->getDatabase());
        $this->assertInstanceOf('\MetaModels\Attribute\AttributeFactory', $metaModelsContainer->getAttributeFactory());
        $this->assertInstanceOf('\MetaModels\Factory', $metaModelsContainer->getFactory());
        $this->assertEquals($container['event-dispatcher'], $metaModelsContainer->getEventDispatcher());
    }
}
