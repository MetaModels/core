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
interface IMetaModelRenderSettingAttribute
{
	/**
	 * Set the parenting render setting.
	 *
	 * @param IMetaModelRenderSettings $parent The parenting instance.
	 *
	 * @return IMetaModelRenderSettingAttribute
	 */
	public function setParent($parent);

	/**
	 * Retrieve the parenting render setting.
	 *
	 * @return IMetaModelRenderSettings
	 */
	public function getParent();

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
	 * @return IMetaModelRenderSettingAttribute The setting itself.
	 */
	public function set($strName, $varSetting);

	/**
	 * Retrieve the jump to information from the setting.
	 *
	 * @return array|null The jump to information or null if none has been set.
	 */
	public function getJumpTo();

	/**
	 * Retrieve the names of all keys in this setting.
	 *
	 * @return string[]
	 */
	public function getKeys();
}

