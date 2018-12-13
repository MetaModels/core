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
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Christopher Bölter <c.boelter@cogizz.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     binron <rtb@gmx.ch>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\DcGeneral\Data;

use ContaoCommunityAlliance\DcGeneral\Data\CollectionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ConfigInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DCGE;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultCollection;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultConfig;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultFilterOptionCollection;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultLanguageInformation;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultLanguageInformationCollection;
use ContaoCommunityAlliance\DcGeneral\Data\FilterOptionCollectionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\MultiLanguageDataProviderInterface;
use MetaModels\Attribute\IAttribute;
use MetaModels\Attribute\IComplex;
use MetaModels\Attribute\ITranslated;
use MetaModels\Filter\IFilter;
use MetaModels\IItem;
use MetaModels\IItems;
use MetaModels\IMetaModel;
use MetaModels\Item;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Data driver class for DC_General.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods) - The interface is too complex, maybe split into traits.
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
     * The event dispatcher to pass to items.
     *
     * @var null|EventDispatcherInterface
     */
    private $dispatcher = null;

    /**
     * The current active language.
     *
     * @var string
     */
    protected $strCurrentLanguage;

    /**
     * Set dispatcher.
     *
     * @param null|EventDispatcherInterface $dispatcher The new value.
     *
     * @return Driver
     */
    public function setDispatcher($dispatcher)
    {
        $this->dispatcher = $dispatcher;

        return $this;
    }

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
     * Retrieve the MetaModel.
     *
     * @return IMetaModel
     *
     * @throws \RuntimeException When the MetaModel could not be retrieved.
     */
    protected function getMetaModel()
    {
        if (!$this->metaModel) {
            if ($this->metaModel === null) {
                throw new \RuntimeException('No MetaModel instance set for ' . $this->strTable);
            }
        }
        return $this->metaModel;
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
     * @return null|ModelInterface
     */
    public function fetch(ConfigInterface $objConfig)
    {
        $backupLanguage = $this->setLanguage($this->getCurrentLanguage());

        if ($objConfig->getId() !== null) {
            $modelId = $objConfig->getId();
        } else {
            $filter  = $this->prepareFilter($objConfig);
            $ids     = $this->getIdsFromFilter($filter, $objConfig);
            $modelId = reset($ids);
        }

        $objItem = $modelId ? $this->getMetaModel()->findById($modelId, $objConfig->getFields() ?: array()) : null;

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

        $this->strTable  = $arrConfig['source'];
        $this->metaModel = $arrConfig['metaModel'];
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
        if (!isset($this->dispatcher)) {
            // @codingStandardsIgnoreStart
            @trigger_error(
                'Not setting an "' . EventDispatcherInterface::class .
                '" via "setDispatcher()" is deprecated and will cause an error in MetaModels 3.0.',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
        }

        $objItem = new Item($this->getMetaModel(), null, $this->dispatcher);
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
     * Prepare a filter and return it.
     *
     * @param ConfigInterface $configuration The configuration.
     *
     * @return IFilter
     */
    protected function prepareFilter(ConfigInterface $configuration)
    {
        $builder = new FilterBuilder($this->getMetaModel(), $configuration);
        return $builder->build();
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
     * @return string[]
     */
    protected function getIdsFromFilter($filter, $config)
    {
        $sorting = $this->extractSorting($config);

        return $this->getMetaModel()->getIdsFromFilter(
            $filter,
            $sorting[0],
            $config->getStart(),
            $config->getAmount(),
            strtoupper($sorting[1])
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
            strtoupper($sorting[1]),
            $config->getFields() ?: array()
        );
    }

    /**
     * Fetch all records (optional filtered, sorted and limited).
     *
     * @param ConfigInterface $objConfig The configuration to be applied.
     *
     * @return CollectionInterface|ModelInterface[]|string[]
     */
    public function fetchAll(ConfigInterface $objConfig)
    {
        $backupLanguage = $this->setLanguage($this->getCurrentLanguage());

        $filter = $this->prepareFilter($objConfig);
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

        $objFilter = $this->prepareFilter($objConfig);

        $arrValues = $this
            ->getMetaModel()
            ->getAttribute($arrProperties[0])
            ->getFilterOptions($objFilter->getMatchingIds(), true);

        $objCollection = new DefaultFilterOptionCollection();
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
        $objFilter = $this->prepareFilter($objConfig);
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
        $attribute = $this->getMetaModel()->getAttribute($strField);
        if ($attribute) {
            $matchingIds = $this
                ->prepareFilter(
                    $this->getEmptyConfig()->setFilter(
                        array(
                            array(
                                'operation' => '=',
                                'property' => $attribute->getColName(),
                                'value' => $varNew
                            )
                        )
                    )
                )
                ->getMatchingIds();
            return (count($matchingIds) == 0) || ($matchingIds == array($intId));
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
     *
     * @throws \RuntimeException For invalid ids.
     */
    public function resetFallback($strField)
    {
        // @codingStandardsIgnoreStart
        @trigger_error(__CLASS__ . '::' . __METHOD__ . ' is deprecated - handle resetting manually', E_USER_DEPRECATED);
        // @codingStandardsIgnoreEnd

        $metaModel = $this->getMetaModel();
        $attribute = $metaModel->getAttribute($strField);
        $ids       = $metaModel->getIdsFromFilter(null);

        if ($attribute instanceof IComplex) {
            $attribute->unsetDataFor($ids);
        }
        if ($attribute instanceof ITranslated) {
            $attribute->unsetValueFor($ids, $this->getCurrentLanguage());
        }
        if ($attribute instanceof IAttribute) {
            $data = array();
            foreach ($ids as $id) {
                $data[$id] = null;
            }
            $attribute->setDataFor($data);
        }

        throw new \RuntimeException('Unknown attribute or type ' . $strField);
    }

    /**
     * Save an item to the data provider.
     *
     * If the item does not have an Id yet, the save operation will add it as a new row to the database and
     * populate the Id of the model accordingly.
     *
     * @param ModelInterface $objItem   The model to save back.
     *
     * @param int|null            $timestamp Optional the timestamp.
     *
     * @return ModelInterface The passed model.
     *
     * @throws \RuntimeException When an incompatible item was passed, an Exception is being thrown.
     */
    public function save(ModelInterface $objItem, $timestamp = null)
    {
        if (null === $timestamp) {
            // @codingStandardsIgnoreStart
            @\trigger_error(
                'Not passing a timestamp has been deprecated and will cause an error in MetaModels 3',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
        }

        if ($objItem instanceof Model) {
            $backupLanguage = $this->setLanguage($this->getCurrentLanguage());

            $objItem->getItem()->save($timestamp);

            $this->setLanguage($backupLanguage);

            return $objItem;
        }

        throw new \RuntimeException('ERROR: incompatible object passed to GeneralDataMetaModel::save()');
    }

    /**
     * Save a collection of items to the data provider.
     *
     * @param CollectionInterface $objItems  The collection containing all items to be saved.
     *
     * @param int                 $timestamp Optional the timestamp.
     *
     * @return void
     *
     * @throws \RuntimeException When an incompatible item was passed.
     */
    public function saveEach(CollectionInterface $objItems, $timestamp = 0)
    {
        if (null === $timestamp) {
            // @codingStandardsIgnoreStart
            @\trigger_error(
                'Not passing a timestamp has been deprecated and will cause an error in MetaModels 3',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
        }

        foreach ($objItems as $objItem) {
            $this->save($objItem, $timestamp);
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
     *
     * @throws \InvalidArgumentException If not both models are compatible with this data provider.
     */
    public function sameModels($objModel1, $objModel2)
    {
        if (!($objModel1 instanceof Model && $objModel2 instanceof Model)) {
            throw new \InvalidArgumentException('Passed models are not valid.');
        }

        $objNative1 = $objModel1->getItem();
        $objNative2 = $objModel2->getItem();
        if ($objNative1->getMetaModel() === $objNative2->getMetaModel()) {
            return true;
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
     * @return null|ModelInterface
     */
    public function createVariant(ConfigInterface $objConfig)
    {
        $objItem = $this->getMetaModel()->findById($objConfig->getId())->varCopy();

        if (!$objItem) {
            return null;
        }

        $model = new Model($objItem);
        $model->setMeta($model::IS_CHANGED, true);

        return $model;
    }

    /**
     * {@inheritdoc}
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
            list($langCode, $country) = explode('_', $langCode, 2);
            $collection->add(new DefaultLanguageInformation($langCode, $country ?: null));
        }

        if (count($collection) > 0) {
            return $collection;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getFallbackLanguage($mixID)
    {
        if ($this->getMetaModel()->isTranslated()) {
            $langCode = $this->getMetaModel()->getFallbackLanguage();

            list($langCode, $country) = explode('_', $langCode, 2);
            return new DefaultLanguageInformation($langCode, $country ?: null);
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
