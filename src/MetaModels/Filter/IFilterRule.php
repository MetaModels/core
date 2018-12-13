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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Filter;

/**
 * This is the MetaModel filter interface.
 */
interface IFilterRule
{
    /**
     * Fetch the ids for all matches for this filter rule.
     *
     * If no entries have been found, the result is an empty array.
     * If no filtering was applied and therefore all ids shall be reported as valid, the return value of NULL is
     * allowed.
     *
     * @return string[]|null
     */
    public function getMatchingIds();
}
