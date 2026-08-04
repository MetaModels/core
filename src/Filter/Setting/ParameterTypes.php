<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2026 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2026 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\Filter\Setting;

use function array_fill_keys;
use function method_exists;

/**
 * Helper to obtain the URL parameter types from a filter setting.
 *
 * This provides the backwards compatibility layer for filter settings not (yet) implementing
 * "getParameterTypes()" - the method will become part of ISimple in MetaModels 3.0.
 *
 * @internal
 */
final class ParameterTypes
{
    /**
     * The lenient legacy type, accepting both slug and GET.
     */
    public const LEGACY_TYPE = 'slugNget';

    /**
     * Obtain the URL parameter types of the passed filter setting.
     *
     * @param ISimple $setting The filter setting to obtain the types from.
     *
     * @return array<string, string> The parameter types as array. parametername => type
     */
    public static function fromSetting(ISimple $setting): array
    {
        if (!method_exists($setting, 'getParameterTypes')) {
            // Settings without any parameter can not be affected - stay silent for them.
            if ([] === ($parameters = $setting->getParameters())) {
                return [];
            }

            // @codingStandardsIgnoreStart
            @trigger_error(
                'Filter setting "' . $setting::class . '" does not implement "getParameterTypes()". ' .
                'The parameters are treated as "' . self::LEGACY_TYPE . '". ' .
                'The method will be required in MetaModels 3.0.',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd

            return array_fill_keys($parameters, self::LEGACY_TYPE);
        }

        /** @var array<string, string> $types */
        $types = $setting->getParameterTypes();

        return $types;
    }
}
