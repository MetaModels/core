<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2017 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\DataAccess;

/**
 * This trait provides some helper functions for database access.
 */
trait DatabaseHelperTrait
{
    /**
     * Build a list of the correct amount of "?" for use in a db query.
     *
     * @param array $parameters The parameters.
     *
     * @return string
     */
    private function buildDatabaseParameterList(array $parameters)
    {
        return implode(',', array_fill(0, count($parameters), '?'));
    }
}
