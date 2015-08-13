<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2015 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     David Maack <david.maack@arcor.de>
 * @copyright  2012-2015 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\Filter\Rules;

use MetaModels\Filter\FilterRule;

/**
 * This is the MetaModel filter interface.
 */
class StaticIdList extends FilterRule
{
    /**
     * The static id list that shall be applied.
     *
     * @var string[]|null
     */
    protected $arrIds = array();

    /**
     * Create a new FilterRule instance.
     *
     * @param string[]|null $arrIds Static list of ids that shall be returned as matches.
     */
    public function __construct($arrIds)
    {
        parent::__construct();
        $this->arrIds = $arrIds;
    }

    /**
     * {@inheritdoc}
     */
    public function getMatchingIds()
    {
        return $this->arrIds;
    }
}
