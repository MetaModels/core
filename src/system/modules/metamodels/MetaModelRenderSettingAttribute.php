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
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class MetaModelRenderSettingAttribute implements IMetaModelRenderSettingAttribute
{
	/**
	 * The base information for this render settings object.
	 *
	 * @var array
	 */
	protected $arrBase = array();

	/**
	 * The parenting instance.
	 *
	 * @var IMetaModelRenderSettings
	 */
	protected $parent;

	/**
	 * Create a new instance.
	 *
	 * @param array                    $arrInformation The array that holds all base information for the new instance.
	 */
	public function __construct($arrInformation = array())
	{
		foreach ($arrInformation as $strKey => $varValue)
		{
			$this->set($strKey, deserialize($varValue));
		}
	}

	/**
	 * For backwards compatibility only as this used to be stdClass.
	 *
	 * @param string $key  The key to set.
	 *
	 * @param mixed $value The value to set.
	 *
	 * @return void
	 */
	public function __set($key, $value)
	{
		if ($GLOBALS['TL_DEBUG'])
		{
			user_error('Please don\'t do magic access to IMetaModelRenderSettingAttribute.', E_USER_DEPRECATED);
		}
		$this->set($key, $value);
	}

	/**
	 * For backwards compatibility only as this used to be stdClass.
	 *
	 * @param string $key  The key to set.
	 *
	 * @return mixed
	 */
	public function __get($key)
	{
		if ($GLOBALS['TL_DEBUG'])
		{
			user_error('Please don\'t do magic access to IMetaModelRenderSettingAttribute.', E_USER_DEPRECATED);
		}
		return $this->get($key);
	}

	/**
	 * Set the parenting render setting.
	 *
	 * @param IMetaModelRenderSettings $parent The parenting instance.
	 *
	 * @return IMetaModelRenderSettingAttribute
	 */
	public function setParent($parent)
	{
		$this->parent = $parent;

		return $this;
	}

	/**
	 * Retrieve the parenting render setting.
	 *
	 * @return IMetaModelRenderSettings
	 */
	public function getParent()
	{
		return $this->parent;
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
		return isset($this->arrBase[$strName]) ? $this->arrBase[$strName] : null;
	}

	/**
	 * Set a base property in the settings object.
	 *
	 * @param string $strName    The name of the setting to set.
	 *
	 * @param mixed  $varSetting The value to use.
	 *
	 * @return IMetaModelRenderSettingAttribute The setting itself.
	 */
	public function set($strName, $varSetting)
	{
		$this->arrBase[$strName] = $varSetting;
		return $this;
	}

	/**
	 * Retrieve the jump to information from the setting.
	 *
	 * @return array|null The jump to information or null if none has been set.
	 */
	public function getJumpTo()
	{
		return $this->parent->getJumpTo();
	}

	/**
	 * Retrieve the names of all keys in this setting.
	 *
	 * @return string[]
	 */
	public function getKeys()
	{
		return array_keys($this->arrBase);
	}
}

