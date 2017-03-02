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
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\DefaultModelFormatterConfig;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingInformationInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\ListingConfigInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use ContaoCommunityAlliance\Translator\StaticTranslator;
use MetaModels\BackendIntegration\InputScreen\IInputScreen;
use MetaModels\BackendIntegration\ViewCombinations;
use MetaModels\DcGeneral\DataDefinition\Definition\MetaModelDefinition;
use MetaModels\DcGeneral\DataDefinition\IMetaModelDataDefinition;
use MetaModels\DcGeneral\DefinitionBuilder\CommandBuilder;
use MetaModels\DcGeneral\DefinitionBuilder\ConditionBuilderWithoutVariants;
use MetaModels\DcGeneral\DefinitionBuilder\ConditionBuilderWithVariants;
use MetaModels\DcGeneral\DefinitionBuilder\DataProviderBuilder;
use MetaModels\DcGeneral\DefinitionBuilder\PaletteBuilder;
use MetaModels\DcGeneral\DefinitionBuilder\PanelBuilder;
use MetaModels\DcGeneral\DefinitionBuilder\PropertyDefinitionBuilder;
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
        $builder = new PropertyDefinitionBuilder($dispatcher);
        $builder->build($container, $this->inputScreen, $this);
        $this->parseBasicDefinition($container);

        $dataBuilder = new DataProviderBuilder($this->inputScreen, $this->serviceContainer->getFactory());
        $dataBuilder->parseDataProvider($container);

        $this->parseBackendView($container);
        $builder = new CommandBuilder($dispatcher, $this->getViewCombinations());
        $builder->build($container, $this->inputScreen, $this);
        $builder = new PanelBuilder($this->inputScreen);
        $builder->build($container);

        $builder = new PaletteBuilder();
        $builder->build($container, $this->inputScreen, $this->translator);

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
    private function convertRenderGroupType($type)
    {
        $lookup = [
            'char'    => GroupAndSortingInformationInterface::GROUP_CHAR,
            'digit'   => GroupAndSortingInformationInterface::GROUP_DIGIT,
            'day'     => GroupAndSortingInformationInterface::GROUP_DAY,
            'weekday' => GroupAndSortingInformationInterface::GROUP_WEEKDAY,
            'week'    => GroupAndSortingInformationInterface::GROUP_WEEK,
            'month'   => GroupAndSortingInformationInterface::GROUP_MONTH,
            'year'    => GroupAndSortingInformationInterface::GROUP_YEAR,
        ];
        if (array_key_exists($type, $lookup)) {
            return $lookup[$type];
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
}
