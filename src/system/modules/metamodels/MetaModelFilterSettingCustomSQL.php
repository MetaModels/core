<?php

class MetaModelFilterSettingCustomSQL extends MetaModelFilterSetting
{
	public function prepareRules(IMetaModelFilter $objFilter, $arrFilterUrl)
	{
		$strSQL = $this->get('customsql');
		// replace the metamodel table name.

		$strSQL = str_replace('{{table}}', $this->getMetaModel()->getTableName(), $strSQL);

		if (strlen($strSQL))
		{
			$objFilterRule = new MetaModelFilterRuleSimpleQuery($strSQL);
			$objFilter->addFilterRule($objFilterRule);
		}
	}
}

?>