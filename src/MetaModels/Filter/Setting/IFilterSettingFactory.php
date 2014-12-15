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
 * @author     David Maack <david.maack@arcor.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Filter\Setting;

use MetaModels\IServiceContainerAware;

/**
 * This is the filter settings factory interface.
 *
 * @see IFilterSettingFactory::createCollection() to create a Filter setting collection instance.
 */
interface IFilterSettingFactory extends IServiceContainerAware
{
    /**
     * Register a type factory.
     *
     * @param IFilterSettingTypeFactory $factory The type factory.
     *
     * @return IFilterSettingFactory
     */
    public function addTypeFactory($factory);

    /**
     * Retrieve the filter setting type factory.
     *
     * @param string $type The type name.
     *
     * @return IFilterSettingTypeFactory|null
     */
    public function getTypeFactory($type);

    /**
     * Create a ICollection instance from the id.
     *
     * @param string $settingId The id of the ICollection.
     *
     * @return ICollection The instance of the filter settings or null if not found.
     */
    public function createCollection($settingId);

    /**
     * Retrieve the list of registered filter setting type factories.
     *
     * @return string[]
     */
    public function getTypeNames();
}
