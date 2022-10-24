<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2019 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Attribute;

/**
 * Interface for "simple" MetaModel attributes.
 * Simple attributes are attributes that only consist of one column in the metamodel table and therefore do not need
 * to be handled as complex fields must be.
 */
interface ISimple extends IAttribute
{
    /**
     * Returns the SQL primitive type declaration in MySQL notation. i.e. "text NULL".
     *
     * @return string
     *
     * @deprecated Implement schema generators instead.
     */
    public function getSQLDataType();

    /**
     * Creates the underlying database structure for this attribute.
     *
     * @return void
     *
     * @deprecated Implement schema generators instead.
     */
    public function createColumn();

    /**
     * Removes the underlying database structure for this attribute.
     *
     * @return void
     *
     * @deprecated Implement schema generators instead.
     */
    public function deleteColumn();

    /**
     * Renames the underlying database structure for this attribute.
     *
     * @param string $strNewColumnName The new column name for the attribute.
     *
     * @return void
     *
     * @deprecated Implement schema generators instead.
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
