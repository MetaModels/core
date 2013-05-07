<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package	   MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

/**
 * base implementation for render settings.
 *
 * @package	   MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class MetaModelRenderSettings implements IMetaModelRenderSettings
{
	/**
	 * The base information for this render settings object.
	 *
	 * @var array
	 */
	protected $arrBase = array();

	/**
	 * The subsettings for all attributes.
	 *
	 * @var array
	 */
	protected $arrSettings = array();

	/**
	 * The jump to information buffered in this setting.
	 *
	 * @var array
	 */
	protected $arrJumpTo;

	/**
	 * Create a new instance.
	 *
	 * @param array $arrInformation The array that holds all base information for the new instance.
	 */
	public function __construct($arrInformation = array())
	{
		foreach ($arrInformation as $strKey => $varValue)
		{
			$this->set($strKey, deserialize($varValue));
		}
	}

	/**
	 * Retrieve a setting from the settings instance.
	 *
	 * @param string $strName The name of the setting to retrieve.
	 *
	 * @return mixed|null The value or null if not set.
	 */
	public function get($strName)
	{
		return $this->arrBase[$strName];
	}

	/**
	 * Set a base property in the settings object.
	 *
	 * @param string $strName    The name of the setting to set.
	 *
	 * @param mixed  $varSetting The value to use.
	 *
	 * @return IMetaModelRenderSettings The setting itself.
	 */
	public function set($strName, $varSetting)
	{
		$this->arrBase[$strName] = $varSetting;
		return $this;
	}

	/**
	 * Get the render information for an attribute.
	 *
	 * @param string $strAttributeName The name of the attribute.
	 *
	 * @return IMetaModelRenderSettingAttribute|null An object or null if the information is not available.
	 */
	public function getSetting($strAttributeName)
	{
		return $this->arrSettings[$strAttributeName];
	}

	/**
	 * Set the render information for an attribute.
	 *
	 * @param string                           $strAttributeName The name of the attribute.
	 *
	 * @param IMetaModelRenderSettingAttribute $objSetting       The object containing all the information.
	 *
	 * @return IMetaModelRenderSettings The instance itself for chaining.
	 */
	public function setSetting($strAttributeName, $objSetting)
	{
		if ($objSetting)
		{
			$this->arrSettings[$strAttributeName] = $objSetting->setParent($this);
		} else {
			unset($this->arrSettings[$strAttributeName]);
		}
		return $this;
	}

	/**
	 * Retrieve the names of all columns getting rendered via this setting.
	 *
	 * @return string[]
	 */
	public function getSettingNames()
	{
		return array_keys($this->arrSettings);
	}

	/**
	 * Retrieve the jump to information from the setting.
	 *
	 * @return array|null The jump to information or null if none has been set.
	 */
	public function getJumpTo()
	{
		return isset($this->arrJumpTo) ? $this->arrJumpTo : null;
	}

	/**
	 * Set the jump to information in the settings object.
	 *
	 * @param mixed  $varSetting The value to use.
	 *
	 * @return IMetaModelRenderSettings The setting itself.
	 */
	public function setJumpTo($varSetting)
	{
		$this->arrJumpTo = $varSetting;
		return $this;
	}
}

