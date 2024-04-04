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
 * @author     David Maack <david.maack@arcor.de>
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
class ConditionOr extends FilterRule
{
    /**
     * The list of child filters that shall be evaluated.
     *
     * @var list<IFilter>
     */
    protected $arrChildFilters = [];

    /**
     * Flag determining if filtering shall return the first non-empty match.
     *
     * This flag determines if the rule shall return the result of the first
     * child that returns at least one element (a return value of NULL from a
     * rule counts as "all ids" in this context and therefore is considered
     * non-empty per definition).
     *
     * @var boolean
     */
    protected $stopAfterMatch = false;

    /**
     * Create a new FilterRule instance.
     *
     * @param boolean $stopAfterMatch Flag determining if filtering shall return
     *                                the first non-empty match.
     */
    public function __construct($stopAfterMatch = false)
    {
        parent::__construct();
        $this->stopAfterMatch = $stopAfterMatch;
    }

    /**
     * Adds a child filter to this rule that will get evaluated when this rule is evaluated.
     *
     * @param IFilter $objFilter The filter to add as child.
     *
     * @return ConditionOr
     */
    public function addChild(IFilter $objFilter)
    {
        $this->arrChildFilters[] = $objFilter;

        return $this;
    }

    /**
     * Fetch the ids from all child filter rules.
     *
     * If no entries have been found, the result is an empty array.
     * If no filtering was applied and therefore all ids shall be reported as
     * valid, the return value of NULL is allowed.
     *
     * The OR filter rule has an embedded shortcut for the first rule that
     * returns "null". When this happens, no further child rules will get
     * evaluated, as the result set can not expand any further.
     *
     * Note: when "stopAfterMatch" has been set, the rule will stop processing
     * also when the first rule returns a non-empty result and return that
     * result.
     *
     * @return list<string>|null
     */
    public function getMatchingIds()
    {
        $arrIds = [];
        foreach ($this->arrChildFilters as $objChildFilter) {
            $arrChildMatches = $objChildFilter->getMatchingIds();
            // NULL => all items - for OR conditions, this can never be more than all, so we are already satisfied here.
            if ($arrChildMatches === null) {
                return null;
            }

            if ($arrChildMatches && $this->stopAfterMatch) {
                return $arrChildMatches;
            }

            if ($arrChildMatches) {
                $arrIds = \array_merge($arrIds, $arrChildMatches);
            }
        }

        return \array_values(\array_unique($arrIds));
    }
}
