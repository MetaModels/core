<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Filter\Setting\Condition;

use MetaModels\Filter\Filter;
use MetaModels\Filter\IFilter;
use MetaModels\Filter\Rules\Condition\ConditionOr as FilterRuleOr;
use MetaModels\Filter\Setting\WithChildren;

/**
 * This filter condition generates a "OR" condition from all child filter settings.
 * The generated rule will return ids that are mentioned in ANY of the child rules.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class ConditionOr extends WithChildren
{
	/**
	 * Generates the filter rules based upon the given filter url.
	 *
	 * @param IFilter        $objFilter    The filter to append the rules to.
	 *
	 * @param string[string] $arrFilterUrl The parameters to evaluate.
	 *
	 * @return void
	 */
	public function prepareRules(IFilter $objFilter, $arrFilterUrl)
	{
		$objFilterRule = new FilterRuleOr($this->get('stop_after_match'));
		foreach ($this->arrChildren as $objChildSetting)
		{
			$objSubFilter = new Filter($this->getMetaModel());
			$objChildSetting->prepareRules($objSubFilter, $arrFilterUrl);
			$objFilterRule->addChild($objSubFilter);
		}
		$objFilter->addFilterRule($objFilterRule);
	}
}

