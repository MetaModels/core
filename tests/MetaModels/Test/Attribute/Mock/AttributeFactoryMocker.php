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
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Test\Attribute\Mock;

use MetaModels\Attribute\IAttributeTypeFactory;
use PHPUnit_Framework_TestCase;

/**
 * This is the factory interface to query instances of attributes.
 * Usually this is only used internally from within the MetaModel class.
 *
 * @package    MetaModels
 * @subpackage Interfaces
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class AttributeFactoryMocker
{
    /**
     * Mock an attribute type factory.
     *
     * @param PHPUnit_Framework_TestCase $testCase   The test case for which to mock.
     *
     * @param string                     $typeName   The type name to mock.
     *
     * @param bool                       $translated Flag if the type shall be translated.
     *
     * @param bool                       $simple     Flag if the type shall be simple.
     *
     * @param bool                       $complex    Flag if the type shall be complex.
     *
     * @param string                     $class      Name of the class to instantiate when createInstance() is called.
     *
     * @param string                     $typeIcon   The icon of the type to mock.
     *
     * @return IAttributeTypeFactory
     */
    public static function mockAttributeFactory(
        $testCase,
        $typeName,
        $translated,
        $simple,
        $complex,
        $class = 'stdClass',
        $typeIcon = 'icon.png'
    ) {
        $mockTypeFactory = $testCase->getMock(
            'MetaModels\Attribute\IAttributeTypeFactory',
            array('getTypeName', 'getTypeIcon', 'createInstance', 'isTranslatedType', 'isSimpleType', 'isComplexType'),
            array()
        );

        $mockTypeFactory
            ->expects($testCase->any())
            ->method('getTypeName')
            ->will(
                $testCase->returnCallback(function () use ($typeName) {
                        return $typeName;
                })
            );

        $mockTypeFactory
            ->expects($testCase->any())
            ->method('getTypeIcon')
            ->will(
                $testCase->returnCallback(function () use ($typeIcon) {
                        return $typeIcon;
                })
            );

        $mockTypeFactory
            ->expects($testCase->any())
            ->method('createInstance')
            ->will(
                $testCase->returnCallback(function ($information, $metaModel) use ($class) {
                        return new $class($information, $metaModel);
                })
            );

        $mockTypeFactory
            ->expects($testCase->any())
            ->method('isTranslatedType')
            ->will(
                $testCase->returnCallback(function () use ($translated) {
                        return $translated;
                })
            );

        $mockTypeFactory
            ->expects($testCase->any())
            ->method('isSimpleType')
            ->will(
                $testCase->returnCallback(function () use ($simple) {
                        return $simple;
                })
            );


        $mockTypeFactory
            ->expects($testCase->any())
            ->method('isComplexType')
            ->will(
                $testCase->returnCallback(function () use ($complex) {
                        return $complex;
                })
            );

        return $mockTypeFactory;
    }
}
