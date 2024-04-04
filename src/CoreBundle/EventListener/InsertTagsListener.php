<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2024 The MetaModels team.
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
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener;

use Contao\StringUtil;
use Contao\Input;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use MetaModels\Attribute\IAttribute;
use MetaModels\Filter\Rules\StaticIdList;
use MetaModels\Filter\Setting\IFilterSettingFactory;
use MetaModels\IFactory;
use MetaModels\IMetaModel;
use MetaModels\ItemList;
use MetaModels\Render\Setting\IRenderSettingFactory;
use Psr\Log\LoggerInterface;

/**
 * This class handles the replacement of all MetaModels insert tags.
 *
 * @codingStandardsIgnoreStart
 *
 * Available insert tags:
 *
 * -- Total Count --
 * mm::total::mod::[ID]
 * mm::total::ce::[ID]
 * mm::total::mm::[MM Table-Name|ID](::[ID filter])
 *
 * -- Item --
 * mm::item::[MM Table-Name|ID]::[Item ID|ID,ID,ID]::[ID render setting](::[Output (Default:text)|html5])
 *
 * -- Attribute --
 * mm::attribute::[MM Table-Name|ID]::[Item ID]::[ID render setting]::[Attribute Col-Name|ID](::[Output (Default:text)|html5|raw])
 *
 * -- JumpTo --
 * mm::jumpTo::[MM Table-Name|ID]::[Item ID]::[ID render setting](::[Parameter (Default:url)|label|page|params.attname])
 *
 * @codingStandardsIgnoreEnd
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
final class InsertTagsListener
{
    /**
     * Database connection.
     *
     * @var Connection
     */
    private Connection $connection;

    /**
     * The MetaModels factory.
     *
     * @var IFactory
     */
    private IFactory $factory;

    /**
     * The render setting factory.
     *
     * @var IRenderSettingFactory
     */
    private IRenderSettingFactory $renderSettingFactory;

    /**
     * The filter setting factory.
     *
     * @var IFilterSettingFactory
     */
    private IFilterSettingFactory $filterSettingFactory;

    /**
     * The logger interface.
     *
     * @var LoggerInterface|null
     */
    private ?LoggerInterface $logger;

    /**
     * InsertTagsListener constructor.
     *
     * @param Connection            $connection           Database connection.
     * @param IFactory              $factory              The MetaModels factory.
     * @param IRenderSettingFactory $renderSettingFactory The render setting factory.
     * @param IFilterSettingFactory $filterSettingFactory The filter setting factory.
     * @param LoggerInterface|null  $logger               The logger interface.
     */
    public function __construct(
        Connection $connection,
        IFactory $factory,
        IRenderSettingFactory $renderSettingFactory,
        IFilterSettingFactory $filterSettingFactory,
        LoggerInterface $logger = null
    ) {
        $this->connection           = $connection;
        $this->factory              = $factory;
        $this->renderSettingFactory = $renderSettingFactory;
        $this->filterSettingFactory = $filterSettingFactory;
        $this->logger               = $logger;
    }

    /**
     * Evaluate an insert tag.
     *
     * @param string $insertTag The tag to evaluate.
     *
     * @return bool|int|string
     */
    public function __invoke(string $insertTag): bool|int|string
    {
        $elements = \explode('::', $insertTag);

        // Check if we have the mm tags.
        if ('mm' !== $elements[0]) {
            return false;
        }

        try {
            // Call the fitting function.
            switch ($elements[1]) {
                // Count for mod or ce elements.
                case 'total':
                    return $this->checkMinExpectElements(4, $elements)
                        ? $this->getCount($elements[2], $elements[3], (isset($elements[4]) ? (int) $elements[4] : null))
                        : false;

                // Get value from an attribute.
                case 'attribute':
                    return $this->checkMinExpectElements(6, $elements)
                        ? $this->getAttribute(
                            $elements[2],
                            $elements[3],
                            $elements[4],
                            $elements[5],
                            ($elements[6] ?? null)
                        )
                        : false;

                // Get item.
                case 'item':
                    return $this->checkMinExpectElements(5, $elements)
                        ? $this->getItem($elements[2], $elements[3], $elements[4], ($elements[5] ?? null))
                        : false;

                // Get jump-to detail page.
                case 'jumpTo':
                    return $this->checkMinExpectElements(5, $elements)
                        ? $this->jumpTo($elements[2], $elements[3], $elements[4], ($elements[5] ?? null))
                        : false;

                default:
            }
        } catch (\Exception $exc) {
            $this->logger?->error(
                'Error by replace tags: ' . $exc->getMessage() . ' | ' . __CLASS__ . ' | ' . __FUNCTION__
            );
        }

        return false;
    }

    /**
     * Get the jumpTo for a chosen value.
     *
     * @param string      $mixMetaModel ID or column name of MetaModels.
     * @param string      $mixDataId    ID of the data row.
     * @param string      $viewId       ID of render setting.
     * @param string|null $strParam     Name of parameter - (Default:url)|label|page|params.[attrname].
     *
     * @return bool|string Return false when nothing was found for the requested value.
     */
    private function jumpTo(
        string $mixMetaModel,
        string $mixDataId,
        string $viewId,
        ?string $strParam
    ): bool|string {
        // Set the param to url if empty.
        if (null === $strParam || '' === $strParam) {
            $strParam = 'url';
        }

        // Get the MetaModel. Return if we can not find one.
        $metaModel = $this->loadMetaModel($mixMetaModel);

        // Get the render setting.
        $renderSetting = $this->renderSettingFactory->createCollection($metaModel, $viewId);

        // Get the data row.
        $item = $metaModel->findById($mixDataId);
        if (null === $item) {
            return false;
        }

        // Render the item and check if we have a jump to.
        $arrRenderedItem = $item->parseValue('text', $renderSetting);
        if (!isset($arrRenderedItem['jumpTo'])) {
            return false;
        }

        // Check if someone want the sub params.
        if (stripos($strParam, 'params.') !== false) {
            $mixAttName = StringUtil::trimsplit('.', $strParam);
            $mixAttName = \array_pop($mixAttName);

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
     * @param int|string  $metaModelIdOrName ID or column name of MetaModels.
     * @param string      $mixDataId         ID or list of IDs of the data row.
     * @param string      $viewId            ID of render setting.
     * @param string|null $outputFormat      Name of output format- (Default:text)|html5.
     *
     * @return bool|string Return false when nothing was found or return the value.
     */
    private function getItem(
        int|string $metaModelIdOrName,
        string $mixDataId,
        string $viewId,
        ?string $outputFormat
    ): bool|string {
        // Get the MetaModel. Return if we can not find one.
        $metaModel = $this->loadMetaModel($metaModelIdOrName);

        // Set output format to default if not set.
        if (null === $outputFormat || '' === $outputFormat) {
            $outputFormat = 'text';
        }

        $objMetaModelList = new ItemList();
        $objMetaModelList
            ->setMetaModel((string) $metaModel->get('id'), $viewId)
            ->overrideOutputFormat($outputFormat);

        // Handle a set of ids.
        /** @var list<string> $arrIds */
        $arrIds = StringUtil::trimsplit(',', $mixDataId);

        // Render an empty insert tag rather than displaying a list with an empty
        // result information - do not return false here because the insert tag itself is correct.
        if (\count($arrIds) < 1) {
            return '';
        }

        $objMetaModelList->addFilterRule(new StaticIdList($arrIds));

        return $objMetaModelList->render(false, $this);
    }

    /**
     * Get from MM X the item with the id Y and parse the attribute Z and return it.
     *
     * @param string      $metaModelIdOrName   ID or column name of MetaModel.
     * @param string      $intDataId           ID of the data row.
     * @param string      $viewId              ID of render setting.
     * @param string      $attributeIdentifier ID or column name of the attribute.
     * @param string|null $outputFormat        Type of output format - (Default:text)|html5|raw.
     *
     * @return bool|string Return false when nothing was found or return the value.
     *
     */
    private function getAttribute(
        string $metaModelIdOrName,
        string $intDataId,
        string $viewId,
        string $attributeIdentifier,
        ?string $outputFormat
    ): bool|string {
        // Get the MM.
        $metaModel = $this->loadMetaModel($metaModelIdOrName);

        // Get item.
        $item = $metaModel->findById($intDataId);
        if (null === $item) {
            return false;
        }

        if (\is_numeric($attributeIdentifier)) {
            $attribute = $metaModel->getAttributeById((int) $attributeIdentifier);
            assert($attribute instanceof IAttribute);
            $attributeIdentifier = $attribute->getColName();
        }

        $originalOutputFormat = $outputFormat;
        // Set output format to default if not set or raw.
        if (null === $outputFormat || '' === $outputFormat || 'raw' === $outputFormat) {
            $outputFormat = 'text';
        }

        // Get render setting.
        $renderSetting = $this->renderSettingFactory->createCollection($metaModel, $viewId);

        // Parse attribute.
        $arrAttr = $item->parseAttribute($attributeIdentifier, $outputFormat, $renderSetting);

        // Reset format to raw if is it.
        if ('raw' === $originalOutputFormat) {
            $outputFormat = 'raw';
        }

        return $arrAttr[$outputFormat] ?? false;
    }

    /**
     * Get count from a module or content element of a mm or from mm with filter direct.
     *
     * @param string     $type       Type of element like mod, ce or mm.
     * @param int|string $identifier ID of content element or module or ID or name of MetaModel.
     * @param int|null   $filterId   ID of the filter.
     *
     * @return int Return the count value.
     * @throws Exception
     */
    private function getCount(string $type, int|string $identifier, ?int $filterId = null): int
    {
        switch ($type) {
            // From module, can be a MetaModel list or filter.
            case 'mod':
                if (false !== ($result = $this->getMetaModelDataFrom('tl_module', (int) $identifier))) {
                    return $this->getCountFor($result['metamodel'], $result['metamodel_filtering']);
                }
                break;

            // From content element, can be a MetaModel list or filter.
            case 'ce':
                if (false !== ($result = $this->getMetaModelDataFrom('tl_content', (int) $identifier))) {
                    return $this->getCountFor($result['metamodel'], $result['metamodel_filtering']);
                }
                break;

            // From MetaModel with filter.
            case 'mm':
                return $this->getCountFor((string) $identifier, $filterId);
                break;

            // Unknown element type.
            default:
                return 0;
        }

        return 0;
    }

    /**
     * Try to load the MetaModel by id or name.
     *
     * @param int|string $nameOrId Name or id of the MetaModel.
     *
     * @return IMetaModel
     */
    private function loadMetaModel(int|string $nameOrId): IMetaModel
    {
        // Name.
        $tableName = $nameOrId;
        if (\is_numeric($nameOrId)) {
            // ID.
            $tableName = $this->factory->translateIdToMetaModelName((string) $nameOrId);
        }

        $metaModel = $this->factory->getMetaModel((string) $tableName);

        if (null === $metaModel) {
            throw new \RuntimeException('MetaModel not found: ' . $nameOrId);
        }

        return $metaModel;
    }

    /**
     * Get the MetaModel id and the filter id.
     *
     * @param string $strTable Name of table.
     * @param int    $intID    ID of the filter.
     *
     * @return false|array Returns null when nothing was found or a \Database\Result with the chosen information.
     *
     * @throws Exception
     */
    private function getMetaModelDataFrom(string $strTable, int $intID): bool|array
    {
        // Check if we know the table.
        if (!$this->connection->createSchemaManager()->tablesExist([$strTable])) {
            return false;
        }

        // Get all information form table or return null if we have no data.
        $statement = $this->connection
            ->createQueryBuilder()
            ->select('t.metamodel', 't.metamodel_filtering')
            ->from($strTable, 't')
            ->where('t.id=:id')
            ->setParameter('id', $intID)
            ->executeQuery();

        // Check if we have some data.
        if ($statement->rowCount() < 1) {
            return false;
        }

        return $statement->fetchAssociative();
    }

    /**
     * Get count form one MM for chosen filter.
     *
     * @param string   $metaModelNameOrId Name or id of the MetaModel.
     * @param int|null $intFilterId       ID of the filter.
     *
     * @return int The count result.
     */
    private function getCountFor(string $metaModelNameOrId, ?int $intFilterId = null): int
    {
        $metaModel = $this->loadMetaModel($metaModelNameOrId);

        $objFilter = $metaModel->getEmptyFilter();
        if (null !== $intFilterId) {
            $collection = $this->filterSettingFactory->createCollection((string) $intFilterId);
            $values     = [];

            foreach ($collection->getParameters() as $key) {
                $values[$key] = Input::get($key);
            }

            $collection->addRules($objFilter, $values);
        }

        return $metaModel->getCount($objFilter);
    }

    /**
     * @param int   $expectCount The expected number of elements.
     * @param array $elements    The elements.
     *
     * @return bool
     */
    private function checkMinExpectElements(int $expectCount, array $elements): bool
    {
        return \count($elements) >= $expectCount;
    }

    /**
     * Check if the item is published.
     *
     * @param IMetaModel $metaModel Current MetaModel.
     * @param int        $intItemId ID of the item.
     *
     * @return bool True => Published | False => Not published
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @deprecated
     *
     */
    protected function isPublishedItem(IMetaModel $metaModel, int $intItemId): bool
    {
        // @codingStandardsIgnoreStart
        @trigger_error(
            'The check isPublishedItem at inserttag item has been removed.',
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd

        return true;
    }
}
