<?php



class MetaModelRenderSettings
{
	protected $arrBase = array();

	protected $arrSettings = array();

	public function __construct()
	{
	}

	public function get($strName)
	{
		return $this->arrBase[$strName];
	}

	public function set($strName, $varSetting)
	{
		$this->arrBase[$strName] = $varSetting;
		return $this;
	}

	public function getSetting($strAttributeName)
	{
		return $this->arrSettings[$strAttributeName];
	}

	public function setSetting($strAttributeName, $objSetting)
	{
		$this->arrSettings[$strAttributeName] = $objSetting;
		return $this;
	}

	public function createDefaultFrom(IMetaModel $objMetaModel)
	{
		foreach ($objMetaModel->getAttributes() as $objAttribute)
		{
			$objSetting = $objAttribute->getDefaultRenderSettings();
			$this->setSetting($objAttribute->getColName(), $objSetting);
/*
			$objSetting = (object)array
			(
				'template' => 'mm_attr_' . $objAttribute->get('type')
			);
			$this->setSetting($objAttribute->getColName(), $objSetting);
*/
		}
		return $this;
	}
}

?>