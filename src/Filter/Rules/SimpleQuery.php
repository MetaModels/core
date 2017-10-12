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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\Filter\Rules;

use Contao\Database;
use MetaModels\Filter\FilterRule;

/**
 * This is the MetaModelFilterRule class for executing a simple database query.
 */
class SimpleQuery extends FilterRule
{
    /**
     * The query string.
     *
     * @var string
     */
    protected $strQueryString;

    /**
     * The query parameters.
     *
     * @var array
     */
    protected $arrParams;

    /**
     * The name of the id column in the query.
     *
     * @var string
     */
    protected $strIdColumn;

    /**
     * The database instance to use.
     *
     * @var Database
     */
    private $dataBase;

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
