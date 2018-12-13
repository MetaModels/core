<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2018 The MetaModels team.
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
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
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
 */
class ConditionAnd extends WithChildren
{
    /**
     * {@inheritdoc}
     */
    public function prepareRules(IFilter $objFilter, $arrFilterUrl)
    {
        $objSubFilter = new Filter($this->getMetaModel());

        foreach ($this->arrChildren as $objChildSetting) {
            $objChildSetting->prepareRules($objSubFilter, $arrFilterUrl);
        }

        $objFilterRule = new FilterRuleAnd();
        $objFilterRule->addChild($objSubFilter);

        $objFilter->addFilterRule($objFilterRule);
    }
}
