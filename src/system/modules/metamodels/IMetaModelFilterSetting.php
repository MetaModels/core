<?php

interface IMetaModelFilterSetting
{
	public function prepareRules(IMetaModelFilter $objFilter, $arrFilterUrl);

	public function generateFilterUrlFrom(IMetaModelItem $objItem);
}

?>