<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2021 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2021 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Test\Attribute;

use MetaModels\Attribute\Base;
use MetaModels\IMetaModel;
use PHPUnit\Framework\TestCase;

/**
 * Test the base attribute.
 *
 * @covers \MetaModels\Attribute\Base
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
        $metaModel = $this->getMockForAbstractClass(IMetaModel::class);

        $metaModel
            ->expects(self::any())
            ->method('getTableName')
            ->willReturn('mm_unittest');

        $metaModel
            ->expects(self::any())
            ->method('getActiveLanguage')
            ->willReturn($language);

        $metaModel
            ->expects(self::any())
            ->method('getFallbackLanguage')
            ->willReturn($fallbackLanguage);

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
            self::assertEquals($value, $attribute->get($key), $key);
        }

        self::assertEquals('baseattribute', $attribute->getColName());
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

        self::assertEquals(null, $attribute->get('foo'));
    }

    /**
     * Test that the attribute does not accept config keys not specified via getAttributeSettingNames().
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function testGetFieldDefinition()
    {
        $GLOBALS['TL_LANGUAGE'] = 'en';
        /** @var Base $attribute */
        $attribute = $this->getAttribute();

        $fieldDefinition = $attribute->getFieldDefinition(
            array(
            'tl_class' => 'some_widget_class',
            'readonly' => true
            )
        );

        self::assertFalse(array_key_exists('filter', $fieldDefinition['eval']));
        self::assertFalse(array_key_exists('search', $fieldDefinition['eval']));

        self::assertEquals('some_widget_class', $fieldDefinition['eval']['tl_class']);
        self::assertEquals(true, $fieldDefinition['eval']['readonly']);
    }
}
