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

namespace MetaModels\Filter\Setting;

use MetaModels\Filter\IFilter;
use MetaModels\Filter\Rules\StaticIdList as FilterRuleIdList;

/**
 * Filter setting implementation of a static list of matching ids.
 *
 * @package       MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class IdList extends Simple
{
    /**
     * {@inheritDoc}
     */
    public function prepareRules(IFilter $objFilter, $arrFilterUrl)
    {
        if ($this->get('items')) {
            $arrItems = explode(',', (string)$this->get('items'));
        } else {
            $arrItems = array();
        }
        $objFilter->addFilterRule(new FilterRuleIdList($arrItems));
    }
}
