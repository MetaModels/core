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

namespace MetaModels\Filter\Rules\Comparing;

use MetaModels\Attribute\IAttribute;
use MetaModels\Filter\IFilterRule;

/**
 * This is the MetaModelFilterRule class for handling "not-equal" comparison on attributes.
 */
class NotEqual implements IFilterRule
{
    /**
     * The attribute to search in.
     *
     * @var IAttribute
     */
    protected $objAttribute = null;

    /**
     * The value to compare with.
     *
     * @var mixed
     */
    protected $varValue = null;

    /**
     * Creates an instance of this class.
     *
     * @param IAttribute $objAttribute The query that shall be executed.
     *
     * @param mixed      $varValue     The value to compare against.
     */
    public function __construct(IAttribute $objAttribute, $varValue)
    {
        $this->objAttribute = $objAttribute;
        $this->varValue     = $varValue;
    }

    /**
     * Fetch the ids for all items that hold a value that is not equal to the passed value.
     *
     * If no entries have been found, the result is an empty array.
     *
     * @return string[]|null
     */
    public function getMatchingIds()
    {
        return $this->objAttribute->filterNotEqual($this->varValue);
    }
}
