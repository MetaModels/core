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

namespace MetaModels\Filter\Rules\Comparing;

use MetaModels\Attribute\IAttribute;
use MetaModels\Filter\IFilterRule;

/**
 * This is the MetaModelFilterRule class for handling "greater-than" comparison on attributes.
 */
class GreaterThan implements IFilterRule
{
    /**
     * The attribute to search in.
     *
     * @var IAttribute
     */
    protected $objAttribute;

    /**
     * The value to compare with.
     *
     * @var mixed
     */
    protected $varValue = null;

    /**
     * Determination if the comparison shall be done inclusive or exclusive.
     *
     * @var boolean
     */
    protected $blnInclusive = false;

    /**
     * Creates an instance of this class.
     *
     * @param IAttribute $objAttribute The attribute that shall be searched.
     * @param mixed      $varValue     The value to compare against.
     * @param bool       $blnInclusive If true, the passed value will be included in the check
     *                                 and therefore make the check an equal-or-greater test.
     */
    public function __construct(IAttribute $objAttribute, $varValue, $blnInclusive = false)
    {
        $this->objAttribute = $objAttribute;
        $this->varValue     = $varValue;
        $this->blnInclusive = $blnInclusive;
    }

    /**
     * Fetch the ids for all items that hold a value that is greater than the passed value.
     *
     * If no entries have been found, the result is an empty array.
     *
     * @return list<string>|null
     */
    public function getMatchingIds()
    {
        return $this->objAttribute->filterGreaterThan($this->varValue, $this->blnInclusive);
    }
}
