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

use MetaModels\Filter\FilterRule;

/**
 * This is the MetaModelFilterRule class for executing a simple database query.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class SimpleQuery extends FilterRule
{
    /**
     * The query string.
     *
     * @var string
     */
    protected $strQueryString = null;

    /**
     * The query parameters.
     *
     * @var array
     */
    protected $arrParams = null;

    /**
     * The name of the id column in the query.
     *
     * @var array
     */
    protected $strIdColumn = null;

    /**
     * Creates an instance of a simple query filter rule.
     *
     * @param string $strQueryString The query that shall be executed.
     *
     * @param array  $arrParams      The query parameters that shall be used.
     *
     * @param string $strIdColumn    The column where the item id is stored in.
     */
    public function __construct($strQueryString, $arrParams = array(), $strIdColumn = 'id')
    {
        parent::__construct(null);
        $this->strQueryString = $strQueryString;
        $this->arrParams      = $arrParams;
        $this->strIdColumn    = $strIdColumn;
    }

    /**
     * {@inheritdoc}
     */
    public function getMatchingIds()
    {
        $objDB      = \Database::getInstance();
        $objMatches = $objDB
            ->prepare($this->strQueryString)
            ->execute($this->arrParams);

        return ($objMatches->numRows == 0) ? array() : $objMatches->fetchEach($this->strIdColumn);
    }
}

