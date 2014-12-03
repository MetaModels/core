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
 * This is the factory interface to query instances of filter settings.
 * Usually this is only used internally from within the MetaModel class.
 */
interface IFilterSettingTypeFactory
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
     * @param array       $information    The filter setting information.
     *
     * @param ICollection $filterSettings The filter setting instance the filter setting shall be created for.
     *
     * @return ISimple|null
     */
    public function createInstance($information, $filterSettings);

    /**
     * Check if the type allows children.
     *
     * @return bool
     */
    public function isNestedType();

    /**
     * Return the maximum amount of children that can be added to this setting (only valid when isNestedType() == true).
     *
     * @return int|null
     */
    public function getMaxChildren();
}
