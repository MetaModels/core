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
	 * the base information for this render settings object.
	 * @var array
	 */
	protected $arrBase = array();

	/**
	 * The subsettings for all attributes.
	 * @var array
	 */
	protected $arrSettings = array();

	/**
	 * Create a new instance.
	 *
	 * @param array $arrInformation the array that holds all base information for the new instance.
	 *
	 * @return IMetaModelRenderSettings the new instance.
	 */
	public function __construct($arrInformation = array())
	{
		foreach ($arrInformation as $strKey => $varValue)
		{
			$this->set($strKey, deserialize($varValue));
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function get($strName)
	{
		return $this->arrBase[$strName];
	}

	/**
	 * {@inheritdoc}
	 */
	public function set($strName, $varSetting)
	{
		$this->arrBase[$strName] = $varSetting;
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSetting($strAttributeName)
	{
		return $this->arrSettings[$strAttributeName];
	}

	/**
	 * {@inheritdoc}
	 */
	public function setSetting($strAttributeName, $objSetting)
	{
		if ($objSetting)
		{
			$this->arrSettings[$strAttributeName] = $objSetting;
		} else {
			unset($this->arrSettings[$strAttributeName]);
		}
		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSettingNames()
	{
		return array_keys($this->arrSettings);
	}
}

?>