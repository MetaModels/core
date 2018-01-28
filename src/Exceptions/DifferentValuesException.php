<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2018 The MetaModels team.
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
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Exceptions;

/**
 * This class compares two variables and emits exceptions when they differ.
 */
class DifferentValuesException extends \Exception
{
    /**
     * Two values are an array but differ in count.
     */
    const ARRAY_COUNT_MISMATCH = 1;

    /**
     * Two values are an array but differ in keys.
     */
    const ARRAY_KEY_MISMATCH = 1;

    /**
     * Two values are an array but have a different value at a certain key.
     */
    const ARRAY_VALUE_MISMATCH = 1;

    /**
     * Two values differ in type.
     */
    const TYPE_MISMATCH = 1;

    /**
     * Two values are different.
     */
    const VALUE_MISMATCH = 1;

    /**
     * The expected value.
     *
     * @var mixed
     */
    private $expected;

    /**
     * The actual value.
     *
     * @var mixed
     */
    private $actual;

    /**
     * Run in strict mode.
     *
     * @var bool
     */
    private $strict;

    /**
     * Create a new instance.
     *
     * @param mixed      $expected The expected value.
     *
     * @param mixed      $actual   The actual value.
     *
     * @param bool       $strict   Compared in strict mode.
     *
     * @param string     $message  The Exception message to throw.
     *
     * @param int        $code     The Exception code.
     *
     * @param \Exception $previous The previous exception used for the exception chaining.
     */
    public function __construct($expected, $actual, $strict, $message = '', $code = 0, \Exception $previous = null)
    {
        parent::__construct(
            $message,
            $code,
            $previous
        );

        $this->expected = $expected;
        $this->actual   = $actual;
        $this->strict   = $strict;
    }

    /**
     * Check if the actual argument is the same as the expected.
     *
     * @param mixed $expected The expected value.
     *
     * @param mixed $actual   The actual value.
     *
     * @param bool  $strict   Run in strict mode.
     *
     * @return void
     *
     * @throws DifferentValuesException When the values differ.
     */
    public static function compare($expected, $actual, $strict = true)
    {
        try {
            self::calculateDiff($expected, $actual, $strict);
        } catch (\Exception $exception) {
            $instance = new DifferentValuesException(
                $expected,
                $actual,
                $strict,
                'The values differ.',
                0,
                $exception
            );
            throw $instance;
        }
    }

    /**
     * Retrieve the value that was expected.
     *
     * @return mixed
     */
    public function getExpected()
    {
        return $this->expected;
    }

    /**
     * Retrieve the value that was actual encountered.
     *
     * @return mixed
     */
    public function getActual()
    {
        return $this->actual;
    }

    /**
     * Retrieve the strict mode flag.
     *
     * @return boolean
     */
    public function isStrict()
    {
        return $this->strict;
    }

    /**
     * Retrieve the messages of all exceptions as chain.
     *
     * @param string $glue The glue to use to combine all exception messages.
     *
     * @return string
     */
    public function getLongMessage($glue = ' ')
    {
        $messages  = array();
        $exception = $this;
        do {
            $messages[] = $exception->getMessage();
        } while (null !== ($exception = $exception->getPrevious()));

        return implode($glue, $messages);
    }

    /**
     * Check if the actual argument is of type array and empty and the expected value is of type string and also empty.
     *
     * @param mixed $expected The expected value.
     *
     * @param mixed $actual   The actual value.
     *
     * @return bool
     */
    private static function isEmptyArrayEquivalent($expected, $actual)
    {
        return (gettype($expected) == 'string')
            && ((gettype($actual) == 'array') || (gettype($actual) == 'NULL'))
            && empty($actual)
            && empty($expected);
    }

    /**
     * Check for differences in arrays.
     *
     * @param array $expected The expected value.
     *
     * @param array $actual   The actual value.
     *
     * @param bool  $strict   Run in strict mode.
     *
     * @return void
     *
     * @throws \LogicException When the values differ.
     */
    private static function calculateArrayDiff($expected, $actual, $strict)
    {
        if (count($expected) !== count($actual)) {
            throw new \LogicException(
                sprintf(
                    'Array element count mismatch. Found %s, expected %s.',
                    count($actual),
                    count($expected)
                ),
                self::ARRAY_COUNT_MISMATCH
            );
        }

        reset($actual);
        foreach ($expected as $key => $value) {
            if ($key !== key($actual)) {
                throw new \LogicException(
                    sprintf(
                        'Array key mismatch. Found %s, expected %s.',
                        key($actual),
                        $key
                    ),
                    self::ARRAY_KEY_MISMATCH
                );
            }

            try {
                self::calculateDiff($value, current($actual), $strict);
            } catch (\Exception $exception) {
                throw new \LogicException(
                    sprintf(
                        'Array value mismatch for key %s.',
                        key($actual)
                    ),
                    self::ARRAY_VALUE_MISMATCH,
                    $exception
                );
            }
            next($actual);
        }
    }

    /**
     * Helper to determine if two values are the same.
     *
     * @param mixed $expected The expected value.
     *
     * @param mixed $actual   The actual value.
     *
     * @param bool  $strict   Run in strict mode.
     *
     * @return void
     *
     * @throws \LogicException When the values differ.
     */
    private static function calculateDiff($expected, $actual, $strict)
    {
        if ($expected === $actual) {
            return;
        }

        if (gettype($expected) !== gettype($actual)) {
            // Only exception of the rule: array values are transported as empty string.
            if (!$strict && self::isEmptyArrayEquivalent($expected, $actual)) {
                return;
            }

            throw new \LogicException(
                sprintf(
                    'Encountered type %s expected %s (Found %s, expected %s)',
                    gettype($actual),
                    gettype($expected),
                    var_export($actual, true),
                    var_export($expected, true)
                ),
                self::TYPE_MISMATCH
            );
        }

        if (is_array($expected)) {
            self::calculateArrayDiff($expected, $actual, $strict);
        }

        throw new \LogicException(
            sprintf(
                'Found %s expected %s',
                var_export($actual, true),
                var_export($expected, true)
            ),
            self::VALUE_MISMATCH
        );
    }
}
