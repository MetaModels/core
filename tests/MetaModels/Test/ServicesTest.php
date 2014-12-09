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
