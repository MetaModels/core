<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2023 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Helper;

use Contao\CoreBundle\Util\LocaleUtil as ContaoLocaleUtil;
use Contao\System;

final class LocaleUtil
{
    /**
     * Converts a Locale ID to a Language Tag and strips keywords
     * after the @ sign.
     * As legacy part we convert a Locale ID (_) to a Language Tag (-)
     * and strips keywords after the @ sign.
     *
     * @param string $localeId The locale id.
     *
     * @return string
     */
    public static function formatAsLanguageTag(string $localeId): string
    {
        return self::formatAsLocale($localeId);
    }

    /**
     * Converts a Language Tag (-) to a Locale ID (_) and strips keywords
     * after the @ sign.
     *
     * @param string $languageTag The language tag.
     *
     * @return string
     */
    public static function formatAsLocale(string $languageTag): string
    {
        return ContaoLocaleUtil::formatAsLocale($languageTag);
    }
}
