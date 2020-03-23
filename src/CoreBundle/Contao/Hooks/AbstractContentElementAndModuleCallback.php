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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2020 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\Contao\Hooks;

use Contao\StringUtil;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\UrlBuilder\UrlBuilder;
use ContaoCommunityAlliance\UrlBuilder\UrlBuilderFactoryInterface;
use Doctrine\DBAL\Connection;
use MetaModels\BackendIntegration\TemplateList;
use MetaModels\CoreBundle\Assets\IconBuilder;
use MetaModels\Filter\Setting\FilterSettingFactory;
use MetaModels\IFactory;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * This class is the abstract base for building the "edit MetaModel" button in the backend.
 */
abstract class AbstractContentElementAndModuleCallback
{
    /**
     * The table name.
     *
     * @var string
     */
    protected static $tableName;

    /**
     * The icon builder.
     *
     * @var IconBuilder
     */
    private $iconBuilder;

    /**
     * The URL builder factory.
     *
     * @var UrlBuilderFactoryInterface
     */
    private $urlBuilderFactory;

    /**
     * The MetaModel factory.
     *
     * @var IFactory
     */
    private $factory;

    /**
     * The filtersetting factory.
     *
     * @var FilterSettingFactory
     */
    private $filterFactory;

    /**
     * The database connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * The template list.
     *
     * @var TemplateList
     */
    private $templateList;

    /**
     * The request stack.
     *
     * @var RequestStack
     */
    private $requestStack;

    /**
     * Create a new instance.
     *
     * @param IconBuilder                $iconBuilder       The icon builder.
     * @param UrlBuilderFactoryInterface $urlBuilderFactory The URL builder.
     * @param IFactory                   $factory           The MetaModel factory.
     * @param FilterSettingFactory       $filterFactory     The filter factory.
     * @param Connection                 $connection        The database connection.
     * @param TemplateList               $templateList      The template list loader.
     * @param RequestStack               $requestStack      The request stack.
     */
    public function __construct(
        IconBuilder $iconBuilder,
        UrlBuilderFactoryInterface $urlBuilderFactory,
        IFactory $factory,
        FilterSettingFactory $filterFactory,
        Connection $connection,
        TemplateList $templateList,
        RequestStack $requestStack
    ) {
        $this->iconBuilder       = $iconBuilder;
        $this->urlBuilderFactory = $urlBuilderFactory;
        $this->filterFactory     = $filterFactory;
        $this->connection        = $connection;
        $this->templateList      = $templateList;
        $this->factory           = $factory;
        $this->requestStack      = $requestStack;
    }

    /**
     * Return the edit wizard.
     *
     * @param \DC_Table $dataContainer The data container.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function editMetaModelButton(\DC_Table $dataContainer)
    {
        if ($dataContainer->value < 1) {
            return '';
        }

        $url = $this->urlBuilderFactory->create('contao/main.php?do=metamodels&act=edit')
            ->setQueryParameter('id', ModelId::fromValues('tl_metamodel', $dataContainer->value)->getSerialized());

        return $this->renderEditButton(
            $GLOBALS['TL_LANG'][static::$tableName]['editmetamodel'][0],
            sprintf(
                StringUtil::specialchars($GLOBALS['TL_LANG'][static::$tableName]['editmetamodel'][1]),
                $dataContainer->value
            ),
            $url
        );
    }

    /**
     * Return the edit wizard.
     *
     * @param \DC_Table $dataContainer The data container.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function editFilterSettingButton(\DC_Table $dataContainer)
    {
        if ($dataContainer->value < 1) {
            return '';
        }

        $url = $this->urlBuilderFactory->create('contao/main.php?do=metamodels&table=tl_metamodel_filtersetting')
            ->setQueryParameter(
                'pid',
                ModelId::fromValues('tl_metamodel_filter', $dataContainer->value)->getSerialized()
            );

        return $this->renderEditButton(
            $GLOBALS['TL_LANG'][static::$tableName]['editfiltersetting'][0],
            sprintf(
                StringUtil::specialchars($GLOBALS['TL_LANG'][static::$tableName]['editfiltersetting'][1]),
                $dataContainer->value
            ),
            $url
        );
    }

    /**
     * Return the edit wizard.
     *
     * @param \DC_Table $dataContainer The data container.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function editRenderSettingButton(\DC_Table $dataContainer)
    {
        if ($dataContainer->value < 1) {
            return '';
        }

        $url = $this->urlBuilderFactory->create('contao/main.php?do=metamodels&table=tl_metamodel_rendersetting')
            ->setQueryParameter(
                'pid',
                ModelId::fromValues('tl_metamodel_rendersettings', $dataContainer->value)->getSerialized()
            );

        return $this->renderEditButton(
            $GLOBALS['TL_LANG'][static::$tableName]['editrendersetting'][0],
            sprintf(
                StringUtil::specialchars($GLOBALS['TL_LANG'][static::$tableName]['editrendersetting'][1]),
                $dataContainer->value
            ),
            $url
        );
    }

    /**
     * Fetch all attribute names for the current MetaModel.
     *
     * @param \DC_Table $objDc The data container calling this method.
     *
     * @return string[] array of all attributes as colName => human name
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function getAttributeNames(\DC_Table $objDc)
    {
        $attributeNames = [
            'sorting' => $GLOBALS['TL_LANG']['MSC']['metamodels_sorting'],
            'random'  => $GLOBALS['TL_LANG']['MSC']['random'],
            'id'      => $GLOBALS['TL_LANG']['MSC']['id'][0]
        ];

        $metaModelName = $this->factory->translateIdToMetaModelName($objDc->activeRecord->metamodel);
        $metaModel     = $this->factory->getMetaModel($metaModelName);

        if ($metaModel) {
            foreach ($metaModel->getAttributes() as $objAttribute) {
                $attributeNames[$objAttribute->getColName()] = $objAttribute->getName();
            }
        }

        return $attributeNames;
    }

    /**
     * Fetch all available filter settings for the current meta model.
     *
     * @param \DC_Table $objDC The data container calling this method.
     *
     * @return string[] array of all attributes as id => human name
     */
    public function getFilterSettings(\DC_Table $objDC)
    {
        $filterSettings = $this->connection->createQueryBuilder()
            ->select('f.id', 'f.name')
            ->from('tl_metamodel_filter', 'f')
            ->where('f.pid=:id')
            ->setParameter('id', $objDC->activeRecord->metamodel)
            ->execute()
            ->fetchAll(\PDO::FETCH_ASSOC);

        $result = [];
        foreach ($filterSettings as $filterSetting) {
            $result[$filterSetting['id']] = $filterSetting['name'];
        }

        // Sort the filter settings.
        asort($result);

        return $result;
    }

    /**
     * Get a list with all allowed attributes for meta title.
     *
     * @param \DC_Table $objDC The data container calling this method.
     *
     * @return array A list with all found attributes.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function getMetaTitleAttributes(\DC_Table $objDC)
    {
        return $this->getFilteredAttributeNames(
            $objDC->activeRecord->metamodel,
            (array) $GLOBALS['METAMODELS']['metainformation']['allowedTitle']
        );
    }

    /**
     * Get a list with all allowed attributes for meta description.
     *
     * @param \DC_Table $objDC The data container calling this method.
     *
     * @return array A list with all found attributes.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function getMetaDescriptionAttributes(\DC_Table $objDC)
    {
        return $this->getFilteredAttributeNames(
            $objDC->activeRecord->metamodel,
            (array) $GLOBALS['METAMODELS']['metainformation']['allowedDescription']
        );
    }

    /**
     * Called from subclass.
     *
     * @param \DC_Table $dataContainer The data container calling this method.
     * @param string    $elementName   The type name to search for.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function buildFilterParamsFor(\DC_Table $dataContainer, $elementName)
    {
        if (!$this->requestStack->getCurrentRequest()->query->get('act')) {
            return;
        }

        $filterId = $this->connection->createQueryBuilder()
            ->select('c.metamodel_filtering')
            ->from(static::$tableName, 'c')
            ->join('c', 'tl_metamodel', 'mm', 'mm.id=c.metamodel')
            ->where('c.id=:id')
            ->setParameter('id', $dataContainer->id)
            ->andWhere('c.type=:type')
            ->setParameter('type', $elementName)
            ->setMaxResults(1)
            ->execute()
            ->fetch(\PDO::FETCH_COLUMN);

        if (!$filterId) {
            unset($GLOBALS['TL_DCA'][static::$tableName]['fields']['metamodel_filterparams']);
            return;
        }

        $collection = $this->filterFactory->createCollection($filterId);
        $dca        = $collection->getParameterDCA();
        foreach ($dca as $fieldName => $subField) {
            $options = [];
            foreach ($subField['options'] as $key => $value) {
                $options[$this->loadCallback($key)] = $value;
            }

            $dca[$fieldName]['options']         = $options;
            $dca[$fieldName]['save_callback'][] = [static::class, 'saveCallback'];
            $dca[$fieldName]['load_callback'][] = [static::class, 'loadCallback'];
        }

        $GLOBALS['TL_DCA'][static::$tableName]['fields']['metamodel_filterparams']['eval']['subfields'] =
            $dca;
    }

    /**
     * Base64 decode.
     *
     * @param string|null $value The value to save.
     *
     * @return string|null
     */
    public function saveCallback(string $value = null)
    {
        return null === $value ? null : \base64_decode($value);
    }

    /**
     * Base64 encode.
     *
     * @param string|null $value The value.
     *
     * @return string|null
     */
    public function loadCallback(string $value = null)
    {
        return null === $value ? null : trim(\base64_encode($value), '=');
    }

    /**
     * Get attributes for checkbox wizard.
     *
     * @param \DC_Table $objDc The current row.
     *
     * @return array
     */
    public function getFilterParameterNames(\DC_Table $objDc)
    {
        $return = array();
        $filter = $objDc->activeRecord->metamodel_filtering;

        if (!$filter) {
            return $return;
        }

        $collection = $this->filterFactory->createCollection($filter);

        return $collection->getParameterFilterNames();
    }

    /**
     * Get frontend templates for filters.
     *
     * @return array
     */
    public function getFilterTemplates()
    {
        return $this->templateList->getTemplatesForBase('mm_filter_');
    }

    /**
     * Fetch the template group for the current MetaModel module.
     *
     * @param string $base The template base.
     *
     * @return array
     */
    protected function getTemplateList($base)
    {
        return $this->templateList->getTemplatesForBase($base);
    }

    /**
     * Fetch all available render settings for the current meta model.
     *
     * @param \DC_Table $objDC The data container calling this method.
     *
     * @return string[] array of all attributes as id => human name
     */
    public function getRenderSettings(\DC_Table $objDC)
    {
        $filterSettings = $this->connection->createQueryBuilder()
            ->select('r.id', 'r.name')
            ->from('tl_metamodel_rendersettings', 'r')
            ->where('r.pid=:id')
            ->setParameter('id', $objDC->activeRecord->metamodel)
            ->execute()
            ->fetchAll(\PDO::FETCH_ASSOC);

        $result = [];
        foreach ($filterSettings as $filterSetting) {
            $result[$filterSetting['id']] = $filterSetting['name'];
        }

        // Sort the filter settings.
        asort($result);

        return $result;
    }

    /**
     * Render an edit button.
     *
     * @param string     $caption The caption (alt attribute of the image).
     * @param string     $title   The title (title attribute of the <a> tag).
     * @param UrlBuilder $url     The URL for the button.
     *
     * @return string
     */
    private function renderEditButton($caption, $title, UrlBuilder $url)
    {
        $icon = $this->iconBuilder->getBackendIconImageTag(
            'system/themes/flexible/icons/alias.svg',
            $caption,
            'style="vertical-align:middle;height:24px;"'
        );

        return sprintf(
            '<a href="%s" title="%s" style="padding-left:3px">%s</a>',
            $url->getUrl(),
            $title,
            $icon
        );
    }

    /**
     * Get a list with all allowed attributes for meta description.
     *
     * If the optional parameter arrTypes is not given, all attributes will be retrieved.
     *
     * @param int      $metaModelId  The id of the MetaModel from which the attributes shall be retrieved from.
     *
     * @param string[] $allowedTypes The attribute type names that shall be retrieved (optional).
     *
     * @return array A list with all found attributes.
     */
    private function getFilteredAttributeNames($metaModelId, $allowedTypes = array())
    {
        $attributeNames = array();

        if ($metaModel = $this->factory->getMetaModel($this->factory->translateIdToMetaModelName($metaModelId))) {
            foreach ($metaModel->getAttributes() as $attribute) {
                if (empty($allowedTypes) || in_array($attribute->get('type'), $allowedTypes)) {
                    $attributeNames[$attribute->getColName()] =
                        sprintf(
                            '%s [%s]',
                            $attribute->getName(),
                            $attribute->getColName()
                        );
                }
            }
        }

        return $attributeNames;
    }
}
