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
 * @author     Christopher Bölter <c.boelter@cogizz.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\DcGeneral\Data;

use ContaoCommunityAlliance\DcGeneral\Data\DCGE;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultFilterOptionCollection;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultLanguageInformation;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultLanguageInformationCollection;
use ContaoCommunityAlliance\DcGeneral\Data\FilterOptionCollectionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\LanguageInformationCollectionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\LanguageInformationInterface;
use ContaoCommunityAlliance\DcGeneral\Data\MultiLanguageDataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ConfigInterface;
use ContaoCommunityAlliance\DcGeneral\Data\CollectionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultConfig;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultCollection;
use MetaModels\Attribute\IAttribute;
use MetaModels\Filter\IFilter;
use MetaModels\Filter\Rules\Condition\ConditionAnd;
use MetaModels\Filter\Rules\Condition\ConditionOr;
use MetaModels\Filter\Rules\Comparing\GreaterThan;
use MetaModels\Filter\Rules\Comparing\LessThan;
use MetaModels\Filter\Rules\SearchAttribute;
use MetaModels\Filter\Filter;
use MetaModels\Filter\Rules\SimpleQuery;
use MetaModels\IItem;
use MetaModels\IItems;
use MetaModels\IMetaModel;
use MetaModels\IMetaModelsServiceContainer;
use MetaModels\Item;

/**
 * Data driver class for DC_General.
 *
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @package    MetaModels
 * @subpackage Core
 */
class Driver implements MultiLanguageDataProviderInterface
{
    /**
     * Name of current table.
     *
     * @var string
     */
    protected $strTable = null;

    /**
     * The MetaModel this DataContainer is working on.
     *
     * @var IMetaModel
     */
    protected $metaModel = null;

    /**
     * The service container.
     *
     * @var IMetaModelsServiceContainer
     */
    protected $serviceContainer = null;

    /**
     * The current active language.
     *
     * @var string
     */
    protected $strCurrentLanguage;

    /**
     * Delete an item.
     *
     * The given value may be either integer, string or an instance of Model
     *
     * @param mixed $varItem Id or the model itself, to delete.
     *
     * @return void
     *
     * @throws \RuntimeException When an unusable object has been passed.
     */
    public function delete($varItem)
    {
        $objModelItem = null;
        // Determine the id.
        if (is_object($varItem) && ($varItem instanceof Model)) {
            $objModelItem = $varItem->getItem();
        } else {
            $objModelItem = $this->getMetaModel()->findById($varItem);
        }
        if ($objModelItem) {
            $this->getMetaModel()->delete($objModelItem);
        }
    }

    /**
     * Save a new Version of a record.
     *
     * @param ModelInterface $objModel    The model to be saved.
     *
     * @param string         $strUsername The username that creates the new version.
     *
     * @return void
     *
     * @throws \RuntimeException As this is currently unimplemented, an Exception is thrown.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function saveVersion(ModelInterface $objModel, $strUsername)
    {
        throw new \RuntimeException('Versioning not supported in MetaModels so far.');
    }

    /**
     * Return a model based of the version information.
     *
     * @param mixed $mixID      The ID of record.
     *
     * @param mixed $mixVersion The ID of the version.
     *
     * @return void
     *
     * @throws \RuntimeException As this is currently unimplemented, an Exception is thrown.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getVersion($mixID, $mixVersion)
    {
        throw new \RuntimeException('Versioning not supported in MetaModels so far.');
    }

    /**
     * Set a version as active.
     *
     * @param mixed $mixID      The ID of record.
     *
     * @param mixed $mixVersion The ID of the version.
     *
     * @return void
     *
     * @throws \RuntimeException As this is currently unimplemented, an Exception is thrown.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setVersionActive($mixID, $mixVersion)
    {
        throw new \RuntimeException('Versioning not supported in MetaModels so far.');
    }

    /**
     * Return the active version from a record.
     *
     * @param mixed $mixID The ID of the record.
     *
     * @return void
     *
     * @throws \RuntimeException As this is currently unimplemented, an Exception is thrown.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getActiveVersion($mixID)
    {
        throw new \RuntimeException('Versioning not supported in MetaModels so far.');
    }

    /**
     * Set a language as active language in Contao and return the previous language.
     *
     * @param string $language The language to set (if any).
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function setLanguage($language = '')
    {
        $previousLanguage = $GLOBALS['TL_LANGUAGE'];		
        if (!empty($language) && ($GLOBALS['TL_LANGUAGE'] !== $language)) {
            $GLOBALS['TL_LANGUAGE'] = $language;
        }

        return $previousLanguage;
    }

    /**
     * Retrieve the service container.
     *
     * @return IMetaModelsServiceContainer
     */
    protected function getServiceContainer()
    {
        return $this->serviceContainer;
    }

    /**
     * Retrieve the MetaModel.
     *
     * @return IMetaModel
     */
    protected function getMetaModel()
    {
        if (!$this->metaModel) {
            $this->metaModel = $this->getServiceContainer()->getFactory()->getMetaModel($this->strTable);
        }

        return $this->metaModel;
    }

    /**
     * Retrieve the Database.
     *
     * @return \Contao\Database
     */
    protected function getDatabase()
    {
        return $this->getServiceContainer()->getDatabase();
    }

    /**
     * Fetch a single or first record by id or filter.
     *
     * If the model shall be retrieved by id, use $objConfig->setId() to populate the config with an Id.
     *
     * If the model shall be retrieved by filter, use $objConfig->setFilter() to populate the config with a filter.
     *
     * @param ConfigInterface $objConfig The config to use.
     *
     * @return ModelInterface
     */
    public function fetch(ConfigInterface $objConfig)
    {
        $backupLanguage = $this->setLanguage($this->getCurrentLanguage());

        if ($objConfig->getId() !== null) {
            $modelId = $objConfig->getId();
        } else {
            $filter  = $this->prepareFilter($objConfig->getFilter());
            $ids     = $this->getIdsFromFilter($filter, $objConfig);
            $modelId = reset($ids);
        }

        $objItem = $modelId ? $this->getMetaModel()->findById($modelId) : null;

        $this->setLanguage($backupLanguage);

        if (!$objItem) {
            return null;
        }

        return new Model($objItem);
    }
    /**
     * Set base config with source and other necessary parameter.
     *
     * @param array $arrConfig The configuration to use.
     *
     * @return void
     *
     * @throws \RuntimeException When no source has been defined.
     */
    public function setBaseConfig(array $arrConfig)
    {
        if (!$arrConfig['source']) {
            throw new \RuntimeException('Missing table name.');
        }

        $this->strTable         = $arrConfig['source'];
        $this->serviceContainer = $arrConfig['service-container'];
    }

    /**
     * Return empty config object.
     *
     * @return ConfigInterface
     */
    public function getEmptyConfig()
    {
        return DefaultConfig::init();
    }

    /**
     * Fetch an empty single record (new item).
     *
     * @return ModelInterface
     */
    public function getEmptyModel()
    {
        $objItem = new Item($this->getMetaModel(), array());
        return new Model($objItem);
    }

    /**
     * Fetch an empty collection.
     *
     * @return CollectionInterface
     */
    public function getEmptyCollection()
    {
        return new DefaultCollection();
    }

    /**
     * Fetch an empty filter item collection.
     *
     * @return FilterOptionCollectionInterface
     */
    public function getEmptyFilterOptionCollection()
    {
        return new DefaultFilterOptionCollection();
    }

    /**
     * Retrieve the attribute for a filter operation.
     *
     * @param array $operation The operation to retrieve the attribute for.
     *
     * @return IAttribute|null
     */
    protected function getAttributeFromFilterOperation($operation)
    {
        $attribute = null;
        if ($operation['property']) {
            $attribute = $this->getMetaModel()->getAttribute($operation['property']);
        }

        return $attribute;
    }

    /**
     * Build an AND or OR query.
     *
     * @param IFilter $filter    The filter to add the operations to.
     *
     * @param array   $operation The operation to convert.
     *
     * @return void
     */
    protected function getAndOrFilter(IFilter $filter, $operation)
    {
        if (!$operation['children']) {
            return;
        }

        if ($operation['operation'] == 'AND') {
            $filterRule = new ConditionAnd();
        } else {
            $filterRule = new ConditionOr();
        }
        $filter->addFilterRule($filterRule);

        foreach ($operation['children'] as $child) {
            $subFilter = new Filter($this->getMetaModel());
            $filterRule->addChild($subFilter);
            $this->calculateSubfilter($child, $subFilter);
        }
    }

    /**
     * Build the sub query for a comparing operator like =,<,>.
     *
     * @param IAttribute $attribute The attribute.
     *
     * @param IFilter    $filter    The filter to add the operations to.
     *
     * @param array      $operation The operation to convert.
     *
     * @return void
     *
     * @throws \RuntimeException When the operation can not be parsed.
     */
    protected function getFilterForComparingOperator($attribute, IFilter $filter, $operation)
    {
        if ($attribute) {
            switch ($operation['operation']) {
                case '=':
                    $filter->addFilterRule(new SearchAttribute(
                        $attribute,
                        $operation['value'],
                        $this->getMetaModel()->getAvailableLanguages()
                    ));
                    return;

                case '>':
                    $filter->addFilterRule(new GreaterThan(
                        $attribute,
                        $operation['value']
                    ));
                    return;

                case '<':
                    $filter->addFilterRule(new LessThan(
                        $attribute,
                        $operation['value']
                    ));
                    return;

                default:
                    throw new \RuntimeException(
                        'Error processing filter array - unknown operation ' .
                        var_export($operation['operation'], true),
                        1
                    );
            }
        }

        if ($this->getDatabase()->fieldExists($operation['property'], $this->getMetaModel()->getTableName())) {
            // System column?
            $filter->addFilterRule(new SimpleQuery(
                sprintf(
                    'SELECT id FROM %s WHERE %s %s?',
                    $this->getMetaModel()->getTableName(),
                    $operation['property'],
                    $operation['operation']
                ),
                array($operation['value'])
            ));

            return;
        }

        throw new \RuntimeException(
            'Error processing filter array - unknown property ' . var_export($operation['property'], true),
            1
        );
    }

    /**
     * Return the filter query for a "foo IN ('a', 'b')" filter.
     *
     * @param IFilter $filter    The filter to add the operations to.
     *
     * @param array   $operation The operation to convert.
     *
     * @return void
     *
     * @throws \RuntimeException When the operation can not be parsed.
     */
    protected function getFilterForInList(IFilter $filter, $operation)
    {
        // Rewrite the IN operation to a rephrased term: "(x=a) OR (x=b) OR ...".
        $subRules = array();
        foreach ($operation['values'] as $varValue) {
            $subRules[] = array(
                'property'  => $operation['property'],
                'operation' => '=',
                'value'     => $varValue
            );
        }
        $this->calculateSubfilter(array(
            'operation' => 'OR',
            'children'    => $subRules
        ), $filter);
    }

    /**
     * Return the filter query for a "foo LIKE '%ba_r%'" filter.
     *
     * The searched value may contain the wildcards '*' and '?' which will get converted to proper SQL.
     *
     * @param IAttribute $attribute The attribute.
     *
     * @param IFilter    $filter    The filter to add the operations to.
     *
     * @param array      $operation The operation to convert.
     *
     * @return void
     *
     * @throws \RuntimeException When the operation can not be parsed.
     */
    protected function getFilterForLike($attribute, IFilter $filter, $operation)
    {
        if ($attribute) {
            $filter->addFilterRule(new SearchAttribute(
                $attribute,
                $operation['value'],
                $this->getMetaModel()->getAvailableLanguages()
            ));

            return;
        }

        if ($this->getDatabase()->fieldExists($operation['property'], $this->getMetaModel()->getTableName())) {
            // System column?
            $filter->addFilterRule(new SimpleQuery(
                sprintf(
                    'SELECT id FROM %s WHERE %s LIKE ?',
                    $this->getMetaModel()->getTableName(),
                    $operation['property']
                ),
                array($operation['value'])
            ));

            return;
        }

        throw new \RuntimeException(
            'Error processing filter array - unknown property ' . var_export($operation['property'], true),
            1
        );
    }

    /**
     * Combine a filter in standard filter array notation.
     *
     * Supported operations are:
     * operation      needed arguments     argument type.
     * AND
     *                'children'           array
     * OR
     *                'children'           array
     * =
     *                'property'           string (the name of a property)
     *                'value'              literal
     * >
     *                'property'           string (the name of a property)
     *                'value'              literal
     * <
     *                'property'           string (the name of a property)
     *                'value'              literal
     * IN
     *                'property'           string (the name of a property)
     *                'values'             array of literal
     *
     * @param array   $operation The filter to be combined into the passed filter object.
     *
     * @param IFilter $filter    The filter object where the rules shall get appended to.
     *
     * @return void
     *
     * @throws \RuntimeException When an improper filter condition is encountered, an exception is thrown.
     */
    protected function calculateSubfilter($operation, IFilter $filter)
    {
        if (!is_array($operation)) {
            throw new \RuntimeException('Error Processing subfilter: ' . var_export($operation, true), 1);
        }

        switch ($operation['operation']) {
            case 'AND':
            case 'OR':
                $this->getAndOrFilter($filter, $operation);
                break;

            case '=':
            case '>':
            case '<':
                $this->getFilterForComparingOperator(
                    $this->getAttributeFromFilterOperation($operation),
                    $filter,
                    $operation
                );
                break;

            case 'IN':
                $this->getFilterForInList($filter, $operation);
                break;

            case 'LIKE':
                $this->getFilterForLike(
                    $this->getAttributeFromFilterOperation($operation),
                    $filter,
                    $operation
                );
                break;

            default:
                throw new \RuntimeException(
                    'Error processing filter array - unknown operation ' . var_export($operation, true),
                    1
                );
        }
    }

    /**
     * Prepare a filter and return it.
     *
     * @param array $arrFilter The values to be applied in attribute name => value style.
     *
     * @return IFilter
     */
    protected function prepareFilter($arrFilter = array())
    {
        $objFilter = $this->getMetaModel()->getEmptyFilter();

        if ($arrFilter) {
            $this->calculateSubfilter(
                array
                (
                    'operation' => 'AND',
                    'children' => $arrFilter
                ),
                $objFilter
            );
        }
        return $objFilter;
    }

    /**
     * Extract the sorting from the given config.
     *
     * @param ConfigInterface $config The configuration to be applied.
     *
     * @return array
     */
    protected function extractSorting($config)
    {
        $sorting = $config->getSorting();

        $sortBy  = key($sorting);
        $sortDir = current($sorting) ?: DCGE::MODEL_SORTING_ASC;

        return array($sortBy, $sortDir);
    }

    /**
     * Fetch the ids via the given filter.
     *
     * @param IFilter         $filter The filter.
     *
     * @param ConfigInterface $config The configuration to be applied.
     *
     * @return \int[]
     */
    protected function getIdsFromFilter($filter, $config)
    {
        $sorting = $this->extractSorting($config);

        return $this->getMetaModel()->getIdsFromFilter(
            $filter,
            $sorting[0],
            $config->getStart(),
            $config->getAmount(),
            $sorting[1]
        );
    }

    /**
     * Fetch the items via the given filter.
     *
     * @param IFilter         $filter The filter.
     *
     * @param ConfigInterface $config The configuration to be applied.
     *
     * @return IItems|IItem[] The collection of IItem instances that match the given filter.
     */
    protected function getItemsFromFilter($filter, $config)
    {
        $sorting = $this->extractSorting($config);

        return $this->getMetaModel()->findByFilter(
            $filter,
            $sorting[0],
            $config->getStart(),
            $config->getAmount(),
            $sorting[1],
            $config->getFields() ?: array()
        );
    }

    /**
     * Fetch all records (optional filtered, sorted and limited).
     *
     * @param ConfigInterface $objConfig The configuration to be applied.
     *
     * @return CollectionInterface
     */
    public function fetchAll(ConfigInterface $objConfig)
    {
        $backupLanguage = $this->setLanguage($this->getCurrentLanguage());

        $filter = $this->prepareFilter($objConfig->getFilter());
        if ($objConfig->getIdOnly()) {
            $this->setLanguage($backupLanguage);

            return $this->getIdsFromFilter($filter, $objConfig);
        }

        $items      = $this->getItemsFromFilter($filter, $objConfig);
        $collection = $this->getEmptyCollection();
        foreach ($items as $objItem) {
            $collection->push(new Model($objItem));
        }
        $this->setLanguage($backupLanguage);

        return $collection;
    }

    /**
     * Retrieve all unique values for the given property.
     *
     * The result set will be an array containing all unique values contained in the MetaModel for the defined
     * attribute in the configuration.
     *
     * Note: this only re-ensembles really used values for at least one data set.
     *
     * The only information being interpreted from the passed config object is the first property to fetch and the
     * filter definition.
     *
     * @param ConfigInterface $objConfig The filter config options.
     *
     * @return FilterOptionCollectionInterface
     *
     * @throws \RuntimeException If improper values have been passed (i.e. not exactly one field requested).
     */
    public function getFilterOptions(ConfigInterface $objConfig)
    {
        $arrProperties = $objConfig->getFields();
        if (count($arrProperties) <> 1) {
            throw new \RuntimeException('objConfig must contain exactly one property to be retrieved.');
        }

        $objFilter = $this->prepareFilter($objConfig->getFilter());

        $arrValues = $this
            ->getMetaModel()
            ->getAttribute($arrProperties[0])
            ->getFilterOptions($objFilter->getMatchingIds(), true);

        $objCollection = $this->getEmptyFilterOptionCollection();
        foreach ($arrValues as $strKey => $strValue) {
            $objCollection->add($strKey, $strValue);
        }

        return $objCollection;
    }

    /**
     * Return the amount of total items (filtering may be used in the config).
     *
     * @param ConfigInterface $objConfig The filter config options.
     *
     * @return int
     */
    public function getCount(ConfigInterface $objConfig)
    {
        $objFilter = $this->prepareFilter($objConfig->getFilter());
        return $this->getMetaModel()->getCount($objFilter);
    }

    /**
     * Return a list with all versions for the model with the given Id.
     *
     * @param mixed   $mixID         The ID of the row.
     *
     * @param boolean $blnOnlyActive If true, only active versions will get returned, if false all version will get
     *                               returned.
     *
     * @return CollectionInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getVersions($mixID, $blnOnlyActive = false)
    {
        // No version support on MetaModels so far, sorry.
        return null;
    }

    /**
     * Determine if a given value is unique within the metamodel.
     *
     * @param string $strField The attribute name.
     *
     * @param mixed  $varNew   The value that shall be checked.
     *
     * @param int    $intId    The (optional) id of the item currently in scope - pass null for new items.
     *
     * @return bool True if the values is not yet contained within the table, false otherwise.
     */
    public function isUniqueValue($strField, $varNew, $intId = null)
    {
        $objFilter = $this->getMetaModel()->getEmptyFilter();

        $objAttribute = $this->getMetaModel()->getAttribute($strField);
        if ($objAttribute) {
            $this->calculateSubfilter(array(
                'operation' => '=',
                'property' => $objAttribute->getColName(),
                'value' => $varNew
            ), $objFilter);
            $arrIds = $objFilter->getMatchingIds();
            return (count($arrIds) == 0) || ($arrIds == array($intId));
        }

        return false;
    }

    /**
     * Reset the fallback field.
     *
     * This clears the given property in all items in the data provider to an empty value.
     *
     * @param string $strField The field to reset.
     *
     * @return void
     */
    public function resetFallback($strField)
    {
        // TODO: Unimplemented so far.
    }

    /**
     * Save an item to the data provider.
     *
     * If the item does not have an Id yet, the save operation will add it as a new row to the database and
     * populate the Id of the model accordingly.
     *
     * @param ModelInterface $objItem The model to save back.
     *
     * @return ModelInterface The passed model.
     *
     * @throws \RuntimeException When an incompatible item was passed, an Exception is being thrown.
     */
    public function save(ModelInterface $objItem)
    {
        if ($objItem instanceof Model) {
            $backupLanguage = $this->setLanguage($this->getCurrentLanguage());

            $objItem->getItem()->save();

            $this->setLanguage($backupLanguage);

            return $objItem;
        }

        throw new \RuntimeException('ERROR: incompatible object passed to GeneralDataMetaModel::save()');
    }

    /**
     * Save a collection of items to the data provider.
     *
     * @param CollectionInterface $objItems The collection containing all items to be saved.
     *
     * @return void
     *
     * @throws \RuntimeException When an incompatible item was passed.
     */
    public function saveEach(CollectionInterface $objItems)
    {
        foreach ($objItems as $objItem) {
            $this->save($objItem);
        }
    }

    /**
     * Check if the attribute exists in the table and holds a value.
     *
     * @param string $strField The name of the attribute that shall be tested.
     *
     * @return boolean
     */
    public function fieldExists($strField)
    {
        return !!(
            in_array($strField, array('id', 'pid', 'tstamp', 'sorting'))
            || $this->getMetaModel()->getAttribute($strField)
        );
    }

    /**
     * Check if two models have the same values in all properties.
     *
     * @param ModelInterface $objModel1 The first model to compare.
     *
     * @param ModelInterface $objModel2 The second model to compare.
     *
     * @return boolean True - If both models are same, false if not.
     */
    public function sameModels($objModel1, $objModel2)
    {
        /** @var Model $objModel1 */
        /** @var Model $objModel2 */
        $objNative1 = $objModel1->getItem();
        $objNative2 = $objModel2->getItem();
        if ($objNative1->getMetaModel() != $objNative2->getMetaModel()) {
            return false;
        }
        foreach ($objNative1->getMetaModel()->getAttributes() as $objAttribute) {
            if ($objNative1->get($objAttribute->getColName()) != $objNative2->get($objAttribute->getColName())) {
                return false;
            }
        }
        return true;
    }

    /**
     * Fetch a variant of a single record by id.
     *
     * @param ConfigInterface $objConfig The config holding the id of the base model.
     *
     * @return ModelInterface
     */
    public function createVariant(ConfigInterface $objConfig)
    {
        $objItem = $this->getMetaModel()->findById($objConfig->getId())->varCopy();

        if (!$objItem) {
            return null;
        }
        return new Model($objItem);
    }

    /**
     * Get all available languages of a certain record.
     *
     * @param mixed $mixID The ID of the record to retrieve.
     *
     * @return LanguageInformationCollectionInterface
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getLanguages($mixID)
    {
        if (!$this->getMetaModel()->isTranslated()) {
            return null;
        }

        $collection = new DefaultLanguageInformationCollection();

        foreach ($this->getMetaModel()->getAvailableLanguages() as $langCode) {
            // TODO: support country code.
            $collection->add(new DefaultLanguageInformation($langCode, null));
        }

        if (count($collection) > 0) {
            return $collection;
        }

        return null;
    }

    /**
     * Get the fallback language of a certain record.
     *
     * @param mixed $mixID The ID of the record to retrieve.
     *
     * @return LanguageInformationInterface|null
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getFallbackLanguage($mixID)
    {
        if ($this->getMetaModel()->isTranslated()) {
            $langCode = $this->getMetaModel()->getFallbackLanguage();
            // TODO: support country code.
            return new DefaultLanguageInformation($langCode, null);
        }

        return null;
    }

    /**
     * Set the current working language for the whole data provider.
     *
     * @param string $strLanguage The new language, use short tag "2 chars like de, fr etc.".
     *
     * @return void
     */
    public function setCurrentLanguage($strLanguage)
    {
        $this->strCurrentLanguage = $strLanguage;
    }

    /**
     * Get the current working language.
     *
     * @return string Short tag for the current working language like de or fr etc.
     */
    public function getCurrentLanguage()
    {
        return $this->strCurrentLanguage;
    }
}
