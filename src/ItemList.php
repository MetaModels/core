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
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     David Maack <david.maack@arcor.de>
 * @author     Oliver Hoff <oliver@hofff.com>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tim Gatzky <info@tim-gatzky.de>
 * @author     Martin Treml <github@r2pi.net>
 * @author     Jeremie Constant <j.constant@imi.de>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Benedict Zinke <bz@presentprogressive.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Fritz Michael Gschwantner <fmg@inspiredminds.at>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels;

use Contao\ContentModel;
use Contao\Model;
use Contao\ModuleModel;
use Contao\StringUtil;
use Contao\System;
use Contao\Template as ContaoTemplate;
use MetaModels\Events\RenderItemListEvent;
use MetaModels\Filter\FilterUrlBuilder;
use MetaModels\Filter\IFilter;
use MetaModels\Filter\IFilterRule;
use MetaModels\Filter\Setting\ICollection as IFilterSettingCollection;
use MetaModels\Filter\Setting\IFilterSettingFactory;
use MetaModels\Helper\LocaleUtil;
use MetaModels\Helper\PaginationLimitCalculator;
use MetaModels\Helper\SortingLinkGenerator;
use MetaModels\Render\Setting\ICollection as IRenderSettingCollection;
use MetaModels\Render\Setting\IRenderSettingFactory;
use MetaModels\Render\Template;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;

use function array_key_exists;
use function array_keys;
use function array_merge;
use function func_num_args;
use function in_array;
use function is_int;
use function is_object;
use function sprintf;
use function strtoupper;
use function trigger_error;

/**
 * Implementation of a general purpose MetaModel listing.
 *
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class ItemList
{
    /**
     * Sort by attribute.
     *
     * @var string
     */
    protected string $strSortBy = '';

    /**
     * Sort by attribute.
     *
     * @var string
     */
    protected string $strSortDirection = 'ASC';

    /**
     * The view id to use.
     *
     * @var string
     */
    protected string $intView = '0';

    /**
     * Output format type.
     *
     * @var string|null
     */
    protected ?string $outputFormat = null;

    /**
     * The MetaModel id to use.
     *
     * @var string
     */
    protected string $intMetaModel = '0';

    /**
     * The filter id to use.
     *
     * @var string
     *
     * @deprecated Not in use anymore - remove in MetaModels 3.0
     */
    protected string $intFilter = '0';

    /**
     * The parameters for the filter.
     *
     * @var array<string, list<string>|string>
     */
    protected array $arrParam = [];

    /**
     * The parameters for the template.
     *
     * @var array<string,mixed>
     */
    private array $templateParameter = [];

    /**
     * The name of the attribute for the title.
     *
     * @var string
     */
    protected string $strTitleAttribute = '';

    /**
     * The name of the attribute for the description.
     *
     * @var string
     */
    protected string $strDescriptionAttribute = '';

    /**
     * The calculator for pagination and limit.
     *
     * @var PaginationLimitCalculator
     */
    private PaginationLimitCalculator $paginationLimitCalculator;

    /**
     *  The filter setting factory.
     *
     * @var IFilterSettingFactory|null
     */
    private ?IFilterSettingFactory $filterFactory;

    /**
     * The MetaModels factory.
     *
     * @var IFactory|null
     */
    private ?IFactory $factory;

    /**
     * The render setting factory.
     *
     * @var IRenderSettingFactory|null
     */
    private ?IRenderSettingFactory $renderSettingFactory;

    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface|null
     */
    private ?EventDispatcherInterface $eventDispatcher;

    /**
     * The language.
     *
     * @var string|null
     */
    private ?string $language = null;

    /**
     * The sorting link generator.
     *
     * @var SortingLinkGenerator|null
     */
    private ?SortingLinkGenerator $sortingLinkGenerator = null;

    /**
     * Create a new instance.
     *
     * @param IFactory|null                 $factory              The MetaModels factory (required in MetaModels 3.0).
     * @param IFilterSettingFactory|null    $filterFactory        The filter setting factory (required in MetaModels
     *                                                            3.0).
     * @param IRenderSettingFactory|null    $renderSettingFactory The render setting factory (required in MetaModels
     *                                                            3.0).
     * @param EventDispatcherInterface|null $eventDispatcher      The event dispatcher (required in MetaModels 3.0).
     * @param FilterUrlBuilder|null         $filterUrlBuilder     The filter url builder.
     * @param string                        $pageParam            The pagination URL key.
     * @param string                        $paramType            The pagination parameter URL type.
     * @param int                           $maxPaginationLinks   The maximum number of pagination links.
     * @param string                        $paginationTemplate   The pagination template.
     * @param string                        $paginationFragment   The pagination fragment.
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        IFactory $factory = null,
        IFilterSettingFactory $filterFactory = null,
        IRenderSettingFactory $renderSettingFactory = null,
        EventDispatcherInterface $eventDispatcher = null,
        FilterUrlBuilder $filterUrlBuilder = null,
        string $pageParam = 'page',
        string $paramType = 'get',
        int $maxPaginationLinks = 0,
        string $paginationTemplate = 'mm_pagination',
        string $paginationFragment = ''
    ) {
        $this->paginationLimitCalculator = new PaginationLimitCalculator(
            $filterUrlBuilder,
            $pageParam,
            $paramType,
            $maxPaginationLinks,
            $paginationTemplate,
            $paginationFragment
        );

        $this->factory              = $factory;
        $this->filterFactory        = $filterFactory;
        $this->renderSettingFactory = $renderSettingFactory;
        $this->eventDispatcher      = $eventDispatcher;
    }

    /**
     * Set the filter setting factory.
     *
     * @param IFilterSettingFactory $filterFactory The filter setting factory.
     *
     * @return ItemList
     *
     * @deprecated Use constructor injection instead.
     */
    public function setFilterFactory(IFilterSettingFactory $filterFactory): self
    {
        // @codingStandardsIgnoreStart
        @trigger_error(
            'Method "' . __METHOD__ . '" is deprecated and will be removed in MetaModels 3.0.',
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd
        $this->filterFactory = $filterFactory;

        return $this;
    }

    /**
     * Retrieve the filter setting factory.
     *
     * @return IFilterSettingFactory
     */
    private function getFilterFactory(): IFilterSettingFactory
    {
        if (null !== $this->filterFactory) {
            return $this->filterFactory;
        }
        // @codingStandardsIgnoreStart
        @trigger_error(
            'Not setting the filter setting factory via constructor is deprecated and will throw ' .
            'an exception in MetaModels 3.0.',
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd
        $factory = System::getContainer()->get('metamodels.filter_setting_factory');
        assert($factory instanceof IFilterSettingFactory);

        return $this->filterFactory = $factory;
    }

    /**
     * Set the factory.
     *
     * @param IFactory $factory The factory.
     *
     * @return ItemList
     *
     * @deprecated Use constructor injection instead.
     */
    public function setFactory(IFactory $factory): self
    {
        // @codingStandardsIgnoreStart
        @trigger_error(
            'Method "' . __METHOD__ . '" is deprecated and will be removed in MetaModels 3.0.',
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd
        $this->factory = $factory;

        return $this;
    }

    /**
     * Retrieve the factory.
     *
     * @return IFactory
     */
    private function getFactory(): IFactory
    {
        if ($this->factory) {
            return $this->factory;
        }
        // @codingStandardsIgnoreStart
        @trigger_error(
            'Not setting the factory via constructor is deprecated and will throw ' .
            'an exception in MetaModels 3.0.',
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd
        $factory = System::getContainer()->get('metamodels.factory');
        assert($factory instanceof IFactory);

        return $this->factory = $factory;
    }

    /**
     * Set the render setting factory.
     *
     * @param IRenderSettingFactory $factory The factory.
     *
     * @return ItemList
     *
     * @deprecated Use constructor injection instead.
     */
    public function setRenderSettingFactory(IRenderSettingFactory $factory): self
    {
        // @codingStandardsIgnoreStart
        @trigger_error(
            'Method "' . __METHOD__ . '" is deprecated and will be removed in MetaModels 3.0.',
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd
        $this->renderSettingFactory = $factory;

        return $this;
    }

    /**
     * Set eventDispatcher.
     *
     * @param EventDispatcherInterface $eventDispatcher The new value.
     *
     * @return ItemList
     *
     * @deprecated Use constructor injection instead.
     */
    public function setEventDispatcher(EventDispatcherInterface $eventDispatcher): self
    {
        // @codingStandardsIgnoreStart
        @trigger_error(
            'Method "' . __METHOD__ . '" is deprecated and will be removed in MetaModels 3.0.',
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd
        $this->eventDispatcher = $eventDispatcher;

        return $this;
    }

    /**
     * Retrieve eventDispatcher.
     *
     * @return EventDispatcherInterface
     */
    private function getEventDispatcher(): EventDispatcherInterface
    {
        if ($this->eventDispatcher) {
            return $this->eventDispatcher;
        }

        // @codingStandardsIgnoreStart
        @trigger_error(
            'Not setting the event dispatcher via constructor is deprecated and will throw ' .
            'an exception in MetaModels 3.0.',
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd
        $dispatcher = System::getContainer()->get('event_dispatcher');
        assert($dispatcher instanceof EventDispatcherInterface);

        return $this->eventDispatcher = $dispatcher;
    }

    /**
     * Set the service container to use.
     *
     * @return ItemList
     *
     * @deprecated The service container will get removed, use the symfony service container instead.
     */
    public function setServiceContainer(): self
    {
        return $this;
    }

    /**
     * Set the service container to use (fallback for MM 2.0 compatibility).
     *
     * @return ItemList
     *
     * @deprecated The service container will get removed, use the symfony service container instead.
     */
    public function setServiceContainerFallback(): self
    {
        return $this;
    }

    /**
     * Retrieve the service container in use.
     *
     * @return IMetaModelsServiceContainer|null
     *
     * @deprecated The service container will get removed, use the symfony service container instead.
     *
     * @psalm-suppress DeprecatedInterface
     */
    public function getServiceContainer(): ?IMetaModelsServiceContainer
    {
        return null;
    }

    /**
     * Set the limit.
     *
     * @param bool $isLimit If true, use limit, if false no limit is applied.
     * @param int  $offset  Like in SQL, first element to be returned (0 based).
     * @param int  $limit   Like in SQL, amount of elements to retrieve.
     *
     * @return ItemList
     */
    public function setLimit(bool $isLimit, int $offset, int $limit): self
    {
        $this
            ->paginationLimitCalculator
            ->setApplyLimitAndOffset($isLimit)
            ->setOffset($offset)
            ->setLimit($limit);

        return $this;
    }

    /**
     * Set page breaking to the given amount of items. A value of 0 disables pagination at all.
     *
     * @param int $limit The amount of items per page. A value of 0 disables pagination.
     *
     * @return ItemList
     */
    public function setPageBreak(int $limit): self
    {
        $this->paginationLimitCalculator->setPerPage($limit);

        return $this;
    }

    /**
     * Set sorting to an attribute or system column optionally in the given direction.
     *
     * @param string $sortBy        The name of the attribute or system column to be used for sorting.
     * @param string $sortDirection The direction, either ASC or DESC (optional).
     *
     * @return ItemList
     */
    public function setSorting(string $sortBy, string $sortDirection = 'ASC'): self
    {
        $this->strSortBy        = $sortBy;
        $this->strSortDirection = ('DESC' === strtoupper($sortDirection)) ? 'DESC' : 'ASC';

        return $this;
    }

    /**
     * Override the output format of the used view.
     *
     * @param string|null $outputFormat The desired output format.
     *
     * @return ItemList
     */
    public function overrideOutputFormat(string $outputFormat = null): self
    {
        $outputFormat = (string) $outputFormat;
        if ('' !== $outputFormat) {
            $this->outputFormat = $outputFormat;
        } else {
            $this->outputFormat = null;
        }

        return $this;
    }

    /**
     * Set MetaModel and render settings.
     *
     * @param string|int $intMetaModel The MetaModel to use.
     * @param string|int $intView      The render settings to use (if 0, the default will be used).
     *
     * @return ItemList
     */
    public function setMetaModel(string|int $intMetaModel, string|int $intView): self
    {
        if (is_int($intMetaModel)) {
            $intMetaModel = (string) $intMetaModel;
            // @codingStandardsIgnoreStart Silencing errors is discouraged
            @trigger_error(
                'Parameter $intMetaModel in "' . __CLASS__ . '::' .__METHOD__. '" has been changed from int to string.',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
        }

        if (is_int($intView)) {
            $intView = (string) $intView;
            // @codingStandardsIgnoreStart Silencing errors is discouraged
            @trigger_error(
                'Parameter $intView in "' . __CLASS__ . '::' .__METHOD__. '" has been changed from int to string.',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
        }

        $this->intMetaModel = $intMetaModel;
        $this->intView      = $intView;

        $this->prepareMetaModel();

        $this->prepareView();

        return $this;
    }

    /**
     * Add the attribute names for meta title and description.
     *
     * @param string $titleAttribute       Name of attribute for title.
     * @param string $descriptionAttribute Name of attribute for description.
     *
     * @return ItemList
     */
    public function setMetaTags(string $titleAttribute, string $descriptionAttribute): self
    {
        $this->strDescriptionAttribute = $descriptionAttribute;
        $this->strTitleAttribute       = $titleAttribute;

        return $this;
    }

    /**
     * Set the language.
     *
     * @param string $language The language.
     *
     * @return $this
     */
    public function setLanguage(string $language): self
    {
        $this->language = $language;

        return $this;
    }

    /**
     * The Metamodel to use.
     *
     * @var IMetaModel|null
     */
    protected $objMetaModel = null;

    /**
     * The render settings to use.
     *
     * @var IRenderSettingCollection|null
     */
    protected $objView = null;

    /**
     * The render template to use (metamodel_).
     *
     * @var Template|null
     */
    protected $objTemplate = null;

    /**
     * The list template (ce_ or mod_).
     *
     * @var ContaoTemplate|null
     */
    private $listTemplate = null;

    /**
     * The filter settings to use.
     *
     * @var IFilterSettingCollection|null
     */
    protected $objFilterSettings = null;

    /**
     * The filter to use.
     *
     * @var IFilter|null
     */
    protected $objFilter = null;

    /**
     * The model, can be module model or content model.
     *
     * @var ContentModel|ModuleModel|null $model
     *
     * @deprecated Do not use.
     */
    private ModuleModel|null|ContentModel $model = null;

    /**
     * Get the model.
     *
     * @return ContentModel|ModuleModel|null
     *
     * @deprecated Do not use.
     */
    public function getModel(): ?Model
    {
        /** @psalm-suppress DeprecatedProperty */
        return $this->model;
    }

    /**
     * Prepare the MetaModel.
     *
     * @return void
     *
     * @throws RuntimeException When the MetaModel can not be found.
     *
     * @psalm-assert IMetaModel $this->objMetaModel
     */
    protected function prepareMetaModel(): void
    {
        $factory            = $this->getFactory();
        $this->objMetaModel = $factory->getMetaModel($factory->translateIdToMetaModelName($this->intMetaModel));
        if (!$this->objMetaModel) {
            throw new RuntimeException('Could not get metamodel with id: ' . $this->intMetaModel);
        }
    }

    /**
     * Prepare the view.
     *
     * NOTE: must be called after prepareMetaModel().
     *
     * @return void
     */
    protected function prepareView(): void
    {
        $metaModel = $this->getMetaModel();
        if ($this->renderSettingFactory) {
            $this->objView = $this->renderSettingFactory->createCollection($metaModel, $this->intView);
        } else {
            /** @psalm-suppress DeprecatedMethod */
            $this->objView = $metaModel->getView((int) $this->intView);
        }

        $this->objTemplate       = new Template((string) $this->objView->get('template'));
        $this->objTemplate->view = $this->objView;
    }

    /**
     * Set the filter setting to use.
     *
     * @param string|int $intFilter The filter setting id to use.
     *
     * @return $this
     *
     * @throws RuntimeException When the filter settings can not be found.
     */
    public function setFilterSettings(string|int $intFilter): self
    {
        if (is_int($intFilter)) {
            $intFilter = (string) $intFilter;
            // @codingStandardsIgnoreStart Silencing errors is discouraged
            @trigger_error(
                'Parameter $intFilter in "' . __CLASS__ . '::' .__METHOD__. '" has been changed from int to string.',
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd
        }

        $this->objFilterSettings = $this->getFilterFactory()->createCollection($intFilter);

        return $this;
    }

    /**
     * Set parameters.
     *
     * @param array<string, array{value: string, use_get: ''|'1'}> $presets The parameter preset values to use.
     * @param array<string, list<string>|string>                   $values  The dynamic parameter values that may be
     *                                                                      used.
     *
     * @return ItemList
     *
     * @throws RuntimeException When no filter settings have been set.
     */
    public function setFilterParameters(array $presets, array $values): self
    {
        if (!$this->objFilterSettings) {
            throw new RuntimeException(
                'Error: no filter object defined, call setFilterSettings() before setFilterParameters().'
            );
        }

        $presetNames     = $this->objFilterSettings->getParameters();
        $filterParamKeys = array_keys($this->objFilterSettings->getParameterFilterNames());

        $processed = [];

        // We have to use all the preset values we want first.
        foreach ($presets as $presetName => $presetValues) {
            if (in_array($presetName, $presetNames, true)) {
                $processed[$presetName] = $presetValues['value'];
            }
        }

        // Now we have to use all FE filter params, that are either:
        // * not contained within the presets
        // * or are overridable.
        foreach ($filterParamKeys as $filterParameterKey) {
            // Unknown parameter? - next please.
            if (!array_key_exists($filterParameterKey, $values)) {
                continue;
            }

            // Not a preset or allowed to override? - use value.
            if ((!array_key_exists($filterParameterKey, $presets)) || (bool) $presets[$filterParameterKey]['use_get']) {
                $processed[$filterParameterKey] = $values[$filterParameterKey];
            }
        }

        $this->arrParam = $processed;

        return $this;
    }

    /**
     * Return the filter.
     *
     * @return IFilter
     */
    public function getFilter(): IFilter
    {
        if (null === $this->objFilter) {
            throw new RuntimeException('No filter is set.');
        }

        return $this->objFilter;
    }

    /**
     * Return the filter settings.
     *
     * @return IFilterSettingCollection
     */
    public function getFilterSettings(): IFilterSettingCollection
    {
        if (null === $this->objFilterSettings) {
            throw new RuntimeException(
                'Error: no filter object defined, call setFilterSettings() before.'
            );
        }

        return $this->objFilterSettings;
    }

    /**
     * Get the template of the module or content element.
     *
     * @return ContaoTemplate
     */
    public function getListTemplate(): ?ContaoTemplate
    {
        return $this->listTemplate;
    }

    /**
     * Set the template of the module or content element.
     *
     * @param ContaoTemplate $template The template.
     *
     * @return self
     */
    public function setListTemplate(ContaoTemplate $template): self
    {
        $this->listTemplate = $template;

        return $this;
    }

    /**
     * Set a template parameter
     *
     * @param string $name  The name of the parameter.
     * @param mixed  $value The value to use.
     *
     * @return void
     */
    public function setTemplateParameter(string $name, mixed $value): void
    {
        $this->templateParameter[$name] = $value;
    }

    /**
     * The items in the list view.
     *
     * @var IItems|null
     */
    protected ?IItems $objItems = null;

    /**
     * Add additional filter rules to the list.
     *
     * Can be overridden by subclasses to add additional filter rules to the filter before it will get evaluated.
     *
     * @return ItemList
     */
    protected function modifyFilter(): self
    {
        return $this;
    }

    /**
     * Add additional filter rules to the list on the fly.
     *
     * @param IFilterRule $filterRule The filter rule to add.
     *
     * @return ItemList
     */
    public function addFilterRule(IFilterRule $filterRule): self
    {
        if (!$this->objFilter) {
            $this->objFilter = $this->getMetaModel()->getEmptyFilter();
        }

        $this->objFilter->addFilterRule($filterRule);

        return $this;
    }

    /**
     * Return all attributes that shall be fetched from the MetaModel.
     *
     * In this base implementation, this only includes the attributes mentioned in the render setting.
     *
     * @return list<string> the names of the attributes to be fetched.
     */
    protected function getAttributeNames(): array
    {
        $attributes = $this->getView()->getSettingNames();
        $metaModel  = $this->getMetaModel();
        /**
         * @psalm-suppress DeprecatedMethod
         * @psalm-suppress TooManyArguments
         */
        if ($metaModel instanceof ITranslatedMetaModel) {
            $desiredLanguage  = $metaModel->getLanguage();
            $fallbackLanguage = $metaModel->getMainLanguage();
            $isTranslated     = true;
        } elseif ($metaModel->isTranslated(false)) {
            // Get the right jumpTo.
            /** @psalm-suppress DeprecatedMethod */
            $desiredLanguage = $metaModel->getActiveLanguage();
            /** @psalm-suppress DeprecatedMethod */
            $fallbackLanguage = $metaModel->getFallbackLanguage();
            $isTranslated     = true;
        } else {
            $desiredLanguage  =
            $fallbackLanguage = System::getContainer()->get('request_stack')?->getCurrentRequest()?->getLocale();
            $isTranslated     = false;
        }

        $filterSettingsId = '';
        foreach ((array) $this->getView()->get('jumpTo') as $jumpTo) {
            $langCode = (string) ($jumpTo['langcode'] ?? '');
            // If either desired language or fallback, keep the result.
            /** @psalm-suppress DeprecatedMethod */
            if (
                $langCode === $desiredLanguage
                || $langCode === $fallbackLanguage
                || !$isTranslated
            ) {
                $filterSettingsId = (string) ($jumpTo['filter'] ?? '');
                // If the desired language, break. Otherwise, try to get the desired one until all have been evaluated.
                if ($desiredLanguage === $langCode) {
                    break;
                }
            }
        }

        if ('' !== $filterSettingsId) {
            $filterSettings = $this->getFilterFactory()->createCollection($filterSettingsId);
            $attributes     = array_merge($filterSettings->getReferencedAttributes(), $attributes);
        }

        return $attributes;
    }

    /**
     * Prepare the rendering.
     *
     * @return ItemList
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function prepare(): self
    {
        if (null !== $this->objItems) {
            return $this;
        }
        $metaModel        = $this->getMetaModel();
        $previousLanguage = $this->setLanguageInMetaModel($metaModel);

        // Create an empty filter object if not done before.
        if (!isset($this->objFilter)) {
            $this->objFilter = $metaModel->getEmptyFilter();
        }

        if (isset($this->objFilterSettings)) {
            $this->objFilterSettings->addRules($this->objFilter, $this->arrParam);
        }

        $this->modifyFilter();

        $total = $metaModel->getCount($this->objFilter);

        $calculator = $this->paginationLimitCalculator;
        $calculator->setTotalAmount($total);
        if (null !== $this->objTemplate) {
            $this->objTemplate->total = $total;
        }

        $this->objItems = $metaModel->findByFilter(
            $this->objFilter,
            $this->strSortBy,
            (int) $calculator->getCalculatedOffset(),
            (int) $calculator->getCalculatedLimit(),
            $this->strSortDirection,
            $this->getAttributeNames()
        );

        $this->resetLanguageInMetaModel($metaModel, $previousLanguage);

        return $this;
    }

    /**
     * Returns the pagination string.
     *
     * Remember to call prepare() first.
     *
     * @return string
     */
    public function getPagination(): string
    {
        return $this->paginationLimitCalculator->getPaginationString();
    }

    /**
     * Returns the item list in the view.
     *
     * @return IItems
     */
    public function getItems(): IItems
    {
        if (null === $this->objItems) {
            throw new RuntimeException('Call prepare first');
        }

        return $this->objItems;
    }

    /**
     * Returns the item list in the view.
     *
     * @return IItems
     *
     * @deprecated The method is deprecated and should not be used anymore.
     */
    public function getObjItems(): IItems
    {
        // @codingStandardsIgnoreStart
        @trigger_error(
            '"' .__METHOD__ . '" is deprecated - use \'getItems()\'.',
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd

        return $this->getItems();
    }

    /**
     * Returns the view.
     *
     * @return IRenderSettingCollection
     */
    public function getView(): IRenderSettingCollection
    {
        if (!$this->objView) {
            throw new RuntimeException('No render setting set - call prepareView() first.');
        }

        return $this->objView;
    }

    /**
     * Returns the MetaModel.
     *
     * @return IMetaModel
     */
    public function getMetaModel(): IMetaModel
    {
        if (!$this->objMetaModel) {
            throw new RuntimeException('No metamodel object set - call prepareMetaModel() first.');
        }

        return $this->objMetaModel;
    }

    /**
     * Retrieve the page object.
     *
     * @return object|null
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function getPage(): ?object
    {
        $isFrontend = (bool) System::getContainer()
            ->get('contao.routing.scope_matcher')
            ?->isFrontendRequest(
                System::getContainer()->get('request_stack')?->getCurrentRequest() ?? Request::create('')
            );

        return ($isFrontend && is_object($page = $GLOBALS['objPage'])) ? $page : null;
    }

    /**
     * Retrieve the output format used by this list.
     *
     * @return string
     */
    public function getOutputFormat(): string
    {
        if (null !== $this->outputFormat) {
            return $this->outputFormat;
        }

        if ('' !== ($format = (string) $this->objView?->get('format'))) {
            return $format;
        }

        $page = $this->getPage();

        if ($page) {
            return $page->outputFormat ?: 'html5';
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
     * @psalm-suppress PossiblyNullArrayOffset
     */
    private function getCaptionText(string $langKey): string
    {
        $tableName = $this->getMetaModel()->getTableName();
        if (
            null !== $this->objView
            && isset($GLOBALS['TL_LANG']['MSC'][$tableName][$this->objView->get('id')][$langKey])
        ) {
            return $GLOBALS['TL_LANG']['MSC'][$tableName][$this->objView->get('id')][$langKey];
        }
        if (null !== ($caption = $GLOBALS['TL_LANG']['MSC'][$tableName][$langKey] ?? null)) {
            return $caption;
        }

        $translator = System::getContainer()->get('translator');
        assert($translator instanceof TranslatorInterface);

        return $translator->trans($langKey, [], 'metamodels_list');
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
    protected function getNoItemsCaption(): string
    {
        return $this->getCaptionText('noItemsMsg');
    }

    /**
     * Set the title and description in the page object.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function setTitleAndDescription(): void
    {
        $page = $this->getPage();
        if ($page && null !== $this->objItems && $this->objItems->getCount()) {
            // Add title if needed.
            if (!empty($this->strTitleAttribute)) {
                while ($this->objItems->next()) {
                    /** @var IItem $currentItem */
                    $currentItem = $this->objItems->current();
                    $titles      = $currentItem->parseAttribute(
                        $this->strTitleAttribute,
                        'text',
                        $this->getView()
                    );

                    if (!empty($titles['text'])) {
                        $page->pageTitle = strip_tags($titles['text']);
                        break;
                    }
                }

                $this->objItems->reset();
            }

            // Add description if needed.
            if (!empty($this->strDescriptionAttribute)) {
                while ($this->objItems->next()) {
                    $currentItem    = $this->objItems->current();
                    $arrDescription = $currentItem->parseAttribute(
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
     * Setter for SortingLinkGenerator.
     *
     * @param SortingLinkGenerator $generator The link generator.
     *
     * @return $this
     */
    public function setSortingLinkGenerator(SortingLinkGenerator $generator): self
    {
        $this->sortingLinkGenerator = $generator;

        return $this;
    }

    /**
     * Render the list view.
     *
     * @param bool        $isNoNativeParsing Flag determining if the parsing shall be done internal or if the template
     *                                       will handle the parsing on its own.
     * @param object|null $caller            The object calling us, might be a Module or ContentElement or anything
     *                                       else.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function render(bool $isNoNativeParsing, object $caller = null): string
    {
        if (func_num_args() > 1) {
            trigger_error('Passing $objCaller as second argument is deprecated', E_USER_DEPRECATED);
            if ($caller instanceof ContentModel || $caller instanceof ModuleModel) {
                /** @psalm-suppress DeprecatedProperty */
                $this->model = $caller;
            }
        }

        if (null === $this->objTemplate) {
            return '';
        }

        $event = new RenderItemListEvent($this, $this->objTemplate, $caller);
        $this->getEventDispatcher()->dispatch($event, MetaModelsEvents::RENDER_ITEM_LIST);
        $this->objTemplate->noItemsMsg = $this->getNoItemsCaption();
        $this->objTemplate->details    = $this->getCaptionText('details');

        $this->prepare();
        $outputFormat = $this->getOutputFormat();

        if (!$isNoNativeParsing && null !== $this->objItems && $this->objItems->getCount()) {
            $metaModel = $this->getMetaModel();
            $previousLanguage = $this->setLanguageInMetaModel($metaModel);
            $this->objTemplate->data = $this->objItems->parseAll($outputFormat, $this->objView);
            $this->resetLanguageInMetaModel($metaModel, $previousLanguage);
            unset($previousLanguage);
        } else {
            $this->objTemplate->data = [];
        }

        $this->setTitleAndDescription();

        $generateSortingLink = function (string $attributeName, string $type): array {
            if (null === $this->sortingLinkGenerator) {
                return [];
            }

            $attribute = $this->getMetaModel()->getAttribute($attributeName);

            if (null === $attribute) {
                throw new RuntimeException('Attribute not found: ' . $attributeName);
            }

            return $this->sortingLinkGenerator->generateSortingLink($attribute, $type);
        };

        $renderSortingLink = static function (string $attributeName, string $type) use ($generateSortingLink): string {
            if ([] === $sortingLink = $generateSortingLink($attributeName, $type)) {
                return '';
            }

            return <<<EOF
                <a href="{$sortingLink['href']}" class="{$sortingLink['class']}"
                   data-escargot-ignore rel="nofollow">{$sortingLink['label']}</a>
            EOF;
        };

        $this->objTemplate->generateSortingLink = $generateSortingLink;
        $this->objTemplate->renderSortingLink   = $renderSortingLink;
        $this->objTemplate->caller              = $caller;
        $this->objTemplate->items               = $this->objItems;
        $this->objTemplate->filterParams        = $this->arrParam;
        $this->objTemplate->parameter           = $this->templateParameter;

        return $this->objTemplate->parse($outputFormat);
    }

    /**
     * @param IMetaModel $metaModel
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public function setLanguageInMetaModel(IMetaModel $metaModel): ?string
    {
        if (!$metaModel instanceof ITranslatedMetaModel) {
            return null;
        }
        if (null === $this->language) {
            // @codingStandardsIgnoreStart
            @trigger_error(
                sprintf(
                    'Not setting a language code in "%s" is deprecated since MetaModels 2.3 and will fail in 3.0',
                    __CLASS__
                ),
                E_USER_DEPRECATED
            );
            // @codingStandardsIgnoreEnd

            // @deprecated usage of TL_LANGUAGE - remove for Contao 5.0.
            $this->language = LocaleUtil::formatAsLocale($GLOBALS['TL_LANGUAGE'] ?? 'en');
        }

        return $metaModel->selectLanguage($this->language);
    }

    private function resetLanguageInMetaModel(IMetaModel $metaModel, ?string $previousLanguage): void
    {
        if ((null === $previousLanguage) || !$metaModel instanceof ITranslatedMetaModel) {
            return;
        }

        $metaModel->selectLanguage($previousLanguage);
    }
}
