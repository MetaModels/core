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
use MetaModels\Test\Attribute\Mock\AttributeFactoryMocker;

/**
 * Test the AttributeTypeFactoryTest - this is a self test of the suite to ensure the base class works as intended.
 */
class AttributeTypeFactoryTestTest extends AttributeTypeFactoryTest
{
    /**
     * Override this method to run the tests on the attribute factory to be tested.
     *
     * @return IAttributeTypeFactory
     */
    protected function getAttributeFactories()
    {
        return $this->mockAttributeFactory('AttributeTypeFactoryTestTest\AttributeTypeFactory', true, false, false);
    }

    /**
     * A little self test for this class to make sure the base tests really work out as expected.
     *
     * @return void
     */
    public function testSelf()
    {
        $this->attributeTypeInformationMakesSense($this->mockAttributeFactory('test_translated', true, false, false));
        $this->attributeTypeInformationMakesSense($this->mockAttributeFactory('test_simple', false, true, false));
        $this->attributeTypeInformationMakesSense($this->mockAttributeFactory('test_complex', false, false, true));

        // Now we want to produce some error here to ensure our self test really also produces failures for nonsense.
        $failed = false;
        try {
            $this->attributeTypeInformationMakesSense(
                $this->mockAttributeFactory('test_translated_simple', true, true, false)
            );
        } catch (\PHPUnit_Framework_ExpectationFailedException $ex) {
            // As expected the assertion failed.
            $failed = true;
        }
        $this->assertTrue($failed, 'Self test failed: translated and simple is possible but should not.');

        $failed = false;
        try {
            $this->attributeTypeInformationMakesSense(
                $this->mockAttributeFactory('test_translated_complex', true, true, false)
            );
        } catch (\PHPUnit_Framework_ExpectationFailedException $ex) {
            // As expected the assertion failed.
            $failed = true;
        }
        $this->assertTrue($failed, 'Self test failed: translated and complex is possible but should not.');

        $failed = false;
        try {
            $this->attributeTypeInformationMakesSense(
                $this->mockAttributeFactory('test_simple_complex', false, true, true)
            );
        } catch (\PHPUnit_Framework_ExpectationFailedException $ex) {
            // As expected the assertion failed.
            $failed = true;
        }
        $this->assertTrue($failed, 'Self test failed: translated and complex is possible but should not.');

        $failed = false;
        try {
            $this->attributeTypeInformationMakesSense(
                $this->mockAttributeFactory('test_none', false, false, false)
            );
        } catch (\PHPUnit_Framework_ExpectationFailedException $ex) {
            // As expected the assertion failed.
            $failed = true;
        }
        $this->assertTrue(
            $failed,
            'Self test failed: Defining attributes that are none of translated, ' .
            'simple or complex is possible but should not.'
        );
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
     * @return IAttributeTypeFactory
     */
    protected function mockAttributeFactory($typeName, $translated, $simple, $complex, $class = 'stdClass')
    {
        return AttributeFactoryMocker::mockAttributeFactory($this, $typeName, $translated, $simple, $complex, $class);
    }
}
