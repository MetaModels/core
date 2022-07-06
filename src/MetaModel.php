<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2022 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Christopher Boelter <c.boelter@cogizz.de>
 * @author     David Greminger <david.greminger@1up.io>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Martin Treml <github@r2pi.net>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Chris Raidler <c.raidler@rad-consulting.ch>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2022 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use MetaModels\Attribute\IAttribute;
use MetaModels\Attribute\IComplex;
use MetaModels\Attribute\ISimple as ISimpleAttribute;
use MetaModels\Attribute\ITranslated;
use MetaModels\Filter\Filter;
use MetaModels\Filter\IFilter;
use MetaModels\Filter\Rules\StaticIdList;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This is the main MetaModel class.
 *
 * @see MetaModelFactory::byId()        to instantiate a MetaModel by its ID.
 * @see MetaModelFactory::byTableName() to instantiate a MetaModel by its table name.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
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
     * The database connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * The system columns to preserve.
     *
     * @var string[]
     */
    private $systemColumns;

    /**
     * The cache existing ids.
     *
     * @var array
     */
    private $existingIds = [];

    /**
     * Instantiate a MetaModel.
     *
     * @param array $arrData                            The information array, for information on the available
     *                                                  columns, refer to documentation of table tl_metamodel.
     * @param EventDispatcherInterface|null $dispatcher The event dispatcher.
     * @param Connection|null               $connection The database connection.
     */
    public function __construct(
        $arrData,
        EventDispatcherInterface $dispatcher = null,
        Connection $connection = null
    ) {
        foreach ($arrData as $strKey => $varValue) {
            $this->arrData[$strKey] = $this->tryUnserialize($varValue);
        }
        $this->systemColumns = array_key_exists('system_columns', $arrData) ? $arrData['system_columns'] : [];

        $this->connection = $connection;
        $this->dispatcher = $dispatcher;
        if (null === $this->dispatcher) {
            // @codingStandardsIgnoreStart
            @trigger_error(
                'Not passing the event dispatcher as 2nd argument to "' . __METHOD__ . '" is deprecated ' .
                'and will cause an error in MetaModels 3.0',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
        }
        if (null === $this->connection) {
            // @codingStandardsIgnoreStart
            @trigger_error(
                'Not passing the database connection as 3rd argument to "' . __METHOD__ . '" is deprecated ' .
                'and will cause an error in MetaModels 3.0',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
        }
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated Inject services via constructor or setter.
     */
    public function getServiceContainer()
    {
        // @codingStandardsIgnoreStart
        @trigger_error(
            '"' .__METHOD__ . '" is deprecated and will get removed.',
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd
        return is_callable($this->serviceContainer) ? call_user_func($this->serviceContainer) : $this->serviceContainer;
    }

    /**
     * Set the service container.
     *
     * NOTE: this is deprecated - to prevent triggering deprecation notices, you may pass a closure here which
     * will then return the service container.
     *
     * @param \Closure|IMetaModelsServiceContainer $serviceContainer The service container.
     *
     * @return MetaModel
     *
     * @deprecated Inject services via constructor or setter.
     */
    public function setServiceContainer($serviceContainer, $deprecationNotice = true)
    {
        if ($deprecationNotice) {
            // @codingStandardsIgnoreStart
            @trigger_error(
                '"' .__METHOD__ . '" is deprecated and will get removed.',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
        }
        $this->serviceContainer = $serviceContainer;

        return $this;
    }

    /**
     * Retrieve the database instance to use.
     *
     * @return \Contao\Database
     *
     * @deprecated Use the doctrine connection instead.
     */
    protected function getDatabase()
    {
        // @codingStandardsIgnoreStart
        @trigger_error(
            '"' .__METHOD__ . '" is deprecated and will get removed.',
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd
        return $this->getServiceContainer()->getDatabase();
    }

    /**
     * Try to unserialize a value.
     *
     * @param mixed $value The string to process.
     *
     * @return mixed
     */
    protected function tryUnserialize($value)
    {
        if (!is_array($value)
            && 0 === strpos($value, 'a:')
            && is_array($unSerialized = unserialize($value, ['allowed_classes' => false]))) {
            return $unSerialized;
        }

        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function addAttribute(IAttribute $objAttribute)
    {
        if ($objAttribute instanceof ITranslated && !$this instanceof ITranslatedMetaModel) {
            // @coverageIgnoreStart
            // @codingStandardsIgnoreStart
            @\trigger_error(
                'Adding translated attributes to untranslated "\MetaModel\IMetamodel" instances is ' .
                'deprecated since MetaModels 2.2 and will error in 3.0. The MetaModel must implement ' .
                '"\MetaModels\ITranslatedMetaModel".',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
            // @coverageIgnoreEnd
        }

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
        return $this->getAttributeImplementing(IComplex::class);
    }

    /**
     * This method retrieves all simple attributes from the current MetaModel.
     *
     * @return ISimpleAttribute[] all simple attributes defined for this instance.
     */
    protected function getSimpleAttributes()
    {
        return $this->getAttributeImplementing(ISimpleAttribute::class);
    }

    /**
     * This method retrieves all translated attributes from the current MetaModel.
     *
     * @return ITranslated[] all translated attributes defined for this instance.
     */
    protected function getTranslatedAttributes()
    {
        return $this->getAttributeImplementing(ITranslated::class);
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
        return $this->getConnection()->createQueryBuilder()
            ->select('t.id')
            ->from($this->getTableName(), 't')
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);
    }

    /**
     * Build a list of the correct amount of "?" for use in a db query.
     *
     * @param array $parameters The parameters.
     *
     * @return string
     *
     * @deprecated Obsolete - Use doctrine query builders instead. To be removed in 3.0
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
        /** @var QueryBuilder $builder */
        $builder = $this->getConnection()->createQueryBuilder();
        $query   = $builder
            ->select('t.*')
            ->from($this->getTableName(), 't')
            ->where($builder->expr()->in('t.id', ':values'))
            ->setParameter('values', $arrIds, Connection::PARAM_STR_ARRAY)
            ->orderBy('FIELD(id, :values)')
            ->execute();

        // If we have an attribute restriction, make sure we keep the system columns. See #196.
        if ($arrAttrOnly) {
            $arrAttrOnly = array_merge($this->systemColumns, $arrAttrOnly);
        }

        $result = [];
        while ($row = $query->fetch(\PDO::FETCH_ASSOC)) {
            $data = [];
            foreach ($row as $attribute => $value) {
                if ((!$arrAttrOnly) || (in_array($attribute, $arrAttrOnly, true))) {
                    $data[$attribute] = $value;
                }
            }

            $result[$row['id']] = $data;
        }

        return $result;
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
     *
     * @deprecated Since 2.2 to be removed in 3.0.
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
                $result[$id][$attributeName] = ($attributeData[$id] ?? null);
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
            $arrItems[] = new Item($this, $arrEntry, $this->dispatcher);
        }

        return new Items($arrItems);
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
        return ($this->arrData['tableName'] ?? null);
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
     *
     * @deprecated Please implement ITranslatedMetaModel instead.
     */
    public function isTranslated(bool $deprecation = true)
    {
        if (!$deprecation) {
            return $this->arrData['translated'];
        }
        if ($this instanceof ITranslatedMetaModel) {
            // @codingStandardsIgnoreStart
            @\trigger_error(
                sprintf('The method "%s" is deprecated since MetaModels 2.2 and to be removed in 3.0. ' .
                    'Please use "instanceof \MetaModels\ITranslatedMetaModel" instead.', __METHOD__),
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
            return true;
        }
        if ($this->arrData['translated']) {
            // @codingStandardsIgnoreStart
            @\trigger_error(
                sprintf(
                    'The method "%s" is deprecated since MetaModels 2.2 and to be removed in 3.0. ' .
                    'MetaModel "%s" should implement "\MetaModels\ITranslatedMetaModel" instead.',
                    __METHOD__,
                    $this->getTableName()
                ),
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
            return true;
        }

        // @codingStandardsIgnoreStart
        @\trigger_error(
            sprintf('The method "%s" is deprecated since MetaModels 2.2 and to be removed in 3.0. ' .
                'Please use "instanceof \MetaModels\ITranslatedMetaModel" instead.', __METHOD__),
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd

        return false;
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
     *
     * @deprecated Please implement ITranslatedMetaModel instead.
     */
    public function getAvailableLanguages()
    {
        if ($this instanceof ITranslatedMetaModel) {
            // @codingStandardsIgnoreStart
            @\trigger_error(
                sprintf('The method "%s" is deprecated since MetaModels 2.2 and to be removed in 3.0. ' .
                    'Please use "\MetaModels\ITranslatedMetaModel::getLanguages" instead.', __METHOD__),
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
            return $this->getLanguages();
        }

        if ($this->isTranslated(false)) {
            // @codingStandardsIgnoreStart
            @\trigger_error(
                sprintf('The method "%s" is deprecated since MetaModels 2.2 and to be removed in 3.0. ' .
                    'Please test for "instanceof "\MetaModels\ITranslatedMetaModel" and use '.
                    '"\MetaModels\ITranslatedMetaModel::getLanguages" instead.', __METHOD__),
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd

            return array_keys((array) $this->arrData['languages']);
        }
        // @codingStandardsIgnoreStart
        @\trigger_error(
            sprintf('The method "%s" is deprecated since MetaModels 2.2 and to be removed in 3.0. ' .
                'Please test for "instanceof \MetaModels\ITranslatedMetaModel".', __METHOD__),
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd

        return null;
    }

    /**
     * {@inheritdoc}
     *
     * @deprecated Please implement ITranslatedMetaModel instead.
     */
    public function getFallbackLanguage()
    {
        if ($this instanceof ITranslatedMetaModel) {
            // @codingStandardsIgnoreStart
            @\trigger_error(
                sprintf('The method "%s" is deprecated since MetaModels 2.2 and to be removed in 3.0. ' .
                    'Please use "\MetaModels\ITranslatedMetaModel::getMainLanguage" instead.', __METHOD__),
                E_USER_DEPRECATED
            );

            return $this->getMainLanguage();
        }

        if ($this->isTranslated(false)) {
            // @codingStandardsIgnoreStart
            @\trigger_error(
                sprintf('The method "%s" is deprecated since MetaModels 2.2 and to be removed in 3.0. ' .
                    'Please implement interface "\MetaModels\ITranslatedMetaModel" and use ' .
                    '"\MetaModels\ITranslatedMetaModel::getMainLanguage" instead.', __METHOD__),
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
            foreach ($this->arrData['languages'] as $strLangCode => $arrData) {
                if ($arrData['isfallback']) {
                    return $strLangCode;
                }
            }
        }
        // @codingStandardsIgnoreStart
        @\trigger_error(
            sprintf('The method "%s" is deprecated since MetaModels 2.2 and to be removed in 3.0. ' .
                'Please test for translations via "instanceof \MetaModels\ITranslatedMetaModel" and call ' .
                '"\MetaModels\ITranslatedMetaModel::getMainLanguage" instead.', __METHOD__),
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd

        return null;
    }

    /**
     * {@inheritdoc}
     *
     * The value is taken from $GLOBALS['TL_LANGUAGE']
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     *
     * @deprecated Please implement ITranslatedMetaModel instead.
     */
    public function getActiveLanguage()
    {
        if ($this instanceof ITranslatedMetaModel) {
            // @codingStandardsIgnoreStart
            @\trigger_error(
                sprintf('The method "%s" is deprecated since MetaModels 2.2 and to be removed in 3.0. ' .
                    'Please use "\MetaModels\ITranslatedMetaModel::getLanguage" and ' .
                    '"\MetaModels\ITranslatedMetaModel::selectLanguage" instead.', __METHOD__),
                E_USER_DEPRECATED
            );
        } else {
            // @codingStandardsIgnoreStart
            @\trigger_error(
                sprintf('The method "%s" is deprecated since MetaModels 2.2 and to be removed in 3.0. ' .
                    'Please use "\MetaModels\ITranslatedMetaModel::getLanguage" instead.', __METHOD__),
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
        }

        return \str_replace('-', '_', $GLOBALS['TL_LANGUAGE']);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttribute($strAttributeName)
    {
        $arrAttributes = $this->getAttributes();
        return ($arrAttributes[$strAttributeName] ?? null);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeById($intId)
    {
        foreach ($this->getAttributes() as $objAttribute) {
            if ($objAttribute->get('id') === $intId) {
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
    protected function getAttributeByNames($attrNames = [])
    {
        if (empty($attrNames)) {
            return $this->arrAttributes;
        }

        $result = [];
        foreach ($attrNames as $attributeName) {
            if (null === $attribute = $this->arrAttributes[$attributeName] ?? null) {
                continue;
            }
            $result[$attributeName] = $attribute;
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
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getIdsFromFilter($objFilter, $strSortBy = '', $intOffset = 0, $intLimit = 0, $strSortOrder = 'ASC')
    {
        if ([] === $arrFilteredIds = array_filter($this->getMatchingIds($objFilter))) {
            return [];
        }

        // If desired, sort the entries.
        if ('' !== $strSortBy) {
            if ($objSortAttribute = $this->getAttribute($strSortBy)) {
                $arrFilteredIds = $objSortAttribute->sortIds($arrFilteredIds, $strSortOrder);
            } elseif ('id' === $strSortBy) {
                if ($strSortOrder === 'ASC') {
                    asort($arrFilteredIds);
                } else {
                    rsort($arrFilteredIds);
                }
            } elseif (in_array($strSortBy, array('pid', 'tstamp', 'sorting'))) {
                // Build the right key for the cache.
                $sortKey = \sprintf('%s-%s', $strSortBy, \strtolower($strSortOrder));
                // Used the cached ID list, and make a list of wanted ID's with the sorting of the cache.
                if (!isset($this->existingIds[$sortKey])) {
                    $this->existingIds[$sortKey] = [];
                }
                $cacheResult = array_intersect($this->existingIds[$sortKey], $arrFilteredIds);
                // Check if we have all ID's or if we have one missing, now we are using the order of the MM Filter.
                if (array_intersect($arrFilteredIds, $cacheResult) === $arrFilteredIds) {
                    if ($intOffset > 0 || $intLimit > 0) {
                        return array_values(array_slice($cacheResult, $intOffset, $intLimit ?: null));
                    }
                    return array_values($cacheResult);
                }

                // Merge the already known and the new one.
                $fullIdList = array_merge((array) $this->existingIds[$sortKey], $arrFilteredIds);
                $fullIdList = \array_keys(\array_flip($fullIdList));

                // Sort by database values.
                $builder = $this->getConnection()->createQueryBuilder();

                $arrSortedFilteredIds = $builder
                    ->select('t.id')
                    ->from($this->getTableName(), 't')
                    ->where($builder->expr()->in('t.id', ':values'))
                    ->setParameter('values', $arrFilteredIds, Connection::PARAM_STR_ARRAY)
                    ->orderBy($strSortBy, $strSortOrder)
                    ->execute()
                    ->fetchAll(\PDO::FETCH_COLUMN);

                // Add the new sorted Id's to the cache and use only the wanted.
                $this->existingIds[$sortKey] = $arrSortedFilteredIds;
                $arrFilteredIds              = array_intersect($arrSortedFilteredIds, $arrFilteredIds);
            } elseif ($strSortBy == 'random') {
                shuffle($arrFilteredIds);
            }
        }

        // Apply limiting then.
        if ($intOffset > 0 || $intLimit > 0) {
            $arrFilteredIds = array_slice($arrFilteredIds, $intOffset, $intLimit ?: null);
        }
        return array_values($arrFilteredIds);
    }

    /**
     * {@inheritdoc}
     */
    public function getCount($objFilter)
    {
        $arrFilteredIds = $this->getMatchingIds($objFilter);
        if (0 === count($arrFilteredIds)) {
            return 0;
        }

        $builder = $this->getConnection()->createQueryBuilder();

        return (int) $builder
            ->select('COUNT(t.id)')
            ->from($this->getTableName(), 't')
            ->where($builder->expr()->in('t.id', ':values'))
            ->setParameter('values', $arrFilteredIds, Connection::PARAM_STR_ARRAY)
            ->execute()
            ->fetch(\PDO::FETCH_COLUMN);
    }

    /**
     * {@inheritdoc}
     */
    public function findVariantBase($objFilter)
    {
        $objNewFilter = $this->copyFilter($objFilter);

        $idList = $this
            ->getConnection()
            ->createQueryBuilder()
            ->select('t.id')
            ->from($this->getTableName(), 't')
            ->where('t.varbase=1')
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);

        $objNewFilter->addFilterRule(new StaticIdList($idList));
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

        $builder = $this->getConnection()->createQueryBuilder();

        $idList = $builder
            ->select('t.id')
            ->from($this->getTableName(), 't')
            ->where('t.varbase=0')
            ->andWhere($builder->expr()->in('t.vargroup', ':ids'))
            ->setParameter('ids', $arrIds, Connection::PARAM_STR_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);

        $objNewFilter->addFilterRule(new StaticIdList($idList));
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

        $builder = $this->getConnection()->createQueryBuilder();

        $idList = $builder
            ->select('t.id')
            ->from($this->getTableName(), 't')
            ->leftJoin('t', $this->getTableName(), 't2', 't.vargroup=t2.vargroup')
            ->where($builder->expr()->in('t2.id', ':ids'))
            ->setParameter('ids', $arrIds, Connection::PARAM_STR_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);

        $objNewFilter->addFilterRule(new StaticIdList($idList));
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

        return [];
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

        $builder = $this->getConnection()->createQueryBuilder();

        $builder
            ->update($this->getTableName(), 't2')
            ->set('t2.' . $strColumn, is_array($varData) ? serialize($varData) : $varData)
            ->where($builder->expr()->in('t2.id', ':ids'))
            ->setParameter('ids', $arrIds, Connection::PARAM_STR_ARRAY)
            ->execute();
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
     * @param bool   $baseAttributes If also the base attributes get updated as well.
     *
     * @return void
     */
    protected function updateVariants($item, $activeLanguage, $allIds, $baseAttributes = false)
    {
        foreach ($this->getAttributes() as $strAttributeId => $objAttribute) {
            // Skip unset attributes.
            if (!$item->isAttributeSet($objAttribute->getColName())) {
                continue;
            }

            if (!$baseAttributes && $item->isVariant() && !($objAttribute->get('isvariant'))) {
                // Skip base attribute.
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
        $data      = ['tstamp' => $item->get('tstamp')];
        $isNewItem = false;
        if ($this->hasVariants()) {
            // No variant group is given, so we have a complete new base item this should be a workaround for these
            // values should be set by the GeneralDataMetaModel or whoever is calling this method.
            if ($item->get('vargroup') === null) {
                $item->set('varbase', '1');
                $item->set('vargroup', '0');
                $isNewItem = true;
            }
            $data['varbase']  = $item->get('varbase');
            $data['vargroup'] = $item->get('vargroup');
        }

        $connection = $this->getConnection();
        $builder    = $connection->createQueryBuilder();
        $parameters = [];
        foreach (array_keys($data) as $key) {
            $parameters[$this->getTableName() . '.' . $key] = ':' . $key;
        }
        $builder
            ->insert($this->getTableName())
            ->values($parameters)
            ->setParameters($data)
            ->execute();

        $item->set('id', $connection->lastInsertId());

        // Add the variant group equal to the id.
        if ($isNewItem) {
            $this->saveSimpleColumn('vargroup', [$item->get('id')], $item->get('id'));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function saveItem($objItem, $timestamp = null)
    {
        if (null === $timestamp) {
            // @codingStandardsIgnoreStart
            @\trigger_error(
                'Not passing a timestamp has been deprecated and will cause an error in MetaModels 3',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
        }

        $baseAttributes = $this->saveBaseColumns($objItem, $timestamp ?: \time());
        if ($this instanceof ITranslatedMetaModel) {
            $strActiveLanguage = $this->getLanguage();
        } elseif ($this->isTranslated(false)) {
            // @codingStandardsIgnoreStart
            @\trigger_error(
                sprintf('Support for translated MetaModels not implementing "\MetaModels\ITranslatedMetaModel" '.
                    'is  deprecated since MetaModels 2.2 and to be removed in 3.0. ' .
                    'Please implement interface "\MetaModels\ITranslatedMetaModel".', __METHOD__),
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
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

        $this->updateVariants($objItem, $strActiveLanguage, $arrAllIds, $baseAttributes);

        // Tell all attributes that the model has been saved. Useful for alias fields, edit counters etc.
        foreach ($this->getAttributes() as $objAttribute) {
            if ($objItem->isAttributeSet($objAttribute->getColName())) {
                $objAttribute->modelSaved($objItem);
            }
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
        $builder = $this->getConnection()->createQueryBuilder();

        $builder
            ->delete($this->getTableName())
            ->where($builder->expr()->in($this->getTableName() . '.id', ':ids'))
            ->setParameter('ids', $arrIds, Connection::PARAM_STR_ARRAY)
            ->execute();
    }

    /**
     * {@inheritdoc}
     */
    public function getEmptyFilter()
    {
        return new Filter($this);
    }

    /**
     * {@inheritdoc}
     */
    public function prepareFilter($intFilterSettings, $arrFilterUrl)
    {
        // @codingStandardsIgnoreStart
        @trigger_error(
            'Method "' . __METHOD__ . '" is deprecated and will get removed in MetaModels 3.0. ' .
            'Use the "metamodels.filter_setting_factory" service instead.',
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd

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
        // @codingStandardsIgnoreStart
        @trigger_error(
            'Method "' . __METHOD__ . '" is deprecated and will get removed in MetaModels 3.0. ' .
            'Use the "metamodels.render_setting_factory" service instead.',
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd

        return $this->getServiceContainer()->getRenderSettingFactory()->createCollection($this, $intViewId);
    }

    /**
     * Obtain the doctrine connection.
     *
     * @return Connection
     *
     * @throws \ReflectionException Throws could not connect to database.
     */
    private function getConnection()
    {
        if ($this->connection) {
            return $this->connection;
        }

        $reflection = new \ReflectionProperty(\Contao\Database::class, 'resConnection');
        $reflection->setAccessible(true);

        return $this->connection = $reflection->getValue($this->getDatabase());
    }

    /**
     * Save the base columns of an item and return true if it is a new item.
     *
     * @param IItem $item      The item to save.
     * @param int   $timestamp The timestamp to use.
     *
     * @return bool
     */
    private function saveBaseColumns(IItem $item, $timestamp)
    {
        $isNew = false;
        $item->set('tstamp', $timestamp);
        if (!$item->get('id')) {
            $isNew = true;
            $this->createNewItem($item);
        }

        // Update system columns.
        if (null !== $item->get('pid')) {
            $this->saveSimpleColumn('pid', [$item->get('id')], $item->get('pid'));
        }
        if (null !== $item->get('sorting')) {
            $this->saveSimpleColumn('sorting', [$item->get('id')], $item->get('sorting'));
        }
        $this->saveSimpleColumn('tstamp', [$item->get('id')], $item->get('tstamp'));

        return $isNew;
    }
}
