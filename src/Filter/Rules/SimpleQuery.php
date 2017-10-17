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
use Contao\System;
use Doctrine\DBAL\Connection;
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
     * @var Connection
     */
    private $dataBase;

    /**
     * Creates an instance of a simple query filter rule.
     *
     * @param string     $strQueryString The query that shall be executed.
     * @param array      $arrParams      The query parameters that shall be used.
     * @param string     $strIdColumn    The column where the item id is stored in.
     * @param Connection $dataBase       The database to use.
     */
    public function __construct($strQueryString, $arrParams = array(), $strIdColumn = 'id', $dataBase = null)
    {
        parent::__construct();

        // BC layer - we used to accept a Contao database instance here.
        if ($dataBase instanceof Database) {
            @trigger_error(
                '"' . __METHOD__ . '" now accepts doctrine instances - ' .
                'passing Contao database instances is deprecated.',
                E_USER_DEPRECATED
            );
            $reflection = new \ReflectionProperty(Database::class, 'resConnection');
            $reflection->setAccessible(true);

            $dataBase = $reflection->getValue($dataBase);
        }
        if (null === $dataBase) {
            @trigger_error(
                'You should pass a doctrine database connection to "' . __METHOD__ . '".',
                E_USER_DEPRECATED
            );
            $dataBase = System::getContainer()->get('database_connection');
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
            ->prepare($this->strQueryString);

        $objMatches->execute($this->arrParams);

        $ids = [];
        foreach ($objMatches->fetchAll(\PDO::FETCH_ASSOC) as $value) {
            $ids[] = $value[$this->strIdColumn];
        }
        return $ids;
    }
}
