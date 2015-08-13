<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2015 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2015 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\Filter\Setting;

/**
 * This is the factory interface to query instances of attributes.
 * Usually this is only used internally from within the MetaModel class.
 *
 * @deprecated This class is part of the backwards compatible layer.
 */
class LegacyFilterSettingTypeFactory extends AbstractFilterSettingTypeFactory
{
    /**
     * Add an attribute type factory for all registered legacy types to the passed factory.
     *
     * @param string $typeName                 The name of the type.
     *
     * @param array  $filterSettingInformation The attribute type information
     *                                         (keys: "class", "image", "nestingAllowed").
     *
     * @return LegacyFilterSettingTypeFactory
     *
     * @throws \RuntimeException For types that have no class defined.
     */
    public static function createLegacyFactory($typeName, $filterSettingInformation)
    {
        if (!isset($filterSettingInformation['class'])) {
            throw new \RuntimeException('Filter setting type ' . $typeName . ' has no class defined.');
        }

        $typeFactory = new static();
        $typeFactory->setTypeClass($filterSettingInformation['class']);
        $typeFactory->setTypeName($typeName);
        $typeFactory->setTypeIcon(
            isset($filterSettingInformation['image'])
            ? $filterSettingInformation['image']
            : 'system/modules/metamodels/assets/images/icons/fields.png'
        );

        if (isset($filterSettingInformation['maxChildren'])) {
            $typeFactory->setMaxChildren($filterSettingInformation['maxChildren']);
        }

        if (isset($filterSettingInformation['attr_filter'])) {
            $typeFactory->allowAttributeTypes();
        }

        return $typeFactory;
    }
}
