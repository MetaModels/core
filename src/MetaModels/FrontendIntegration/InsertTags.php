<?php

/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
 * @package     MetaModels
 * @subpackage  Core
 * @author      Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author      Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright   The MetaModels team.
 * @license     LGPL-3+.
 * @filesource
 */

namespace MetaModels\FrontendIntegration;

use Database\Result;
use MetaModels\Filter\Rules\StaticIdList;
use MetaModels\IMetaModel;
use MetaModels\IMetaModelsServiceContainer;
use MetaModels\ItemList;

/**
 * MetaModelInsertTags.
 * 
 * Available insert tags:
 * 
 * -- Total Count --
 * mm::total::mod::[id]
 * mm::total::ce::[id]
 * 
 * -- Item --
 * mm::item::[MM Name|ID]::[Item ID|ID,ID,ID]::[ID render setting](::[Output raw|text|html|..])
 * mm::detail::[MM Name|ID]::[Item ID]::[ID render setting](::[Output raw|text|html|..])
 * 
 * -- Attribute --
 * mm::attribute::[MM Name|ID]::[Item ID]::[Attribute Name|ID](::[Output raw|text|html|..])
 * 
 * -- JumpTo --
 * mm::jumpTo::[MM Name|ID]::[Item ID]::[ID render setting](::[Parameter (Default:url)|label|page|params.attname])
 */
class InsertTags extends \Controller
{
    /**
     * Retrieve the service container.
     *
     * @return IMetaModelsServiceContainer
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function getServiceContainer()
    {
        return $GLOBALS['container']['metamodels-service-container'];
    }

    /**
     * Evaluate an insert tag.
     *
     * @param string $strTag The tag to evaluate.
     *
     * @return bool|string
     */
    public function replaceTags($strTag)
    {
        $arrElements = explode('::', $strTag);

        // Check if we have the mm tags.
        if ($arrElements[0] != 'mm') {
            return false;
        }

        try {
            // Call the fitting function.
            switch ($arrElements[1])
            {
                // Count for mod or ce elements.
                case 'total':
                    return $this->getCount($arrElements[2], $arrElements[3]);

                // Get value from an attribute.
                case 'attribute':
                    return $this->getAttribute($arrElements[2], $arrElements[3], $arrElements[4], $arrElements[5]);

                // Get item.
                case 'item':
                    return $this->getItem($arrElements[2], $arrElements[3], $arrElements[4], $arrElements[5]);

                case 'jumpTo':
                    return $this->jumpTo($arrElements[2], $arrElements[3], $arrElements[4], $arrElements[5]);

                default:
            }
        } catch (\Exception $exc) {
            $this->log('Error by replace tags: ' . $exc->getMessage(), __CLASS__ . ' | ' . __FUNCTION__, TL_ERROR);
        }

        return false;
    }

    /**
     * Get the jumpTo for a chosen value.
     *
     * @param string|int $mixMetaModel       ID or name of MetaModels.
     *
     * @param int        $mixDataId          ID of the data row.
     *
     * @param int        $intIdRenderSetting ID of render setting.
     *
     * @param string     $strParam           Name of parameter - Default:url|label|page|params.[attrname].
     *
     * @return boolean|string Return false when nothing was found for the requested value.
     */
    protected function jumpTo($mixMetaModel, $mixDataId, $intIdRenderSetting, $strParam = 'url')
    {
        // Set the param to url if empty.
        if (empty($strParam)) {
            $strParam = 'url';
        }

        // Get the MetaModel. Return if we can not find one.
        $objMetaModel = $this->loadMetaModel($mixMetaModel);
        if ($objMetaModel == null) {
            return false;
        }

        // Get the render setting.
        $objRenderSettings = $this
            ->getServiceContainer()
            ->getRenderSettingFactory()
            ->createCollection($objMetaModel, $intIdRenderSetting);
        if ($objRenderSettings == null) {
            return false;
        }

        // Get the data row.
        $objItem = $objMetaModel->findById($mixDataId);
        if ($objItem == null) {
            return false;
        }

        // Render the item and check if we have a jump to.
        $arrRenderedItem = $objItem->parseValue('text', $objRenderSettings);
        if (!isset($arrRenderedItem['jumpTo'])) {
            return false;
        }

        // Check if someone want the sub params.
        if (stripos($strParam, 'params.') !== false) {
            $mixAttName = trimsplit('.', $strParam);
            $mixAttName = array_pop($mixAttName);

            if (isset($arrRenderedItem['jumpTo']['params'][$mixAttName])) {
                return $arrRenderedItem['jumpTo']['params'][$mixAttName];
            }
        } elseif (isset($arrRenderedItem['jumpTo'][$strParam])) {
            // Else just return the ask param.
            return $arrRenderedItem['jumpTo'][$strParam];
        }

        // Nothing hit the output. Return false.
        return false;
    }

    /**
     * Get an item.
     *
     * @param string|int $metaModelIdOrName  ID or name of MetaModels.
     *
     * @param string|int $mixDataId          ID of the data row.
     *
     * @param int        $intIdRenderSetting ID of render setting.
     *
     * @param string     $strOutput          Name of output. Default:null (fallback to htmlfynf)|text|html5|xhtml|...
     *
     * @return boolean|string Return false when nothing was found or return the value.
     */
    protected function getItem($metaModelIdOrName, $mixDataId, $intIdRenderSetting, $strOutput = null)
    {
        // Get the MetaModel. Return if we can not find one.
        $objMetaModel = $this->loadMetaModel($metaModelIdOrName);
        if ($objMetaModel == null) {
            return false;
        }

        // Set output to default if not set.
        if (empty($strOutput)) {
            $strOutput = 'html5';
        }

        $objMetaModelList = new ItemList();
        $objMetaModelList
            ->setServiceContainer($this->getServiceContainer())
            ->setMetaModel($objMetaModel->get('id'), $intIdRenderSetting)
            ->overrideOutputFormat($strOutput);

        // Handle a set of ids.
        $arrIds = trimsplit(',', $mixDataId);

        // Check each id if published.
        foreach ($arrIds as $intKey => $intId) {
            if (!$this->isPublishedItem($objMetaModel, $intId)) {
                unset($arrIds[$intKey]);
            }
        }

        // Render an empty insert tag rather than displaying a list with an empty.
        // result information. do not return false here because the insert tag itself is correct.
        if (count($arrIds) < 1) {
            return '';
        }

        $objMetaModelList->addFilterRule(new StaticIdList($arrIds));
        return $objMetaModelList->render(false, $this);
    }

    /**
     * Get from MM X the item with the id Y and parse the attribute Z and return it.
     *
     * @param string|int $metaModelIdOrName ID or name of MetaModels.
     *
     * @param int        $intDataId         ID of the data row.
     *
     * @param string     $strAttributeName  Name of the attribute.
     *
     * @param string     $strOutput         Name of output. Default:raw|text|html5|xhtml|...
     *
     * @return boolean|string Return false when nothing was found or return the value.
     */
    protected function getAttribute($metaModelIdOrName, $intDataId, $strAttributeName, $strOutput = 'raw')
    {
        // Get the MM.
        $objMM = $this->loadMetaModel($metaModelIdOrName);
        if ($objMM == null) {
            return false;
        }

        // Set output to default if not set.
        if (empty($strOutput)) {
            $strOutput = 'raw';
        }

        // Get item.
        $objMetaModelItem = $objMM->findById($intDataId);

        // Parse attribute.
        $arrAttr = $objMetaModelItem->parseAttribute($strAttributeName);

        // ToDo: Maybe this should not always be a text element.
        return $arrAttr[$strOutput];
    }

    /**
     * Get count from a module or content element of a mm.
     *
     * @param string $strType Type of element like mod or ce.
     *
     * @param int    $intID   ID of content element or module.
     *
     * @return boolean|string Return false when nothing was found or the count value.
     */
    protected function getCount($strType, $intID)
    {
        switch ($strType)
        {
            // From module, can be a MetaModel list or filter.
            case 'mod':
                $objMetaModelResult = $this->getMetaModelDataFrom('tl_module', $intID);
                break;

            // From content element, can be a MetaModel list or filter.
            case 'ce':
                $objMetaModelResult = $this->getMetaModelDataFrom('tl_content', $intID);
                break;

            // Unknown element type.
            default:
                return false;
        }

        // Check if we have data.
        if ($objMetaModelResult != null) {
            return $this->getCountFor($objMetaModelResult->metamodel, $objMetaModelResult->metamodel_filtering);
        }

        return false;
    }

    /**
     * Try to load the MetaModel by id or name.
     *
     * @param mixed $nameOrId Name or id of the MetaModel.
     *
     * @return IMetaModel|null
     */
    protected function loadMetaModel($nameOrId)
    {
        if (is_numeric($nameOrId)) {
            // ID.
            $tableName = $this->getServiceContainer()->getFactory()->translateIdToMetaModelName($nameOrId);
        } elseif (is_string($nameOrId)) {
            // Name.
            $tableName = $nameOrId;
        }

        if (isset($tableName)) {
            return $this->getServiceContainer()->getFactory()->getMetaModel($tableName);
        }

        // Unknown.
        return null;
    }

    /**
     * Get the MetaModel id and the filter id.
     *
     * @param string $strTable Name of table.
     *
     * @param int    $intID    ID of the filter.
     *
     * @return null|Result Returns null when nothing was found or a \Database\Result with the chosen information.
     */
    protected function getMetaModelDataFrom($strTable, $intID)
    {
        $objDB = $this->getServiceContainer()->getDatabase();

        // Check if we know the table.
        if (!$objDB->tableExists($strTable)) {
            return null;
        }

        // Get all information form table or return null if we have no data.
        $objResult = $objDB
                ->prepare('SELECT metamodel, metamodel_filtering FROM ' . $strTable . ' WHERE id=?')
                ->limit(1)
                ->execute($intID);

        // Check if we have some data.
        if ($objResult->numRows < 1) {
            return null;
        }

        return $objResult;
    }

    /**
     * Get count form one MM for chosen filter.
     *
     * @param int $intMetaModelId ID of the metamodels.
     *
     * @param int $intFilterId    ID of the filter.
     *
     * @return boolean|int False for no data or integer for the count result.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function getCountFor($intMetaModelId, $intFilterId)
    {
        $objMetaModel = $this->loadMetaModel($intMetaModelId);
        if ($objMetaModel == null) {
            return false;
        }

        // FIXME: sanitize input parameters.
        $objFilter = $objMetaModel->prepareFilter($intFilterId, $_GET);

        return $objMetaModel->getCount($objFilter);
    }

    /**
     * Check if the item is published.
     *
     * @param IMetaModel $objMetaModel Current metamodels.
     * @param int        $intItemId    Id of the item.
     *
     * @return boolean True => Published | False => Not published
     */
    protected function isPublishedItem($objMetaModel, $intItemId)
    {
        // Check publish state of an item.
        $objAttrCheckPublish = $this
            ->getServiceContainer()
            ->getDatabase()
            ->prepare('SELECT colname FROM tl_metamodel_attribute WHERE pid=? AND check_publish=1')
            ->limit(1)
            ->execute($objMetaModel->get('id'));

        if ($objAttrCheckPublish->numRows > 0) {
            $objItem = $objMetaModel->findById($intItemId);
            if (!$objItem->get($objAttrCheckPublish->colname)) {
                return false;
            }
        }

        return true;
    }
}
