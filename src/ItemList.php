<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2019 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Oliver Hoff <oliver@hofff.com>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tim Gatzky <info@tim-gatzky.de>
 * @author     Martin Treml <github@r2pi.net>
 * @author     Jeremie Constant <j.constant@imi.de>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels;

use Contao\StringUtil;
use Contao\System;
use MetaModels\Events\RenderItemListEvent;
use MetaModels\Filter\Setting\IFilterSettingFactory;
use MetaModels\Helper\PaginationLimitCalculator;
use MetaModels\Render\Setting\IRenderSettingFactory;
use MetaModels\Render\Template;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Implementation of a general purpose MetaModel listing.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class ItemList implements IServiceContainerAware
{
    /**
     * Sort by attribute.
     *
     * @var string
     */
    protected $strSortBy = '';

    /**
     * Sort by attribute.
     *
     * @var string
     */
    protected $strSortDirection = 'ASC';

    /**
     * The view to use.
     *
     * @var int
     */
    protected $intView = 0;

    /**
     * Sort by attribute.
     *
     * @var string
     */
    protected $strOutputFormat;

    /**
     * The MetaModel to use.
     *
     * @var int
     */
    protected $intMetaModel = 0;

    /**
     * The filter to use.
     *
     * @var int
     */
    protected $intFilter = 0;

    /**
     * The parameters for the filter.
     *
     * @var string[]
     */
    protected $arrParam = array();

    /**
     * The name of the attribute for the title.
     *
     * @var string
     */
    protected $strTitleAttribute = '';

    /**
     * The name of the attribute for the description.
     *
     * @var string
     */
    protected $strDescriptionAttribute = '';

    /**
     * The service container.
     *
     * @var IMetaModelsServiceContainer|\Closure
     *
     * @deprecated The service container will get removed.
     */
    private $serviceContainer;

    /**
     * The calculator for pagination and limit.
     *
     * @var PaginationLimitCalculator
     */
    private $paginationLimitCalculator;

    /**
     *  The filter setting factory.
     *
     * @var IFilterSettingFactory
     */
    private $filterFactory;

    /**
     * The MetaModels factory.
     *
     * @var IFactory
     */
    private $factory;

    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * The render setting factory.
     *
     * @var IRenderSettingFactory
     */
    private $renderSettingFactory;

    /**
     * Create a new instance.
     */
    public function __construct()
    {
        $this->paginationLimitCalculator = new PaginationLimitCalculator();
    }

    /**
     * Set the filter setting factory.
     *
     * @param IFilterSettingFactory $filterFactory The filter setting factory.
     *
     * @return ItemList
     */
    public function setFilterFactory(IFilterSettingFactory $filterFactory)
    {
        $this->filterFactory = $filterFactory;

        return $this;
    }

    /**
     * Retrieve the filter setting factory.
     *
     * @return IFilterSettingFactory
     */
    private function getFilterFactory()
    {
        if ($this->filterFactory) {
            return $this->filterFactory;
        }

        return $this->getServiceContainer()->getFilterFactory();
    }

    /**
     * Set the factory.
     *
     * @param IFactory $factory The factory.
     *
     * @return ItemList
     */
    public function setFactory(IFactory $factory)
    {
        $this->factory = $factory;

        return $this;
    }

    /**
     * Retrieve the filter setting factory.
     *
     * @return IFactory
     */
    private function getFactory()
    {
        if ($this->factory) {
            return $this->factory;
        }

        return $this->getServiceContainer()->getFactory();
    }

    /**
     * Set the render setting factory.
     *
     * @param IRenderSettingFactory $factory The factory.
     *
     * @return ItemList
     */
    public function setRenderSettingFactory(IRenderSettingFactory $factory)
    {
        $this->renderSettingFactory = $factory;

        return $this;
    }

    /**
     * Set eventDispatcher.
     *
     * @param EventDispatcherInterface $eventDispatcher The new value.
     *
     * @return ItemList
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;

        return $this;
    }

    /**
     * Retrieve eventDispatcher.
     *
     * @return EventDispatcherInterface
     */
    private function getEventDispatcher()
    {
        if ($this->eventDispatcher) {
            return $this->eventDispatcher;
        }

        return $this->getServiceContainer()->getEventDispatcher();
    }

    /**
     * Set the service container to use.
     *
     * @param IMetaModelsServiceContainer $serviceContainer The service container.
     *
     * @return ItemList
     *
     * @deprecated The service container will get removed, use the symfony service container instead.
     */
    public function setServiceContainer(IMetaModelsServiceContainer $serviceContainer)
    {
        $this->serviceContainer = $serviceContainer;

        return $this;
    }

    /**
     * Set the service container to use (fallback for MM 2.0 compatibility).
     *
     * @param \Closure $serviceContainer The service container retriever.
     *
     * @return ItemList
     *
     * @deprecated The service container will get removed, use the symfony service container instead.
     */
    public function setServiceContainerFallback($serviceContainer)
    {
        $this->serviceContainer = $serviceContainer;

        return $this;
    }

    /**
     * Try to set the default service container.
     *
     * @return void
     *
     * @throws \RuntimeException When the service container could not be set.
     */
    private function useDefaultServiceContainer()
    {
        $this->serviceContainer = System::getContainer()->get('metamodels.service_container');

        if (!($this->serviceContainer instanceof IMetaModelsServiceContainer)) {
            throw new \RuntimeException('Unable to retrieve default service container.');
        }
    }

    /**
     * Retrieve the service container in use.
     *
     * @return IMetaModelsServiceContainer|null
     *
     * @deprecated The service container will get removed, use the symfony service container instead.
     */
    public function getServiceContainer()
    {
        if (!$this->serviceContainer) {
            $this->useDefaultServiceContainer();
        }
        if (is_callable($this->serviceContainer)) {
            return $this->serviceContainer = $this->serviceContainer->__invoke();
        }

        return $this->serviceContainer;
    }

    /**
     * Set the limit.
     *
     * @param bool $blnUse    If true, use limit, if false no limit is applied.
     *
     * @param int  $intOffset Like in SQL, first element to be returned (0 based).
     *
     * @param int  $intLimit  Like in SQL, amount of elements to retrieve.
     *
     * @return ItemList
     */
    public function setLimit($blnUse, $intOffset, $intLimit)
    {
        $this
            ->paginationLimitCalculator
            ->setApplyLimitAndOffset($blnUse)
            ->setOffset($intOffset)
            ->setLimit($intLimit);

        return $this;
    }

    /**
     * Set page breaking to the given amount of items. A value of 0 disables pagination at all.
     *
     * @param int $intLimit The amount of items per page. A value of 0 disables pagination.
     *
     * @return ItemList
     */
    public function setPageBreak($intLimit)
    {
        $this->paginationLimitCalculator->setPerPage($intLimit);

        return $this;
    }

    /**
     * Set sorting to an attribute or system column optionally in the given direction.
     *
     * @param string $strSortBy    The name of the attribute or system column to be used for sorting.
     *
     * @param string $strDirection The direction, either ASC or DESC (optional).
     *
     * @return ItemList
     */
    public function setSorting($strSortBy, $strDirection = 'ASC')
    {
        $this->strSortBy        = $strSortBy;
        $this->strSortDirection = ($strDirection == 'DESC') ? 'DESC' : 'ASC';

        return $this;
    }

    /**
     * Override the output format of the used view.
     *
     * @param string|null $strOutputFormat The desired output format.
     *
     * @return ItemList
     */
    public function overrideOutputFormat($strOutputFormat = null)
    {
        $strOutputFormat = strval($strOutputFormat);
        if (strlen($strOutputFormat)) {
            $this->strOutputFormat = $strOutputFormat;
        } else {
            unset($this->strOutputFormat);
        }

        return $this;
    }

    /**
     * Set MetaModel and render settings.
     *
     * @param int $intMetaModel The MetaModel to use.
     *
     * @param int $intView      The render settings to use (if 0, the default will be used).
     *
     * @return ItemList
     */
    public function setMetaModel($intMetaModel, $intView)
    {
        $this->intMetaModel = $intMetaModel;
        $this->intView      = $intView;

        $this->prepareMetaModel();

        $this->prepareView();

        return $this;
    }

    /**
     * Add the attribute names for meta title and description.
     *
     * @param string $strTitleAttribute       Name of attribute for title.
     *
     * @param string $strDescriptionAttribute Name of attribue for description.
     *
     * @return ItemList
     */
    public function setMetaTags($strTitleAttribute, $strDescriptionAttribute)
    {
        $this->strDescriptionAttribute = $strDescriptionAttribute;
        $this->strTitleAttribute       = $strTitleAttribute;

        return $this;
    }

    /**
     * The Metamodel to use.
     *
     * @var IMetaModel
     */
    protected $objMetaModel;

    /**
     * The render settings to use.
     *
     * @var \MetaModels\Render\Setting\ICollection
     */
    protected $objView;

    /**
     * The render template to use.
     *
     * @var Template
     */
    protected $objTemplate;

    /**
     * The filter settings to use.
     *
     * @var \MetaModels\Filter\Setting\ICollection
     */
    protected $objFilterSettings;

    /**
     * The filter to use.
     *
     * @var \MetaModels\Filter\IFilter
     */
    protected $objFilter;

    /**
     * Prepare the MetaModel.
     *
     * @return void
     *
     * @throws \RuntimeException When the MetaModel can not be found.
     */
    protected function prepareMetaModel()
    {
        $factory            = $this->getFactory();
        $this->objMetaModel = $factory->getMetaModel($factory->translateIdToMetaModelName($this->intMetaModel));
        if (!$this->objMetaModel) {
            throw new \RuntimeException('Could get metamodel id: ' . $this->intMetaModel);
        }
    }

    /**
     * Prepare the view.
     *
     * NOTE: must be called after prepareMetaModel().
     *
     * @return void
     */
    protected function prepareView()
    {
        if ($this->renderSettingFactory) {
            $this->objView = $this->renderSettingFactory->createCollection($this->objMetaModel, $this->intView);
        } else {
            $this->objView = $this->objMetaModel->getView($this->intView);
        }

        if ($this->objView) {
            $this->objTemplate       = new Template($this->objView->get('template'));
            $this->objTemplate->view = $this->objView;
        } else {
            // Fallback to default.
            $this->objTemplate = new Template('metamodel_full');
        }
    }

    /**
     * Set the filter setting to use.
     *
     * @param int $intFilter The filter setting to use.
     *
     * @return $this
     *
     * @throws \RuntimeException When the filter settings can not be found.
     */
    public function setFilterSettings($intFilter)
    {
        $this->intFilter = $intFilter;

        $this->objFilterSettings = $this->getFilterFactory()->createCollection($this->intFilter);

        if (!$this->objFilterSettings) {
            throw new \RuntimeException('Error: no filter object defined.');
        }

        return $this;
    }

    /**
     * Set parameters.
     *
     * @param string[] $arrPresets The parameter preset values to use.
     *
     * @param string[] $arrValues  The dynamic parameter values that may be used.
     *
     * @return ItemList
     *
     * @throws \RuntimeException When no filter settings have been set.
     */
    public function setFilterParameters($arrPresets, $arrValues)
    {
        if (!$this->objFilterSettings) {
            throw new \RuntimeException(
                'Error: no filter object defined, call setFilterSettings() before setFilterParameters().'
            );
        }

        $arrPresetNames    = $this->objFilterSettings->getParameters();
        $arrFEFilterParams = array_keys($this->objFilterSettings->getParameterFilterNames());

        $arrProcessed = array();

        // We have to use all the preset values we want first.
        foreach ($arrPresets as $strPresetName => $arrPreset) {
            if (in_array($strPresetName, $arrPresetNames)) {
                $arrProcessed[$strPresetName] = $arrPreset['value'];
            }
        }

        // Now we have to use all FE filter params, that are either:
        // * not contained within the presets
        // * or are overridable.
        foreach ($arrFEFilterParams as $strParameter) {
            // Unknown parameter? - next please.
            if (!array_key_exists($strParameter, $arrValues)) {
                continue;
            }

            // Not a preset or allowed to override? - use value.
            if ((!array_key_exists($strParameter, $arrPresets)) || $arrPresets[$strParameter]['use_get']) {
                $arrProcessed[$strParameter] = $arrValues[$strParameter];
            }
        }

        $this->arrParam = $arrProcessed;

        return $this;
    }

    /**
     * Return the filter.
     *
     * @return \MetaModels\Filter\IFilter
     */
    public function getFilter()
    {
        return $this->objFilter;
    }

    /**
     * Return the filter settings.
     *
     * @return \MetaModels\Filter\Setting\ICollection
     */
    public function getFilterSettings()
    {
        return $this->objFilterSettings;
    }

    /**
     * The items in the list view.
     *
     * @var \MetaModels\IItems
     */
    protected $objItems = null;

    /**
     * Add additional filter rules to the list.
     *
     * Can be overridden by subclasses to add additional filter rules to the filter before it will get evaluated.
     *
     * @return ItemList
     */
    protected function modifyFilter()
    {
        return $this;
    }

    /**
     * Add additional filter rules to the list on the fly.
     *
     * @param \MetaModels\Filter\IFilterRule $objFilterRule The filter rule to add.
     *
     * @return ItemList
     */
    public function addFilterRule($objFilterRule)
    {
        if (!$this->objFilter) {
            $this->objFilter = $this->objMetaModel->getEmptyFilter();
        }

        $this->objFilter->addFilterRule($objFilterRule);

        return $this;
    }

    /**
     * Return all attributes that shall be fetched from the MetaModel.
     *
     * In this base implementation, this only includes the attributes mentioned in the render setting.
     *
     * @return string[] the names of the attributes to be fetched.
     */
    protected function getAttributeNames()
    {
        $arrAttributes = $this->objView->getSettingNames();

        // Get the right jumpTo.
        $strDesiredLanguage  = $this->getMetaModel()->getActiveLanguage();
        $strFallbackLanguage = $this->getMetaModel()->getFallbackLanguage();

        $intFilterSettings = 0;

        foreach ((array) $this->getView()->get('jumpTo') as $arrJumpTo) {
            // If either desired language or fallback, keep the result.
            if (!$this->getMetaModel()->isTranslated()
                || $arrJumpTo['langcode'] == $strDesiredLanguage
                || $arrJumpTo['langcode'] == $strFallbackLanguage) {
                $intFilterSettings = $arrJumpTo['filter'];
                // If the desired language, break. Otherwise try to get the desired one until all have been evaluated.
                if ($strDesiredLanguage == $arrJumpTo['langcode']) {
                    break;
                }
            }
        }

        if ($intFilterSettings) {
            $objFilterSettings = $this->getFilterFactory()->createCollection($intFilterSettings);
            $arrAttributes     = array_merge($objFilterSettings->getReferencedAttributes(), $arrAttributes);
        }

        return $arrAttributes;
    }

    /**
     * Prepare the rendering.
     *
     * @return ItemList
     */
    public function prepare()
    {
        if ($this->objItems) {
            return $this;
        }

        // Create an empty filter object if not done before.
        if (!$this->objFilter) {
            $this->objFilter = $this->objMetaModel->getEmptyFilter();
        }

        if ($this->objFilterSettings) {
            $this->objFilterSettings->addRules($this->objFilter, $this->arrParam);
        }

        $this->modifyFilter();

        $intTotal = $this->objMetaModel->getCount($this->objFilter);

        $calculator = $this->paginationLimitCalculator;
        $calculator->setTotalAmount($intTotal);
        $curPage = (int) \Input::get('page');
        if ($curPage > 1) {
            $calculator->setCurrentPage($curPage);
        }
        $this->objTemplate->total = $intTotal;

        $this->objItems = $this->objMetaModel->findByFilter(
            $this->objFilter,
            $this->strSortBy,
            $calculator->getCalculatedOffset(),
            $calculator->getCalculatedLimit(),
            $this->strSortDirection,
            $this->getAttributeNames()
        );

        return $this;
    }

    /**
     * Returns the pagination string.
     *
     * Remember to call prepare() first.
     *
     * @return string
     */
    public function getPagination()
    {
        return $this->paginationLimitCalculator->getPaginationString();
    }

    /**
     * Returns the item list in the view.
     *
     * @return IItems
     */
    public function getItems()
    {
        return $this->objItems;
    }

    /**
     * Returns the view.
     *
     * @return \MetaModels\Render\Setting\ICollection
     */
    public function getView()
    {
        return $this->objView;
    }

    /**
     * Returns the MetaModel.
     *
     * @return IMetaModel
     */
    public function getMetaModel()
    {
        return $this->objMetaModel;
    }

    /**
     * Retrieve the page object.
     *
     * @return object
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function getPage()
    {
        return (TL_MODE == 'FE' && is_object($GLOBALS['objPage'])) ? $GLOBALS['objPage'] : null;
    }

    /**
     * Retrieve the output format used by this list.
     *
     * @return string
     */
    public function getOutputFormat()
    {
        if (isset($this->strOutputFormat)) {
            return $this->strOutputFormat;
        }

        if (isset($this->objView) && $this->objView->get('format')) {
            return $this->objView->get('format');
        }

        $page = $this->getPage();

        if ($page && $page->outputFormat) {
            return $page->outputFormat;
        }

        return 'text';
    }

    /**
     * Retrieve the translation string for the given lang key.
     *
     * In order to achieve the correct caption text, the function tries several translation strings sequentially.
     * The first language key that is set will win, even if it is to be considered empty.
     *
     * This message is looked up in the following order:
     * 1. $GLOBALS['TL_LANG']['MSC'][<mm tablename>][<render settings id>][$langKey]
     * 2. $GLOBALS['TL_LANG']['MSC'][<mm tablename>][$langKey]
     * 3. $GLOBALS['TL_LANG']['MSC'][$langKey]
     *
     * @param string $langKey The language key to retrieve.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function getCaptionText($langKey)
    {
        $tableName = $this->getMetaModel()->getTableName();
        if (isset($this->objView)
            && isset($GLOBALS['TL_LANG']['MSC'][$tableName][$this->objView->get('id')][$langKey])
        ) {
            return $GLOBALS['TL_LANG']['MSC'][$tableName][$this->objView->get('id')][$langKey];
        } elseif (isset($GLOBALS['TL_LANG']['MSC'][$tableName][$langKey])) {
            return $GLOBALS['TL_LANG']['MSC'][$tableName][$langKey];
        }

        return $GLOBALS['TL_LANG']['MSC'][$langKey];
    }

    /**
     * Retrieve the caption text for "No items found" message.
     *
     * In order to achieve the correct caption text, the function tries several translation strings sequentially.
     * The first language key that is set will win, even if it is to be considered empty.
     *
     * This message is looked up in the following order:
     * 1. $GLOBALS['TL_LANG']['MSC'][<mm tablename>][<render settings id>]['noItemsMsg']
     * 2. $GLOBALS['TL_LANG']['MSC'][<mm tablename>]['noItemsMsg']
     * 3. $GLOBALS['TL_LANG']['MSC']['noItemsMsg']
     *
     * @return string
     */
    protected function getNoItemsCaption()
    {
        return $this->getCaptionText('noItemsMsg');
    }

    /**
     * Set the title and description in the page object.
     *
     * @return void
     */
    private function setTitleAndDescription()
    {
        $page = $this->getPage();
        if ($page && $this->objItems->getCount()) {
            // Add title if needed.
            if (!empty($this->strTitleAttribute)) {
                while ($this->objItems->next()) {
                    /** @var IItem $objCurrentItem */
                    $objCurrentItem = $this->objItems->current();
                    $arrTitle       = $objCurrentItem->parseAttribute(
                        $this->strTitleAttribute,
                        'text',
                        $this->getView()
                    );

                    if (!empty($arrTitle['text'])) {
                        $page->pageTitle = strip_tags($arrTitle['text']);
                        break;
                    }
                }

                $this->objItems->reset();
            }

            // Add description if needed.
            if (!empty($this->strDescriptionAttribute)) {
                while ($this->objItems->next()) {
                    $objCurrentItem = $this->objItems->current();
                    $arrDescription = $objCurrentItem->parseAttribute(
                        $this->strDescriptionAttribute,
                        'text',
                        $this->getView()
                    );

                    if (!empty($arrDescription['text'])) {
                        $page->description = StringUtil::substr($arrDescription['text'], 160);
                        break;
                    }
                }

                $this->objItems->reset();
            }
        }
    }

    /**
     * Render the list view.
     *
     * @param bool   $blnNoNativeParsing Flag determining if the parsing shall be done internal or if the template will
     *                                   handle the parsing on it's own.
     *
     * @param object $objCaller          The object calling us, might be a Module or ContentElement or anything else.
     *
     * @return string
     */
    public function render($blnNoNativeParsing, $objCaller)
    {
        $event = new RenderItemListEvent($this, $this->objTemplate, $objCaller);
        $this->getEventDispatcher()->dispatch(MetaModelsEvents::RENDER_ITEM_LIST, $event);

        $this->objTemplate->noItemsMsg = $this->getNoItemsCaption();
        $this->objTemplate->details    = $this->getCaptionText('details');

        $this->prepare();
        $strOutputFormat = $this->getOutputFormat();

        if ($this->objItems->getCount() && !$blnNoNativeParsing) {
            $this->objTemplate->data = $this->objItems->parseAll($strOutputFormat, $this->objView);
        } else {
            $this->objTemplate->data = array();
        }

        $this->setTitleAndDescription();

        $this->objTemplate->caller       = $objCaller;
        $this->objTemplate->items        = $this->objItems;
        $this->objTemplate->filterParams = $this->arrParam;

        return $this->objTemplate->parse($strOutputFormat);
    }
}
