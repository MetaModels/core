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
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Helper;

/**
 * This is a static helper to detect "empty" values.
 */
class EmptyTest
{
    /**
     * Test if the value is empty.
     *
     * @param mixed $mixValue The value to test.
     *
     * @return bool
     */
    public static function isEmptyValue($mixValue): bool
    {
        if (is_array($mixValue)) {
            return self::isArrayEmpty($mixValue);
        }
        if ('' === $mixValue) {
            return true;
        }
        if (null === $mixValue) {
            return true;
        }

        return false;
    }

    /**
     * Run through each level of an array and check if we have at least one empty value.
     *
     * @param array $array The array to check.
     *
     * @return boolean True => empty, False => some values found.
     */
    public static function isArrayEmpty(array $array): bool
    {
        // First off check for simple types.
        if ([] === $array) {
            return true;
        }
        // Next check for a value array.
        if (array_key_exists('value', $array) && is_array($array['value'])) {
            return self::isArrayEmpty($array['value']);
        }
        // Now check sub arrays.
        foreach ($array as $value) {
            if (!self::isEmptyValue($value)) {
                return false;
            }
        }

        return true;
    }
}
