<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package	   MetaModels
 * @subpackage Backend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */
if (!defined('TL_ROOT'))
{
	die('You cannot access this file directly!');
}

/**
 * This filter condition generates a filter rule for a predefined SQL query.
 * The generated rule will only return ids that are returned from this query.
 * 
 * @package	   MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class MetaModelFilterSettingCustomSQL extends MetaModelFilterSetting
{
	public function prepareRules(IMetaModelFilter $objFilter, $arrFilterUrl)
	{
		$strSQL = $this->get('customsql');
		// replace the metamodel table name.
		$strSQL = str_replace('{{table}}', $this->getMetaModel()->getTableName(), $strSQL);
		// and insert tags.
		$strSQL = MetaModelController::getInstance()->replaceInsertTags($strSQL);
		// TODO: support for arguments would be nice here.

		if (strlen($strSQL))
		{
			$objFilterRule = new MetaModelFilterRuleSimpleQuery($strSQL);
			$objFilter->addFilterRule($objFilterRule);
		}
	}
}

?>