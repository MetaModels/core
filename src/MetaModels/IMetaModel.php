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
 * @author     David Maack <david.maack@arcor.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels;

use MetaModels\Attribute\IAttribute;
use MetaModels\Filter\IFilter;

/**
 * This is the main MetaModel interface.
 *
 * This interface handles all attribute definition instantiation and can be queried for a view instance to certain
 * entries.
 *
 * @see MetaModelFactory::byId                             To instantiate a MetaModel by its ID.
 *
 * @see \MetaModels\IFactory::getMetaModel($metaModelName) To instantiate a MetaModel by its table name.
 */
interface IMetaModel
{
    /**
     * Retrieve the service container.
     *
     * @return IMetaModelsServiceContainer
     */
    public function getServiceContainer();

    /**
     * Adds an attribute to the internal list of attributes.
     *
     * @param IAttribute $objAttribute The attribute instance to add.
     *
     * @return IMetaModel
     */
    public function addAttribute(IAttribute $objAttribute);

    /**
     * Checks if an attribute with the given name has been added to the internal list.
     *
     * @param string $strAttributeName The name of the attribute to search.
     *
     * @return bool
     */
    public function hasAttribute($strAttributeName);

    /**
     * Get a configuration setting.
     *
     * @param string $strKey The key of the property that shall be fetched.
     *
     * @return mixed The value provided during instantiation.
     *               This includes all attributes from the database table tl_metamodel.
     */
    public function get($strKey);

    /**
     * Retrieve the table name for this MetaModel.
     *
     * @return string the table name for the MetaModel table.
     */
    public function getTableName();

    /**
     * Retrieve the human readable name for this MetaModel.
     *
     * @return string the name for the MetaModel.
     */
    public function getName();

    /**
     * Returns the array of all attribute instances that are defined for this MetaModel instance.
     *
     * @return IAttribute[] as name=>instance
     */
    public function getAttributes();

    /**
     * Return the array of attribute instances that are not defined for variant overriding.
     *
     * @return array
     */
    public function getInVariantAttributes();

    /**
     * Determines if this MetaModel instance is subject to translation.
     *
     * @return bool true if the MetaModel is translated, false otherwise.
     */
    public function isTranslated();

    /**
     * Determines if this MetaModel instance is subject to variant handling.
     *
     * @return bool true if variants are handled, false otherwise.
     */
    public function hasVariants();

    /**
     * Fetches all language codes that have been marked as available for translation in this MetaModel.
     *
     * @return string[]|null An array containing all codes if the MetaModel is translated,
     *                       null if translation is not active.
     */
    public function getAvailableLanguages();

    /**
     * Fetches the language code that has been marked as fallback language for translation in this MetaModel.
     *
     * @return string|null If the MetaModel is translated the language code to be used as fallback,
     *                     null if translation is not active.
     */
    public function getFallbackLanguage();

    /**
     * Get the current active language.
     *
     * @return string the language code.
     */
    public function getActiveLanguage();

    /**
     * Fetches the instance of the attribute with the given name.
     *
     * @param string $strAttributeName The name of the attribute to search.
     *
     * @return IAttribute the instance or null if not found.
     */
    public function getAttribute($strAttributeName);

    /**
     * Fetches the instance of the attribute with the given id.
     *
     * @param int $intId The id of the attribute to search.
     *
     * @return null|IAttribute the instance or null if not found.
     */
    public function getAttributeById($intId);

    /**
     * Search the MetaModel for the item with the given Id.
     *
     * @param int      $intId       The Id to be searched.
     *
     * @param string[] $arrAttrOnly Names of the attributes that shall be enclosed in the result, defaults to empty
     *                              which means all attributes.
     *
     * @return IItem|null The item if found, NULL otherwise.
     */
    public function findById($intId, $arrAttrOnly = array());

    /**
     * Filter the MetaModel by the provided filter settings.
     *
     * @param IFilter|null $objFilter    The filter object to use or null if none.
     *
     * @param string       $strSortBy    Optional name of the attribute the entries shall be sorted.
     *
     * @param int          $intOffset    Optional offset for the first item.
     *
     * @param int          $intLimit     Optional amount of items to retrieve.
     *
     * @param string       $strSortOrder Optional sorting direction, either 'ASC'(default) or 'DESC'.
     *
     * @param string[]     $arrAttrOnly  Names of the attributes that shall be enclosed in the result, defaults to
     *                                   empty which means all attributes.
     *
     * @return IItems|IItem[] The collection of IItem instances that match the given filter.
     */
    public function findByFilter(
        $objFilter,
        $strSortBy = '',
        $intOffset = 0,
        $intLimit = 0,
        $strSortOrder = 'ASC',
        $arrAttrOnly = array()
    );

    /**
     * Filter the MetaModel by the provided filter settings and return the ids of all matching items.
     *
     * @param IFilter|null $objFilter    The filter object to use or null if none.
     *
     * @param string       $strSortBy    Optional name of the attribute the entries shall be sorted.
     *
     * @param int          $intOffset    Optional offset for the first item.
     *
     * @param int          $intLimit     Optional amount of items to retrieve.
     *
     * @param string       $strSortOrder Optional sorting direction, either 'ASC'(default) or 'DESC'.
     *
     * @return string[] the ids of items that match the given filter.
     */
    public function getIdsFromFilter($objFilter, $strSortBy = '', $intOffset = 0, $intLimit = 0, $strSortOrder = 'ASC');

    /**
     * Fetch the amount of matching items against the given filter.
     *
     * @param IFilter|null $objFilter The filter object to use or null if none.
     *
     * @return int the amount of matching items.
     */
    public function getCount($objFilter);

    /**
     * Get all variant base items, filtered by the provided filter settings.
     *
     * @param IFilter $objFilter The filter to use or null if no filtering.
     *
     * @return IItems the collection of IItem instances that match the given filter.
     */
    public function findVariantBase($objFilter);

    /**
     * Get variants for the given ids, filtered by the provided filter settings.
     *
     * @param array   $arrIds    The Ids of the base elements.
     *
     * @param IFilter $objFilter The filter to use or null if no filtering.
     *
     * @return IItems The collection of IItem instances that match the given filter.
     */
    public function findVariants($arrIds, $objFilter);

    /**
     * Find all variants of the given item.
     *
     * This methods makes no difference between the variant base item and other variants.
     *
     * @param array        $arrIds    The Ids of the base elements.
     *
     * @param IFilter|null $objFilter The filter to use or null if no filtering.
     *
     * @return IItems the collection of IItem instances that match the given filter.
     */
    public function findVariantsWithBase($arrIds, $objFilter);

    /**
     * Get all options of the given attribute.
     *
     * @param string  $strAttribute The attribute to fetch options from.
     *
     * @param IFilter $objFilter    The filter to use or null if no filtering.
     *
     * @return array all options matching the given filter for the given attribute to be usable in a filter select
     *               widget.
     */
    public function getAttributeOptions($strAttribute, $objFilter = null);

    /**
     * Save an item into the database.
     *
     * @param IItem $objItem The item to save to the database.
     *
     * @return void
     */
    public function saveItem($objItem);

    /**
     * Remove an item from the database.
     *
     * @param IItem $objItem The item to delete from the database.
     *
     * @return void
     */
    public function delete(IItem $objItem);

    /**
     * Prepare an empty filter object for this meta model. The returned filter contains no rules.
     *
     * @return IFilter the filter object.
     */
    public function getEmptyFilter();

    /**
     * Generates a filter object that takes the given attributes into account.
     *
     * @param int   $intFilterSettings The id of the filter settings to use.
     *
     * @param array $arrFilterUrl      The filter url parameters (usually the contents of $_GET etc.).
     *
     * @return IFilter the generated filter object.
     */
    public function prepareFilter($intFilterSettings, $arrFilterUrl);

    /**
     * Return a IMetaModelRenderSettings instance for this MetaModel.
     *
     * @param int $intViewId The id of the render settings to retrieve.
     *
     * @return \MetaModels\Render\Setting\ICollection
     */
    public function getView($intViewId = 0);
}
