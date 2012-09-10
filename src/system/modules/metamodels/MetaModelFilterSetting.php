<?php

abstract class MetaModelFilterSetting implements IMetaModelFilterSetting
{
	/**
	 * The parenting filter setting container this setting belongs to.
	 *
	 * @var IMetaModelFilterSettings
	 */
	protected $objFilterSettings = null;

	protected $arrData = array();

	public function __construct($objFilterSetting, $arrData)
	{
		$this->objFilterSetting = $objFilterSetting;
		$this->arrData = $arrData;
	}

	public function get($strKey)
	{
		return $this->arrData[$strKey];
	}

	protected function getFilterSettings()
	{
		return $this->objFilterSetting;
	}

	protected function getMetaModel()
	{
		return $this->objFilterSetting->getMetaModel();
	}

	public function generateFilterUrlFrom(IMetaModelItem $objItem, IMetaModelRenderSettings $objRenderSetting)
	{
		return array();
	}
}

?>