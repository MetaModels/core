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

namespace MetaModels\Filter;

/**
 * This is the MetaModel filter interface.
 */
interface IFilter
{
    /**
     * Create an copy of this filter.
     *
     * @return IFilter
     */
    public function createCopy();

    /**
     * Adds a filter rule to this filter chain.
     *
     * @param IFilterRule $objFilterRule The filter rule to add.
     *
     * @return IFilter
     */
    public function addFilterRule(IFilterRule $objFilterRule);

    /**
     * Narrow down the list of Ids that match the given filter.
     *
     * @return string[]|null all matching Ids or null if all ids did match.
     */
    public function getMatchingIds();
}
