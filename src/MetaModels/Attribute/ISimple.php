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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Attribute;

/**
 * Interface for "simple" MetaModel attributes.
 * Simple attributes are attributes that only consist of one column in the metamodel table and therefore do not need
 * to be handled as complex fields must be.
 *
 * @package    MetaModels
 * @subpackage Interfaces
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
interface ISimple extends IAttribute
{

    /**
     * Returns the SQL primitive type declaration in MySQL notation. i.e. "text NULL".
     *
     * @return string
     */
    public function getSQLDataType();

    /**
     * Creates the underlying database structure for this attribute.
     *
     * @return void
     */
    public function createColumn();

    /**
     * Removes the underlying database structure for this attribute.
     *
     * @return void
     */
    public function deleteColumn();

    /**
     * Renames the underlying database structure for this attribute.
     *
     * @param string $strNewColumnName The new column name for the attribute.
     *
     * @return void
     */
    public function renameColumn($strNewColumnName);

    /**
     * Take the raw data from the DB column and unserialize it.
     *
     * @param string $value The input value.
     *
     * @return mixed
     */
    public function unserializeData($value);

    /**
     * Take the unserialized data and serialize it for the native DB column.
     *
     * @param mixed $value The input value.
     *
     * @return string
     */
    public function serializeData($value);
}
