<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Filter\Rules;

use MetaModels\Attribute\IAttribute;
use MetaModels\Attribute\IComplex;
use MetaModels\Attribute\ITranslated;
use MetaModels\Filter\FilterRule;

/**
 * This is the MetaModelFilterRule class for handling string value searches on attributes.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
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
