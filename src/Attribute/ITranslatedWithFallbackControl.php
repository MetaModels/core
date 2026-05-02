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

namespace MetaModels\Attribute;

/**
 * Extended interface for translated attributes that support fetching raw per-language data without automatic fallback
 * to the main language.
 *
 * This separate interface allows opt-in without breaking existing ITranslated implementors.
 * Consumers check instanceof before calling getTranslatedDataForWithoutFallback().
 */
interface ITranslatedWithFallbackControl extends ITranslated
{
    /**
     * Get values for the given items in a certain language without falling back to the main language.
     *
     * Unlike getTranslatedDataFor(), this method returns only data actually stored for $strLangCode.
     * No second pass against the main language is performed.
     *
     * @param list<string> $arrIds      The ids for which values shall be retrieved.
     * @param string       $strLangCode The language code for which the data shall be retrieved.
     *
     * @return array<string, array<string, mixed>> the values.
     */
    public function getTranslatedDataForWithoutFallback(array $arrIds, string $strLangCode): array;

    /**
     * Copy source data to a new item for a given language.
     *
     * Implementations may transform the data before persisting it — e.g. a unique-alias attribute
     * re-generates the slug to satisfy its uniqueness constraint. The default behaviour is to call
     * setTranslatedDataFor() verbatim.
     *
     * @param array<string, mixed> $sourceData  The raw attribute data of the source item for $strLangCode.
     * @param string               $newId       The id of the new (copied) item.
     * @param string               $strLangCode The language code being processed.
     *
     * @return void
     */
    public function copyTranslatedDataFor(array $sourceData, string $newId, string $strLangCode): void;
}
