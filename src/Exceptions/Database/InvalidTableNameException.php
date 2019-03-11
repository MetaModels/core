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
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Exceptions\Database;

/**
 * Class InvalidTableNameException
 */
class InvalidTableNameException extends \RuntimeException
{
    /**
     * Create a new exception for a invalid table name.
     *
     * @param string     $tableName Table name.
     * @param int        $code      The optional Exception code.
     * @param \Exception $previous  The optional previous throwable used for the exception chaining.
     *
     * @return static
     */
    public static function invalidCharacters($tableName, $code = 0, $previous = null)
    {
        return new static(sprintf('The table name "%s" is invalid.', $tableName), $code, $previous);
    }
}
