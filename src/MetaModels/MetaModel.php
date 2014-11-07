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

namespace MetaModels;

use MetaModels\Attribute\IComplex;
use MetaModels\Attribute\ISimple;
use MetaModels\Attribute\ITranslated;
use MetaModels\Filter\Filter;
use MetaModels\Attribute\IAttribute;
use MetaModels\Filter\IFilter;
use MetaModels\Render\Setting\Factory as RenderSettingFactory;
use MetaModels\Filter\Rules\StaticIdList;
use MetaModels\Filter\Setting\Factory as FilterFactory;

/**
 * This is the main MetaModel class.
 *
 * @see MetaModelFactory::byId()        to instantiate a MetaModel by its ID.
 * @see MetaModelFactory::byTableName() to instantiate a MetaModel by its table name.
 *
 * This class handles all attribute definition instantiation and can be queried for a view instance to certain entries.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class MetaModel implements IMetaModel
{
    /**
     * Information data of this MetaModel instance.
     *
     * This is the data from tl_metamodel.
     *
     * @var array
     */
    protected $arrData = array();

    /**
     * This holds all attribute instances.
     *
     * Association is $colName => object
     *
     * @var array
     */
    protected $arrAttributes = array();

    /**
     * The attached Database instance.
     *
     * @var \Database
     */
    protected $database;

    /**
     * Instantiate a MetaModel.
     *
     * @param array $arrData The information array, for information on the available columns, refer to
     *                       documentation of table tl_metamodel.
     */
    public function __construct($arrData)
    {
        foreach ($arrData as $strKey => $varValue) {
            // @codingStandardsIgnoreStart
            $unSerialized = @unserialize($varValue);
            // @codingStandardsIgnoreEnd

            if (is_array($unSerialized)) {
                $this->arrData[$strKey] = $unSerialized;
            } else {
                $this->arrData[$strKey] = $varValue;
            }
        }

        $this->database = \Database::getInstance();
    }

    /**
     * Retrieve the database instance to use.
     *
     * @return \Database
     */
    protected function getDatabase()
    {
        return $this->database;
    }

    /**
     * Adds an attribute to the internal list of attributes.
     *
     * @param IAttribute $objAttribute The attribute instance to add.
     *
     * @return IMetaModel Self for fluent coding.
     */
    public function addAttribute(IAttribute $objAttribute)
    {
        if (!$this->hasAttribute($objAttribute->getColName())) {
            $this->arrAttributes[$objAttribute->getColName()] = $objAttribute;
        }

        return $this;
    }

    /**
     * Checks if an attribute with the given name has been added to the internal list.
     *
     * @param string $strAttributeName The name of the attribute to search.
     *
     * @return bool
     */
    public function hasAttribute($strAttributeName)
    {
        return array_key_exists($strAttributeName, $this->arrAttributes);
    }

    /**
     * Determine if the given attribute is a complex one.
     *
     * @param IAttribute $objAttribute The attribute to test.
     *
     * @return bool true if it is complex, false otherwise.
     */
    protected function isComplexAttribute($objAttribute)
    {
        return $objAttribute instanceof IComplex;
    }

    /**
     * Determine if the given attribute is a simple one.
     *
     * @param IAttribute $objAttribute The attribute to test.
     *
     * @return bool true if it is simple, false otherwise.
     */
    protected function isSimpleAttribute($objAttribute)
    {
        return $objAttribute instanceof ISimple;
    }

    /**
     * Determine if the given attribute is a translated one.
     *
     * @param IAttribute $objAttribute The attribute to test.
     *
     * @return bool true if it is translated, false otherwise.
     */
    protected function isTranslatedAttribute($objAttribute)
    {
        return $objAttribute instanceof ITranslated;
    }

    /**
     * This method retrieves all complex attributes from the current MetaModel.
     *
     * @return IComplex[] all complex attributes defined for this instance.
     */
    protected function getComplexAttributes()
    {
        $arrResult = array();
        foreach ($this->getAttributes() as $objAttribute) {
            if ($this->isComplexAttribute($objAttribute)) {
                $arrResult[] = $objAttribute;
            }
        }
        return $arrResult;
    }

    /**
     * This method retrieves all simple attributes from the current MetaModel.
     *
     * @return ISimple[] all simple attributes defined for this instance.
     */
    protected function getSimpleAttributes()
    {
        $arrResult = array();
        foreach ($this->getAttributes() as $objAttribute) {
            if ($this->isSimpleAttribute($objAttribute)) {
                $arrResult[] = $objAttribute;
            }
        }
        return $arrResult;
    }

    /**
     * This method retrieves all translated attributes from the current MetaModel.
     *
     * @return ITranslated[] all translated attributes defined for this instance.
     */
    protected function getTranslatedAttributes()
    {
        $arrResult = array();
        foreach ($this->getAttributes() as $objAttribute) {
            if ($this->isTranslatedAttribute($objAttribute)) {
                $arrResult[] = $objAttribute;
            }
        }
        return $arrResult;
    }

    /**
     * Narrow down the list of Ids that match the given filter.
     *
     * @param IFilter $objFilter The filter to search the matching ids for.
     *
     * @return array all matching Ids.
     */
    protected function getMatchingIds($objFilter)
    {
        if ($objFilter) {
            $arrFilteredIds = $objFilter->getMatchingIds();
            if ($arrFilteredIds !== null) {
                return $arrFilteredIds;
            }
        }

        // Either no filter object or all ids allowed => return all ids.
        // if no id filter is passed, we assume all ids are provided.
        $objRow = $this->getDatabase()->execute('SELECT id FROM ' . $this->getTableName());

        return $objRow->fetchEach('id');
    }

    /**
     * Convert a database result to a result array.
     *
     * @param \Database\Result $objRow      The database result.
     *
     * @param array            $arrAttrOnly The list of attributes to return, if any.
     *
     * @return array
     */
    protected function convertRowsToResult($objRow, $arrAttrOnly = array())
    {
        $arrResult = array();

        while ($objRow->next()) {
            $arrData = array();

            foreach ($objRow->row() as $strKey => $varValue) {
                if ((!$arrAttrOnly) || (in_array($strKey, $arrAttrOnly))) {
                    // @codingStandardsIgnoreStart
                    $unSerialized = @unserialize($varValue);
                    // @codingStandardsIgnoreEnd

                    if (is_array($unSerialized)) {
                        $arrData[$strKey] = $unSerialized;
                    } else {
                        $arrData[$strKey] = $varValue;
                    }
                }
            }
            $arrResult[$objRow->id] = $arrData;
        }

        return $arrResult;
    }

    /**
     * Fetch the "native" database rows with the given ids.
     *
     * @param int[]    $arrIds      The ids of the items to retrieve the order of ids is used for sorting of the return
     *                              values.
     *
     * @param string[] $arrAttrOnly Names of the attributes that shall be contained in the result, defaults to array()
     *                              which means all attributes.
     *
     * @return array an array containing the database rows with each column "deserialized".
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function fetchRows($arrIds, $arrAttrOnly = array())
    {
        // Ensure proper integer ids for SQL injection safety reasons.
        $strIdList = implode(',', array_map('intval', $arrIds));
        $objRow    = $this->getDatabase()->executeUncached(sprintf(
            'SELECT * FROM %s WHERE id IN (%s) ORDER BY FIELD(id,%s)',
            $this->getTableName(),
            $strIdList,
            $strIdList
        ));

        if ($objRow->numRows == 0) {
            return array();
        }

        // If we have an attribute restriction, make sure we keep the system columns. See #196.
        if ($arrAttrOnly) {
            $arrAttrOnly = array_merge($GLOBALS['METAMODELS_SYSTEM_COLUMNS'], $arrAttrOnly);
        }

        return $this->convertRowsToResult($objRow, $arrAttrOnly);
    }

    /**
     * This method is called to retrieve the data for certain items from the database.
     *
     * @param int[]    $ids      The ids of the items to retrieve the order of ids is used for sorting of the
     *                           return values.
     *
     * @param array    $result   The current values.
     *
     * @param string[] $attrOnly Names of the attributes that shall be contained in the result, defaults to array()
     *                           which means all attributes.
     *
     * @return array an array of all matched items, sorted by the id list.
     */
    protected function fetchAdditionalAttributes($ids, $result, $attrOnly = array())
    {
        foreach (array_merge($this->getComplexAttributes(), $this->getTranslatedAttributes()) as $attribute) {
            /** @var IAttribute $attribute */
            $attributeName = $attribute->getColName();

            if (!in_array($attributeName, $attrOnly)) {
                continue;
            }

            // If it is translated, fetch the translated data now.
            if ($this->isTranslatedAttribute($attribute)) {
                /** @var ITranslated $attribute */
                $attributeData = $attribute->getTranslatedDataFor($ids, $this->getActiveLanguage());
                $missing       = array_diff($ids, array_keys($attributeData));

                if ($missing) {
                    $attributeData += $attribute->getTranslatedDataFor($missing, $this->getFallbackLanguage());
                }
            } else {
                /** @var IComplex $attribute */
                $attributeData = $attribute->getDataFor($ids);
            }

            foreach (array_keys($result) as $id) {
                $result[$id][$attributeName] = isset($attributeData[$id]) ? $attributeData[$id] : null;
            }
        }

        return $result;
    }

    /**
     * This method is called to retrieve the data for certain items from the database.
     *
     * @param int[]    $arrIds      The ids of the items to retrieve the order of ids is used for sorting of the
     *                              return values.
     *
     * @param string[] $arrAttrOnly Names of the attributes that shall be contained in the result, defaults to array()
     *                              which means all attributes.
     *
     * @return \MetaModels\IItems a collection of all matched items, sorted by the id list.
     */
    protected function getItemsWithId($arrIds, $arrAttrOnly = array())
    {
        $arrIds = array_unique(array_filter($arrIds));

        if (!$arrIds) {
            return new Items(array());
        }

        if (!$arrAttrOnly) {
            $arrAttrOnly = array_keys($this->getAttributes());
        }

        $arrResult = $this->fetchRows($arrIds, $arrAttrOnly);

        // Give simple attributes the chance for editing the "simple" data.
        foreach ($this->getSimpleAttributes() as $objAttribute) {
            // Get current simple attribute.
            $strColName = $objAttribute->getColName();

            // Run each row.
            foreach (array_keys($arrResult) as $intId) {
                if (!isset($arrResult[$intId][$strColName])) {
                    continue;
                }
                $arrResult[$intId][$strColName] = $objAttribute->unserializeData($arrResult[$intId][$strColName]);
            }
        }

        // Determine "independent attributes" (complex and translated) and inject their content into the row.
        $arrResult = $this->fetchAdditionalAttributes($arrIds, $arrResult, $arrAttrOnly);
        $arrItems  = array();
        foreach ($arrResult as $arrEntry) {
            $arrItems[] = new Item($this, $arrEntry);
        }

        $objItems = new Items($arrItems);

        return $objItems;
    }

    /**
     * Clone the given filter or create an empty one if no filter has been passed.
     *
     * @param IFilter|null $objFilter The filter to clone.
     *
     * @return IFilter the cloned filter.
     */
    protected function copyFilter($objFilter)
    {
        if ($objFilter) {
            $objNewFilter = $objFilter->createCopy();
        } else {
            $objNewFilter = $this->getEmptyFilter();
        }
        return $objNewFilter;
    }

    /**
     * {@inheritdoc}
     */
    public function get($strKey)
    {
        // Try to retrieve via getter method.
        $strGetter = 'get'.$strKey;
        if (method_exists($this, $strGetter)) {
            return $this->$strGetter();
        }

        // Return via raw array if available.
        if (array_key_exists($strKey, $this->arrData)) {
            return $this->arrData[$strKey];
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getTableName()
    {
        return array_key_exists('tableName', $this->arrData) ? $this->arrData['tableName'] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->arrData['name'];
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributes()
    {
        return $this->arrAttributes;
    }


    /**
     * {@inheritdoc}
     */
    public function getInVariantAttributes()
    {
        $arrAttributes = $this->getAttributes();
        if (!$this->hasVariants()) {
            return $arrAttributes;
        }
        // Remove all attributes that are selected for overriding.
        foreach ($arrAttributes as $strAttributeId => $objAttribute) {
            if ($objAttribute->get('isvariant')) {
                unset($arrAttributes[$strAttributeId]);
            }
        }
        return $arrAttributes;
    }


    /**
     * {@inheritdoc}
     */
    public function isTranslated()
    {
        return $this->arrData['translated'];
    }

    /**
     * {@inheritdoc}
     */
    public function hasVariants()
    {
        return $this->arrData['varsupport'];
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailableLanguages()
    {
        if ($this->isTranslated()) {
            return array_keys((array) $this->arrData['languages']);
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getFallbackLanguage()
    {
        if ($this->isTranslated()) {
            foreach ($this->arrData['languages'] as $strLangCode => $arrData) {
                if ($arrData['isfallback']) {
                    return $strLangCode;
                }
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     *
     * The value is taken from $GLOBALS['TL_LANGUAGE']
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function getActiveLanguage()
    {
        return $GLOBALS['TL_LANGUAGE'];
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute($strAttributeName)
    {
        $arrAttributes = $this->getAttributes();
        return array_key_exists($strAttributeName, $arrAttributes)
            ? $arrAttributes[$strAttributeName]
            : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeById($intId)
    {
        foreach ($this->getAttributes() as $objAttribute) {
            if ($objAttribute->get('id') == $intId) {
                return $objAttribute;
            }
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function findById($intId, $arrAttrOnly = array())
    {
        if (!$intId) {
            return null;
        }
        $objItems = $this->getItemsWithId(array($intId), $arrAttrOnly);
        if ($objItems && $objItems->first()) {
            return $objItems->getItem();
        }
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function findByFilter(
        $objFilter,
        $strSortBy = '',
        $intOffset = 0,
        $intLimit = 0,
        $strSortOrder = 'ASC',
        $arrAttrOnly = array()
    ) {
        return $this->getItemsWithId(
            $this->getIdsFromFilter(
                $objFilter,
                $strSortBy,
                $intOffset,
                $intLimit,
                $strSortOrder
            ),
            $arrAttrOnly
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getIdsFromFilter($objFilter, $strSortBy = '', $intOffset = 0, $intLimit = 0, $strSortOrder = 'ASC')
    {
        $arrFilteredIds = $this->getMatchingIds($objFilter);

        // If desired, sort the entries.
        if ($arrFilteredIds && $strSortBy != '') {
            if ($objSortAttribute = $this->getAttribute($strSortBy)) {
                $arrFilteredIds = $objSortAttribute->sortIds($arrFilteredIds, $strSortOrder);
            } elseif (in_array($strSortBy, array('id', 'pid', 'tstamp', 'sorting'))) {
                // Sort by database values.
                $arrFilteredIds = $this->getDatabase()->execute(
                    sprintf(
                        'SELECT id FROM %s WHERE id IN(%s) ORDER BY %s %s',
                        $this->getTableName(),
                        implode(',', $arrFilteredIds),
                        $strSortBy,
                        $strSortOrder
                    )
                )->fetchEach('id');
            } elseif ($strSortBy == 'random') {
                shuffle($arrFilteredIds);
            }
        }

        // Apply limiting then.
        if ($intOffset > 0 || $intLimit > 0) {
            $arrFilteredIds = array_slice($arrFilteredIds, $intOffset, $intLimit);
        }
        return $arrFilteredIds;
    }

    /**
     * {@inheritdoc}
     */
    public function getCount($objFilter)
    {
        $arrFilteredIds = $this->getMatchingIds($objFilter);
        if (count($arrFilteredIds) == 0) {
            return 0;
        }

        $objRow = $this->getDatabase()->execute(sprintf(
            'SELECT COUNT(id) AS count FROM %s WHERE id IN(%s)',
            $this->getTableName(),
            implode(',', $arrFilteredIds)
        ));

        return $objRow->count;
    }

    /**
     * {@inheritdoc}
     */
    public function findVariantBase($objFilter)
    {
        $objNewFilter = $this->copyFilter($objFilter);

        $objRow = $this->getDatabase()->execute('SELECT id FROM ' . $this->getTableName() . ' WHERE varbase=1');

        $objNewFilter->addFilterRule(new StaticIdList($objRow->fetchEach('id')));
        return $this->findByFilter($objNewFilter);
    }

    /**
     * {@inheritdoc}
     */
    public function findVariants($arrIds, $objFilter)
    {
        if (!$arrIds) {
            // Return an empty result.
            return $this->getItemsWithId(array());
        }
        $objNewFilter = $this->copyFilter($objFilter);

        $objRow = $this->getDatabase()->execute(sprintf(
            'SELECT id,vargroup FROM %s WHERE varbase=0 AND vargroup IN (%s)',
            $this->getTableName(),
            implode(',', $arrIds)
        ));

        $objNewFilter->addFilterRule(new StaticIdList($objRow->fetchEach('id')));
        return $this->findByFilter($objNewFilter);
    }

    /**
     * {@inheritdoc}
     */
    public function findVariantsWithBase($arrIds, $objFilter)
    {
        if (!$arrIds) {
            // Return an empty result.
            return $this->getItemsWithId(array());
        }
        $objNewFilter = $this->copyFilter($objFilter);

        $objRow = $this->getDatabase()->execute(sprintf(
            'SELECT id,vargroup FROM %s WHERE vargroup IN (SELECT vargroup FROM %s WHERE id IN (%s))',
            $this->getTableName(),
            $this->getTableName(),
            implode(',', $arrIds)
        ));
        $objNewFilter->addFilterRule(new StaticIdList($objRow->fetchEach('id')));
        return $this->findByFilter($objNewFilter);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeOptions($strAttribute, $objFilter = null)
    {
        $objAttribute = $this->getAttribute($strAttribute);
        if ($objAttribute) {
            if ($objFilter) {
                $arrFilteredIds = $this->getMatchingIds($objFilter);
                $arrFilteredIds = $objAttribute->sortIds($arrFilteredIds, 'ASC');
                return $objAttribute->getFilterOptions($arrFilteredIds, true);
            } else {
                return $objAttribute->getFilterOptions(null, true);
            }
        }

        return array();
    }

    /**
     * Update the value of a native column for the given ids with the given data.
     *
     * @param string $strColumn The column name to update (i.e. tstamp).
     *
     * @param array  $arrIds    The ids of the rows that shall be updated.
     *
     * @param mixed  $varData   The data to save. If this is an array, it is automatically serialized.
     *
     * @return void
     */
    protected function saveSimpleColumn($strColumn, $arrIds, $varData)
    {
        if (is_array($varData)) {
            $varData = serialize($varData);
        }

        $this->getDatabase()
            ->prepare(
                sprintf(
                    'UPDATE %s SET %s=? WHERE id IN (%s)',
                    $this->getTableName(),
                    $strColumn,
                    implode(',', $arrIds)
                )
            )
            ->execute($varData);
    }

    /**
     * Update an attribute for the given ids with the given data.
     *
     * @param IAttribute $objAttribute The attribute to save.
     *
     * @param array      $arrIds       The ids of the rows that shall be updated.
     *
     * @param mixed      $varData      The data to save in raw data.
     *
     * @param string     $strLangCode  The language code to save.
     *
     * @return void
     *
     * @throws \RuntimeException When an unknown attribute type is encountered.
     */
    protected function saveAttribute($objAttribute, $arrIds, $varData, $strLangCode)
    {
        // Call the serializeData for all simple attributes.
        if ($this->isSimpleAttribute($objAttribute)) {
            /** @var \MetaModels\Attribute\ISimple $objAttribute */
            $varData = $objAttribute->serializeData($varData);
        }

        $arrData = array();
        foreach ($arrIds as $intId) {
            $arrData[$intId] = $varData;
        }

        // Check for translated fields first, then for complex and save as simple then.
        if ($strLangCode && $this->isTranslatedAttribute($objAttribute)) {
            /** @var ITranslated $objAttribute */
            $objAttribute->setTranslatedDataFor($arrData, $strLangCode);
        } elseif ($this->isComplexAttribute($objAttribute)) {
            // Complex saving.
            $objAttribute->setDataFor($arrData);
        } elseif ($this->isSimpleAttribute($objAttribute)) {
            $objAttribute->setDataFor($arrData);
        } else {
            throw new \RuntimeException(
                'Unknown attribute type, can not save. Interfaces implemented: ' .
                implode(', ', class_implements($objAttribute))
            );
        }
    }

    /**
     * Update the variants with the value if needed.
     *
     * @param IItem  $item           The item to save.
     *
     * @param string $activeLanguage The language the values are in.
     *
     * @param int[]  $allIds         The ids of all variants.
     *
     * @return void
     */
    protected function updateVariants($item, $activeLanguage, $allIds)
    {
        foreach ($this->getAttributes() as $strAttributeId => $objAttribute) {
            if ($item->isVariant() && !($objAttribute->get('isvariant'))) {
                // Base not found, skip attribute.
                continue;
            }

            if ($item->isVariantBase() && !($objAttribute->get('isvariant'))) {
                // We have to override in variants.
                $arrIds = $allIds;
            } else {
                $arrIds = array($item->get('id'));
            }
            $this->saveAttribute($objAttribute, $arrIds, $item->get($strAttributeId), $activeLanguage);
        }
    }

    /**
     * Create a new item in the database.
     *
     * @param IItem $item The item to be created.
     *
     * @return void
     */
    protected function createNewItem($item)
    {
        $arrData = array
        (
            'tstamp' => $item->get('tstamp')
        );

        $blnNewBaseItem = false;
        if ($this->hasVariants()) {
            // No variant group is given, so we have a complete new base item this should be a workaround for these
            // values should be set by the GeneralDataMetaModel or whoever is calling this method.
            if ($item->get('vargroup') === null) {
                $item->set('varbase', '1');
                $item->set('vargroup', '0');
                $blnNewBaseItem = true;
            }
            $arrData['varbase']  = $item->get('varbase');
            $arrData['vargroup'] = $item->get('vargroup');
        }

        $intItemId = $this->getDatabase()
            ->prepare('INSERT INTO ' . $this->getTableName() . ' %s')
            ->set($arrData)
            ->execute()
            ->insertId;
        $item->set('id', $intItemId);

        // Add the variant group equal to the id.
        if ($blnNewBaseItem) {
            $this->saveSimpleColumn('vargroup', array($item->get('id')), $item->get('id'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function saveItem($objItem)
    {
        $objItem->set('tstamp', time());
        if (!$objItem->get('id')) {
            $this->createNewItem($objItem);
        }

        // Update system columns.
        if ($objItem->get('pid') !== null) {
            $this->saveSimpleColumn('pid', array($objItem->get('id')), $objItem->get('pid'));
        }
        if ($objItem->get('sorting') !== null) {
            $this->saveSimpleColumn('sorting', array($objItem->get('id')), $objItem->get('sorting'));
        }
        $this->saveSimpleColumn('tstamp', array($objItem->get('id')), $objItem->get('tstamp'));

        if ($this->isTranslated()) {
            $strActiveLanguage = $this->getActiveLanguage();
        } else {
            $strActiveLanguage = null;
        }

        $arrAllIds = array();
        if ($objItem->isVariantBase()) {
            $objVariants = $this->findVariantsWithBase(array($objItem->get('id')), null);
            foreach ($objVariants as $objVariant) {
                /** @var IItem $objVariant */
                $arrAllIds[] = $objVariant->get('id');
            }
        }

        $this->updateVariants($objItem, $strActiveLanguage, $arrAllIds);

        // Tell all attributes that the model has been saved. Useful for alias fields, edit counters etc.
        foreach ($this->getAttributes() as $objAttribute) {
            $objAttribute->modelSaved($objItem);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function delete(IItem $objItem)
    {
        $arrIds = array($objItem->get('id'));
        // Determine if the model is a variant base and if so, fetch the variants additionally.
        if ($objItem->isVariantBase()) {
            $objVariants = $objItem->getVariants(new Filter($this));
            foreach ($objVariants as $objVariant) {
                /** @var IItem $objVariant */
                $arrIds[] = $objVariant->get('id');
            }
        }

        // Complex attributes shall delete their values first.
        foreach ($this->getAttributes() as $objAttribute) {
            if ($this->isComplexAttribute($objAttribute)) {
                /** @var IComplex $objAttribute */
                $objAttribute->unsetDataFor($arrIds);
            }
        }
        // Now make the real row disappear.
        $this->getDatabase()->execute(sprintf(
            'DELETE FROM %s WHERE id IN (%s)',
            $this->getTableName(),
            implode(',', $arrIds)
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getEmptyFilter()
    {
        $objFilter = new Filter($this);

        return $objFilter;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareFilter($intFilterSettings, $arrFilterUrl)
    {
        $objFilter = $this->getEmptyFilter();
        if ($intFilterSettings) {
            $objFilterSettings = FilterFactory::byId($intFilterSettings);
            $objFilterSettings->addRules($objFilter, $arrFilterUrl);
        }
        return $objFilter;
    }

    /**
     * {@inheritdoc}
     */
    public function getView($intViewId = 0)
    {
        return RenderSettingFactory::byId($this, $intViewId);
    }
}
