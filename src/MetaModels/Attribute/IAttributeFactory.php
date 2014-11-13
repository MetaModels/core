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

namespace MetaModels\Attribute;

use MetaModels\IMetaModel;

/**
 * This is the factory interface to query instances of attributes.
 * Usually this is only used internally from within the MetaModel class.
 *
 * @package    MetaModels
 * @subpackage Interfaces
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
interface IAttributeFactory
{
    /**
     * Flag for filtering translated attributes.
     */
    const FLAG_INCLUDE_TRANSLATED = 1;

    /**
     * Flag for translated attributes.
     */
    const FLAG_INCLUDE_SIMPLE = 2;

    /**
     * Flag for complex attributes.
     */
    const FLAG_INCLUDE_COMPLEX = 4;

    /**
     * Flag for retrieving all attribute types.
     */
    const FLAG_ALL = 7;

    /**
     * Flag for filtering untranslated attributes.
     *
     * NOTE: When using this flag, translated complex and translated simple types will also get returned.
     */
    const FLAG_ALL_UNTRANSLATED = 6;

    /**
     * Create an attribute instance from an information array.
     *
     * @param array      $information The attribute information.
     *
     * @param IMetaModel $metaModel   The MetaModel instance for which the attribute shall be created.
     *
     * @return IAttribute|null
     */
    public function createAttribute($information, $metaModel);

    /**
     * Add a type factory to this factory.
     *
     * @param IAttributeTypeFactory $typeFactory The type factory to add.
     *
     * @return IFactory
     */
    public function addTypeFactory(IAttributeTypeFactory $typeFactory);

    /**
     * Retrieve a type factory from this factory.
     *
     * @param string $typeFactory The name of the type factory to retrieve.
     *
     * @return IAttributeTypeFactory
     */
    public function getTypeFactory($typeFactory);

    /**
     * Check if the attribute matches the flags.
     *
     * @param string $factory The name of the factory to check.
     *
     * @param int    $flags   The flags to match.
     *
     * @return bool
     */
    public function attributeTypeMatchesFlags($factory, $flags);

    /**
     * Retrieve the type names registered in the factory.
     *
     * @param bool|int $flags The flags for retrieval. See the interface constants for the different values.
     *
     * @return string[]
     */
    public function getTypeNames($flags = false);

    /**
     * Collect all attribute information for a MetaModel.
     *
     * The resulting information will then get passed to the attribute factories to create attribute instances.
     *
     * @param IMetaModel $metaModel The MetaModel for which attribute information shall be retrieved.
     *
     * @return array
     */
    public function collectAttributeInformation(IMetaModel $metaModel);

    /**
     * Create all attribute instances for the given MetaModel.
     *
     * @param IMetaModel $metaModel The MetaModel to create the attributes for.
     *
     * @return IAttribute[]
     */
    public function createAttributesForMetaModel($metaModel);

    /**
     * Retrieve the icon for a certain attribute type.
     *
     * @param string $type The name of the type to retrieve the icon for.
     *
     * @return string
     */
    public function getIconForType($type);
}
