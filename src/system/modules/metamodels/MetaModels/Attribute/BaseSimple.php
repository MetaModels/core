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

namespace MetaModels\Attribute;

use MetaModels\Helper\TableManipulation;

/**
 * Reference implementation for Simple attributes.
 * Simple fields are fields that only consist of one column in the metamodel table and therefore do not need
 * to be handled as complex fields must be.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class BaseSimple extends Base implements ISimple
{
    /**
     * Updates the meta information of the attribute.
     *
     * This tells the attribute to perform any actions that must be done to correctly initialize the new value
     * and to perform any action to undo the changes that had been done for the previous value.
     * i.e.: when an attribute type needs columns in an an auxiliary table, these will have to be updated herein.
     *
     * This method may throw an exception, when the new value is invalid or any problems appear, the MetaModelAttribute
     * will then keep the old meta value.
     *
     * @param string $strMetaName Name of the meta information that shall be updated.
     *
     * @param mixed  $varNewValue The new value for this meta information.
     *
     * @return \MetaModels\Attribute\IAttribute The instance of this attribute, to support chaining.
     */
    public function handleMetaChange($strMetaName, $varNewValue)
    {
        // By default we accept any change of meta information.
        if ($strMetaName == 'colname')
        {
            if ($this->get($strMetaName) != $varNewValue)
            {
                $this->renameColumn($varNewValue);
            }
            return $this;
        }
        return parent::handleMetaChange($strMetaName, $varNewValue);
    }

    /**
     * This method is called to store the data for certain items to the database.
     *
     * @param mixed[int] $arrValues The values to be stored into database. Mapping is item id=>value.
     *
     * @return void
     */
    public function setDataFor($arrValues)
    {
        $strTable   = $this->getMetaModel()->getTableName();
        $strColName = $this->getColName();
        foreach ($arrValues as $intId => $varData)
        {
            if (is_array($varData))
            {
                $varData = serialize($varData);
            }
            \Database::getInstance()
                ->prepare(sprintf('UPDATE %s SET %s=? WHERE id=%s', $strTable, $strColName, $intId))
                ->execute($varData);
        }
    }

    /**
     * Retrieve the filter options of this attribute.
     *
     * Retrieve values for use in filter options, that will be understood by DC_ filter
     * panels and frontend filter select boxes.
     * One can influence the amount of returned entries with the two parameters.
     * For the id list, the value "null" represents (as everywhere in MetaModels) all entries.
     * An empty array will return no entries at all.
     * The parameter "used only" determines, if only really attached values shall be returned.
     * This is only relevant, when using "null" as id list for attributes that have pre configured
     * values like select lists and tags i.e.
     *
     * @param array $arrIds   The ids of items that the values shall be fetched from.
     *
     * @param bool  $usedOnly Determines if only "used" values shall be returned.
     *
     * @param null  $arrCount By default null, pass an array to get the count of items per option for each id.
     *
     * @return array All options matching the given conditions as name => value.
     */
    public function getFilterOptions($arrIds, $usedOnly, &$arrCount = null)
    {
        $strCol = $this->getColName();
        if ($arrIds)
        {
            // Ensure proper integer ids for SQL injection safety reasons.
            $strIdList = implode(',', array_map('intval', $arrIds));
            $objRow    = \Database::getInstance()->execute('
                SELECT ' . $strCol . ', COUNT(' . $strCol . ') as mm_count
                FROM ' . $this->getMetaModel()->getTableName() .
                ' WHERE id IN (' . $strIdList . ')
                GROUP BY ' . $strCol . '
                ORDER BY FIELD(id,' . $strIdList . ')');
        }
        elseif ($usedOnly)
        {
            $objRow = \Database::getInstance()->execute('
                SELECT ' . $strCol . ', COUNT(' . $strCol . ') as mm_count
                FROM ' . $this->getMetaModel()->getTableName() . '
                GROUP BY ' . $strCol
            );
        }
        else
        {
            // We can not do anything here, must be handled by the derived attribute class.
            return array();
        }

        $arrResult = array();
        while ($objRow->next())
        {
            if (is_array($arrCount))
            {
                $arrCount[$objRow->$strCol] = $objRow->mm_count;
            }

            $arrResult[$objRow->$strCol] = $objRow->$strCol;
        }
        return $arrResult;
    }

    /**
     * Sorts the given array list by field value in the given direction.
     *
     * This base implementation does a plain SQL sort by native value as defined by MySQL.
     *
     * @param int[]  $arrIds       A list of Ids from the MetaModel table.
     *
     * @param string $strDirection The direction for sorting. either 'ASC' or 'DESC', as in plain SQL.
     *
     * @return int[] The sorted integer array.
     */
    public function sortIds($arrIds, $strDirection)
    {
        // Base implementation, do a simple sorting on given column.
        $arrIds = \Database::getInstance()->prepare(sprintf(
            'SELECT id FROM %s WHERE id IN (%s) ORDER BY %s %s',
            $this->getMetaModel()->getTableName(),
            implode(',', $arrIds),
            $this->getColName(),
            $strDirection))
            ->execute()
            ->fetchEach('id');
        return $arrIds;
    }

    /**
     * Search all items that match the given expression.
     *
     * Base implementation, perform string matching search.
     * The standard wildcards * (many characters) and ? (a single character) are supported.
     *
     * @param string $strPattern The text to search for. This may contain wildcards.
     *
     * @return int[] the ids of matching items.
     */
    public function searchFor($strPattern)
    {
        // Base implementation, do a simple search on given column.
        $objQuery = \Database::getInstance()->prepare(sprintf(
            'SELECT id FROM %s WHERE %s LIKE ?',
            $this->getMetaModel()->getTableName(),
            $this->getColName()
            ))
            ->executeUncached(str_replace(array('*', '?'), array('%', '_'), $strPattern));

        $arrIds = $objQuery->fetchEach('id');
        return $arrIds;
    }

    /**
     * Returns the SQL primitive type declaration in MySQL notation.
     *
     * In this base class a sane value of "blob" allowing NULL is used.
     * Deriving classes SHOULD override this function.
     *
     * @return string 'blob NULL'
     */
    public function getSQLDataType()
    {
        return 'blob NULL';
    }

    /**
     * Create auxiliary data like a column in the MetaModel table or references in another table etc.
     *
     * @return void
     */
    public function destroyAUX()
    {
        parent::destroyAUX();
        $this->deleteColumn();
    }

    /**
     * Delete all auxiliary data like a column in the MetaModel table or references in another table etc.
     *
     * @return void
     */
    public function initializeAUX()
    {
        parent::initializeAUX();
        $this->createColumn();
    }

    /**
     * Creates the underlying database structure for this field.
     *
     * You have to override this function in field types, when you want to have multi column structure etc.
     *
     * @return void
     */
    public function createColumn()
    {
        if ($this->getColName())
        {
            TableManipulation::createColumn(
                $this->getMetaModel()->getTableName(),
                $this->getColName(),
                $this->getSQLDataType()
            );
        }
    }

    /**
     * Removes the underlying database structure for this field.
     *
     * @return void
     */
    public function deleteColumn()
    {
        // Try to delete the column. If it does not exist as we can assume it has been deleted already then.
        if ($this->getColName()
        && \Database::getInstance()->fieldExists($this->getColName(), $this->getMetaModel()->getTableName(), true))
        {
            TableManipulation::dropColumn($this->getMetaModel()->getTableName(), $this->getColName());
        }
    }

    /**
     * Renames the underlying database structure for this field.
     *
     * @param string $strNewColumnName The new column name.
     *
     * @return void
     */
    public function renameColumn($strNewColumnName)
    {
        TableManipulation::checkColumnName($strNewColumnName);
        if ($this->getColName()
        && \Database::getInstance()->fieldExists($this->getColName(), $this->getMetaModel()->getTableName(), true))
        {
            TableManipulation::renameColumn(
                $this->getMetaModel()->getTableName(),
                $this->getColName(),
                $strNewColumnName,
                $this->getSQLDataType()
            );
        } else {
            $strBackupColName = $this->getColName();
            $this->set('colname', $strNewColumnName);
            $this->createColumn();
            $this->set('colname', $strBackupColName);
        }
    }

    /**
     * Take the raw data from the DB column and unserialize it.
     *
     * @param string $value The input value.
     *
     * @return mixed
     */
    public function unserializeData($value)
    {
        return $value;
    }

    /**
     * Take the unserialized data and serialize it for the native DB column.
     *
     * @param mixed $value The input value.
     *
     * @return string
     */
    public function serializeData($value)
    {
        return $value;
    }
}

