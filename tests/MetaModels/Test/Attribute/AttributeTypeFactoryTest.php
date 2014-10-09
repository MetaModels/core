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

namespace MetaModels\Test\Attribute;

use MetaModels\Attribute\IAttributeTypeFactory;
use MetaModels\Test\TestCase;

/**
 * Test an attribute factory.
 *
 * Extend from this class when testing IAttributeTypeFactory derivatives by defining the class property.
 */
class AttributeTypeFactoryTest extends TestCase
{
    /**
     * Override this method to run the tests on the attribute factory to be tested.
     *
     * @return IAttributeTypeFactory[]
     */
    protected function getAttributeFactories()
    {
        return array();
    }

    /**
     * Test that the factory information makes sense - a type is exactly either translated, simple or complex.
     *
     * @param IAttributeTypeFactory $attributeFactory The attribute type factory to test.
     *
     * @return void
     */
    public function attributeTypeInformationMakesSense($attributeFactory)
    {
        $this->assertTrue(
            $attributeFactory->isTranslatedType()
            || $attributeFactory->isSimpleType()
            || $attributeFactory->isComplexType(),
            $attributeFactory->getTypeName() . ' is neither simple, complex nor translated. But must exactly one.'
        );
    }

    /**
     * Tests the defined IAttributeTypeFactory.
     *
     * @return void
     */
    public function testAttributeFactory()
    {
        // It does not make sense to test this very class.
        if (get_class($this) == __CLASS__) {
            return;
        }

        $attributeFactories = $this->getAttributeFactories();
        if (!$attributeFactories) {
            $this->markTestSkipped('No factories to test. Skipping test ' . get_class($this));
        }

        foreach ($attributeFactories as $attributeFactory) {
            $this->attributeTypeInformationMakesSense($attributeFactory);
        }
    }
}
