<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2020 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2020 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener;

use Contao\CoreBundle\ServiceAnnotation\Hook;
use Contao\StringUtil;
use Contao\Input;
use Contao\System;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use MetaModels\Filter\Rules\StaticIdList;
use MetaModels\Filter\Setting\IFilterSettingFactory;
use MetaModels\IFactory;
use MetaModels\IMetaModel;
use MetaModels\ItemList;
use MetaModels\Render\Setting\IRenderSettingFactory;
use Terminal42\ServiceAnnotationBundle\ServiceAnnotationInterface;

/**
 * This class handles the replacement of all MetaModels insert tags.
 *
 * @codingStandardsIgnoreStart
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
 *
 * @codingStandardsIgnoreEnd
 */
class InsertTagsListener implements ServiceAnnotationInterface
{
    /**
     * Database connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * The MetaModels factory.
     *
     * @var IFactory
     */
    private $factory;

    /**
     * The render setting factory.
     *
     * @var IRenderSettingFactory
     */
    private $renderSettingFactory;

    /**
     * The filter setting factory.
     *
     * @var IFilterSettingFactory
     */
    private $filterSettingFactory;

    /**
     * InsertTagsListener constructor.
     *
     * @param Connection            $connection           Database connection.
     * @param IFactory              $factory              The MetaModels factory.
     * @param IRenderSettingFactory $renderSettingFactory The render setting factory.
     * @param IFilterSettingFactory $filterSettingFactory The filter setting factory.
     */
    public function __construct(
        Connection $connection,
        IFactory $factory,
        IRenderSettingFactory $renderSettingFactory,
        IFilterSettingFactory $filterSettingFactory
    ) {
        $this->connection           = $connection;
        $this->factory              = $factory;
        $this->renderSettingFactory = $renderSettingFactory;
        $this->filterSettingFactory = $filterSettingFactory;
    }

    /**
     * Evaluate an insert tag.
     *
     * @param string $insertTag The tag to evaluate.
     *
     * @return bool|string
     *
     * @Hook("replaceInsertTags")
     */
    public function __invoke($insertTag)
    {
        $elements = explode('::', $insertTag);

        // Check if we have the mm tags.
        if ('mm' !== $elements[0]) {
            return false;
        }

        try {
            // Call the fitting function.
            switch ($elements[1]) {
                // Count for mod or ce elements.
                case 'total':
                    return $this->getCount($elements[2], $elements[3]);

                // Get value from an attribute.
                case 'attribute':
                    return $this->getAttribute($elements[2], $elements[3], $elements[4], $elements[5]);

                // Get item.
                case 'item':
                    return $this->getItem($elements[2], $elements[3], $elements[4], $elements[5]);

                case 'jumpTo':
                    return $this->jumpTo($elements[2], $elements[3], $elements[4], $elements[5]);

                default:
            }
        } catch (\Exception $exc) {
            System::log('Error by replace tags: ' . $exc->getMessage(), __CLASS__ . ' | ' . __FUNCTION__, TL_ERROR);
        }

        return false;
    }

    /**
     * Get the jumpTo for a chosen value.
     *
     * @param string|int $mixMetaModel ID or name of MetaModels.
     *
     * @param int        $mixDataId    ID of the data row.
     *
     * @param int        $viewId       ID of render setting.
     *
     * @param string     $strParam     Name of parameter - Default:url|label|page|params.[attrname].
     *
     * @return boolean|string Return false when nothing was found for the requested value.
     */
    protected function jumpTo($mixMetaModel, $mixDataId, $viewId, $strParam = 'url')
    {
        // Set the param to url if empty.
        if (empty($strParam)) {
            $strParam = 'url';
        }

        // Get the MetaModel. Return if we can not find one.
        $metaModel = $this->loadMetaModel($mixMetaModel);
        if (null === $metaModel) {
            return false;
        }

        // Get the render setting.
        if (null === $renderSettings = $this->renderSettingFactory->createCollection($metaModel, $viewId)) {
            return false;
        }

        // Get the data row.
        $item = $metaModel->findById($mixDataId);
        if (null === $item) {
            return false;
        }

        // Render the item and check if we have a jump to.
        $arrRenderedItem = $item->parseValue('text', $renderSettings);
        if (!isset($arrRenderedItem['jumpTo'])) {
            return false;
        }

        // Check if someone want the sub params.
        if (stripos($strParam, 'params.') !== false) {
            $mixAttName = StringUtil::trimsplit('.', $strParam);
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
     * @param string|int $metaModelIdOrName ID or name of MetaModels.
     *
     * @param string|int $mixDataId         ID of the data row.
     *
     * @param int        $viewId            ID of render setting.
     *
     * @param string     $strOutput         Name of output. Default:null (fallback to htmlfynf)|text|html5|xhtml|...
     *
     * @return boolean|string Return false when nothing was found or return the value.
     */
    protected function getItem($metaModelIdOrName, $mixDataId, $viewId, $strOutput = null)
    {
        // Get the MetaModel. Return if we can not find one.
        $metaModel = $this->loadMetaModel($metaModelIdOrName);
        if (null === $metaModel) {
            return false;
        }

        // Set output to default if not set.
        if (empty($strOutput)) {
            $strOutput = 'html5';
        }

        $objMetaModelList = new ItemList();
        $objMetaModelList
            ->setObjMetaModel($metaModel->get('id'), $viewId)
            ->overrideOutputFormat($strOutput);

        // Handle a set of ids.
        $arrIds = StringUtil::trimsplit(',', $mixDataId);

        // Check each id if published.
        foreach ($arrIds as $intKey => $intId) {
            if (!$this->isPublishedItem($metaModel, $intId)) {
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
     *
     * @throws \RuntimeException If $intDataId does not provide an existingMetaModel ID.
     */
    protected function getAttribute($metaModelIdOrName, $intDataId, $strAttributeName, $strOutput = 'raw')
    {
        // Get the MM.
        $objMM = $this->loadMetaModel($metaModelIdOrName);
        if (null === $objMM) {
            return false;
        }

        // Set output to default if not set.
        if (empty($strOutput)) {
            $strOutput = 'raw';
        }

        // Get item.
        $objMetaModelItem = $objMM->findById($intDataId);
        if (null === $objMetaModelItem) {
            throw new \RuntimeException('MetaModel item not found: ' . $intDataId);
        }

        // Parse attribute.
        $arrAttr = $objMetaModelItem->parseAttribute($strAttributeName);

        return $arrAttr[$strOutput];
    }

    /**
     * Get count from a module or content element of a mm.
     *
     * @param string $strType Type of element like mod or ce.
     *
     * @param int    $intID   ID of content element or module.
     *
     * @return int Return the count value.
     */
    protected function getCount($strType, $intID): int
    {
        switch ($strType) {
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
                return 0;
        }

        // Check if we have data.
        if (null !== $objMetaModelResult) {
            return $this->getCountFor($objMetaModelResult->metamodel, $objMetaModelResult->metamodel_filtering);
        }

        return 0;
    }

    /**
     * Try to load the MetaModel by id or name.
     *
     * @param mixed $nameOrId Name or id of the MetaModel.
     *
     * @return IMetaModel|null
     */
    protected function loadMetaModel($nameOrId): ?IMetaModel
    {
        if (is_numeric($nameOrId)) {
            // ID.
            $tableName = $this->factory->translateIdToMetaModelName($nameOrId);
        } elseif (is_string($nameOrId)) {
            // Name.
            $tableName = $nameOrId;
        }

        if (isset($tableName)) {
            return $this->factory->getMetaModel($tableName);
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
     * @return null|\stdClass Returns null when nothing was found or a \Database\Result with the chosen information.
     *
     * @throws \Doctrine\DBAL\DBALException When an database error occur.
     */
    protected function getMetaModelDataFrom($strTable, $intID)
    {
        // Check if we know the table.
        if (!$this->connection->getSchemaManager()->tablesExist([$strTable])) {
            return null;
        }

        // Get all information form table or return null if we have no data.
        $statement = $this->connection
            ->prepare('SELECT metamodel, metamodel_filtering FROM ' . $strTable . ' WHERE id=? LIMIT 0,1');

        $statement->bindValue(1, $intID);
        $statement->execute();

        // Check if we have some data.
        if ($statement->rowCount() < 1) {
            return null;
        }

        return $statement->fetch(FetchMode::STANDARD_OBJECT);
    }

    /**
     * Get count form one MM for chosen filter.
     *
     * @param int $intMetaModelId ID of the metamodels.
     *
     * @param int $intFilterId    ID of the filter.
     *
     * @return int The count result.
     */
    protected function getCountFor($intMetaModelId, $intFilterId): int
    {
        $metaModel = $this->loadMetaModel($intMetaModelId);
        if (null === $metaModel) {
            return 0;
        }

        $objFilter = $metaModel->getEmptyFilter();
        if ($intFilterId) {
            $collection = $this->filterSettingFactory->createCollection($intFilterId);
            $values     = [];

            foreach ($collection->getParameters() as $key) {
                $values[$key] = Input::get($key);
            }

            $collection->addRules($objFilter, $values);
        }

        return $metaModel->getCount($objFilter);
    }

    /**
     * Check if the item is published.
     *
     * @param IMetaModel $metaModel Current metamodels.
     * @param int        $intItemId Id of the item.
     *
     * @return boolean True => Published | False => Not published
     *
     * @throws \Doctrine\DBAL\DBALException When a database error occur.
     */
    protected function isPublishedItem($metaModel, $intItemId): bool
    {
        // Check publish state of an item.
        $statement = $this->connection
            ->prepare('SELECT colname FROM tl_metamodel_attribute WHERE pid=? AND check_publish=1 LIMIT 0,1');

        $statement->bindValue(1, $metaModel->get('id'));
        $statement->execute();

        if ($statement->rowCount() > 0) {
            $checkPublish = $statement->fetch(FetchMode::STANDARD_OBJECT);
            $item         = $metaModel->findById($intItemId);

            if (null === $item || !$item->get($checkPublish->colname)) {
                return false;
            }
        }

        return true;
    }
}
