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

use Contao\Database;
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
     * The database instance to use.
     *
     * @var Database
     */
    private $dataBase = null;

    /**
     * Creates an instance of a simple query filter rule.
     *
     * @param string   $strQueryString The query that shall be executed.
     *
     * @param array    $arrParams      The query parameters that shall be used.
     *
     * @param string   $strIdColumn    The column where the item id is stored in.
     *
     * @param Database $dataBase       The database to use.
     */
    public function __construct($strQueryString, $arrParams = array(), $strIdColumn = 'id', $dataBase = null)
    {
        parent::__construct();

        if ($dataBase === null) {
            $dataBase = \Database::getInstance();
        }
        $this->strQueryString = $strQueryString;
        $this->arrParams      = $arrParams;
        $this->strIdColumn    = $strIdColumn;
        $this->dataBase       = $dataBase;
    }

    /**
     * {@inheritdoc}
     */
    public function getMatchingIds()
    {
        $objMatches = $this->dataBase
            ->prepare($this->strQueryString)
            ->execute($this->arrParams);

        return ($objMatches->numRows == 0) ? array() : $objMatches->fetchEach($this->strIdColumn);
    }
}
