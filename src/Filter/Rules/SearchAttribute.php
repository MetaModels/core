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

namespace MetaModels\Filter\Rules;

use MetaModels\Attribute\IAttribute;
use MetaModels\Attribute\IComplex;
use MetaModels\Attribute\ITranslated;
use MetaModels\Filter\FilterRule;

/**
 * This is the MetaModelFilterRule class for handling string value searches on attributes.
 */
class SearchAttribute extends FilterRule
{
    /**
     * The attribute to search in.
     *
     * @var IAttribute|ITranslated|IComplex
     */
    protected $objAttribute = null;

    /**
     * The value to search for.
     *
     * @var string
     */
    protected $strValue = null;

    /**
     * The valid languages to match (only used when searching a translated attribute).
     *
     * @var array
     */
    protected $arrValidLanguages = null;

    /**
     * Creates an instance of a simple query filter rule.
     *
     * @param IAttribute $objAttribute      The attribute to be searched.
     *
     * @param string     $strValue          The value to be searched for. Wildcards (* and ? allowed).
     *
     * @param array      $arrValidLanguages The list of valid languages to be searched in.
     */
    public function __construct($objAttribute, $strValue = '', $arrValidLanguages = array())
    {
        parent::__construct();
        $this->objAttribute      = $objAttribute;
        $this->strValue          = $strValue;
        $this->arrValidLanguages = $arrValidLanguages;
    }

    /**
     * {@inheritdoc}
     */
    public function getMatchingIds()
    {
        if ($this->objAttribute instanceof ITranslated) {
            return $this->objAttribute->searchForInLanguages($this->strValue, $this->arrValidLanguages);
        }

        return $this->objAttribute->searchFor($this->strValue);
    }
}
