<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2016 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2016 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\Attribute;

use MetaModels\Helper\TableManipulation;

/**
 * Reference implementation for Simple attributes.
 * Simple fields are fields that only consist of one column in the metamodel table and therefore do not need
 * to be handled as complex fields must be.
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
        if ($strMetaName == 'colname') {
            if ($this->get($strMetaName) != $varNewValue) {
                $this->renameColumn($varNewValue);
            }
            return $this;
        }
        return parent::handleMetaChange($strMetaName, $varNewValue);
    }

    /**
     * This method is called to store the data for certain items to the database.
     *
     * @param mixed $arrValues The values to be stored into database. Mapping is item id=>value.
     *
     * @return void
     */
    public function setDataFor($arrValues)
    {
        $strTable   = $this->getMetaModel()->getTableName();
        $strColName = $this->getColName();
        foreach ($arrValues as $intId => $varData) {
            if (is_array($varData)) {
                $varData = serialize($varData);
            }
            $this->getMetaModel()->getServiceContainer()->getDatabase()
                ->prepare(sprintf('UPDATE %s SET %s=? WHERE id=%s', $strTable, $strColName, $intId))
                ->execute($varData);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getFilterOptions($idList, $usedOnly, &$arrCount = null)
    {
        // If empty list, return empty result. See also #379 for discussion.
        if ($idList === array()) {
            return array();
        }

        $strCol = $this->getColName();
        if ($idList) {
            $objRow = $this->getMetaModel()->getServiceContainer()->getDatabase()
                ->prepare(
                    'SELECT ' . $strCol . ', COUNT(' . $strCol . ') as mm_count
                    FROM ' . $this->getMetaModel()->getTableName() .
                    ' WHERE id IN (' . $this->parameterMask($idList) . ')
                    GROUP BY ' . $strCol . '
                    ORDER BY FIELD(id,' . $this->parameterMask($idList). ')'
                )
                ->execute(array_merge($idList, $idList));
        } elseif ($usedOnly) {
            $objRow = $this->getMetaModel()->getServiceContainer()->getDatabase()->execute(
                'SELECT ' . $strCol . ', COUNT(' . $strCol . ') as mm_count
                FROM ' . $this->getMetaModel()->getTableName() . '
                GROUP BY ' . $strCol . '
                ORDER BY ' . $strCol
            );
        } else {
            // We can not do anything here, must be handled by the derived attribute class.
            return array();
        }

        $arrResult = array();
        while ($objRow->next()) {
            if (is_array($arrCount)) {
                $arrCount[$objRow->$strCol] = $objRow->mm_count;
            }

            $arrResult[$objRow->$strCol] = $objRow->$strCol;
        }
        return $arrResult;
    }

    /**
     * {@inheritdoc}
     *
     * This base implementation does a plain SQL sort by native value as defined by MySQL.
     */
    public function sortIds($idList, $strDirection)
    {
        // Base implementation, do a simple sorting on given column.
        $idList = $this->getMetaModel()->getServiceContainer()->getDatabase()
            ->prepare(
                sprintf(
                    'SELECT id FROM %s WHERE id IN (%s) ORDER BY %s %s',
                    $this->getMetaModel()->getTableName(),
                    $this->parameterMask($idList),
                    $this->getColName(),
                    $strDirection
                )
            )
            ->execute($idList)
            ->fetchEach('id');
        return $idList;
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
        $objQuery = $this->getMetaModel()->getServiceContainer()->getDatabase()
            ->prepare(
                sprintf(
                    'SELECT id FROM %s WHERE %s LIKE ?',
                    $this->getMetaModel()->getTableName(),
                    $this->getColName()
                )
            )
            ->execute(str_replace(array('*', '?'), array('%', '_'), $strPattern));

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
        if ($this->getColName()) {
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
            && $this->getMetaModel()->getServiceContainer()->getDatabase()->fieldExists(
                $this->getColName(),
                $this->getMetaModel()->getTableName(),
                true
            )
        ) {
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
            && $this->getMetaModel()->getServiceContainer()->getDatabase()->fieldExists(
                $this->getColName(),
                $this->getMetaModel()->getTableName(),
                true
            )
        ) {
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
