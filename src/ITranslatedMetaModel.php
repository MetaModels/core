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
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels;

/**
 * This defines a translated MetaModel.
 */
interface ITranslatedMetaModel extends IMetaModel
{
    /**
     * Fetches all language codes that have been marked as available for translation in this MetaModel.
     *
     * @return list<string> An array containing all codes.
     */
    public function getLanguages(): array;

    /**
     * Fetches the language code that has been marked as fallback language in this MetaModel.
     *
     * @return string The language code to be used as fallback.
     */
    public function getMainLanguage(): string;

    /**
     * Get the current active language.
     *
     * @return string The current language code.
     */
    public function getLanguage(): string;

    /**
     * Set the current language and return the previous language.
     *
     * @param string $activeLanguage The language code to set.
     *
     * @return string The previous language code.
     */
    public function selectLanguage(string $activeLanguage): string;
}
