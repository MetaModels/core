<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2025 The MetaModels team.
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
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2025 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\DcGeneral\Data;

use ContaoCommunityAlliance\DcGeneral\Data\CollectionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ConfigInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\Data\DCGE;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultCollection;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultConfig;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultFilterOptionCollection;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultLanguageInformation;
use ContaoCommunityAlliance\DcGeneral\Data\DefaultLanguageInformationCollection;
use ContaoCommunityAlliance\DcGeneral\Data\FilterOptionCollectionInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\MultiLanguageDataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\Data\VersionModel;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use MetaModels\Attribute\IAttribute;
use MetaModels\Attribute\IComplex;
use MetaModels\Attribute\ITranslated;
use MetaModels\Filter\IFilter;
use MetaModels\Helper\LocaleUtil;
use MetaModels\IItem;
use MetaModels\IItems;
use MetaModels\IMetaModel;
use MetaModels\Item;
use MetaModels\ITranslatedMetaModel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Data driver class for DC_General.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods) - The interface is too complex, maybe split into traits.
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @psalm-suppress DeprecatedTrait
 */
class Driver implements MultiLanguageDataProviderInterface
{
    /** @psalm-suppress DeprecatedTrait */
    use DriverBcLayerTrait;

    /**
     * Name of current table.
     *
     * @var string|null
     */
    protected string|null $strTable = null;

    /**
     * The MetaModel this DataContainer is working on.
     *
     * @var IMetaModel|null
     */
    protected IMetaModel|null $metaModel = null;

    /**
     * The event dispatcher to pass to items.
     *
     * @var EventDispatcherInterface|null
     */
    private EventDispatcherInterface|null $dispatcher = null;

    /**
     * The current active language.
     *
     * @var string
     */
    protected string $strCurrentLanguage = '';

    /**
     * The database connection.
     *
     * @var Connection|null
     */
    private Connection|null $connection = null;

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
     * Set the connection to use.
     *
     * @param Connection $connection The connection.
     *
     * @return Driver
     */
    public function setConnection(Connection $connection)
    {
        $this->connection = $connection;

        return $this;
    }

    /**
     * Delete an item.
     *
     * The given value may be either integer, string or an instance of Model
     *
     * @param mixed $item Id or the model itself, to delete.
     *
     * @return void
     *
     * @throws \RuntimeException When an unusable object has been passed.
     */
    #[\Override]
    public function delete($item)
    {
        $metaModel = $this->getMetaModel();
        assert($metaModel instanceof IMetaModel);

        // Determine the id.
        if (\is_object($item) && ($item instanceof Model)) {
            $objModelItem = $item->getItem();
        } else {
            $objModelItem = $metaModel->findById($item);
        }
        if ($objModelItem) {
            $metaModel->delete($objModelItem);
        }
    }

    /**
     * Save a new Version of a record.
     *
     * Stores the model's properties the same way a normal edit would arrive at them - through
     * {@see Model::getProperty()}, which already runs every attribute's valueToWidget() - so
     * restoring later can go back through setProperty()/widgetToValue() and reuse the very same
     * save path a regular edit takes. This covers complex attributes (tags, table fields, ...)
     * without any attribute-specific code, at the cost of only covering the currently active
     * language for translated attributes - see ".claude/dcg-versionierung.md".
     *
     * @param ModelInterface $model    The model to be saved.
     * @param string         $username The username that creates the new version.
     *
     * @return void
     *
     * @throws Exception When the database can not be queried.
     */
    #[\Override]
    public function saveVersion(ModelInterface $model, $username)
    {
        assert($this->connection instanceof Connection);
        $fromTable = $this->getMetaModel()->getTableName();

        $count = (int) $this->connection
            ->createQueryBuilder()
            ->select('COUNT(*) AS count')
            ->from('tl_version')
            ->andWhere('pid = :pid')
            ->andWhere('fromTable = :fromTable')
            ->setParameter('pid', $model->getId())
            ->setParameter('fromTable', $fromTable)
            ->executeQuery()
            ->fetchOne();

        $newVersion = $count + 1;

        $this->connection->insert('tl_version', [
            'pid'       => $model->getId(),
            'tstamp'    => \time(),
            'version'   => $newVersion,
            'fromTable' => $fromTable,
            'username'  => $username,
            'data'      => \serialize($model->getPropertiesAsArray()),
        ]);

        $this->setVersionActive($model->getId(), $newVersion);
    }

    /**
     * Return a model based of the version information.
     *
     * @param mixed $mixID      The ID of record.
     * @param mixed $mixVersion The ID of the version.
     *
     * @return ModelInterface|null
     *
     * @throws Exception When the database can not be queried.
     */
    #[\Override]
    public function getVersion($mixID, $mixVersion)
    {
        assert($this->connection instanceof Connection);

        $row = $this->connection
            ->createQueryBuilder()
            ->select('data')
            ->from('tl_version')
            ->andWhere('pid = :pid')
            ->andWhere('version = :version')
            ->andWhere('fromTable = :fromTable')
            ->setParameter('pid', $mixID)
            ->setParameter('version', $mixVersion)
            ->setParameter('fromTable', $this->getMetaModel()->getTableName())
            ->executeQuery()
            ->fetchAssociative();

        if (false === $row) {
            return null;
        }

        $data = \unserialize((string) $row['data']);
        if (!\is_array($data)) {
            return null;
        }

        $model = $this->getEmptyModel();
        $model->setId($mixID);
        foreach ($data as $propertyName => $value) {
            if ('id' === $propertyName) {
                continue;
            }

            $model->setProperty((string) $propertyName, $value);
        }

        return $model;
    }

    /**
     * Set a version as active.
     *
     * @param mixed $mixID      The ID of record.
     * @param mixed $mixVersion The ID of the version.
     *
     * @return void
     *
     * @throws Exception When the database can not be queried.
     */
    #[\Override]
    public function setVersionActive($mixID, $mixVersion)
    {
        assert($this->connection instanceof Connection);
        $fromTable    = $this->getMetaModel()->getTableName();
        $updateValues = ['pid' => $mixID, 'fromTable' => $fromTable];

        // "active" is a strict tinyint(1), not the char(1) flag most Contao/MetaModels tables use -
        // an empty string fails under strict SQL mode.
        $this->connection->update('tl_version', ['active' => 0], $updateValues);
        $this->connection->update(
            'tl_version',
            ['active' => 1],
            ['pid' => $mixID, 'fromTable' => $fromTable, 'version' => $mixVersion]
        );
    }

    /**
     * Return the active version from a record.
     *
     * @param mixed $mixID The ID of the record.
     *
     * @return mixed
     *
     * @throws Exception When the database can not be queried.
     */
    #[\Override]
    public function getActiveVersion($mixID)
    {
        assert($this->connection instanceof Connection);

        $version = $this->connection
            ->createQueryBuilder()
            ->select('version')
            ->from('tl_version')
            ->andWhere('pid = :pid')
            ->andWhere('fromTable = :fromTable')
            ->andWhere('active = :active')
            ->setParameter('pid', $mixID)
            ->setParameter('fromTable', $this->getMetaModel()->getTableName())
            ->setParameter('active', '1')
            ->executeQuery()
            ->fetchOne();

        return false === $version ? null : $version;
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
        $metaModel = $this->getMetaModel();
        // @deprecated usage of TL_LANGUAGE - remove for Contao 5.0.
        $previousLanguage = ($metaModel instanceof ITranslatedMetaModel)
            ? $metaModel->getLanguage()
            : LocaleUtil::formatAsLocale($GLOBALS['TL_LANGUAGE']);

        if (empty($language)) {
            return $previousLanguage;
        }

        if ($metaModel instanceof ITranslatedMetaModel) {
            $previousLanguage = $metaModel->selectLanguage($language);
        }

        $language = LocaleUtil::formatAsLanguageTag($language);
        // @deprecated usage of TL_LANGUAGE - remove for Contao 5.0.
        if ($GLOBALS['TL_LANGUAGE'] !== $language) {
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
                throw new \RuntimeException('No MetaModel instance set for ' . (string) $this->strTable);
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
     * @param ConfigInterface $config The config to use.
     *
     * @return null|ModelInterface
     */
    #[\Override]
    public function fetch(ConfigInterface $config)
    {
        $backupLanguage = $this->setLanguage($currentLanguage = $this->getCurrentLanguage());

        if ($config->getId() !== null) {
            $modelId = $config->getId();
        } else {
            $filter  = $this->prepareFilter($config);
            $ids     = $this->getIdsFromFilter($filter, $config);
            $modelId = \reset($ids);
        }

        if (!(($model = $this->getMetaModel()) instanceof IMetaModel)) {
            return null;
        }
        assert(null === $modelId || is_string($modelId));

        $objItem = (null !== $modelId) ? $model->findById($modelId, $config->getFields() ?? []) : null;

        $this->setLanguage($backupLanguage);

        if (!$objItem) {
            return null;
        }

        return new Model($objItem, $currentLanguage);
    }

    /**
     * Set base config with source and other necessary parameter.
     *
     * @param array $config The configuration to use.
     *
     * @return void
     *
     * @throws \RuntimeException When no source has been defined.
     */
    #[\Override]
    public function setBaseConfig(array $config)
    {
        if (!$config['source']) {
            throw new \RuntimeException('Missing table name.');
        }

        $this->strTable  = $config['source'];
        $this->metaModel = $config['metaModel'] ?? null;
    }

    /**
     * Return empty config object.
     *
     * @return ConfigInterface
     */
    #[\Override]
    public function getEmptyConfig()
    {
        return DefaultConfig::init();
    }

    /**
     * Fetch an empty single record (new item).
     *
     * @return ModelInterface
     */
    #[\Override]
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
        return new Model($objItem, $this->getCurrentLanguage());
    }

    /**
     * Fetch an empty collection.
     *
     * @return CollectionInterface
     */
    #[\Override]
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
        if (null === $this->connection) {
            // @codingStandardsIgnoreStart
            @trigger_error(
                'Not setting a "' . Connection::class .
                '" via "setConnection()" is deprecated and will cause an error in MetaModels 3.0.',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
        }
        $builder = new FilterBuilder($this->getMetaModel(), $configuration, $this->connection);

        return $builder->build();
    }

    /**
     * Extract the sorting from the given config.
     *
     * @param ConfigInterface $config The configuration to be applied.
     *
     * @return array{string, string}|null
     */
    protected function extractSorting($config)
    {
        $sorting = $config->getSorting();
        $sortBy  = \key($sorting);
        if (null === $sortBy) {
            return null;
        }
        $sortDir = $sorting[$sortBy] ?? DCGE::MODEL_SORTING_ASC;

        return [$sortBy, \strtoupper($sortDir)];
    }

    /**
     * Fetch the ids via the given filter.
     *
     * @param IFilter         $filter The filter.
     * @param ConfigInterface $config The configuration to be applied.
     *
     * @return list<string>
     */
    protected function getIdsFromFilter($filter, $config)
    {
        $sorting = $this->extractSorting($config);

        return $this->getMetaModel()->getIdsFromFilter(
            $filter,
            $sorting[0] ?? '',
            $config->getStart(),
            $config->getAmount(),
            $sorting[1] ?? DCGE::MODEL_SORTING_ASC,
        );
    }

    /**
     * Fetch the items via the given filter.
     *
     * @param IFilter         $filter The filter.
     * @param ConfigInterface $config The configuration to be applied.
     *
     * @return IItems The collection of IItem instances that match the given filter.
     */
    protected function getItemsFromFilter($filter, $config)
    {
        $sorting = $this->extractSorting($config);

        return $this->getMetaModel()->findByFilter(
            $filter,
            $sorting[0] ?? '',
            $config->getStart(),
            $config->getAmount(),
            $sorting[1] ?? DCGE::MODEL_SORTING_ASC,
            $config->getFields() ?? []
        );
    }

    /**
     * Fetch all records (optional filtered, sorted and limited).
     *
     * @param ConfigInterface $config The configuration to be applied.
     *
     * @return CollectionInterface|list<string>
     */
    #[\Override]
    public function fetchAll(ConfigInterface $config)
    {
        $backupLanguage = $this->setLanguage($this->getCurrentLanguage());

        $filter = $this->prepareFilter($config);
        if ($config->getIdOnly()) {
            $this->setLanguage($backupLanguage);

            return $this->getIdsFromFilter($filter, $config);
        }

        $items      = $this->getItemsFromFilter($filter, $config);
        $collection = $this->getEmptyCollection();
        foreach ($items as $objItem) {
            if (!($objItem instanceof IItem)) {
                continue;
            }

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
     * @param ConfigInterface $config The filter config options.
     *
     * @return FilterOptionCollectionInterface
     *
     * @throws \RuntimeException If improper values have been passed (i.e. not exactly one field requested).
     */
    #[\Override]
    public function getFilterOptions(ConfigInterface $config)
    {
        $arrProperties = $config->getFields();
        if (\count($arrProperties ?? []) <> 1) {
            throw new \RuntimeException('objConfig must contain exactly one property to be retrieved.');
        }

        $objFilter = $this->prepareFilter($config);

        $metaModel = $this->getMetaModel();
        assert($metaModel instanceof IMetaModel);

        $backupLanguage = $this->setLanguage($this->getCurrentLanguage());
        $arrValues = $metaModel
            ->getAttributeOptions($arrProperties[0] ?? '', $objFilter);
        $this->setLanguage($backupLanguage);

        $objCollection = new DefaultFilterOptionCollection();
        foreach ($arrValues as $strKey => $strValue) {
            $objCollection->add($strKey, $strValue);
        }

        return $objCollection;
    }

    /**
     * Return the amount of total items (filtering may be used in the config).
     *
     * @param ConfigInterface $config The filter config options.
     *
     * @return int
     */
    #[\Override]
    public function getCount(ConfigInterface $config)
    {
        $objFilter = $this->prepareFilter($config);
        $metaModel = $this->getMetaModel();
        assert($metaModel instanceof IMetaModel);

        return $metaModel->getCount($objFilter);
    }

    /**
     * Return a list with all versions for the model with the given Id.
     *
     * @param mixed   $mixID      The ID of the row.
     * @param boolean $onlyActive If true, only active versions will get returned, if false all version will get
     *                            returned.
     *
     * @return CollectionInterface
     *
     * @throws Exception When the database can not be queried.
     */
    #[\Override]
    public function getVersions($mixID, $onlyActive = false)
    {
        assert($this->connection instanceof Connection);

        $queryBuilder = $this->connection
            ->createQueryBuilder()
            ->select('tstamp', 'version', 'username', 'active')
            ->from('tl_version')
            ->andWhere('pid = :pid')
            ->andWhere('fromTable = :fromTable')
            ->setParameter('pid', $mixID)
            ->setParameter('fromTable', $this->getMetaModel()->getTableName());

        if ($onlyActive) {
            $queryBuilder->andWhere('active = :active')->setParameter('active', '1');
        } else {
            $queryBuilder->orderBy('version', 'DESC');
        }

        // A version-list row is metadata about a version (tstamp/version/username/active), not a
        // MetaModels item - VersionModel (a plain property bag) fits, the full Item/attribute
        // machinery getEmptyModel() would pull in does not and does not implement
        // VersionModelInterface, which the edit mask's version drop-down requires.
        $collection = $this->getEmptyCollection();
        foreach ($queryBuilder->executeQuery()->fetchAllAssociative() as $row) {
            $model = new VersionModel();
            $model->setProviderName($this->getMetaModel()->getTableName());
            foreach ($row as $propertyName => $value) {
                $model->setProperty($propertyName, $value);
            }

            // The template submits this as "version" to restore - the item id stays with $mixID and
            // is not what identifies a single entry in this list, the version number does.
            $model->setIdRaw($row['version']);

            $collection->push($model);
        }

        return $collection;
    }

    /**
     * Determine if a given value is unique within the metamodel.
     *
     * @param string $field     The attribute name.
     * @param mixed  $new       The value that shall be checked.
     * @param mixed  $primaryId The (optional) id of the item currently in scope - pass null for new items.
     *
     * @return bool True if the values is not yet contained within the table, false otherwise.
     */
    #[\Override]
    public function isUniqueValue($field, $new, $primaryId = null)
    {
        $model = $this->getMetaModel();
        assert($model instanceof IMetaModel);
        $attribute = $model->getAttribute($field);
        if (null !== $attribute) {
            $matchingIds = $this
                ->prepareFilter(
                    $this->getEmptyConfig()->setFilter(
                        [
                            [
                                'operation' => '=',
                                'property'  => $attribute->getColName(),
                                'value'     => $new
                            ]
                        ]
                    )
                )
                ->getMatchingIds();

            return ([] === $matchingIds) || ([(string) $primaryId] === $matchingIds);
        }

        return false;
    }

    /**
     * Reset the fallback field.
     *
     * This clears the given property in all items in the data provider to an empty value.
     *
     * @param string $field The field to reset.
     *
     * @return never
     *
     * @throws \RuntimeException For invalid ids.
     */
    #[\Override]
    public function resetFallback($field)
    {
        // @codingStandardsIgnoreStart
        @trigger_error(__CLASS__ . '::' . __METHOD__ . ' is deprecated - handle resetting manually', E_USER_DEPRECATED);
        // @codingStandardsIgnoreEnd

        $metaModel = $this->getMetaModel();
        assert($metaModel instanceof IMetaModel);

        $attribute = $metaModel->getAttribute($field);
        $ids       = $metaModel->getIdsFromFilter(null);

        if ($attribute instanceof IComplex) {
            $attribute->unsetDataFor($ids);
        }
        if ($attribute instanceof ITranslated) {
            $attribute->unsetValueFor($ids, $this->getCurrentLanguage());
        }
        if ($attribute instanceof IAttribute) {
            $data = [];
            foreach ($ids as $id) {
                $data[$id] = null;
            }
            $attribute->setDataFor($data);
        }

        throw new \RuntimeException('Unknown attribute or type ' . $field);
    }

    /**
     * Save an item to the data provider.
     *
     * If the item does not have an Id yet, the save operation will add it as a new row to the database and
     * populate the Id of the model accordingly.
     *
     * @param ModelInterface $item      The model to save back.
     * @param int|null       $timestamp Optional the timestamp.
     *
     * @return ModelInterface The passed model.
     *
     * @throws \RuntimeException When an incompatible item was passed, an Exception is being thrown.
     */
    #[\Override]
    public function save(ModelInterface $item, $timestamp = null)
    {
        if (null === $timestamp) {
            // @codingStandardsIgnoreStart
            @\trigger_error(
                'Not passing a timestamp has been deprecated and will cause an error in MetaModels 3',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
        }

        if ($item instanceof Model) {
            $backupLanguage = $this->setLanguage($this->getCurrentLanguage());

            $mmItem = $item->getItem();
            assert($mmItem instanceof IItem);
            $mmItem->save($timestamp);

            $this->setLanguage($backupLanguage);

            return $item;
        }

        throw new \RuntimeException('ERROR: incompatible object passed to GeneralDataMetaModel::save()');
    }

    /**
     * Save a collection of items to the data provider.
     *
     * @param CollectionInterface $items     The collection containing all items to be saved.
     * @param int|null            $timestamp Optional the timestamp.
     *
     * @return void
     *
     * @throws \RuntimeException When an incompatible item was passed.
     */
    #[\Override]
    public function saveEach(CollectionInterface $items, $timestamp = 0)
    {
        if (null === $timestamp) {
            // @codingStandardsIgnoreStart
            @\trigger_error(
                'Not passing a timestamp has been deprecated and will cause an error in MetaModels 3',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
        }

        foreach ($items as $objItem) {
            $this->save($objItem, $timestamp);
        }
    }

    /**
     * Check if the attribute exists in the table and holds a value.
     *
     * @param string $columnName The name of the attribute that shall be tested.
     *
     * @return boolean
     */
    #[\Override]
    public function fieldExists($columnName)
    {
        return !!(
            \in_array($columnName, ['id', 'pid', 'tstamp', 'sorting'])
            || $this->getMetaModel()->getAttribute($columnName)
        );
    }

    /**
     * Check if two models have the same values in all properties.
     *
     * @param ModelInterface $firstModel  The first model to compare.
     * @param ModelInterface $secondModel The second model to compare.
     *
     * @return boolean True - If both models are same, false if not.
     *
     * @throws \InvalidArgumentException If not both models are compatible with this data provider.
     */
    #[\Override]
    public function sameModels($firstModel, $secondModel)
    {
        if (!($firstModel instanceof Model && $secondModel instanceof Model)) {
            throw new \InvalidArgumentException('Passed models are not valid.');
        }

        $objNative1 = $firstModel->getItem();
        assert($objNative1 instanceof IItem);
        $objNative2 = $secondModel->getItem();
        assert($objNative2 instanceof IItem);
        foreach ($objNative1->getMetaModel()->getAttributes() as $objAttribute) {
            if ($objNative1->get($objAttribute->getColName()) !== $objNative2->get($objAttribute->getColName())) {
                return false;
            }
        }

        return true;
    }

    /**
     * Fetch a variant of a single record by id.
     *
     * @param ConfigInterface $objConfig The config holding the id of the base model.
     * @param array           $doNotCopy The array of doNotCopy.
     *
     * @return null|ModelInterface
     */
    public function createVariant(ConfigInterface $objConfig, array $doNotCopy = [])
    {
        $metaModel = $this->getMetaModel();
        assert($metaModel instanceof IMetaModel);
        $item = $metaModel->findById($objConfig->getId());
        assert($item instanceof IItem);
        $objItem = $item->varCopy();

        // If a field has the eval[doNotCopy] flag set the value to null.
        foreach ($doNotCopy as $field) {
            $objItem->set($field, null);
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
    #[\Override]
    public function getLanguages($mixID)
    {
        $metaModel = $this->getMetaModel();
        if ($metaModel instanceof ITranslatedMetaModel) {
            $collection = new DefaultLanguageInformationCollection();
            foreach ($metaModel->getLanguages() as $langCode) {
                [$langCode, $locale] = \explode('_', $langCode, 2) + [null, null];
                $collection->add(new DefaultLanguageInformation($langCode, $locale));
            }
            if (\count($collection) > 0) {
                return $collection;
            }

            return null;
        }

        /**
         * @psalm-suppress DeprecatedMethod
         * @psalm-suppress TooManyArguments
         */
        if (!($metaModel instanceof ITranslatedMetaModel) && !$metaModel->isTranslated(false)) {
            return null;
        }

        // @coverageIgnoreStart
        return $this->getLanguagesBcLayer($metaModel);
        // @coverageIgnoreEnd
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    #[\Override]
    public function getFallbackLanguage($mixID)
    {
        $metaModel = $this->getMetaModel();
        if ($metaModel instanceof ITranslatedMetaModel) {
            [$langCode, $locale] = \explode('_', $metaModel->getMainLanguage(), 2) + [null, null];
            return new DefaultLanguageInformation($langCode, $locale);
        }

        /**
         * @psalm-suppress DeprecatedMethod
         * @psalm-suppress TooManyArguments
         */
        if (!($metaModel instanceof ITranslatedMetaModel) && !$metaModel->isTranslated(false)) {
            return null;
        }

        // @coverageIgnoreStart
        return $this->getFallbackLanguageBcLayer($metaModel);
        // @coverageIgnoreEnd
    }

    /**
     * Set the current working language for the whole data provider.
     *
     * @param string $language The new language, use short tag "2 chars like de, for etc.".
     *
     * @return DataProviderInterface
     */
    #[\Override]
    public function setCurrentLanguage($language)
    {
        $this->strCurrentLanguage = $language;

        return $this;
    }

    /**
     * Get the current working language.
     *
     * @return string Short tag for the current working language like de or for etc.
     */
    #[\Override]
    public function getCurrentLanguage()
    {
        return '' === $this->strCurrentLanguage ? 'en' : $this->strCurrentLanguage;
    }
}
