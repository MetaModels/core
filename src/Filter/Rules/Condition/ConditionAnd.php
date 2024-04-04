<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2024 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Filter\Rules\Condition;

use MetaModels\Filter\FilterRule;
use MetaModels\Filter\IFilter;

/**
 * This is the MetaModel filter interface.
 */
class ConditionAnd extends FilterRule
{
    /**
     * The list of child filters that shall be evaluated.
     *
     * @var IFilter[]
     */
    protected $arrChildFilters = [];

    /**
     * Adds a child filter to this rule that will get evaluated when this rule is evaluated.
     *
     * @param IFilter $objFilter The filter to add as child.
     *
     * @return ConditionAnd
     */
    public function addChild(IFilter $objFilter)
    {
        $this->arrChildFilters[] = $objFilter;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getMatchingIds()
    {
        if (0 === count($this->arrChildFilters)) {
            return [];
        }

        $ids = null;
        foreach ($this->arrChildFilters as $objChildFilter) {
            $matchingIds = $objChildFilter->getMatchingIds();
            if ([] === $matchingIds) {
                // Empty array, no items allowed by this rule, break out.
                return [];
            }

            // If null => all items allowed by this rule => ignore it.
            if (null === $matchingIds) {
                continue;
            }

            if (null === $ids) {
                $ids = $matchingIds;
                continue;
            }

            $ids = \array_intersect($ids, $matchingIds);
        }

        return \is_array($ids) ? \array_values($ids) : null;
    }
}
