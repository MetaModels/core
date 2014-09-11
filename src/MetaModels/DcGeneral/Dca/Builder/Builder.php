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
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\DcGeneral\Dca\Builder;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\ResizeImageEvent;
use ContaoCommunityAlliance\Contao\Bindings\Events\System\LoadLanguageFileEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetOperationButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPasteButtonEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultModelRelationshipDefinition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\ModelRelationshipDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CommandInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CopyCommand;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CutCommand;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingInformationInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel\DefaultFilterElementInformation;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel\DefaultLimitElementInformation;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel\DefaultSearchElementInformation;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel\DefaultSortElementInformation;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel\DefaultSubmitElementInformation;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Panel\SubmitElementInformationInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\PanelRowCollectionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\PanelRowInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\FilterBuilder;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\ParentChildCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\ParentChildConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\RootCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\RootConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DcGeneralEvents;
use ContaoCommunityAlliance\DcGeneral\Event\PostDuplicateModelEvent;
use ContaoCommunityAlliance\Translator\StaticTranslator;
use ContaoCommunityAlliance\Translator\TranslatorChain;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinition;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\Dca\ContaoDataProviderInformation;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DataProviderDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultBasicDefinition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultDataProviderDefinition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultPalettesDefinition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultPropertiesDefinition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\DefaultProperty;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\PalettesDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\PropertiesDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Command;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CommandCollectionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\DefaultModelFormatterConfig;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\ListingConfigInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette\DefaultPaletteCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\BooleanCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionChain;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyValueCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Legend;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Palette;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Property;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\PopulateEnvironmentEvent;
use MetaModels\BackendIntegration\InputScreen\IInputScreen;
use MetaModels\BackendIntegration\ViewCombinations;
use MetaModels\DcGeneral\DataDefinition\Definition\MetaModelDefinition;
use MetaModels\DcGeneral\DataDefinition\IMetaModelDataDefinition;
use MetaModels\DcGeneral\DataDefinition\Palette\Condition\Property\IsVariantAttribute;
use MetaModels\DcGeneral\Events\MetaModel\RenderItem;
use MetaModels\Events\BuildAttributeEvent;
use MetaModels\Events\PopulateAttributeEvent;
use MetaModels\Factory;
use MetaModels\Helper\ToolboxFile;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Build the container config from MetaModels information.
 */
class Builder
{
    const PRIORITY = 50;

    /**
     * The translator instance this builder adds values to.
     *
     * @var StaticTranslator
     */
    protected $translator;

    /**
     * The event dispatcher currently in use.
     *
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * Create a new instance and instantiate the translator.
     */
    public function __construct()
    {
        $this->translator = new StaticTranslator();
    }

    /**
     * Map all translation values from the given array to the given destination domain using the optional base key.
     *
     * @param array  $array   The array holding the translation values.
     *
     * @param string $domain  The target domain.
     *
     * @param string $baseKey The base key to prepend the values of the array with.
     *
     * @return void
     */
    protected function mapTranslations($array, $domain, $baseKey = '')
    {
        foreach ($array as $key => $value) {
            $newKey = ($baseKey ? $baseKey . '.' : '') . $key;
            if (is_array($value)) {
                $this->mapTranslations($value, $domain, $newKey);
            } else {
                $this->translator->setValue($newKey, $value, $domain);
            }
        }
    }

    /**
     * Handle a populate environment event for MetaModels.
     *
     * @param PopulateEnvironmentEvent $event      The event payload.
     *
     * @param string                   $eventName  The name of the called event.
     *
     * @param EventDispatcherInterface $dispatcher The event dispatcher.
     *
     * @return void
     */
    public function populate(PopulateEnvironmentEvent $event, $eventName, $dispatcher)
    {
        $container = $event->getEnvironment()->getDataDefinition();

        if (!($container instanceof IMetaModelDataDefinition)) {
            return;
        }

        $this->dispatcher = $dispatcher;

        $translator = $event->getEnvironment()->getTranslator();

        if (!$translator instanceof TranslatorChain) {
            $translatorChain = new TranslatorChain();
            $translatorChain->add($translator);
            $event->getEnvironment()->setTranslator($translatorChain);
        } else {
            $translatorChain = $translator;
        }

        // Map the tl_metamodel_item domain over to this domain.
        $this->dispatcher->dispatch(
            ContaoEvents::SYSTEM_LOAD_LANGUAGE_FILE,
            new LoadLanguageFileEvent('tl_metamodel_item')
        );

        $this->mapTranslations(
            $GLOBALS['TL_LANG']['tl_metamodel_item'],
            $event->getEnvironment()->getDataDefinition()->getName()
        );

        $translatorChain->add($this->translator);

        $metaModel   = $this->getMetaModel($container);
        $environment = $event->getEnvironment();
        foreach ($metaModel->getAttributes() as $attribute) {
            $event = new PopulateAttributeEvent($metaModel, $attribute, $environment);
            // Trigger BuildAttribute Event.
            $this->dispatcher->dispatch($event::NAME, $event);
        }

        $this->dispatcher->addListener(
            sprintf(
                '%s[%s][%s]',
                GetOperationButtonEvent::NAME,
                $metaModel->getTableName(),
                'createvariant'
            ),
            'MetaModels\DcGeneral\Events\MetaModel\CreateVariantButton::createButton'
        );

        $this->dispatcher->addListener(
            sprintf(
                '%s[%s]',
                GetPasteButtonEvent::NAME,
                $metaModel->getTableName()
            ),
            'MetaModels\DcGeneral\Events\MetaModel\PasteButton::handle'
        );

        // Add some sepcial actions for variants.
        if ($metaModel->hasVariants()) {
            $this->dispatcher->addListener(
                sprintf(
                    '%s[%s]',
                    PostDuplicateModelEvent::NAME,
                    $metaModel->getTableName()
                ),
                'MetaModels\DcGeneral\Events\MetaModel\DuplicateModel::handle'
            );

            $this->dispatcher->addListener(
                sprintf(
                    '%s[%s][%s]',
                    DcGeneralEvents::ACTION,
                    $metaModel->getTableName(),
                    'createvariant'
                ),
                'MetaModels\DcGeneral\Events\MetaModel\CreateVariantButton::handleCreateVariantAction'
            );
        }
    }

    /**
     * Return the input screen details.
     *
     * @param IMetaModelDataDefinition $container The data container.
     *
     * @return IInputScreen
     */
    protected function getInputScreenDetails(IMetaModelDataDefinition $container)
    {
        return ViewCombinations::getInputScreenDetails($container->getName());
    }

    /**
     * Retrieve the MetaModel for the data container.
     *
     * @param IMetaModelDataDefinition $container The data container.
     *
     * @return \MetaModels\IMetaModel|null
     */
    protected function getMetaModel(IMetaModelDataDefinition $container)
    {
        return Factory::byTableName($container->getName());
    }

    /**
     * Retrieve the data provider definition.
     *
     * @param IMetaModelDataDefinition $container The data container.
     *
     * @return DataProviderDefinitionInterface|DefaultDataProviderDefinition
     */
    protected function getDataProviderDefinition(IMetaModelDataDefinition $container)
    {
        // Parse data provider.
        if ($container->hasDataProviderDefinition()) {
            return $container->getDataProviderDefinition();
        }

        $config = new DefaultDataProviderDefinition();
        $container->setDataProviderDefinition($config);
        return $config;
    }

    /**
     * Handle a build data definition event for MetaModels.
     *
     * @param BuildDataDefinitionEvent $event      The event payload.
     *
     * @param string                   $eventName  The name of the called event.
     *
     * @param EventDispatcherInterface $dispatcher The event dispatcher.
     *
     * @return void
     */
    public function build(BuildDataDefinitionEvent $event, $eventName, $dispatcher)
    {
        $this->dispatcher = $dispatcher;
        $container        = $event->getContainer();

        if (!($container instanceof IMetaModelDataDefinition)) {
            return;
        }

        $this->parseMetaModelDefinition($container);
        $this->parseProperties($container);
        $this->parseBasicDefinition($container);
        $this->parseDataProvider($container);
        $this->parseBackendView($container);
        $this->parsePanels($container);

        $this->parsePalettes($container);

        // Attach renderer to event.
        RenderItem::register($dispatcher);
    }

    /**
     * Ensure at least one submit element is present in any of the rows.
     *
     * If no submit element is present, this method will create one at the end of the last row.
     *
     * @param PanelRowCollectionInterface $panelRows The panel rows.
     *
     * @return void
     */
    protected function ensureSubmitElement($panelRows)
    {
        // Check if we have a submit button.
        $hasSubmit = false;
        foreach ($panelRows as $panelRow) {
            foreach ($panelRow as $element) {
                if ($element instanceof SubmitElementInformationInterface) {
                    $hasSubmit = true;
                    break;
                }

                if ($hasSubmit) {
                    break;
                }
            }
        }

        // If not add a submit.
        if (!$hasSubmit && $panelRows->getRowCount()) {
            $row = $panelRows->getRow($panelRows->getRowCount() - 1);
            $row->addElement(new DefaultSubmitElementInformation(), 0);
        }
    }

    /**
     * Parse the panels, if we have some one.
     *
     * @param IMetaModelDataDefinition $container The panel container.
     *
     * @return void
     */
    protected function parsePanels(IMetaModelDataDefinition $container)
    {
        // Check if we have a BackendViewDef.
        if ($container->hasDefinition(Contao2BackendViewDefinitionInterface::NAME)) {
            /** @var Contao2BackendViewDefinitionInterface $view */
            $view = $container->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
        } else {
            return;
        }

        // Get the panel layout.
        $inputScreen = $this->getInputScreenDetails($container);
        $panelLayout = $inputScreen->getPanelLayout();

        // Check if we have a layout.
        if (empty($panelLayout)) {
            return;
        }

        // Get the layout from the dca.
        $arrRows = trimsplit(';', $panelLayout);

        // Create a new panel container.
        $panel     = $view->getPanelLayout();
        $panelRows = $panel->getRows();

        foreach ($arrRows as $rowNo => $rowElements) {
            // Get the row, if we have one or create a new one.
            if ($panelRows->getRowCount() < ($rowNo + 1)) {
                $panelRow = $panelRows->addRow();
            } else {
                $panelRow = $panelRows->getRow($rowNo);
            }

            // Get the fields.
            $fields = trimsplit(',', $rowElements);
            $fields = array_reverse($fields);

            $this->parsePanelRow($fields, $panelRow, $inputScreen);

            // If we have no entries for this row, remove it.
            if ($panelRow->getCount() == 0) {
                $panelRows->deleteRow($rowNo);
            }
        }

        $this->ensureSubmitElement($panelRows);
    }

    /**
     * Parse a single row with all elements.
     *
     * @param array             $fields      A list of fields for adding to the row.
     *
     * @param PanelRowInterface $panelRow    The row container itself.
     *
     * @param IInputScreen      $inputScreen The input screen with information about the attributes.
     *
     * @return void
     */
    protected function parsePanelRow($fields, PanelRowInterface $panelRow, IInputScreen $inputScreen)
    {
        // Parse each type.
        foreach ($fields as $field) {
            switch ($field)
            {
                case 'sort':
                    $this->parsePanelSort($panelRow, $inputScreen);
                    break;

                case 'limit':
                    $this->parsePanelLimit($panelRow);
                    break;

                case 'filter':
                    $this->parsePanelFilter($panelRow, $inputScreen);
                    break;

                case 'search':
                    $this->parsePanelSearch($panelRow, $inputScreen);
                    break;

                case 'submit':
                    $this->parsePanelSubmit($panelRow);
                    break;

                default:
                    break;
            }
        }
    }

    /**
     * Add filter elements to the panel.
     *
     * @param PanelRowInterface $row         The row to which the element shall get added to.
     *
     * @param IInputScreen      $inputScreen The Input screen with some information.
     *
     * @return void
     */
    protected function parsePanelFilter(PanelRowInterface $row, IInputScreen $inputScreen)
    {
        foreach ($inputScreen->getProperties() as $property => $value) {
            if (isset($value['info']['filter'])) {
                $element = new DefaultFilterElementInformation();
                $element->setPropertyName($property);
                if (!$row->hasElement($element->getName())) {
                    $row->addElement($element);
                }
            }
        }
    }

    /**
     * Add sort element to the panel.
     *
     * @param PanelRowInterface $row         The row to which the element shall get added to.
     *
     * @param IInputScreen      $inputScreen The Input screen with some information.
     *
     * @return void
     */
    protected function parsePanelSort(PanelRowInterface $row, IInputScreen $inputScreen)
    {
        if (!$row->hasElement('sort')) {
            $element = new DefaultSortElementInformation();
            $row->addElement($element);
        }
    }

    /**
     * Add search element to the panel.
     *
     * @param PanelRowInterface $row         The row to which the element shall get added to.
     *
     * @param IInputScreen      $inputScreen The Input screen with some information.
     *
     * @return void
     */
    protected function parsePanelSearch(PanelRowInterface $row, IInputScreen $inputScreen)
    {
        if ($row->hasElement('search')) {
            $element = $row->getElement('search');
        } else {
            $element = new DefaultSearchElementInformation();
        }

        foreach ($inputScreen->getProperties() as $property => $value) {
            if (isset($value['info']['search'])) {
                $element->addProperty($property);
            }
        }

        if ($element->getPropertyNames() && !$row->hasElement('search')) {
            $row->addElement($element);
        }
    }

    /**
     * Add  elements to the panel.
     *
     * @param PanelRowInterface $row The row to which the element shall get added to.
     *
     * @return void
     */
    protected function parsePanelLimit(PanelRowInterface $row)
    {
        if (!$row->hasElement('limit')) {
            $row->addElement(new DefaultLimitElementInformation());
        }
    }

    /**
     * Add  elements to the panel.
     *
     * @param PanelRowInterface $row The row to which the element shall get added to.
     *
     * @return void
     */
    protected function parsePanelSubmit(PanelRowInterface $row)
    {
        if (!$row->hasElement('submit')) {
            $row->addElement(new DefaultSubmitElementInformation());
        }
    }

    /**
     * Parse the basic configuration and populate the definition.
     *
     * @param IMetaModelDataDefinition $container The data container.
     *
     * @return void
     */
    protected function parseMetaModelDefinition(IMetaModelDataDefinition $container)
    {
        if ($container->hasMetaModelDefinition()) {
            $definition = $container->getMetaModelDefinition();
        } else {
            $definition = new MetaModelDefinition();
            $container->setMetaModelDefinition($definition);
        }

        if (!$definition->hasActiveRenderSetting()) {
            $definition->setActiveRenderSetting(ViewCombinations::getRenderSetting($container->getName()));
        }

        if (!$definition->hasActiveInputScreen()) {
            $definition->setActiveInputScreen(ViewCombinations::getInputScreen($container->getName()));
        }
    }

    /**
     * Parse the basic configuration and populate the definition.
     *
     * @param IMetaModelDataDefinition $container The data container.
     *
     * @return void
     */
    protected function parseBasicDefinition(IMetaModelDataDefinition $container)
    {
        if ($container->hasBasicDefinition()) {
            $config = $container->getBasicDefinition();
        } else {
            $config = new DefaultBasicDefinition();
            $container->setBasicDefinition($config);
        }

        $config->setDataProvider($container->getName());

        $inputScreen = $this->getInputScreenDetails($container);

        if ($inputScreen->isHierarchical()) {
            // Hierarchical mode - Records are displayed as tree (see site structure).
            $config->setMode(BasicDefinitionInterface::MODE_HIERARCHICAL);
        } elseif ($inputScreen->isParented()) {
            // Displays the child records of a parent record (see style sheets module).
            $config->setMode(BasicDefinitionInterface::MODE_PARENTEDLIST);
        } elseif ($inputScreen->isFlat()) {
            // Flat mode.
            $config->setMode(BasicDefinitionInterface::MODE_FLAT);
        }

        $config
            ->setEditable($inputScreen->isEditable())
            ->setCreatable($inputScreen->isCreatable())
            ->setDeletable($inputScreen->isDeletable());

        $this->calculateConditions($container);
    }

    /**
     * Parse the correct conditions.
     *
     * @param IMetaModelDataDefinition $container The data container.
     *
     * @return void
     */
    protected function calculateConditions(IMetaModelDataDefinition $container)
    {
        if ($container->hasDefinition(ModelRelationshipDefinitionInterface::NAME)) {
            $definition = $container->getDefinition(ModelRelationshipDefinitionInterface::NAME);
        } else {
            $definition = new DefaultModelRelationshipDefinition();

            $container->setDefinition(ModelRelationshipDefinitionInterface::NAME, $definition);
        }

        if ($this->getMetaModel($container)->hasVariants()) {
            $this->calculateConditionsWithVariants($container, $definition);
        } else {
            $this->calculateConditionsWithoutVariants($container, $definition);
        }
    }

    /**
     * Parse the correct conditions for a MetaModel with variant support.
     *
     * @param IMetaModelDataDefinition             $container  The data container.
     *
     * @param ModelRelationshipDefinitionInterface $definition The relationship container.
     *
     * @return RootConditionInterface
     */
    protected function getRootCondition($container, $definition)
    {
        $rootProvider = $container->getName();

        if (($relationship = $definition->getRootCondition()) === null) {
            $relationship = new RootCondition();
            $relationship
                ->setSourceName($rootProvider);
            $definition->setRootCondition($relationship);
        }

        return $relationship;
    }

    /**
     * Parse the correct conditions for a MetaModel with variant support.
     *
     * @param IMetaModelDataDefinition             $container  The data container.
     *
     * @param ModelRelationshipDefinitionInterface $definition The relationship container.
     *
     * @return void
     */
    protected function addHierarchicalConditions(IMetaModelDataDefinition $container, $definition)
    {
        // Not hierarchical? Get out.
        if ($container->getBasicDefinition()->getMode() !== BasicDefinitionInterface::MODE_HIERARCHICAL) {
            return;
        }

        $relationship = $this->getRootCondition($container, $definition);

        if (!$relationship->getSetters()) {
            $relationship
                ->setSetters(array(array('property' => 'pid', 'value' => '0')));
        }

        $builder = FilterBuilder::fromArrayForRoot((array)$relationship->getFilterArray())->getFilter();

        $builder->andPropertyEquals('pid', 0);

        $relationship
            ->setFilterArray($builder->getAllAsArray());

        $setter  = array(array('to_field' => 'pid', 'from_field' => 'id'));
        $inverse = array();

        /** @var ParentChildConditionInterface $relationship */
        $relationship = $definition->getChildCondition($container->getName(), $container->getName());
        if ($relationship === null) {
            $relationship = new ParentChildCondition();
            $relationship
                ->setSourceName($container->getName())
                ->setDestinationName($container->getName());
            $definition->addChildCondition($relationship);
        } else {
            $setter  = array_merge_recursive($setter, $relationship->getSetters());
            $inverse = array_merge_recursive($inverse, $relationship->getInverseFilterArray());
        }

        // For tl_ prefix, the only unique target can be the id?
        // maybe load parent dc and scan for unique in config then.
        $relationship
            ->setFilterArray(
                FilterBuilder::fromArray($relationship->getFilterArray())
                    ->getFilter()
                    ->andRemotePropertyEquals('pid', 'id')
                    ->getAllAsArray()
            )
            ->setSetters($setter)
            ->setInverseFilterArray($inverse);
    }

    /**
     * Parse the correct conditions for a MetaModel with variant support.
     *
     * @param IMetaModelDataDefinition             $container  The data container.
     *
     * @param ModelRelationshipDefinitionInterface $definition The relationship container.
     *
     * @return void
     */
    protected function addParentCondition(IMetaModelDataDefinition $container, $definition)
    {
        $inputScreen = $this->getInputScreenDetails($container);

        if ($this->getInputScreenDetails($container)->isStandalone()) {
            return;
        }

        $setter  = array(array('to_field' => 'pid', 'from_field' => 'id'));
        $inverse = array();

        /** @var ParentChildConditionInterface $relationship */
        $relationship = $definition->getChildCondition($inputScreen->getParentTable(), $container->getName());
        if (!$relationship instanceof ParentChildConditionInterface) {
            $relationship = new ParentChildCondition();
            $relationship
                ->setSourceName($inputScreen->getParentTable())
                ->setDestinationName($container->getName());
            $definition->addChildCondition($relationship);
        } else {
            $setter  = array_merge_recursive($setter, $relationship->getSetters());
            $inverse = array_merge_recursive($inverse, $relationship->getInverseFilterArray());
        }

        // For tl_ prefix, the only unique target can be the id?
        // maybe load parent dc and scan for unique in config then.
        $relationship
            ->setFilterArray(
                FilterBuilder::fromArray($relationship->getFilterArray())
                    ->getFilter()
                    ->andRemotePropertyEquals('pid', 'id')
                    ->getAllAsArray()
            )
            ->setSetters($setter)
            ->setInverseFilterArray($inverse);
    }

    /**
     * Parse the correct conditions for a MetaModel with variant support.
     *
     * @param IMetaModelDataDefinition             $container  The data container.
     *
     * @param ModelRelationshipDefinitionInterface $definition The relationship container.
     *
     * @return void
     */
    protected function calculateConditionsWithVariants(IMetaModelDataDefinition $container, $definition)
    {
        // Basic conditions.
        $this->addHierarchicalConditions($container, $definition);
        $this->addParentCondition($container, $definition);

        // Conditions for metamodels variants.
        $relationship = $this->getRootCondition($container, $definition);
        $relationship->setSetters(array_merge_recursive(
            array(array('property' => 'varbase', 'value' => '1')),
            $relationship->getSetters()
        ));

        $builder = FilterBuilder::fromArrayForRoot((array)$relationship->getFilterArray())->getFilter();

        $builder->andPropertyEquals('varbase', 1);

        $relationship->setFilterArray($builder->getAllAsArray());

        $setter  = array(
            array('to_field' => 'varbase', 'value' => '0'),
            array('to_field' => 'vargroup', 'from_field' => 'vargroup')
        );
        $inverse = array();

        /** @var ParentChildConditionInterface $relationship */
        $relationship = $definition->getChildCondition($container->getName(), $container->getName());

        if ($relationship === null) {
            $relationship = new ParentChildCondition();
            $relationship
                ->setSourceName($container->getName())
                ->setDestinationName($container->getName());
            $definition->addChildCondition($relationship);
        } else {
            $setter  = array_merge_recursive($setter, $relationship->getSetters());
            $inverse = array_merge_recursive($inverse, $relationship->getInverseFilterArray());
        }

        $relationship
            ->setFilterArray(
                FilterBuilder::fromArray($relationship->getFilterArray())
                    ->getFilter()
                    ->getBuilder()
                    ->encapsulateOr()
                        ->andRemotePropertyEquals('vargroup', 'vargroup')
                        ->andRemotePropertyEquals('vargroup', 'id')
                        ->andRemotePropertyEquals('varbase', 0, true)
                    ->getAllAsArray()
            )
            ->setSetters($setter)
            ->setInverseFilterArray($inverse);
    }

    /**
     * Parse the correct conditions for a MetaModel with variant support.
     *
     * @param IMetaModelDataDefinition             $container  The data container.
     *
     * @param ModelRelationshipDefinitionInterface $definition The relationship container.
     *
     * @return void
     *
     * @throws \RuntimeException When the conditions can not be determined yet.
     */
    protected function calculateConditionsWithoutVariants(IMetaModelDataDefinition $container, $definition)
    {
        $inputScreen = $this->getInputScreenDetails($container);
        if (!$inputScreen->isStandalone()) {
            if ($container->getBasicDefinition()->getMode() == BasicDefinitionInterface::MODE_HIERARCHICAL) {
                // FIXME: if parent table is not the same table, we are screwed here.
                throw new \RuntimeException('Hierarchical mode with parent table is not supported yet.');
            }
        }

        $this->addHierarchicalConditions($container, $definition);
        $this->addParentCondition($container, $definition);
    }

    /**
     * Create the data provider definition in the container if not already set.
     *
     * @param IMetaModelDataDefinition $container The data container.
     *
     * @return void
     */
    protected function parseDataProvider(IMetaModelDataDefinition $container)
    {
        $config = $this->getDataProviderDefinition($container);

        // Check config if it already exists, if not, add it.
        if (!$config->hasInformation($container->getName())) {
            $providerInformation = new ContaoDataProviderInformation();
            $providerInformation->setName($container->getName());
            $config->addInformation($providerInformation);
        } else {
            $providerInformation = $config->getInformation($container->getName());
        }

        if ($providerInformation instanceof ContaoDataProviderInformation) {
            $providerInformation
                ->setTableName($container->getName())
                ->setClassName('MetaModels\DcGeneral\Data\Driver')
                ->setInitializationData(array(
                    'source' => $container->getName()
                ))
                ->isVersioningEnabled(false);
            $container->getBasicDefinition()->setDataProvider($container->getName());
        }

        // If in hierarchical mode, set the root provider.
        if ($container->getBasicDefinition()->getMode() == BasicDefinitionInterface::MODE_HIERARCHICAL) {
            $container->getBasicDefinition()->setRootDataProvider($container->getName());
        }

        // If not standalone, set the correct parent provider.
        if (!$this->getInputScreenDetails($container)->isStandalone()) {
            $inputScreen = $this->getInputScreenDetails($container);

            // Check config if it already exists, if not, add it.
            if (!$config->hasInformation($inputScreen->getParentTable())) {
                $providerInformation = new ContaoDataProviderInformation();
                $providerInformation->setName($inputScreen->getParentTable());
                $config->addInformation($providerInformation);
            } else {
                $providerInformation = $config->getInformation($inputScreen->getParentTable());
            }

            if ($providerInformation instanceof ContaoDataProviderInformation) {
                $providerInformation
                    ->setTableName($inputScreen->getParentTable())
                    ->setInitializationData(
                        array(
                            'source' => $inputScreen->getParentTable()
                        )
                    );

                // How can we honor other drivers? We do only check for MetaModels and legacy SQL here.
                if (in_array($inputScreen->getParentTable(), Factory::getAllTables())) {
                    $providerInformation
                        ->setClassName('MetaModels\DcGeneral\Data\Driver');
                }

                $container->getBasicDefinition()->setParentDataProvider($inputScreen->getParentTable());
            }
        }
    }

    /**
     * Parse and build the backend view definition for the old Contao2 backend view.
     *
     * @param IMetaModelDataDefinition $container The data container.
     *
     * @throws DcGeneralInvalidArgumentException When the contained view definition is of invalid type.
     *
     * @return void
     */
    protected function parseBackendView(IMetaModelDataDefinition $container)
    {
        if ($container->hasDefinition(Contao2BackendViewDefinitionInterface::NAME)) {
            $view = $container->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
        } else {
            $view = new Contao2BackendViewDefinition();
            $container->setDefinition(Contao2BackendViewDefinitionInterface::NAME, $view);
        }

        if (!$view instanceof Contao2BackendViewDefinitionInterface) {
            throw new DcGeneralInvalidArgumentException(
                'Configured BackendViewDefinition does not implement Contao2BackendViewDefinitionInterface.'
            );
        }

        $this->parseListing($container, $view);
        $this->parseModelOperations($view, $container);
    }


    /**
     * Parse the listing configuration.
     *
     * @param IMetaModelDataDefinition              $container The data container.
     *
     * @param Contao2BackendViewDefinitionInterface $view      The view definition.
     *
     * @return void
     */
    protected function parseListing(IMetaModelDataDefinition $container, Contao2BackendViewDefinitionInterface $view)
    {
        $listing = $view->getListingConfig();

        if ($listing->getRootLabel() === null) {
            $listing->setRootLabel($this->getMetaModel($container)->get('name'));
        }

        if (($listing->getRootIcon() === null)
            && (($inputScreen = $this->getInputScreenDetails($container)) !== null)
        ) {
            $icon = ToolboxFile::convertValueToPath($inputScreen->getIcon());
            // Determine image to use.
            if ($icon && file_exists(TL_ROOT . '/' . $icon)) {
                $event = new ResizeImageEvent($icon, 16, 16);
                $this->dispatcher->dispatch(ContaoEvents::IMAGE_RESIZE, $event);
                $icon = $event->getResultImage();
            } else {
                $icon = 'system/modules/metamodels/assets/images/icons/metamodels.png';
            }

            $listing->setRootIcon($icon);
        }

        $this->parseListSorting($container, $listing);
        $this->parseListLabel($container, $listing);
    }

    /**
     * Generate a 16x16 pixel version of the passed image file. If this can not be done, the default image is returned.
     *
     * @param string $icon The name of the image file.
     *
     * @return null|string
     */
    public function getBackendIcon($icon)
    {
        // Determine the image to use.
        if ($icon) {
            $icon = ToolboxFile::convertValueToPath($icon);

            /** @var ResizeImageEvent $event */
            $event = $this->dispatcher->dispatch(
                ContaoEvents::IMAGE_RESIZE,
                new ResizeImageEvent($icon, 16, 16)
            );

            if (file_exists(TL_ROOT . '/' . $event->getResultImage())) {
                return $event->getResultImage();
            }
        }

        return 'system/modules/metamodels/assets/images/icons/metamodels.png';
    }

    /**
     * Convert a render group type from InputScreen value to GroupAndSortingInformationInterface value.
     *
     * @param string $type The group type.
     *
     * @return string
     */
    protected function convertRenderGroupType($type)
    {
        switch ($type) {
            case 'char':
                return GroupAndSortingInformationInterface::GROUP_CHAR;

            case 'digit':
                return GroupAndSortingInformationInterface::GROUP_DIGIT;

            case 'day':
                return GroupAndSortingInformationInterface::GROUP_DAY;

            case 'weekday':
                return GroupAndSortingInformationInterface::GROUP_WEEKDAY;

            case 'week':
                return GroupAndSortingInformationInterface::GROUP_WEEK;

            case 'month':
                return GroupAndSortingInformationInterface::GROUP_MONTH;

            case 'year':
                return GroupAndSortingInformationInterface::GROUP_YEAR;

            default:
        }

        return GroupAndSortingInformationInterface::GROUP_NONE;
    }

    /**
     * Parse the sorting part of listing configuration.
     *
     * @param IMetaModelDataDefinition $container The data container.
     *
     * @param ListingConfigInterface   $listing   The listing configuration.
     *
     * @return void
     */
    protected function parseListSorting(IMetaModelDataDefinition $container, ListingConfigInterface $listing)
    {
        $inputScreen = ViewCombinations::getInputScreenDetails($container->getName());

        $listing->setRootIcon($this->getBackendIcon($inputScreen->getIcon()));

        $definitions = $listing->getGroupAndSortingDefinition();
        foreach ($inputScreen->getGroupingAndSorting() as $information) {
            $definition = $definitions->add();
            $definition->setName($information->getName());
            if ($information->isDefault() && !$definitions->hasDefault()) {
                $definitions->markDefault($definition);
            }

            if ($information->isManualSorting()) {
                $propertyInformation = $definition->add();
                $propertyInformation
                    ->setProperty('sorting')
                    ->setSortingMode('ASC');
            } elseif ($information->getRenderSortAttribute()) {
                $propertyInformation = $definition->add();
                $propertyInformation
                    ->setProperty($information->getRenderSortAttribute())
                    ->setSortingMode($information->getRenderSortDirection());
            }

            if ($information->getRenderGroupAttribute()) {
                $propertyInformation = $definition->add(0);
                $propertyInformation
                    ->setProperty($information->getRenderGroupAttribute())
                    ->setGroupingMode($this->convertRenderGroupType($information->getRenderGroupType()))
                    ->setGroupingLength($information->getRenderGroupLength());
            }
        }
    }

    /**
     * Parse the sorting part of listing configuration.
     *
     * @param IMetaModelDataDefinition $container The data container.
     *
     * @param ListingConfigInterface   $listing   The listing config.
     *
     * @return void
     */
    protected function parseListLabel(IMetaModelDataDefinition $container, ListingConfigInterface $listing)
    {
        $providerName = $container->getBasicDefinition()->getDataProvider();
        if (!$listing->hasLabelFormatter($providerName)) {
            $formatter = new DefaultModelFormatterConfig();
            $listing->setLabelFormatter($container->getBasicDefinition()->getDataProvider(), $formatter);
        } else {
            $formatter = $listing->getLabelFormatter($providerName);
        }

        $formatter->setPropertyNames(
            array_merge(
                $formatter->getPropertyNames(),
                $container->getPropertiesDefinition()->getPropertyNames()
            )
        );

        if (!$formatter->getFormat()) {
            $formatter->setFormat(str_repeat('%s ', count($formatter->getPropertyNames())));
        }
    }

    /**
     * Retrieve or create a command instance of the given name.
     *
     * @param CommandCollectionInterface $collection    The command collection.
     *
     * @param string                     $operationName The name of the operation.
     *
     * @return CommandInterface
     */
    protected function getCommandInstance(CommandCollectionInterface $collection, $operationName)
    {
        if ($collection->hasCommandNamed($operationName)) {
            $command = $collection->getCommandNamed($operationName);
        } else {
            switch ($operationName) {
                case 'cut':
                    $command = new CutCommand();
                    break;

                case 'copy':
                    $command = new CopyCommand();
                    break;

                default:
                    $command = new Command();
            }

            $command->setName($operationName);
            $collection->addCommand($command);
        }

        return $command;
    }

    /**
     * Build a command into the the command collection.
     *
     * @param CommandCollectionInterface $collection      The command collection.
     *
     * @param string                     $operationName   The operation name.
     *
     * @param array                      $queryParameters The query parameters for the operation.
     *
     * @param string                     $icon            The icon to use in the backend.
     *
     * @param array                      $extraValues     The extra values for the command.
     *
     * @return Builder
     */
    protected function createCommand(
        CommandCollectionInterface $collection,
        $operationName,
        $queryParameters,
        $icon,
        $extraValues
    ) {
        $command    = $this->getCommandInstance($collection, $operationName);
        $parameters = $command->getParameters();
        foreach ($queryParameters as $name => $value) {
            if (!isset($parameters[$name])) {
                $parameters[$name] = $value;
            }
        }

        if (!$command->getLabel() && !isset($extraValues['label'])) {
            $command->setLabel($operationName . '.0');
        } elseif (!$command->getLabel() && isset($extraValues['label'])) {
            $command->setLabel($extraValues['label']);
        }

        if (!$command->getDescription() && !isset($extraValues['description'])) {
            $command->setDescription($operationName . '.1');
        } elseif (!$command->getDescription() && isset($extraValues['description'])) {
            $command->setDescription($extraValues['description']);
        }

        $extra         = $command->getExtra();
        $extra['icon'] = $icon;

        foreach ($extraValues as $name => $value) {
            if (!isset($extra[$name])) {
                $extra[$name] = $value;
            }
        }

        return $this;
    }

    /**
     * Parse the defined model scoped operations and populate the definition.
     *
     * @param Contao2BackendViewDefinitionInterface $view      The backend view information.
     *
     * @param IMetaModelDataDefinition              $container The data container.
     *
     * @return void
     */
    protected function parseModelOperations(
        Contao2BackendViewDefinitionInterface $view,
        IMetaModelDataDefinition $container
    ) {
        $collection = $view->getModelCommands();
        $this->createCommand(
            $collection,
            'edit',
            array('act' => 'edit'),
            'edit.gif',
            array()
        )
        ->createCommand(
            $collection,
            'copy',
            array('act' => ''),
            'copy.gif',
            array('attributes' => 'onclick="Backend.getScrollOffset();"')
        )
        ->createCommand(
            $collection,
            'cut',
            array('act' => 'paste', 'mode' => 'cut'),
            'cut.gif',
            array(
                'attributes' => 'onclick="Backend.getScrollOffset();"'
            )
        )
        ->createCommand(
            $collection,
            'delete',
            array('act' => 'delete'),
            'delete.gif',
            array(
                'attributes' => sprintf(
                    'onclick="if (!confirm(\'%s\')) return false; Backend.getScrollOffset();"',
                    // FIXME: we need the translation manager here.
                    $GLOBALS['TL_LANG']['MSC']['deleteConfirm']
                )
            )
        )
        ->createCommand(
            $collection,
            'show',
            array('act' => 'show'),
            'show.gif',
            array()
        );

        if ($this->getMetaModel($container)->hasVariants()) {
            $this->createCommand(
                $collection,
                'createvariant',
                array('act' => 'createvariant'),
                'system/modules/metamodels/assets/images/icons/variants.png',
                array()
            );
        }

        // Check if we have some children.
        foreach (ViewCombinations::getParentedInputScreens() as $screen) {
            /** @var \MetaModels\BackendIntegration\InputScreen\IInputScreen $screen */
            if ($screen->getParentTable() != $container->getName()) {
                continue;
            }

            $metaModel  = $screen->getMetaModel();
            $arrCaption = array(
                '',
                sprintf(
                    $GLOBALS['TL_LANG']['MSC']['metamodel_edit_as_child']['label'],
                    $metaModel->getName()
                )
            );

            foreach ($screen->getBackendCaption() as $arrLangEntry) {
                if ($arrLangEntry['label'] != '' && $arrLangEntry['langcode'] == $GLOBALS['TL_LANGUAGE']) {
                    $arrCaption = array($arrLangEntry['description'], $arrLangEntry['label']);
                }
            }

            $this->createCommand(
                $collection,
                'edit_' . $metaModel->getTableName(),
                array('table' => $metaModel->getTableName()),
                self::getBackendIcon($screen->getIcon()),
                array
                (
                    'attributes'  => 'onclick="Backend.getScrollOffset();"',
                    'label'       => $arrCaption[0],
                    'description' => $arrCaption[1],
                    'idparam'     => 'pid'
                )
            );
        }
    }

    /**
     * Build the property information for a certain property from the data container array.
     *
     * @param IMetaModelDataDefinition      $container   The MetaModel data definition.
     *
     * @param PropertiesDefinitionInterface $definition  The property collection definition.
     *
     * @param string                        $propName    The name of the property.
     *
     * @param IInputScreen                  $inputScreen The input screen instance.
     *
     * @return void
     */
    protected function buildPropertyFromDca(
        IMetaModelDataDefinition $container,
        PropertiesDefinitionInterface $definition,
        $propName,
        IInputScreen $inputScreen
    ) {
        $property     = $inputScreen->getProperty($propName);
        $propInfo     = $property['info'];
        $metaModel    = $this->getMetaModel($container);
        $attribute    = $metaModel->getAttribute($propName);
        $isTranslated = $metaModel->isTranslated()
            && in_array('MetaModels\Attribute\ITranslated', class_implements($attribute));

        if ($definition->hasProperty($propName)) {
            $property = $definition->getProperty($propName);
        } else {
            $property = new DefaultProperty($propName);
            $definition->addProperty($property);
        }

        if (!$property->getLabel() && isset($propInfo['label'])) {
            $lang = $propInfo['label'];

            if (is_array($lang)) {
                $label       = reset($lang);
                $description = next($lang);

                $property->setDescription($description);
            } else {
                $label = $lang;
            }

            $property->setLabel($label);
        }

        if (!$property->getDescription() && isset($propInfo['description'])) {
            $property->setDescription($propInfo['description']);
        }

        if (!$property->getDefaultValue() && isset($propInfo['default'])) {
            $property->setDefaultValue($propInfo['default']);
        }

        if (isset($propInfo['exclude'])) {
            $property->setExcluded($propInfo['exclude']);
        }

        if (isset($propInfo['search'])) {
            $property->setSearchable($propInfo['search']);
        }

        if (isset($propInfo['sorting'])) {
            $property->setSortable($propInfo['sorting']);
        }

        if (isset($propInfo['filter'])) {
            $property->setFilterable($propInfo['filter']);
        }

        if (!$property->getWidgetType() && isset($propInfo['inputType'])) {
            $property->setWidgetType($propInfo['inputType']);
        }

        if (!$property->getOptions() && isset($propInfo['options'])) {
            $property->setOptions($propInfo['options']);
        }

        if (!$property->getExplanation() && isset($propInfo['explanation'])) {
            $property->setExplanation($propInfo['explanation']);
        }

        if (!$property->getExtra() && isset($propInfo['eval'])) {
            $extra = $propInfo['eval'];
            if ($isTranslated) {
                $extra['tl_class'] = 'translat-attr' . (!empty($extra['tl_class']) ? ' ' . $extra['tl_class'] : '');
            }
            $property->setExtra($extra);
        }
    }

    /**
     * Parse the defined properties and populate the definition.
     *
     * @param IMetaModelDataDefinition $container The data container.
     *
     * @return void
     */
    protected function parseProperties(IMetaModelDataDefinition $container)
    {
        if ($container->hasPropertiesDefinition()) {
            $definition = $container->getPropertiesDefinition();
        } else {
            $definition = new DefaultPropertiesDefinition();
            $container->setPropertiesDefinition($definition);
        }

        $metaModel   = Factory::byTableName($container->getName());
        $inputScreen = $this->getInputScreenDetails($container);

        // If the current metamodels has variants add the varbase and vargroup to the definition.
        if ($metaModel->hasVariants()) {
            $this->buildPropertyFromDca($container, $definition, 'varbase', $inputScreen);
            $this->buildPropertyFromDca($container, $definition, 'vargroup', $inputScreen);
        }

        foreach ($metaModel->getAttributes() as $attribute) {
            $this->buildPropertyFromDca($container, $definition, $attribute->getColName(), $inputScreen);

            $event = new BuildAttributeEvent($metaModel, $attribute, $container, $inputScreen, $this);
            // Trigger BuildAttribute Event.
            $this->dispatcher->dispatch($event::NAME, $event);
        }
    }

    /**
     * Parse the palettes from the input screen into the data container.
     *
     * @param IMetaModelDataDefinition $container The data container.
     *
     * @return void
     */
    protected function parsePalettes(IMetaModelDataDefinition $container)
    {
        $inputScreen = $this->getInputScreenDetails($container);
        $metaModel   = $this->getMetaModel($container);

        if ($container->hasDefinition(PalettesDefinitionInterface::NAME)) {
            $palettesDefinition = $container->getDefinition(PalettesDefinitionInterface::NAME);
        } else {
            $palettesDefinition = new DefaultPalettesDefinition();
            $container->setDefinition(PalettesDefinitionInterface::NAME, $palettesDefinition);
        }

        $palette = new Palette();
        $palette
            ->setName('default')
            ->setCondition(new DefaultPaletteCondition());
        $palettesDefinition->addPalette($palette);

        foreach ($inputScreen->getLegends() as $legendName => $legend) {
            $paletteLegend = new Legend($legendName);
            $paletteLegend->setInitialVisibility($legend['visible']);
            $palette->addLegend($paletteLegend);

            $this->translator->setValue($legendName . '_legend', $legend['name'], $container->getName());

            foreach ($legend['properties'] as $propertyName) {
                $property = new Property($propertyName);
                $paletteLegend->addProperty($property);
                $propInfo = $inputScreen->getProperty($propertyName);

                $chain = new PropertyConditionChain();
                $property->setEditableCondition($chain);

                $chain->addCondition(new BooleanCondition(
                    !(isset($propInfo['info']['readonly']) && $propInfo['info']['readonly'])
                ));

                if ($metaModel->hasVariants() && !$metaModel->getAttribute($propertyName)->get('isvariant')) {
                    $chain->addCondition(new PropertyValueCondition('varbase', 1));
                }

                $extra = $propInfo['info'];
                $chain = new PropertyConditionChain();
                $property->setVisibleCondition($chain);
                $chain->addCondition(new BooleanCondition(
                    !((isset($extra['doNotShow']) && $extra['doNotShow'])
                        || (isset($extra['hideInput']) && $extra['hideInput']))
                ));

                $propertyConditions = $inputScreen->getConditionsFor($propertyName);
                if ($propertyConditions !== null) {
                    $chain->addCondition($propertyConditions);
                }

                // If variants, do show only if allowed.
                if ($metaModel->hasVariants()) {
                    $chain->addCondition(new IsVariantAttribute());
                }
            }
        }
    }
}
