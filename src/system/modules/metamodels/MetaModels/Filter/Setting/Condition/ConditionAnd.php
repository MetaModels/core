<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
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
use MetaModels\Filter\Rules\Condition\ConditionAnd as FilterRuleAnd;
use MetaModels\Filter\Setting\WithChildren;

/**
 * This filter condition generates a "AND" condition from all child filter settings.
 * The generated rule will only return ids that are mentioned in ALL child rules.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class ConditionAnd extends WithChildren
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
        $objSubFilter = new Filter($this->getMetaModel());

        foreach ($this->arrChildren as $objChildSetting)
        {
            $objChildSetting->prepareRules($objSubFilter, $arrFilterUrl);
        }

        $objFilterRule = new FilterRuleAnd();
        $objFilterRule->addChild($objSubFilter);

        $objFilter->addFilterRule($objFilterRule);
    }
}

