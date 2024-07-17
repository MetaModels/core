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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Marc Reimann <reimann@mediendepot-ruhr.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\Contao\Hooks;

use Contao\DC_Table;
use Contao\StringUtil;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\UrlBuilder\UrlBuilder;
use ContaoCommunityAlliance\UrlBuilder\UrlBuilderFactoryInterface;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use MetaModels\BackendIntegration\TemplateList;
use MetaModels\CoreBundle\Assets\IconBuilder;
use MetaModels\Filter\Setting\FilterSettingFactory;
use MetaModels\IFactory;
use RuntimeException;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Contracts\Translation\TranslatorInterface;

use function asort;
use function base64_decode;
use function base64_encode;
use function in_array;
use function reset;
use function sprintf;
use function trim;

/**
 * This class is the abstract base for building the "edit MetaModel" button in the backend.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
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
    private IconBuilder $iconBuilder;

    /**
     * The URL builder factory.
     *
     * @var UrlBuilderFactoryInterface
     */
    private UrlBuilderFactoryInterface $urlBuilderFactory;

    /**
     * The MetaModel factory.
     *
     * @var IFactory
     */
    private IFactory $factory;

    /**
     * The filtersetting factory.
     *
     * @var FilterSettingFactory
     */
    private FilterSettingFactory $filterFactory;

    /**
     * The database connection.
     *
     * @var Connection
     */
    private Connection $connection;

    /**
     * The template list.
     *
     * @var TemplateList
     */
    private TemplateList $templateList;

    /**
     * The request stack.
     *
     * @var RequestStack
     */
    private RequestStack $requestStack;

    private TranslatorInterface $translator;

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
     * @param TranslatorInterface        $translator        The translator.
     */
    public function __construct(
        IconBuilder $iconBuilder,
        UrlBuilderFactoryInterface $urlBuilderFactory,
        IFactory $factory,
        FilterSettingFactory $filterFactory,
        Connection $connection,
        TemplateList $templateList,
        RequestStack $requestStack,
        TranslatorInterface $translator,
    ) {
        $this->iconBuilder       = $iconBuilder;
        $this->urlBuilderFactory = $urlBuilderFactory;
        $this->filterFactory     = $filterFactory;
        $this->connection        = $connection;
        $this->templateList      = $templateList;
        $this->factory           = $factory;
        $this->requestStack      = $requestStack;
        $this->translator        = $translator;
    }

    /**
     * Return the edit wizard.
     *
     * @param DC_Table $dataContainer The data container.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function editMetaModelButton(DC_Table $dataContainer)
    {
        if ($dataContainer->value < 1) {
            return '';
        }

        $url = $this->urlBuilderFactory->create('contao/metamodels?act=edit')
            ->setQueryParameter('id', ModelId::fromValues('tl_metamodel', $dataContainer->value)->getSerialized());

        return $this->renderEditButton(
            $this->translator->trans('editmetamodel.label', [], static::$tableName),
            StringUtil::specialchars(
                $this->translator->trans(
                    'editmetamodel.description',
                    ['%id%' => $dataContainer->value],
                    static::$tableName
                )
            ),
            $url
        );
    }

    /**
     * Return the edit wizard.
     *
     * @param DC_Table $dataContainer The data container.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function editFilterSettingButton(DC_Table $dataContainer)
    {
        if ($dataContainer->value < 1) {
            return '';
        }

        $url = $this->urlBuilderFactory->create('contao/metamodels?table=tl_metamodel_filtersetting')
            ->setQueryParameter(
                'pid',
                ModelId::fromValues('tl_metamodel_filter', $dataContainer->value)->getSerialized()
            );

        return $this->renderEditButton(
            $this->translator->trans('editfiltersetting.label', [], static::$tableName),
            StringUtil::specialchars(
                $this->translator->trans(
                    'editfiltersetting.description',
                    ['%id%' => $dataContainer->value],
                    static::$tableName
                )
            ),
            $url
        );
    }

    /**
     * Return the edit wizard.
     *
     * @param DC_Table $dataContainer The data container.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function editRenderSettingButton(DC_Table $dataContainer)
    {
        if ($dataContainer->value < 1) {
            return '';
        }

        $url = $this->urlBuilderFactory->create('contao/metamodels?table=tl_metamodel_rendersetting')
            ->setQueryParameter(
                'pid',
                ModelId::fromValues('tl_metamodel_rendersettings', $dataContainer->value)->getSerialized()
            );

        return $this->renderEditButton(
            $this->translator->trans('editrendersetting.label', [], static::$tableName),
            StringUtil::specialchars(
                $this->translator->trans(
                    'editrendersetting.description',
                    ['%id%' => $dataContainer->value],
                    static::$tableName
                ),
            ),
            $url
        );
    }

    /**
     * Fetch all attribute names for the current MetaModel.
     *
     * @param DC_Table $objDc The data container calling this method.
     *
     * @return string[] array of all attributes as colName => human name
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function getAttributeNames(DC_Table $objDc)
    {
        $attributeNames = [
            'sorting' => $this->translator->trans('metamodels_sorting', [], 'metamodels_list'),
            'random'  => $this->translator->trans('random', [], 'metamodels_list'),
            'id'      => $this->translator->trans('id', [], 'metamodels_list')
        ];

        assert(null !== $objDc->activeRecord);
        try {
            $metaModelName = $this->factory->translateIdToMetaModelName($objDc->activeRecord->metamodel);
        } catch (RuntimeException $exception) {
            // No valid MetaModel selected, can not add attributes of it.
            return $attributeNames;
        }
        $metaModel     = $this->factory->getMetaModel($metaModelName);

        if ($metaModel) {
            foreach ($metaModel->getAttributes() as $objAttribute) {
                $attributeNames[$objAttribute->getColName()] = $objAttribute->getName();
            }
        }

        return $attributeNames;
    }

    /**
     * Fetch all available filter settings for the current MetaModel.
     *
     * @param DC_Table $objDC The data container calling this method.
     *
     * @return string[] array of all attributes as id => human name
     */
    public function getFilterSettings(DC_Table $objDC)
    {
        assert(null !== $objDC->activeRecord);
        $filterSettings = $this->connection->createQueryBuilder()
            ->select('f.id', 'f.name')
            ->from('tl_metamodel_filter', 'f')
            ->where('f.pid=:id')
            ->setParameter('id', $objDC->activeRecord->metamodel)
            ->executeQuery()
            ->fetchAllAssociative();

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
     * @param DC_Table $objDC The data container calling this method.
     *
     * @return array A list with all found attributes.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function getMetaTitleAttributes(DC_Table $objDC)
    {
        assert(null !== $objDC->activeRecord);

        /** @psalm-suppress ArgumentTypeCoercion - We HOPE there is a list of strings. */
        return $this->getFilteredAttributeNames(
            $objDC->activeRecord->metamodel,
            (array) $GLOBALS['METAMODELS']['metainformation']['allowedTitle']
        );
    }

    /**
     * Get a list with all allowed attributes for meta description.
     *
     * @param DC_Table $objDC The data container calling this method.
     *
     * @return array A list with all found attributes.
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function getMetaDescriptionAttributes(DC_Table $objDC)
    {
        assert(null !== $objDC->activeRecord);

        /** @psalm-suppress ArgumentTypeCoercion - We HOPE there is a list of strings. */
        return $this->getFilteredAttributeNames(
            $objDC->activeRecord->metamodel,
            (array) $GLOBALS['METAMODELS']['metainformation']['allowedDescription']
        );
    }

    /**
     * Called from subclass.
     *
     * @param DC_Table $dataContainer The data container calling this method.
     * @param string   $elementName   The type name to search for.
     *
     * @return void
     *
     * @throws Exception
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function buildFilterParamsFor(DC_Table $dataContainer, $elementName)
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request || !$request->query->has('act')) {
            return;
        }

        $filterIds = $this->connection->createQueryBuilder()
            ->select('c.metamodel_filtering')
            ->from(static::$tableName, 'c')
            ->join('c', 'tl_metamodel', 'mm', 'mm.id=c.metamodel')
            ->where('c.id=:id')
            ->setParameter('id', $dataContainer->id)
            ->andWhere('c.type=:type')
            ->setParameter('type', $elementName)
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchFirstColumn();

        if (false === ($filterId = reset($filterIds)) || 0 === $filterId) {
            unset($GLOBALS['TL_DCA'][static::$tableName]['fields']['metamodel_filterparams']);
            return;
        }

        $translatedNull = $this->translator->trans('filter_option.null', [], static::$tableName);

        $collection = $this->filterFactory->createCollection($filterId);
        $dca        = $collection->getParameterDCA();
        foreach ($dca as $fieldName => $subField) {
            $options = ['--null--' => $translatedNull];
            foreach (($subField['options'] ?? []) as $key => $value) {
                $newKey = $this->loadCallback($key);
                if (null !== $newKey) {
                    $options[$newKey] = $value;
                }
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
        return ('--null--' === $value || null === $value) ? null : base64_decode($value);
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
        return null === $value ? '--null--' : trim(base64_encode($value), '=');
    }

    /**
     * Get attributes for checkbox wizard.
     *
     * @param DC_Table $objDc The current row.
     *
     * @return array
     */
    public function getFilterParameterNames(DC_Table $objDc)
    {
        assert(null !== $objDc->activeRecord);

        $return = [];
        $filter = $objDc->activeRecord->metamodel_filtering;

        if (!$filter) {
            return $return;
        }

        return $this->filterFactory->createCollection($filter)->getParameterFilterNames();
    }

    /**
     * Get frontend templates for filters.
     *
     * @param DC_Table $dcTable The data container calling this method.
     *
     * @return array
     */
    public function getFilterTemplates(DC_Table $dcTable)
    {
        assert(null !== $dcTable->activeRecord);

        if ($dcTable->activeRecord->type === 'metamodels_frontendclearall') {
            return $this->templateList->getTemplatesForBase('mm_clearall_');
        }

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
     * Fetch all available render settings for the current MetaModel.
     *
     * @param DC_Table $objDC The data container calling this method.
     *
     * @return string[] array of all attributes as id => human name
     */
    public function getRenderSettings(DC_Table $objDC)
    {
        assert(null !== $objDC->activeRecord);

        $filterSettings = $this->connection->createQueryBuilder()
            ->select('r.id', 'r.name')
            ->from('tl_metamodel_rendersettings', 'r')
            ->where('r.pid=:id')
            ->setParameter('id', $objDC->activeRecord->metamodel)
            ->executeQuery()
            ->fetchAllAssociative();

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
    private function renderEditButton(string $caption, string $title, UrlBuilder $url): string
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
     * @param string       $metaModelId  The id of the MetaModel from which the attributes shall be retrieved from.
     * @param list<string> $allowedTypes The attribute type names that shall be retrieved.
     *
     * @return array A list with all found attributes.
     */
    private function getFilteredAttributeNames(string $metaModelId, array $allowedTypes): array
    {
        $attributeNames = [];

        try {
            $metaModelName = $this->factory->translateIdToMetaModelName($metaModelId);
        } catch (RuntimeException $exception) {
            // No valid MetaModel selected, can not add attributes of it.
            return $attributeNames;
        }
        if ($metaModel = $this->factory->getMetaModel($metaModelName)) {
            foreach ($metaModel->getAttributes() as $attribute) {
                if (empty($allowedTypes) || in_array($attribute->get('type'), $allowedTypes, true)) {
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
