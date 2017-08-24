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

use Contao\Database;
use MetaModels\Filter\IFilter;
use MetaModels\IMetaModel;

/**
 * This class resolves an id list.
 */
class IdResolver
{
    use DatabaseHelperTrait;

    /**
     * The database.
     *
     * @var Database
     */
    private $database;

    /**
     * The metamodel we work on.
     *
     * @var IMetaModel
     */
    private $metaModel;

    /**
     * The MetaModel table name.
     *
     * @var string
     */
    private $tableName;

    /**
     * The filter.
     *
     * @var IFilter
     */
    private $filter;

    /**
     * The sort attribute.
     *
     * @var string
     */
    private $sortBy;

    /**
     * The sort order.
     *
     * @var string
     */
    private $sortOrder = 'ASC';

    /**
     * The offset.
     *
     * @var int
     */
    private $offset = 0;

    /**
     * The limit.
     *
     * @var int
     */
    private $limit = 0;

    /**
     * Create a new instance.
     *
     * @param IMetaModel $metaModel The MetaModel.
     * @param Database   $database  The database.
     */
    public function __construct(IMetaModel $metaModel, Database $database)
    {
        $this->database  = $database;
        $this->metaModel = $metaModel;
        $this->tableName = $metaModel->getTableName();
    }

    /**
     * Create a new instance.
     *
     * @param IMetaModel $metaModel The MetaModel.
     * @param Database   $database  The database.
     *
     * @return IdResolver
     */
    public static function create(IMetaModel $metaModel, Database $database)
    {
        return new static($metaModel, $database);
    }

    /**
     * Retrieve filter.
     *
     * @return IFilter
     */
    public function getFilter()
    {
        return $this->filter;
    }

    /**
     * Set filter.
     *
     * @param IFilter $filter The new value.
     *
     * @return IdResolver
     */
    public function setFilter(IFilter $filter = null)
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * Retrieve attribute.
     *
     * @return string
     */
    public function getSortBy()
    {
        return $this->sortBy;
    }

    /**
     * Set attribute.
     *
     * @param string $sortBy The new value.
     *
     * @return IdResolver
     */
    public function setSortBy($sortBy)
    {
        $this->sortBy = (string) $sortBy;

        return $this;
    }

    /**
     * Retrieve sort order.
     *
     * @return string
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * Set sort order.
     *
     * @param string $sortOrder The new value.
     *
     * @return IdResolver
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder == 'DESC' ? 'DESC' : 'ASC';

        return $this;
    }

    /**
     * Retrieve offset.
     *
     * @return int
     */
    public function getOffset()
    {
        return $this->offset;
    }

    /**
     * Set offset.
     *
     * @param int $offset The new value.
     *
     * @return IdResolver
     */
    public function setOffset($offset)
    {
        $this->offset = (int) $offset;

        return $this;
    }

    /**
     * Retrieve limit.
     *
     * @return int
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * Set limit.
     *
     * @param int $limit The new value.
     *
     * @return IdResolver
     */
    public function setLimit($limit)
    {
        $this->limit = (int) $limit;

        return $this;
    }

    /**
     * Retrieve the id list.
     *
     * @return string[]
     */
    public function getIds()
    {
        $filteredIds = $this->getMatchingIds();

        // If desired, sort the entries.
        if (!empty($filteredIds) && null !== $this->sortBy) {
            $filteredIds = $this->sortIds($filteredIds);
        }

        // Apply limiting then.
        if ($this->offset > 0 || $this->limit > 0) {
            $filteredIds = array_slice($filteredIds, $this->offset, $this->limit ?: null);
        }
        return array_unique(array_filter($filteredIds));
    }

    /**
     * Fetch the amount of matching items.
     *
     * @return int
     */
    public function count()
    {
        $filteredIds = $this->getMatchingIds();
        if (count($filteredIds) == 0) {
            return 0;
        }

        $result = $this
            ->database
            ->prepare(sprintf(
                'SELECT COUNT(id) AS count FROM %s WHERE id IN(%s)',
                $this->tableName,
                $this->buildDatabaseParameterList($filteredIds)
            ))
            ->execute($filteredIds);

        return $result->count;
    }

    /**
     * Narrow down the list of Ids that match the given filter.
     *
     * @return array all matching Ids.
     */
    private function getMatchingIds()
    {
        if (null !== $this->filter && null !== ($matchingIds = $this->filter->getMatchingIds())) {
            return $matchingIds;
        }

        // Either no filter object or all ids allowed => return all ids.
        // if no id filter is passed, we assume all ids are provided.
        $rows = $this->database->execute('SELECT id FROM ' . $this->tableName);

        return $rows->fetchEach('id');
    }

    /**
     * Sort the ids.
     *
     * @param string[] $filteredIds The id list.
     *
     * @return array
     */
    private function sortIds($filteredIds)
    {
        switch (true) {
            case ('random' === $this->sortBy):
                shuffle($filteredIds);
                return $filteredIds;
            case (null !== ($attribute = $this->metaModel->getAttribute($this->sortBy))):
                return $attribute->sortIds($filteredIds, $this->sortOrder);
            case (in_array($this->sortBy, ['id', 'pid', 'tstamp', 'sorting'])):
                // Sort by database values.
                return $this
                    ->database
                    ->prepare(
                        sprintf(
                            'SELECT id FROM %s WHERE id IN(%s) ORDER BY %s %s',
                            $this->tableName,
                            $this->buildDatabaseParameterList($filteredIds),
                            $this->sortBy,
                            $this->sortOrder
                        )
                    )
                    ->execute($filteredIds)
                    ->fetchEach('id');
            default:
                // Nothing we can do about this.
        }

        return $filteredIds;
    }
}
