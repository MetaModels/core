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

namespace MetaModels\Test\Attribute;

use MetaModels\Attribute\AttributeFactory;
use MetaModels\Attribute\IAttributeTypeFactory;
use MetaModels\Attribute\IAttributeFactory;
use MetaModels\IMetaModelsServiceContainer;
use MetaModels\MetaModelsEvents;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Test the attribute factory.
 *
 * @covers \MetaModels\Attribute\AttributeFactory
 */
class AttributeFactoryTest extends TestCase
{
    /**
     * Test that the attribute factory creation fires an event.
     *
     * @return void
     */
    public function testCreateFactoryFiresEvent()
    {
        $serviceContainer = $this->getMockForAbstractClass(IMetaModelsServiceContainer::class);

        $eventDispatcher = $this->getMockForAbstractClass(EventDispatcherInterface::class);
        $eventDispatcher
            ->expects($this->exactly(1))
            ->method('dispatch')
            ->with($this->equalTo(MetaModelsEvents::ATTRIBUTE_FACTORY_CREATE));
        $eventDispatcher
            ->expects($this->exactly(1))
            ->method('hasListeners')
            ->with($this->equalTo(MetaModelsEvents::ATTRIBUTE_FACTORY_CREATE))
            ->willReturn(true);

        $factory = new AttributeFactory($eventDispatcher);
        $factory->setServiceContainer($serviceContainer);

        $this->assertSame($serviceContainer, $factory->getServiceContainer());
    }

    /**
     * Test to add an attribute factory to a factory and retrieve it again.
     *
     * @return void
     */
    public function testAddTypeFactoryAndGetTypeFactory()
    {
        $factory = new AttributeFactory($this->getMockForAbstractClass(EventDispatcherInterface::class));

        $this->assertNull($factory->getTypeFactory('test'));
        $attributeFactory = $this->mockAttributeFactory('test', true, false, false);

        $this->assertSame(
            $factory,
            $factory->addTypeFactory($attributeFactory)
        );

        $this->assertSame($attributeFactory, $factory->getTypeFactory('test'));
    }

    /**
     * Test a single attribute type mock.
     *
     * @param IAttributeFactory $factory          The factory to test.
     *
     * @param string            $attributeFactory The attribute type factory to test.
     *
     * @param bool              $shouldTranslated Flag if the attribute factory should say the type is translated.
     *
     * @param bool              $shouldSimple     Flag if the attribute factory should say the type is simple.
     *
     * @param bool              $shouldComplex    Flag if the attribute factory should say the type is complex.
     *
     * @return void
     */
    protected function mockFactoryTester($factory, $attributeFactory, $shouldTranslated, $shouldSimple, $shouldComplex)
    {
        $this->assertSame(
            true,
            $factory->attributeTypeMatchesFlags(
                $attributeFactory,
                IAttributeFactory::FLAG_ALL
            ),
            $attributeFactory . '.FLAG_ALL'
        );

        $this->assertSame(
            $shouldTranslated,
            $factory->attributeTypeMatchesFlags(
                $attributeFactory,
                IAttributeFactory::FLAG_INCLUDE_TRANSLATED
            ),
            $attributeFactory . '.FLAG_INCLUDE_TRANSLATED'
        );

        $this->assertSame(
            $shouldSimple,
            $factory->attributeTypeMatchesFlags(
                $attributeFactory,
                IAttributeFactory::FLAG_INCLUDE_SIMPLE
            ),
            $attributeFactory . '.FLAG_INCLUDE_SIMPLE'
        );

        $this->assertSame(
            $shouldComplex,
            $factory->attributeTypeMatchesFlags(
                $attributeFactory,
                IAttributeFactory::FLAG_INCLUDE_COMPLEX
            ),
            $attributeFactory . '.FLAG_INCLUDE_COMPLEX'
        );
    }

    /**
     * Test that the method attributeTypeMatchesFlags() works correctly.
     *
     * @return void
     */
    public function testAttributeTypeMatchesFlags()
    {
        $factory = new AttributeFactory($this->getMockForAbstractClass(EventDispatcherInterface::class));
        $factory->addTypeFactory($this->mockAttributeFactory('test_translated', true, false, false));
        $factory->addTypeFactory($this->mockAttributeFactory('test_simple', false, true, false));
        $factory->addTypeFactory($this->mockAttributeFactory('test_complex', false, false, true));
        $factory->addTypeFactory($this->mockAttributeFactory('test_simplecomplex', false, true, true));
        $factory->addTypeFactory($this->mockAttributeFactory('test_translatedsimple', true, true, false));
        $factory->addTypeFactory($this->mockAttributeFactory('test_translatedcomplex', true, false, true));
        $factory->addTypeFactory($this->mockAttributeFactory('test_translatedsimplecomplex', true, true, true));

        $this->mockFactoryTester($factory, 'test_translated', true, false, false);
        $this->mockFactoryTester($factory, 'test_simple', false, true, false);
        $this->mockFactoryTester($factory, 'test_complex', false, false, true);
        $this->mockFactoryTester($factory, 'test_simplecomplex', false, true, true);
        $this->mockFactoryTester($factory, 'test_translatedsimple', true, true, false);
        $this->mockFactoryTester($factory, 'test_translatedcomplex', true, false, true);
        $this->mockFactoryTester($factory, 'test_translatedsimplecomplex', true, true, true);
    }

    /**
     * Test that the method attributeTypeMatchesFlags() works correctly.
     *
     * @return void
     */
    public function testGetTypeNames()
    {
        $factory = new AttributeFactory($this->getMockForAbstractClass(EventDispatcherInterface::class));

        $this->assertSame(
            array(),
            $factory->getTypeNames(IAttributeFactory::FLAG_ALL),
            'FLAG_ALL'
        );

        $this->assertSame(
            array(),
            $factory->getTypeNames(IAttributeFactory::FLAG_INCLUDE_TRANSLATED),
            'FLAG_INCLUDE_TRANSLATED'
        );

        $this->assertSame(
            array(),
            $factory->getTypeNames(IAttributeFactory::FLAG_INCLUDE_SIMPLE),
            'FLAG_INCLUDE_SIMPLE'
        );

        $this->assertSame(
            array(),
            $factory->getTypeNames(IAttributeFactory::FLAG_INCLUDE_COMPLEX),
            'FLAG_INCLUDE_COMPLEX'
        );

        $this->assertSame(
            array(),
            $factory->getTypeNames(IAttributeFactory::FLAG_ALL_UNTRANSLATED),
            'FLAG_ALL_UNTRANSLATED'
        );

        $factory->addTypeFactory($this->mockAttributeFactory('test_translated', true, false, false));
        $factory->addTypeFactory($this->mockAttributeFactory('test_simple', false, true, false));
        $factory->addTypeFactory($this->mockAttributeFactory('test_complex', false, false, true));
        $factory->addTypeFactory($this->mockAttributeFactory('test_simplecomplex', false, true, true));
        $factory->addTypeFactory($this->mockAttributeFactory('test_translatedsimple', true, true, false));
        $factory->addTypeFactory($this->mockAttributeFactory('test_translatedcomplex', true, false, true));
        $factory->addTypeFactory($this->mockAttributeFactory('test_translatedsimplecomplex', true, true, true));

        $this->assertSame(
            array(
                'test_translated',
                'test_simple',
                'test_complex',
                'test_simplecomplex',
                'test_translatedsimple',
                'test_translatedcomplex',
                'test_translatedsimplecomplex',
            ),
            $factory->getTypeNames(IAttributeFactory::FLAG_ALL),
            'FLAG_ALL'
        );

        $this->assertSame(
            array(
                'test_translated',
                'test_translatedsimple',
                'test_translatedcomplex',
                'test_translatedsimplecomplex',
            ),
            $factory->getTypeNames(IAttributeFactory::FLAG_INCLUDE_TRANSLATED),
            'FLAG_INCLUDE_TRANSLATED'
        );

        $this->assertSame(
            array(
                'test_simple',
                'test_simplecomplex',
                'test_translatedsimple',
                'test_translatedsimplecomplex',
            ),
            $factory->getTypeNames(IAttributeFactory::FLAG_INCLUDE_SIMPLE),
            'FLAG_INCLUDE_SIMPLE'
        );

        $this->assertSame(
            array(
                'test_complex',
                'test_simplecomplex',
                'test_translatedcomplex',
                'test_translatedsimplecomplex',
            ),
            $factory->getTypeNames(IAttributeFactory::FLAG_INCLUDE_COMPLEX),
            'FLAG_INCLUDE_COMPLEX'
        );

        $this->assertSame(
            array(
                'test_simple',
                'test_complex',
                'test_simplecomplex',
                'test_translatedsimple',
                'test_translatedcomplex',
                'test_translatedsimplecomplex',
            ),
            $factory->getTypeNames(IAttributeFactory::FLAG_ALL_UNTRANSLATED),
            'FLAG_ALL_UNTRANSLATED'
        );
    }

    /**
     * Test the icon retrieval.
     *
     * @return void
     */
    public function testGetTypeIcon()
    {
        $factory     = new AttributeFactory($this->getMockForAbstractClass(EventDispatcherInterface::class));
        $typeFactory = $this->mockAttributeFactory('test', true, false, false, new \stdClass, 'icon.png');
        $factory->addTypeFactory($typeFactory);

        $this->assertEquals($typeFactory->getTypeIcon(), $factory->getIconForType('test'));
    }

    /**
     * Mock an attribute type factory.
     *
     * @param string $typeName   The type name to mock.
     *
     * @param bool   $translated Flag if the type shall be translated.
     *
     * @param bool   $simple     Flag if the type shall be simple.
     *
     * @param bool   $complex    Flag if the type shall be complex.
     *
     * @param string $class      Name of the class to instantiate when createInstance() is called.
     *
     * @param string $typeIcon   The icon of the type to mock.
     *
     * @return IAttributeTypeFactory
     */
    protected function mockAttributeFactory(
        $typeName,
        $translated,
        $simple,
        $complex,
        $class = 'stdClass',
        $typeIcon = 'icon.png'
    ) {
        $mockTypeFactory = $this->getMockForAbstractClass(IAttributeTypeFactory::class);

        $mockTypeFactory
            ->expects($this->any())
            ->method('getTypeName')
            ->willReturnCallback(function () use ($typeName) {
                return $typeName;
            });
        $mockTypeFactory
            ->expects($this->any())
            ->method('getTypeIcon')
            ->willReturnCallback(function () use ($typeIcon) {
                return $typeIcon;
            });
        $mockTypeFactory
            ->expects($this->any())
            ->method('createInstance')
            ->willReturnCallback(function ($information, $metaModel) use ($class) {
                return new $class($information, $metaModel);
            });
        $mockTypeFactory
            ->expects($this->any())
            ->method('isTranslatedType')
            ->willReturnCallback(function () use ($translated) {
                return $translated;
            });
        $mockTypeFactory
            ->expects($this->any())
            ->method('isSimpleType')
            ->willReturnCallback(function () use ($simple) {
                return $simple;
            });
        $mockTypeFactory
            ->expects($this->any())
            ->method('isComplexType')
            ->willReturnCallback(function () use ($complex) {
                return $complex;
            });

        return $mockTypeFactory;
    }
}
