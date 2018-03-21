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
 * @author     Christopher Boelter <c.boelter@cogizz.de>
 * @author     David Greminger <david.greminger@1up.io>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Martin Treml <github@r2pi.net>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Chris Raidler <c.raidler@rad-consulting.ch>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels;

use MetaModels\DataAccess\DatabaseHelperTrait;
use MetaModels\DataAccess\IdResolver;
use MetaModels\DataAccess\ItemPersister;
use MetaModels\DataAccess\ItemRetriever;
use MetaModels\Filter\Filter;
use MetaModels\Attribute\IAttribute;
use MetaModels\Filter\IFilter;
use MetaModels\Filter\Rules\SimpleQuery;
use MetaModels\Filter\Rules\StaticIdList;

/**
 * This is the main MetaModel class.
 *
 * @see MetaModelFactory::byId()        to instantiate a MetaModel by its ID.
 * @see MetaModelFactory::byTableName() to instantiate a MetaModel by its table name.
 *
 * This class handles all attribute definition instantiation and can be queried for a view instance to certain entries.
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class MetaModel implements IMetaModel
{
    use DatabaseHelperTrait;

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
        return (bool) $this->arrData['varsupport'];
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
     * {@inheritdoc}
     */
    public function findById($intId, $arrAttrOnly = array())
    {
        if (!$intId) {
            return null;
        }
        $database  = $this->getDatabase();
        $retriever = new ItemRetriever($this, $database);
        $resolver  = new IdResolver($this, $database);
        $resolver
            ->setFilter($this->getEmptyFilter()->addFilterRule(new StaticIdList([$intId])))
            ->setLimit(1);
        $items = $retriever
            ->setAttributes($arrAttrOnly ?: array_keys($this->arrAttributes))
            ->findItems($resolver);
        if ($items && $items->first()) {
            return $items->getItem();
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
        $arrAttrOnly = []
    ) {
        $database  = $this->getDatabase();
        $retriever = new ItemRetriever($this, $database);
        $resolver  = IdResolver::create($this, $database);
        $resolver
            ->setFilter($objFilter)
            ->setSortOrder($strSortOrder)
            ->setSortBy($strSortBy)
            ->setLimit($intLimit)
            ->setOffset($intOffset);

        return $retriever->setAttributes($arrAttrOnly ?: array_keys($this->arrAttributes))->findItems($resolver);
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function getIdsFromFilter($objFilter, $strSortBy = '', $intOffset = 0, $intLimit = 0, $strSortOrder = 'ASC')
    {
        return IdResolver::create($this, $this->getDatabase())
            ->setFilter($objFilter)
            ->setSortOrder($strSortOrder)
            ->setSortBy($strSortBy)
            ->setLimit($intLimit)
            ->setOffset($intOffset)
            ->getIds();
    }

    /**
     * {@inheritdoc}
     */
    public function getCount($objFilter)
    {
        return IdResolver::create($this, $this->getDatabase())->setFilter($objFilter)->count();
    }

    /**
     * {@inheritdoc}
     */
    public function findVariantBase($objFilter)
    {
        $filter = $this->copyFilter($objFilter);
        $filter->addFilterRule(new SimpleQuery('SELECT id FROM ' . $this->getTableName() . ' WHERE varbase=1'));
        return $this->findByFilter($filter);
    }

    /**
     * {@inheritdoc}
     */
    public function findVariants($arrIds, $objFilter)
    {
        if (!$arrIds) {
            // Return an empty result.
            return new Items([]);
        }

        $filter = $this->copyFilter($objFilter);
        $filter->addFilterRule(new SimpleQuery(
            sprintf(
                'SELECT id,vargroup FROM %s WHERE varbase=0 AND vargroup IN (%s)',
                $this->getTableName(),
                $this->buildDatabaseParameterList($arrIds)
            ),
            $arrIds
        ));

        return $this->findByFilter($filter);
    }

    /**
     * {@inheritdoc}
     */
    public function findVariantsWithBase($arrIds, $objFilter)
    {
        if (!$arrIds) {
            // Return an empty result.
            return new Items([]);
        }
        $filter = $this->copyFilter($objFilter);

        $filter->addFilterRule(new SimpleQuery(
            sprintf(
                'SELECT id,vargroup FROM %1$s WHERE vargroup IN (SELECT vargroup FROM %1$s WHERE id IN (%2$s))',
                $this->getTableName(),
                $this->buildDatabaseParameterList($arrIds)
            ),
            $arrIds
        ));

        return $this->findByFilter($filter);
    }

    /**
     * {@inheritdoc}
     */
    public function getAttributeOptions($strAttribute, $objFilter = null)
    {
        if (null === ($attribute = $this->getAttribute($strAttribute))) {
            return [];
        }

        if ($objFilter) {
            $filteredIds = IdResolver::create($this, $this->getDatabase())
                ->setFilter($objFilter)
                ->setSortBy($strAttribute)
                ->getIds();

            return $attribute->getFilterOptions($filteredIds, true);
        }

        return $attribute->getFilterOptions(null, true);
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

        $persister = new ItemPersister($this, $this->getDatabase());
        $persister->saveItem($objItem, $timestamp ?: \time());
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

    /**
     * Clone the given filter or create an empty one if no filter has been passed.
     *
     * @param IFilter|null $objFilter The filter to clone.
     *
     * @return IFilter the cloned filter.
     */
    private function copyFilter($objFilter)
    {
        if ($objFilter) {
            $objNewFilter = $objFilter->createCopy();
        } else {
            $objNewFilter = $this->getEmptyFilter();
        }
        return $objNewFilter;
    }

    /**
     * Retrieve the database instance to use.
     *
     * @return \Contao\Database
     */
    private function getDatabase()
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
    private function tryUnserialize($value)
    {
        if (!is_array($value) && (substr($value, 0, 2) == 'a:')) {
            $unSerialized = unserialize($value);
        }

        if (isset($unSerialized) && is_array($unSerialized)) {
            return $unSerialized;
        }

        return $value;
    }
}
