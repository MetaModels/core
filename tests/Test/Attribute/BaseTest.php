<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2017 The MetaModels team.
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
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\Test\Attribute;

use MetaModels\Attribute\Base;
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
     * Create the attribute with the given values.
     *
     * @param array           $data      The initialization array.
     *
     * @param null|IMetaModel $metaModel The MetaModel instance.
     *
     * @return Base
     */
    protected function getAttribute($data = array(), $metaModel = null)
    {
        $attributes = array_replace_recursive(
            array(
                'id'           => 1,
                'pid'          => 1,
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
            ),
            $data
        );

        $serialized = array();
        foreach ($attributes as $key => $value) {
            if (is_array($value)) {
                $serialized[$key] = serialize($value);
            } else {
                $serialized[$key] = $value;
            }
        }

        /** @var Base $attribute */
        return $this
            ->getMockForAbstractClass(
                'MetaModels\Attribute\Base',
                array(
                    $metaModel ?: $this->mockMetaModel('en', 'en'),
                    $serialized
                )
            );
    }

    /**
     * Test that the constructor properly initializes all values properly.
     *
     * @return void
     */
    public function testCreation()
    {
        $attributes = array(
            'id'           => 1,
            'pid'          => 1,
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

        /** @var Base $attribute */
        $attribute = $this->getAttribute($attributes);

        foreach ($attributes as $key => $value) {
            $this->assertEquals($value, $attribute->get($key), $key);
        }

        $this->assertEquals('baseattribute', $attribute->getColName());
    }

    /**
     * Test that the attribute does not accept config keys not specified via getAttributeSettingNames().
     *
     * @return void
     */
    public function testDoesNotAcceptArbitraryConfiguration()
    {
        /** @var Base $attribute */
        $attribute = $this->getAttribute(array('foo' => 'bar'));

        $this->assertEquals(null, $attribute->get('foo'));
    }

    /**
     * Test that the attribute does not accept config keys not specified via getAttributeSettingNames().
     *
     * @return void
     */
    public function testGetFieldDefinition()
    {
        /** @var Base $attribute */
        $attribute = $this->getAttribute();

        $fieldDefinition = $attribute->getFieldDefinition(
            array(
            'tl_class' => 'some_widget_class',
            'readonly' => true
            )
        );

        $this->assertFalse(array_key_exists('filter', $fieldDefinition['eval']));
        $this->assertFalse(array_key_exists('search', $fieldDefinition['eval']));

        $this->assertEquals('some_widget_class', $fieldDefinition['eval']['tl_class']);
        $this->assertEquals(true, $fieldDefinition['eval']['readonly']);
    }
}
