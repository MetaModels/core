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
interface IAttributeTypeFactory
{
    /**
     * Return the type name - this is the internal type name used by MetaModels.
     *
     * @return string
     */
    public function getTypeName();

    /**
     * Retrieve the (relative to TL_ROOT) path to a icon for the type.
     *
     * @return string
     */
    public function getTypeIcon();

    /**
     * Create a new instance with the given information.
     *
     * @param array      $information The attribute information.
     *
     * @param IMetaModel $metaModel   The MetaModel instance the attribute shall be created for.
     *
     * @return IAttribute|null
     */
    public function createInstance($information, $metaModel);

    /**
     * Check if the type is translated.
     *
     * @return bool
     */
    public function isTranslatedType();

    /**
     * Check if the type is of simple nature.
     *
     * @return bool
     */
    public function isSimpleType();

    /**
     * Check if the type is of complex nature.
     *
     * @return bool
     */
    public function isComplexType();
}
