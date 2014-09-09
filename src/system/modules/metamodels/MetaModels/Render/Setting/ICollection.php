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

namespace MetaModels\Render\Setting;

/**
 * Interface for render settings.
 *
 * @package    MetaModels
 * @subpackage Interface
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
interface ICollection
{
    /**
     * Retrieve a setting from the settings instance.
     *
     * @param string $strName The name of the setting to retrieve.
     *
     * @return mixed|null The value or null if not set.
     */
    public function get($strName);

    /**
     * Set a base property in the settings object.
     *
     * @param string $strName    The name of the setting to set.
     *
     * @param mixed  $varSetting The value to use.
     *
     * @return ICollection The setting itself.
     */
    public function set($strName, $varSetting);

    /**
     * Get the render information for an attribute.
     *
     * @param string $strAttributeName The name of the attribute.
     *
     * @return ISimple|null An object or null if the information is not available.
     */
    public function getSetting($strAttributeName);

    /**
     * Set the render information for an attribute.
     *
     * @param string  $strAttributeName The name of the attribute.
     *
     * @param ISimple $objSetting       The object containing all the information.
     *
     * @return ICollection The instance itself for chaining.
     */
    public function setSetting($strAttributeName, $objSetting);

    /**
     * Retrieve the names of all columns getting rendered via this setting.
     *
     * @return string[]
     */
    public function getSettingNames();

    /**
     * Retrieve the jump to information from the setting.
     *
     * @return array|null The jump to information or null if none has been set.
     */
    public function getJumpTo();

    /**
     * Set the jump to information in the settings object.
     *
     * @param mixed $varSetting The value to use.
     *
     * @return ISimple The setting itself.
     */
    public function setJumpTo($varSetting);
}
