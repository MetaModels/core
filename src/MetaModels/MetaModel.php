<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2015 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Christopher Boelter <c.boelter@cogizz.de>
 * @author     David Greminger <david.greminger@1up.io>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Martin Treml <github@r2pi.net>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Chris Raidler <c.raidler@rad-consulting.ch>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2012-2015 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels;

use MetaModels\Attribute\IComplex;
use MetaModels\Attribute\ISimple as ISimpleAttribute;
use MetaModels\Attribute\ITranslated;
use MetaModels\DataAccess\ItemPersister;
use MetaModels\Filter\Filter;
use MetaModels\Attribute\IAttribute;
use MetaModels\Filter\IFilter;
use MetaModels\Filter\Rules\StaticIdList;

/**
 * This is the main MetaModel class.
 *
 * @see MetaModelFactory::byId()        to instantiate a MetaModel by its ID.
 * @see MetaModelFactory::byTableName() to instantiate a MetaModel by its table name.
 *
 * This class handles all attribute definition instantiation and can be queried for a view instance to certain entries.
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
     * The service container.
     *
     * @var IMetaModelsServiceContainer
     */
    protected $serviceContainer;

    /**
     * Instantiate a MetaModel.
     *
     * @param array $arrData The information array, for information on the available columns, refer to
     *                       documentation of table tl_metamodel.
     */
    public function __construct($arrData)
    {
        foreach ($arrData as $strKey => $varValue) {
            $this->arrData[$strKey] = $this->tryUnserialize($varValue);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getServiceContainer()
    {
        return $this->serviceContainer;
    }

    /**
     * Set the service container.
     *
     * @param IMetaModelsServiceContainer $serviceContainer The service container.
     *
     * @return MetaModel
     */
    public function setServiceContainer($serviceContainer)
    {
        $this->serviceContainer = $serviceContainer;

        return $this;
    }

    /**
     * Retrieve the database instance to use.
     *
     * @return \Contao\Database
     */
    protected function getDatabase()
    {
        return $this->serviceContainer->getDatabase();
    }

    /**
     * Try to unserialize a value.
     *
     * @param string $value The string to process.
     *
     * @return mixed
     */
    protected function tryUnserialize($value)
    {
        if (!is_array($value) && (substr($value, 0, 2) == 'a:')) {
            $unSerialized = unserialize($value);
        }

        if (isset($unSerialized) && is_array($unSerialized)) {
            return $unSerialized;
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttribute(IAttribute $objAttribute)
    {
        if (!$this->hasAttribute($objAttribute->getColName())) {
            $this->arrAttributes[$objAttribute->getColName()] = $objAttribute;
        }

        return $this;
    }

    /**
     * {@inheritdoc}
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
        return $objAttribute instanceof ISimpleAttribute;
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
     * Retrieve all attributes implementing the given interface.
     *
     * @param string $interface The interface name.
     *
     * @return array
     */
    protected function getAttributeImplementing($interface)
    {
        $result = array();
        foreach ($this->getAttributes() as $colName => $attribute) {
            if ($attribute instanceof $interface) {
                $result[$colName] = $attribute;
            }
        }

        return $result;
    }

    /**
     * This method retrieves all complex attributes from the current MetaModel.
     *
     * @return IComplex[] all complex attributes defined for this instance.
     */
    protected function getComplexAttributes()
    {
        return $this->getAttributeImplementing('MetaModels\Attribute\IComplex');
    }

    /**
     * This method retrieves all simple attributes from the current MetaModel.
     *
     * @return ISimpleAttribute[] all simple attributes defined for this instance.
     */
    protected function getSimpleAttributes()
    {
        return $this->getAttributeImplementing('MetaModels\Attribute\ISimple');
    }

    /**
     * This method retrieves all translated attributes from the current MetaModel.
     *
     * @return ITranslated[] all translated attributes defined for this instance.
     */
    protected function getTranslatedAttributes()
    {
        return $this->getAttributeImplementing('MetaModels\Attribute\ITranslated');
    }

    /**
     * Narrow down the list of Ids that match the given filter.
     *
     * @param IFilter|null $objFilter The filter to search the matching ids for.
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
     * @param string[]         $arrAttrOnly The list of attributes to return, if any.
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
                    $arrData[$strKey] = $varValue;
                }
            }

            /** @noinspection PhpUndefinedFieldInspection */
            $arrResult[$objRow->id] = $arrData;
        }

        return $arrResult;
    }

    /**
     * Build a list of the correct amount of "?" for use in a db query.
     *
     * @param array $parameters The parameters.
     *
     * @return string
     */
    protected function buildDatabaseParameterList($parameters)
    {
        return implode(',', array_fill(0, count($parameters), '?'));
    }

    /**
     * Fetch the "native" database rows with the given ids.
     *
     * @param string[] $arrIds      The ids of the items to retrieve the order of ids is used for sorting of the return
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
        $parameters = array_merge($arrIds, $arrIds);
        $objRow     = $this->getDatabase()
            ->prepare(
                sprintf(
                    'SELECT * FROM %s WHERE id IN (%s) ORDER BY FIELD(id,%s)',
                    $this->getTableName(),
                    $this->buildDatabaseParameterList($arrIds),
                    $this->buildDatabaseParameterList($arrIds)
                )
            )
            ->execute($parameters);

        /** @noinspection PhpUndefinedFieldInspection */
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
     * @param ITranslated $attribute The attribute to fetch the values for.
     *
     * @param string[]    $ids       The ids of the items to retrieve the order of ids is used for sorting of the return
     *                               values.
     *
     * @return array an array of all matched items, sorted by the id list.
     */
    protected function fetchTranslatedAttributeValues(ITranslated $attribute, $ids)
    {
        $attributeData = $attribute->getTranslatedDataFor($ids, $this->getActiveLanguage());
        $missing       = array_diff($ids, array_keys($attributeData));

        if ($missing) {
            $attributeData += $attribute->getTranslatedDataFor($missing, $this->getFallbackLanguage());
        }

        return $attributeData;
    }

    /**
     * This method is called to retrieve the data for certain items from the database.
     *
     * @param string[] $ids      The ids of the items to retrieve the order of ids is used for sorting of the
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
        $attributes     = $this->getAttributeByNames($attrOnly);
        $attributeNames = array_intersect(
            array_keys($attributes),
            array_keys(array_merge($this->getComplexAttributes(), $this->getTranslatedAttributes()))
        );

        foreach ($attributeNames as $attributeName) {
            $attribute = $attributes[$attributeName];

            /** @var IAttribute $attribute */
            $attributeName = $attribute->getColName();

            // If it is translated, fetch the translated data now.
            if ($this->isTranslatedAttribute($attribute)) {
                /** @var ITranslated $attribute */
                $attributeData = $this->fetchTranslatedAttributeValues($attribute, $ids);
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
                // Do only skip if the key does not exist. Do not use isset() here as "null" is a valid value.
                if (!array_key_exists($strColName, $arrResult[$intId])) {
                    continue;
                }
                $value  = $arrResult[$intId][$strColName];
                $value2 = $objAttribute->unserializeData($arrResult[$intId][$strColName]);
                // Deprecated fallback, attributes should deserialize themselves for a long time now.
                if ($value === $value2) {
                    $value2 = $this->tryUnserialize($value);
                    if ($value !== $value2) {
                        trigger_error(
                            sprintf(
                                'Attribute type %s should implement method unserializeData() and  serializeData().',
                                $objAttribute->get('type')
                            ),
                            E_USER_DEPRECATED
                        );
                    }
                }
                // End of deprecated fallback.
                $arrResult[$intId][$strColName] = $value2;
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
        $strGetter = 'get' . $strKey;
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
        $tmp = explode('-', $GLOBALS['TL_LANGUAGE']);
        return array_shift($tmp);
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
     * Retrieve all attributes with the given names.
     *
     * @param string[] $attrNames The attribute names, if empty all attributes will be returned.
     *
     * @return IAttribute[]
     */
    protected function getAttributeByNames($attrNames = array())
    {
        if (empty($attrNames)) {
            return $this->arrAttributes;
        }

        $result = array();
        foreach ($attrNames as $attributeName) {
            $result[$attributeName] = $this->arrAttributes[$attributeName];
        }

        return $result;
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
                $arrFilteredIds = $this
                    ->getDatabase()
                    ->prepare(
                        sprintf(
                            'SELECT id FROM %s WHERE id IN(%s) ORDER BY %s %s',
                            $this->getTableName(),
                            $this->buildDatabaseParameterList($arrFilteredIds),
                            $strSortBy,
                            $strSortOrder
                        )
                    )
                    ->execute($arrFilteredIds)
                    ->fetchEach('id');
            } elseif ($strSortBy == 'random') {
                shuffle($arrFilteredIds);
            }
        }

        // Apply limiting then.
        if ($intOffset > 0 || $intLimit > 0) {
            $arrFilteredIds = array_slice($arrFilteredIds, $intOffset, $intLimit ?: null);
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

        $objRow = $this
            ->getDatabase()
            ->prepare(sprintf(
                'SELECT COUNT(id) AS count FROM %s WHERE id IN(%s)',
                $this->getTableName(),
                $this->buildDatabaseParameterList($arrFilteredIds)
            ))
            ->execute($arrFilteredIds);

        /** @noinspection PhpUndefinedFieldInspection */
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

        $objRow = $this
            ->getDatabase()
            ->prepare(sprintf(
                'SELECT id,vargroup FROM %s WHERE varbase=0 AND vargroup IN (%s)',
                $this->getTableName(),
                $this->buildDatabaseParameterList($arrIds)
            ))
            ->execute($arrIds);

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

        $objRow = $this
            ->getDatabase()
            ->prepare(sprintf(
                'SELECT id,vargroup FROM %1$s WHERE vargroup IN (SELECT vargroup FROM %1$s WHERE id IN (%2$s))',
                $this->getTableName(),
                $this->buildDatabaseParameterList($arrIds)
            ))
            ->execute($arrIds);

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
     * {@inheritdoc}
     */
    public function saveItem($objItem)
    {
        $persister = new ItemPersister($this, $this->getDatabase());
        $persister->saveItem($objItem);
    }

    /**
     * {@inheritdoc}
     */
    public function delete(IItem $objItem)
    {
        $persister = new ItemPersister($this, $this->getDatabase());
        $persister->deleteItem($objItem);
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
            $objFilterSettings = $this->getServiceContainer()->getFilterFactory()->createCollection($intFilterSettings);
            $objFilterSettings->addRules($objFilter, $arrFilterUrl);
        }
        return $objFilter;
    }

    /**
     * {@inheritdoc}
     */
    public function getView($intViewId = 0)
    {
        return $this->getServiceContainer()->getRenderSettingFactory()->createCollection($this, $intViewId);
    }
}
