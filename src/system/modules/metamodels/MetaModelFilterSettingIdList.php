<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package	   MetaModels
 * @subpackage Interfaces
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

/**
 * Filter setting implementation of a static list of matching ids.
 *
 * @package	   MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class MetaModelFilterSettingIdList extends MetaModelFilterSetting
{
	public function prepareRules(IMetaModelFilter $objFilter, $arrFilterUrl)
	{
		if ($this->get('items'))
		{
			$arrItems = explode(',', (string)$this->get('items'));
		} else {
			$arrItems = array();
		}
		$objFilter->addFilterRule(new MetaModelFilterRuleStaticIdList($arrItems));
	}
}

?>