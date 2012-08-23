<?php



interface IMetaModelRenderSettings
{
	/**
	 * Retrieve a setting from the settings instance.
	 *
	 * @param string $strName the name of the setting to retrieve.
	 *
	 * @return mixed|null the value or null if not set.
	 */
	public function get($strName);

	/**
	 * Set a base property in the settings object
	 *
	 * @param string $strName    the name of the setting to set
	 *
	 * @param mixed  $varSetting the value to use.
	 *
	 * @return void
	 */
	public function set($strName, $varSetting);

	/**
	 * Get the render information for an attribute.
	 *
	 * @param string $strAttributeName the name of the attribute.
	 *
	 * @return object|null an object or null if the information is not available.
	 */
	public function getSetting($strAttributeName);

	/**
	 * Set the render information for an attribute.
	 *
	 * @param string $strAttributeName the name of the attribute.
	 *
	 * @param object $objSetting       the object containing all the information.
	 *
	 * @return IMetaModelRenderSettings the instance itself for chaining.
	 */
	public function setSetting($strAttributeName, $objSetting);
}

?>