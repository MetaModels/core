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

use MetaModels\IMetaModel;
use MetaModels\Test\TestCase;

/**
 * Test the base attribute.
 */
class BaseTest extends TestCase
{
    /**
     * Mock a MetaModel.
     *
     * @param string $language         The language.
     * @param string $fallbackLanguage The fallback language.
     *
     * @return IMetaModel
     */
    protected function mockMetaModel($language, $fallbackLanguage)
    {
        $metaModel = $this->getMock(
            'MetaModels\MetaModel',
            array(),
            array(array())
        );

        $metaModel
            ->expects($this->any())
            ->method('getTableName')
            ->will($this->returnValue('mm_unittest'));

        $metaModel
            ->expects($this->any())
            ->method('getActiveLanguage')
            ->will($this->returnValue($language));

        $metaModel
            ->expects($this->any())
            ->method('getFallbackLanguage')
            ->will($this->returnValue($fallbackLanguage));

        return $metaModel;
    }

    /**
     * Test that the constructor properly initializes all values properly.
     *
     * @return void
     */
    public function testCreation()
    {
        $attributes = array(
            // Settings originating from tl_metamodel_attribute.
            'id'           => 1,
            'pid'          => 1,
            'sorting'      => 1,
            'tstamp'       => 0,
            'name'         => array(
                'en'       => 'name English',
                'de'       => 'name German',
            ),
            'description'  => array(
                'en'       => 'description English',
                'de'       => 'description German',
            ),
            'type'         => 'base',
            'colname'      => 'baseattribute',
            'isvariant'    => 1,
            // Settings originating from tl_metamodel_dcasetting.
            'tl_class'     => 'custom_class',
            'readonly'     => 1
        );

        $serialized = array();
        foreach ($attributes as $key => $value) {
            if (is_array($value)) {
                $serialized[$key] = serialize($value);
            } else {
                $serialized[$key] = $value;
            }
        }

        /** @var \MetaModels\Attribute\Base $attribute */
        $attribute = $this
            ->getMockForAbstractClass(
                'MetaModels\Attribute\Base',
                array(
                    $this->mockMetaModel('de', 'en'),
                    $serialized
                )
            );

        foreach ($attributes as $key => $value) {
            $this->assertEquals($value, $attribute->get($key), $key);
        }
    }
}
