<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2024 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Exceptions\Database;

/**
 * Class TableDoesNotExistException
 */
class TableExistsException extends \RuntimeException
{
    /**
     * Create a new exception for an existing table.
     *
     * @param string     $tableName Table name.
     * @param int        $code      The optional Exception code.
     * @param \Exception $previous  The optional previous throwable used for the exception chaining.
     *
     * @return self
     */
    public static function withName($tableName, $code = 0, $previous = null)
    {
        return new self(\sprintf('Table "%s" already exists.', $tableName), $code, $previous);
    }
}
