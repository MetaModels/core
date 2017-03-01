<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2015 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Christopher Boelter <christopher@boelter.eu>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Alexander Menk <a.menk@imi.de>
 * @copyright  2012-2015 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\DcGeneral\Dca\Builder;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\ResizeImageEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinition;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultBasicDefinition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultPalettesDefinition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultPropertiesDefinition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\PalettesDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\PropertiesDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\DefaultProperty;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\Command;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CommandCollectionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CommandInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CopyCommand;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\CutCommand;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\DefaultModelFormatterConfig;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingInformationInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\ListingConfigInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\SelectCommand;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette\DefaultPaletteCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\BooleanCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionChain;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyValueCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Legend;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Palette;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Property;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use ContaoCommunityAlliance\Translator\StaticTranslator;
use MetaModels\Attribute\ITranslated;
use MetaModels\BackendIntegration\InputScreen\IInputScreen;
use MetaModels\BackendIntegration\ViewCombinations;
use MetaModels\DcGeneral\DataDefinition\Definition\MetaModelDefinition;
use MetaModels\DcGeneral\DataDefinition\IMetaModelDataDefinition;
use MetaModels\DcGeneral\DataDefinition\Palette\Condition\Property\IsVariantAttribute;
use MetaModels\DcGeneral\DefinitionBuilder\ConditionBuilderWithoutVariants;
use MetaModels\DcGeneral\DefinitionBuilder\ConditionBuilderWithVariants;
use MetaModels\DcGeneral\DefinitionBuilder\DataProviderBuilder;
use MetaModels\DcGeneral\DefinitionBuilder\PanelBuilder;
use MetaModels\DcGeneral\Events\MetaModel\BuildAttributeEvent;
use MetaModels\DcGeneral\Events\MetaModel\BuildMetaModelOperationsEvent;
use MetaModels\DcGeneral\Events\MetaModel\RenderItem;
use MetaModels\Helper\ToolboxFile;
use MetaModels\IMetaModelsServiceContainer;

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
    private $translator;

    /**
     * The MetaModel this builder is responsible for.
     *
     * @var IMetaModelsServiceContainer
     */
    private $serviceContainer;

    /**
     * The input screen to use.
     *
     * @var IInputScreen
     */
    private $inputScreen;

    /**
     * Create a new instance and instantiate the translator.
     *
     * @param IMetaModelsServiceContainer $serviceContainer The name of the MetaModel being created.
     *
     * @param IInputScreen                $inputScreen      The input screen to use.
     *
     * @param int                         $renderSetting    The render setting.
     */
    public function __construct($serviceContainer, $inputScreen, $renderSetting)
    {
        $this->serviceContainer = $serviceContainer;
        $this->inputScreen      = $inputScreen;
        $this->translator       = new StaticTranslator();
        $this->renderSetting    = $renderSetting;
    }

    /**
     * Retrieve the translator.
     *
     * @return StaticTranslator
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * Retrieve the MetaModel.
     *
     * @return \MetaModels\IMetaModel
     */
    protected function getMetaModel()
    {
        return $this->inputScreen->getMetaModel();
    }

    /**
     * Retrieve the MetaModel.
     *
     * @return ViewCombinations|null
     */
    protected function getViewCombinations()
    {
        return $this->serviceContainer->getService('metamodels-view-combinations');
    }

    /**
     * Handle a build data definition event for MetaModels.
     *
     * @param BuildDataDefinitionEvent $event The event payload.
     *
     * @return void
     */
    public function build(BuildDataDefinitionEvent $event)
    {
        $dispatcher = $this->serviceContainer->getEventDispatcher();
        $container  = $event->getContainer();
        /** @var $container IMetaModelDataDefinition */
        $this->parseMetaModelDefinition($container);
        $this->parseProperties($container);
        $this->parseBasicDefinition($container);

        $dataBuilder = new DataProviderBuilder($this->inputScreen, $this->serviceContainer->getFactory());
        $dataBuilder->parseDataProvider($container);

        $this->parseBackendView($container);
        $builder = new PanelBuilder($this->inputScreen);
        $builder->build($container);

        $this->parsePalettes($container);

        // Attach renderer to event.
        RenderItem::register($dispatcher);
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
            $definition->setActiveRenderSetting($this->getViewCombinations()->getRenderSetting($container->getName()));
        }

        if (!$definition->hasActiveInputScreen()) {
            $definition->setActiveInputScreen($this->getViewCombinations()->getInputScreen($container->getName()));
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

        $inputScreen = $this->inputScreen;

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

        if ($this->getMetaModel()->hasVariants()) {
            ConditionBuilderWithVariants::calculateConditions($container, $this->inputScreen);
            return;
        }
        ConditionBuilderWithoutVariants::calculateConditions($container, $this->inputScreen);
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
        $this->addSelectCommand($view, $container);
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
            $listing->setRootLabel($this->getMetaModel()->get('name'));
        }

        if (($listing->getRootIcon() === null)
            && (($inputScreen = $this->inputScreen) !== null)
        ) {
            $icon = ToolboxFile::convertValueToPath($inputScreen->getIcon());
            // Determine image to use.
            if ($icon && file_exists(TL_ROOT . '/' . $icon)) {
                $event = new ResizeImageEvent($icon, 16, 16);
                $this->serviceContainer->getEventDispatcher()->dispatch(ContaoEvents::IMAGE_RESIZE, $event);
                $icon = $event->getResultImage();
            } else {
                $icon = 'system/modules/metamodels/assets/images/icons/metamodels.png';
            }

            $listing->setRootIcon($icon);
        }

        $this->parseListSorting($listing);
        $this->parseListLabel($container, $listing);

        if ($inputScreen = $this->inputScreen) {
            $listing->setShowColumns($inputScreen->isShowColumns());
            $renderSetting = $this
                ->serviceContainer
                ->getService('metamodels-view-combinations')
                ->getRenderSetting($container->getName());

            $metaModel = $this->serviceContainer->getFactory()->getMetaModel($container->getName());
            /** @var $renderSettingCollection \MetaModels\Render\Setting\Collection */
            $renderSettingCollection = $this
                ->serviceContainer
                ->getRenderSettingFactory()
                ->createCollection($metaModel, $renderSetting);
            $listing
                ->getLabelFormatter($container->getName())
                ->setPropertyNames($renderSettingCollection->getSettingNames());
        }
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
            $event = $this->serviceContainer->getEventDispatcher()->dispatch(
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
     * @param ListingConfigInterface $listing The listing configuration.
     *
     * @return void
     */
    protected function parseListSorting(ListingConfigInterface $listing)
    {
        $inputScreen = $this->inputScreen;

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
                    ->setManualSorting()
                    ->setProperty('sorting')
                    ->setSortingMode('ASC');
                    // FIXME: allow selection of the manual sorting property and its direction in the backend.
            } elseif ($information->getRenderSortAttribute()) {
                $propertyInformation = $definition->add();
                $propertyInformation
                    ->setProperty($information->getRenderSortAttribute())
                    ->setSortingMode($information->getRenderSortDirection());
            }

            $groupType = $this->convertRenderGroupType($information->getRenderGroupType());
            if ($groupType !== GroupAndSortingInformationInterface::GROUP_NONE
                && $information->getRenderGroupAttribute()
            ) {
                $propertyInformation = $definition->add(0);
                $propertyInformation
                    ->setProperty($information->getRenderGroupAttribute())
                    ->setGroupingMode($groupType)
                    ->setGroupingLength($information->getRenderGroupLength())
                    ->setSortingMode($information->getRenderSortDirection());
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
        $renderSetting = $this->serviceContainer->getRenderSettingFactory()->createCollection(
            $this->getMetaModel(),
            $this->renderSetting
        );
        $formatter->setPropertyNames(
            array_merge(
                $formatter->getPropertyNames(),
                $renderSetting->getSettingNames()
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

        if (!$command->getLabel()) {
            $command->setLabel($operationName . '.0');
            if (isset($extraValues['label'])) {
                $command->setLabel($extraValues['label']);
            }
        }

        if (!$command->getDescription()) {
            $command->setDescription($operationName . '.1');
            if (isset($extraValues['description'])) {
                $command->setDescription($extraValues['description']);
            }
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
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
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

        if ($this->getMetaModel()->hasVariants()) {
            $this->createCommand(
                $collection,
                'createvariant',
                array('act' => 'createvariant'),
                'system/modules/metamodels/assets/images/icons/variants.png',
                array()
            );
        }

        // Check if we have some children.
        foreach ($this->getViewCombinations()->getParentedInputScreens($container->getName()) as $screen) {
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

        $event = new BuildMetaModelOperationsEvent(
            $this->getMetaModel(),
            $container,
            $this->inputScreen,
            $this
        );
        $this->serviceContainer->getEventDispatcher()->dispatch($event::NAME, $event);
    }

    /**
     * Build the property information for a certain property from the data container array.
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
        PropertiesDefinitionInterface $definition,
        $propName,
        IInputScreen $inputScreen
    ) {
        $property  = $inputScreen->getProperty($propName);
        $propInfo  = $property['info'];
        $metaModel = $this->getMetaModel();
        $attribute = $metaModel->getAttribute($propName);

        if (!$attribute) {
            return;
        }

        $isTranslated = $metaModel->isTranslated() && ($attribute instanceof ITranslated);

        if ($definition->hasProperty($propName)) {
            $property = $definition->getProperty($propName);
        } else {
            $property = new DefaultProperty($propName);
            $definition->addProperty($property);
        }

        if (!$property->getLabel()) {
            if (isset($propInfo['label'])) {
                $lang = $propInfo['label'];

                if (is_array($lang)) {
                    $label       = reset($lang);
                    $description = next($lang);

                    $property->setDescription($description);
                } else {
                    $label = $lang;
                }
            } else {
                $label = $attribute->getName();
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

        if (isset($propInfo['eval'])) {
            $extra = $propInfo['eval'];
            if ($isTranslated) {
                $extra['tl_class'] = 'translat-attr' . (!empty($extra['tl_class']) ? ' ' . $extra['tl_class'] : '');
            }
            $property->setExtra(array_merge((array) $property->getExtra(), $extra));
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

        $metaModel = $this->getMetaModel();

        // If the current metamodels has variants add the varbase and vargroup to the definition.
        if ($metaModel->hasVariants()) {
            $this->buildPropertyFromDca($definition, 'varbase', $this->inputScreen);
            $this->buildPropertyFromDca($definition, 'vargroup', $this->inputScreen);
        }

        foreach ($metaModel->getAttributes() as $attribute) {
            $this->buildPropertyFromDca($definition, $attribute->getColName(), $this->inputScreen);

            $event = new BuildAttributeEvent($metaModel, $attribute, $container, $this->inputScreen, $this);
            // Trigger BuildAttribute Event.
            $this->serviceContainer->getEventDispatcher()->dispatch($event::NAME, $event);
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
        $metaModel = $this->getMetaModel();

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

        foreach ($this->inputScreen->getLegends() as $legendName => $legend) {
            $paletteLegend = new Legend($legendName);
            $paletteLegend->setInitialVisibility($legend['visible']);
            $palette->addLegend($paletteLegend);

            $this->translator->setValue($legendName . '_legend', $legend['name'], $container->getName());

            foreach ($legend['properties'] as $propertyName) {
                $property = new Property($propertyName);
                $paletteLegend->addProperty($property);
                $propInfo = $this->inputScreen->getProperty($propertyName);

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

                $propertyConditions = $this->inputScreen->getConditionsFor($propertyName);
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

    /**
     * Add the select command to the backend view definition.
     *
     * @param Contao2BackendViewDefinitionInterface $view      The backend view definition.
     * @param IMetaModelDataDefinition              $container The metamodel data definition.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function addSelectCommand(Contao2BackendViewDefinitionInterface $view, $container)
    {
        /** @var BasicDefinitionInterface $definition */
        $definition = $container->getBasicDefinition();

        // No ations allowed. Don't add the select command button.
        if (!$definition->isEditable() && !$definition->isDeletable() && !$definition->isCreatable()) {
            return;
        }

        $commands = $view->getGlobalCommands();
        $command  = new SelectCommand();

        $command
            ->setName('all')
            ->setLabel('MSC.all.0')
            ->setDescription('MSC.all.1');

        $parameters        = $command->getParameters();
        $parameters['act'] = 'select';

        $extra          = $command->getExtra();
        $extra['class'] = 'header_edit_all';

        $commands->addCommand($command);
    }
}
