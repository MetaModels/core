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

namespace MetaModels\Filter\Setting;

/**
 * This is the factory interface to query instances of attributes.
 * Usually this is only used internally from within the MetaModel class.
 *
 * @package    MetaModels
 * @subpackage Interfaces
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
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
