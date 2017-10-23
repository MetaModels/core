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
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\Exceptions\Database;

/**
 * Class InvalidColumnNameException
 */
class InvalidColumnNameException extends \RuntimeException
{
    /**
     * Create a new exception for a invalid column name.
     *
     * @param string     $columnName Column name.
     * @param int        $code       The optional Exception code.
     * @param \Exception $previous   The optional previous throwable used for the exception chaining.
     *
     * @return static
     */
    public static function invalidCharacters($columnName, $code = 0, $previous = null)
    {
        return new static(sprintf('The column name "%s" is invalid.', $columnName), $code, $previous);
    }

    /**
     * Create a new exception for a invalid column name.
     *
     * @param string     $columnName Column name.
     * @param int        $code       The optional Exception code.
     * @param \Exception $previous   The optional previous throwable used for the exception chaining.
     *
     * @return static
     */
    public static function systemColumn($columnName, $code = 0, $previous = null)
    {
        return new static(sprintf('The column name "%s" is reserved for system use.', $columnName), $code, $previous);
    }
}
