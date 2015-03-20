<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\Attribute;

/**
 * This is the interface for translated attributes.
 * To create MetaModelAttribute instances, use the @link{MetaModelAttributeFactory}
 * This interface handles all interfacing needed for translated attributes.
 *
 * @package    MetaModels
 * @subpackage Interfaces
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
// FIXME: Should be renamed to ITranslatedAttribute
interface ITranslated extends IAttribute
{
    /**
     * Search matches for the given expression.
     *
     * @param string $strPattern   The text to search for. This may contain wildcards.
     *
     * @param array  $arrLanguages Array of valid language codes that shall be searched. (optional)
     *                             If empty, all languages will be taken into account.
     *
     * @return string[] the ids of matching items.
     */
    public function searchForInLanguages($strPattern, $arrLanguages = array());

    /**
     * Set a value for an item in a certain language.
     *
     * @param mixed[] $arrValues   The values to be set in id => value layout.
     *
     * @param string  $strLangCode The language code for which the data shall be retrieved.
     *
     * @return void
     */
    public function setTranslatedDataFor($arrValues, $strLangCode);

    /**
     * Get values for the given items in a certain language.
     *
     * @param string[] $arrIds      The ids for which values shall be retrieved.
     *
     * @param string   $strLangCode The language code for which the data shall be retrieved.
     *
     * @return mixed[] the values.
     */
    public function getTranslatedDataFor($arrIds, $strLangCode);

    /**
     * Remove values for items in a certain language.
     *
     * @param string[] $arrIds      The ids for which values shall be removed.
     *
     * @param string   $strLangCode The language code for which the data shall be removed.
     *
     * @return void
     */
    public function unsetValueFor($arrIds, $strLangCode);
}
