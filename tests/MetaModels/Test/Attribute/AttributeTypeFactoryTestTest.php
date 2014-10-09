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
        return array(
            $this->mockAttributeFactory('AttributeTypeFactoryTestTest\AttributeTypeFactory', true, false, false)
        );
    }

    /**
     * Mock a factory with the given name and check that attributeTypeInformationMakesSense() works correctly.
     *
     * @param string $name       The type name to mock.
     *
     * @param bool   $translated Translation flag.
     *
     * @param bool   $simple     Simple flag.
     *
     * @param bool   $complex    Complex flag.
     *
     * @return void
     */
    protected function checkMockedFactory($name, $translated, $simple, $complex)
    {
        $this->attributeTypeInformationMakesSense(
            $this->mockAttributeFactory($name, $translated, $simple, $complex)
        );
    }

    /**
     * A little self test for this class to make sure the base tests really work out as expected.
     *
     * @return void
     */
    public function testSelf()
    {
        $this->checkMockedFactory('test_translated', true, false, false);
        $this->checkMockedFactory('test_simple', false, true, false);
        $this->checkMockedFactory('test_complex', false, false, true);
        $this->checkMockedFactory('test_simplecomplex', false, true, true);
        $this->checkMockedFactory('test_translatedsimple', true, true, false);
        $this->checkMockedFactory('test_translatedcomplex', true, false, true);
        $this->checkMockedFactory('test_translatedsimplecomplex', true, true, true);

        // Now we want to produce some error here to ensure our self test really also produces failures for nonsense.

        $failed = false;
        try {
            $this->checkMockedFactory('test_none', false, false, false);
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
