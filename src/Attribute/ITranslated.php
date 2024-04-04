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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Attribute;

use MetaModels\ITranslatedMetaModel;

/**
 * This is the interface for translated attributes.
 * To create MetaModelAttribute instances, use the @link{MetaModelAttributeFactory}
 * This interface handles all interfacing needed for translated attributes.
 */
interface ITranslated extends IAttribute
{
    /**
     * Queries the attribute for it's parent MetaModel instance.
     *
     * @return ITranslatedMetaModel the MetaModel instance.
     */
    public function getMetaModel();

    /**
     * Search matches for the given expression.
     *
     * @param string                 $strPattern   The text to search for. This may contain wildcards.
     * @param list<non-empty-string> $arrLanguages Array of valid language codes that shall be searched.
     *                                             (optional) If empty, all languages will be taken into account.
     *
     * @return list<string> the ids of matching items.
     */
    public function searchForInLanguages($strPattern, $arrLanguages = []);

    /**
     * Set a value for an item in a certain language.
     *
     * @param array<string, array<string, mixed>> $arrValues   The values to be set in id => value layout.
     * @param string                              $strLangCode The language code for which the data shall be retrieved.
     *
     * @return void
     */
    public function setTranslatedDataFor($arrValues, $strLangCode);

    /**
     * Get values for the given items in a certain language.
     *
     * @param list<string> $arrIds      The ids for which values shall be retrieved.
     * @param string       $strLangCode The language code for which the data shall be retrieved.
     *
     * @return array<string, array<string, mixed>> the values.
     */
    public function getTranslatedDataFor($arrIds, $strLangCode);

    /**
     * Remove values for items in a certain language.
     *
     * @param list<string> $arrIds      The ids for which values shall be removed.
     * @param string       $strLangCode The language code for which the data shall be removed.
     *
     * @return void
     */
    public function unsetValueFor($arrIds, $strLangCode);
}
