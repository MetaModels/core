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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\BackendIntegration;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\GenerateFrontendUrlEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\Controller\GetPageDetailsEvent;
use MetaModels\Attribute\Factory as AttributeFactory;
use MetaModels\Factory as MetaModelFactory;
use MetaModels\Filter\IFilter;
use MetaModels\Filter\Setting\Factory as FilterFactory;
use MetaModels\IItem;
use MetaModels\IMetaModel;
use MetaModels\Item;
use MetaModels\Render\Setting\ICollection;

class SearchablePages
{
    /**
     * The configuration
     *
     * @var array|null
     */
    protected $arrConfig = null;

    /**
     * A list with all pages found by Contao.
     *
     * @var array
     */
    protected $arrFoundPages = array();

    /**
     * List with some env vars.
     *
     * @var array
     */
    protected $arrEnv = array();

    /**
     * Cache for intern work.
     *
     * @var array
     */
    protected static $arrCache = array();

    /**
     * Construct.
     */
    public function __construct()
    {
        // Set the config.
        if (isset($GLOBALS['FooBaa']['SearchablePages']) && count($GLOBALS['FooBaa']['SearchablePages']) != 0) {
            $this->arrConfig = $GLOBALS['FooBaa']['SearchablePages'];
        }
    }

    /**
     * Get the event Dispatcher.
     *
     * @return \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    protected function getEventDispatcher()
    {
        if (!isset($arrEnv['EventDispatcher'])) {
            $arrEnv['EventDispatcher'] = $GLOBALS['container']['event-dispatcher'];
        }
        return $arrEnv['EventDispatcher'];
    }

    /**
     * Get the attribute Factory.
     *
     * @return AttributeFactory
     */
    protected function getAttributeFactory()
    {
        if (!isset($arrEnv['AttributeFactory'])) {
            $arrEnv['AttributeFactory'] = new AttributeFactory($this->getEventDispatcher());
        }
        return $arrEnv['AttributeFactory'];
    }

    /**
     * Get the MetaModels Factory.
     *
     * @return MetaModelFactory
     */
    protected function getMetaModelsFactory()
    {
        if (!isset($arrEnv['MetaModelsFactory'])) {
            $arrEnv['MetaModelsFactory'] = new MetaModelFactory($this->getEventDispatcher(),
                $this->getAttributeFactory());
        }
        return $arrEnv['MetaModelsFactory'];
    }

    /**
     * Get a metamodels by name or id.
     *
     * @param string|int $mixIdentifier The Name or ID of a metamodels.
     *
     * @param boolean    $blnYowl       If yowl is true check if we have really a mm .
     *
     * @return IMetaModel
     */
    protected function getMetaModels($mixIdentifier, $blnYowl)
    {
        // Get the factory.
        $objFactory = $this->getMetaModelsFactory();
        // Id to name.
        if (is_numeric($mixIdentifier)) {
            $mixIdentifier = $objFactory->translateIdToMetaModelName($mixIdentifier);
        }
        // Check the cache.
        if (!isset(self::$arrCache['mm'][$mixIdentifier])) {
            // Create mm, if yowl is true check if we have really a mm .
            $objMetaModels = $objFactory->getMetaModel($mixIdentifier);
            // If yowl is on and we have no mm throw a new exception.
            if ($blnYowl == true && $objMetaModels == null) {
                throw new \RuntimeException('Could not find the MetaModels with the name ' . $mixIdentifier);
            }
            // Add to the cache.
            self::$arrCache['mm'][$mixIdentifier] = $objMetaModels;
        }
        // Return the metamodels.
        return self::$arrCache['mm'][$mixIdentifier];
    }

    /**
     * Get a filter based on the id.
     *
     * @param int $intId Id of the filter
     *
     * @return \MetaModels\Filter\Setting\ICollection The filter
     */
    protected function getFilterSettings($intId)
    {
        if (!isset(self::$arrCache['filter'][$intId])) {
            self::$arrCache['filter'][$intId] = FilterFactory::byId($intId);
        }
        return self::$arrCache['filter'][$intId];
    }

    /**
     * Get the view for a metamodels.
     *
     * @param int $intMetaModels ID of the MetaModels.
     *
     * @param int $intView       ID of the view.
     *
     * @return \MetaModels\Render\Setting\ICollection
     */
    protected function getView($intMetaModels, $intView)
    {
        if (!isset(self::$arrCache['view'][$intView])) {
            $objMetaModels                    = $this->getMetaModels($intMetaModels, true);
            self::$arrCache['view'][$intView] = $objMetaModels->getView($intView);
        }
        return self::$arrCache['view'][$intView];
    }

    /**
     * Set parameters.
     *
     * @param int      $intFilterId The id of the filter.
     *
     * @param string[] $arrPresets  The parameter preset values to use.
     *
     * @param string[] $arrValues   The dynamic parameter values that may be used.
     *
     * @return array
     */
    public function setFilterParameters($intFilterId, $arrPresets, $arrValues)
    {
        $objFilterSettings = $this->getFilterSettings($intFilterId);
        $arrPresetNames    = $objFilterSettings->getParameters();
        $arrFEFilterParams = array_keys($objFilterSettings->getParameterFilterNames());
        $arrProcessed      = array();
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
        return $arrProcessed;
    }

    /**
     * Start point for the hook.
     *
     * @param array $arrPages    List with all pages.
     *
     * @param int   $intRootPage ID of the root page.
     *
     * @return array
     */
    public function addPages($arrPages, $intRootPage)
    {
        // Save the pages in the locale array.
        $this->arrFoundPages = $arrPages;
        unset($arrPages);

        $settings = \Database::getInstance()
            ->prepare('SELECT * FROM tl_metamodel_searchable_pages')
            ->execute();

        // Run each setting.
        while ($settings->next()) {
            $this->getMetaModelsPages(
                $settings->pid,
                $settings->setFilter,
                $settings->filterparams,
                $settings->setRendersetting
            );
        }

        asort($this->arrFoundPages);

        // Return the new list.
        return $this->arrFoundPages;
    }

    /**
     * Get a MetaModels, a filter and a rendersetting. Get all items based on the filter
     * and build the jumpTo urls.
     *
     * @param int   $intMetaModels    ID of the MetaModels.
     *
     * @param int   $intFilter        ID of the filter setting.
     *
     * @param array $arrPresetParams  The list with the parameter settings for the filters.
     *
     * @param int   $intRenderSetting ID of the rendersetting.
     */
    function getMetaModelsPages($intMetaModels, $intFilter, $arrPresetParams, $intRenderSetting)
    {
        // Get the MetaModels.
        $objMetaModels        = $this->getMetaModels($intMetaModels, true);
        $objFilter            = $objMetaModels->getEmptyFilter();
        $arrAvailableLanguage = $objMetaModels->getAvailableLanguages();
        $strCurrentLanguage   = $GLOBALS['TL_LANGUAGE'];
        $arrNewEntries        = array();

        // Get the view.
        $objView    = $this->getView($intMetaModels, $intRenderSetting);
        $arrJumpTos = $objView->get('jumpTo');

        // Set the filter if we have a filter id.
        if (!empty($intFilter)) {

            $arrProcessed     = $this->setFilterParameters($intFilter, $arrPresetParams, array());
            $objFilterSetting = $this->getFilterSettings($intFilter);
            $objFilterSetting->addRules($objFilter, $arrProcessed);
        }

        foreach ($arrAvailableLanguage as $strLanguage) {
            // Set the language.
            $GLOBALS['TL_LANGUAGE'] = $strLanguage;

            // Get the object.
            $objItems = $objMetaModels->findByFilter
            (
                $objFilter
            );

            /** @var Item $objItem */
            foreach ($objItems as $objItem) {
                $arrJumpTo       = $objItem->buildJumpToLink($objView);
                $srtUrl          = \Environment::get('base') . $arrJumpTo['url'];
                $arrNewEntries[] = $srtUrl;
            }
        }

        // Remove the detail pages.
        foreach ($arrJumpTos as $arrJumpTo) {
            // Get the page from the url.
            $event = new GetPageDetailsEvent($arrJumpTo['value']);
            $this->getEventDispatcher()->dispatch(ContaoEvents::CONTROLLER_GET_PAGE_DETAILS, $event);
            $arrPage = $event->getPageDetails();

            // Build the url.
            $event = new GenerateFrontendUrlEvent($arrPage, null, $arrPage['language']);
            $this->getEventDispatcher()->dispatch(ContaoEvents::CONTROLLER_GENERATE_FRONTEND_URL, $event);

            // Make a full url from it.
            $strFullUrl = \Environment::get('base') . $event->getUrl();
            if (($strKey = array_search($strFullUrl, $this->arrFoundPages)) !== false) {
                unset($this->arrFoundPages[$strKey]);
            }
        }

        // Reset language.
        $GLOBALS['TL_LANGUAGE'] = $strCurrentLanguage;

        // Merge all results.
        $this->arrFoundPages = array_merge($this->arrFoundPages, $arrNewEntries);
    }
}