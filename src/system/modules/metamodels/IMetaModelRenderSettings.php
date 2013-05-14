<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package	   MetaModels
 * @subpackage Interface
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

/**
 * Interface for render settings.
 *
 * @package	   MetaModels
 * @subpackage Interface
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
interface IMetaModelRenderSettings
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
	 * @return IMetaModelRenderSettings The setting itself.
	 */
	public function set($strName, $varSetting);

	/**
	 * Get the render information for an attribute.
	 *
	 * @param string $strAttributeName The name of the attribute.
	 *
	 * @return object|null An object or null if the information is not available.
	 */
	public function getSetting($strAttributeName);

	/**
	 * Set the render information for an attribute.
	 *
	 * @param string $strAttributeName The name of the attribute.
	 *
	 * @param object $objSetting       The object containing all the information.
	 *
	 * @return IMetaModelRenderSettings The instance itself for chaining.
	 */
	public function setSetting($strAttributeName, $objSetting);

	/**
	 * Retrieve the names of all columns getting rendered via this setting.
	 *
	 * @return string[]
	 */
	public function getSettingNames();
}

