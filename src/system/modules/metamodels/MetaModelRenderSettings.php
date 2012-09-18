<?php



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
}

?>