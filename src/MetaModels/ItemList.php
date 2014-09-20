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

use MetaModels\Factory as ModelFactory;
use MetaModels\Filter\Setting\Factory as FilterFactory;
use MetaModels\Render\Template;

/**
 * Implementation of a general purpose MetaModel listing.
 *
 * @package    MetaModels
 * @subpackage Frontend
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 */
class ItemList
{
    /**
     * Use limit.
     *
     * @var bool
     */
    protected $blnUseLimit = false;

    /**
     * Offset.
     *
     * @var int
     */
    protected $intOffset = 0;

    /**
     * Limit.
     *
     * @var int
     */
    protected $intLimit = 0;

    /**
     * Pagination page break.
     *
     * @var int
     */
    protected $intPerPage = 0;

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
        $this->blnUseLimit = $blnUse;
        $this->intOffset   = $intOffset;
        $this->intLimit    = $intLimit;

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
        $this->intPerPage = $intLimit;

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
     * Set output format.
     *
     * @param string $strFormat The name of the template output format to use.
     *
     * @return ItemList
     * @deprecated Use overrideOutputFormat instead
     */
    public function setTemplateFormat($strFormat)
    {
        $this->overrideOutputFormat($strFormat);
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
     * Set filter and parameter.
     *
     * @param int      $intFilter  The filter settings to use (if 0, the default will be used).
     *
     * @param string[] $arrPresets The parameter preset values to use.
     *
     * @param string[] $arrValues  The dynamic parameter values that may be used.
     *
     * @return ItemList
     *
     * @deprecated Use setFilterSettings() and setFilterParameters().
     */
    public function setFilterParam($intFilter, $arrPresets, $arrValues)
    {
        $this->setFilterSettings($intFilter);

        $this->setFilterParameters($arrPresets, $arrValues);

        return $this;
    }

    /**
     * Add the attribute names for meta title and description.
     *
     * @param string $strTitleAttribute       Name of attribute for title.
     *
     * @param string $strDescriptionAttribute Name of attribue for description.
     *
     * @return void
     */
    public function setMetaTags($strTitleAttribute, $strDescriptionAttribute)
    {
        $this->strDescriptionAttribute = $strDescriptionAttribute;
        $this->strTitleAttribute       = $strTitleAttribute;
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
        $this->objMetaModel = ModelFactory::byId($this->intMetaModel);
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
        $this->objView = $this->objMetaModel->getView($this->intView);

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

        $this->objFilterSettings = FilterFactory::byId($this->intFilter);

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
     * The calculated pagination, if any.
     *
     * @var string
     */
    protected $strPagination = '';

    /**
     * Calculate the pagination based upon the offset, limit and total amount of items.
     *
     * @param int $intTotal The total amount of items.
     *
     * @return void
     */
    protected function calculatePagination($intTotal)
    {
        $intOffset = null;
        $intLimit  = null;

        // If defined, we override the pagination here.
        if ($this->blnUseLimit && ($this->intLimit || $this->intOffset)) {
            if ($this->intLimit) {
                $intLimit = $this->intLimit;
            }
            if ($this->intOffset) {
                $intOffset = $this->intOffset;
            }
        }

        if ($this->intPerPage > 0) {
            // If a total limit has been defined, we need to honor that.
            if (!is_null($intLimit) && ($intTotal > $intLimit)) {
                $intTotal -= $intLimit;
            }
            $intTotal -= $intOffset;

            // Get the current page.
            $intPage = \Input::getInstance()->get('page') ? \Input::getInstance()->get('page') : 1;

            if ($intPage > ($intTotal / $this->intPerPage)) {
                $intPage = (int)ceil($intTotal / $this->intPerPage);
            }

            // Set limit and offset.
            $pageOffset = ((max($intPage, 1) - 1) * $this->intPerPage);
            $intOffset += $pageOffset;
            if (is_null($intLimit)) {
                $intLimit = $this->intPerPage;
            } else {
                $intLimit = min(($intLimit - $intOffset), $this->intPerPage);
            }

            // Add pagination menu.
            $objPagination = new \Pagination($intTotal, $this->intPerPage);

            $this->strPagination = $objPagination->generate("\n  ");
        } else {
            if (is_null($intLimit)) {
                $intLimit = 0;
            }
            if (is_null($intOffset)) {
                $intOffset = 0;
            }
        }
        $this->intLimit  = $intLimit;
        $this->intOffset = $intOffset;
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

        foreach ((array)$this->getView()->get('jumpTo') as $arrJumpTo) {
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
            $objFilterSettings = FilterFactory::byId($intFilterSettings);
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

        $this->calculatePagination($intTotal);

        $this->objItems = $this->objMetaModel->findByFilter(
            $this->objFilter,
            $this->strSortBy,
            $this->intOffset,
            $this->intLimit,
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
        return $this->strPagination;
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
     * Retrieve the output format used by this list.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function getOutputFormat()
    {
        if (isset($this->strOutputFormat)) {
            return $this->strOutputFormat;
        }

        if (isset($this->objView) && $this->objView->get('format')) {
            return $this->objView->get('format');
        }

        if (TL_MODE == 'FE' && is_object($GLOBALS['objPage']) && $GLOBALS['objPage']->outputFormat) {
            return $GLOBALS['objPage']->outputFormat;
        }

        return 'text';
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
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function getNoItemsCaption()
    {
        $tableName = $this->getMetaModel()->getTableName();
        if (isset($this->objView)
            && isset($GLOBALS['TL_LANG']['MSC'][$tableName][$this->objView->get('id')]['noItemsMsg'])
        ) {
            return $GLOBALS['TL_LANG']['MSC'][$tableName][$this->objView->get('id')]['noItemsMsg'];
        } elseif (isset($GLOBALS['TL_LANG']['MSC'][$tableName]['noItemsMsg'])) {
            return $GLOBALS['TL_LANG']['MSC'][$tableName]['noItemsMsg'];
        }

        return $GLOBALS['TL_LANG']['MSC']['noItemsMsg'];
    }

    /**
     * Retrieve the caption text for the "Show details" link.
     *
     * In order to achieve the correct caption text, the function tries several translation strings sequentially.
     * The first language key that is set will win, even if it is to be considered empty.
     *
     * This message is looked up in the following order:
     * 1. $GLOBALS['TL_LANG']['MSC'][<mm tablename>][<render settings id>]['details']
     * 2. $GLOBALS['TL_LANG']['MSC'][<mm tablename>]['details']
     * 3. $GLOBALS['TL_LANG']['MSC']['details']
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function getDetailsCaption()
    {
        $tableName = $this->getMetaModel()->getTableName();
        if (isset($this->objView)
            && isset($GLOBALS['TL_LANG']['MSC'][$tableName][$this->objView->get('id')]['details'])
        ) {
            return $GLOBALS['TL_LANG']['MSC'][$tableName][$this->objView->get('id')]['details'];
        } elseif (isset($GLOBALS['TL_LANG']['MSC'][$tableName]['details'])) {
            return $GLOBALS['TL_LANG']['MSC'][$tableName]['details'];
        }

        return $GLOBALS['TL_LANG']['MSC']['details'];
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
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function render($blnNoNativeParsing, $objCaller)
    {
        $this->objTemplate->noItemsMsg = $this->getNoItemsCaption();
        $this->objTemplate->details    = $this->getDetailsCaption();

        $this->prepare();
        $strOutputFormat = $this->getOutputFormat();

        if ($this->objItems->getCount() && !$blnNoNativeParsing) {
            $this->objTemplate->data = $this->objItems->parseAll($strOutputFormat, $this->objView);
        } else {
            $this->objTemplate->data = array();
        }

        // Add title if needed.
        if ($GLOBALS['objPage'] && $this->objItems->getCount() && !empty($this->strTitleAttribute)) {
            while ($this->objItems->next()) {
                $objCurrentItem = $this->objItems->current();
                $arrTitle       = $objCurrentItem->parseAttribute($this->strTitleAttribute, 'text');

                if (isset($arrTitle['text']) && !empty($arrTitle['text'])) {
                    $GLOBALS['objPage']->pageTitle = strip_tags($arrTitle['text']);
                    break;
                }
            }

            $this->objItems->reset();
        }

        // Add description if needed.
        if ($GLOBALS['objPage'] && $this->objItems->getCount() && !empty($this->strDescriptionAttribute)) {
            while ($this->objItems->next()) {
                $objCurrentItem = $this->objItems->current();
                $arrDescription = $objCurrentItem->parseAttribute($this->strDescriptionAttribute, 'text');

                if (isset($arrDescription['text']) && !empty($arrDescription['text'])) {
                    $GLOBALS['objPage']->description = \String::getInstance()->substr($arrDescription['text'], 120);
                    break;
                }
            }

            $this->objItems->reset();
        }

        $this->objTemplate->caller       = $objCaller;
        $this->objTemplate->items        = $this->objItems;
        $this->objTemplate->filterParams = $this->arrParam;

        return $this->objTemplate->parse($strOutputFormat);
    }
}
