<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2019 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
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
 */
class ConditionOr extends WithChildren
{
    /**
     * {@inheritdoc}
     */
    public function prepareRules(IFilter $objFilter, $arrFilterUrl)
    {
        $objFilterRule = new FilterRuleOr($this->get('stop_after_match'));
        foreach ($this->arrChildren as $objChildSetting) {
            $objSubFilter = new Filter($this->getMetaModel());
            $objChildSetting->prepareRules($objSubFilter, $arrFilterUrl);
            $objFilterRule->addChild($objSubFilter);
        }
        $objFilter->addFilterRule($objFilterRule);
    }
}
