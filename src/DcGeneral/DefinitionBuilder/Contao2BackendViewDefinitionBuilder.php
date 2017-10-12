<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2017 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\DcGeneral\DefinitionBuilder;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\ResizeImageEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinition;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\DefaultModelFormatterConfig;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingInformationInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\ListingConfigInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;
use MetaModels\BackendIntegration\InputScreen\IInputScreen;
use MetaModels\BackendIntegration\InputScreen\IInputScreenGroupingAndSorting;
use MetaModels\DcGeneral\DataDefinition\IMetaModelDataDefinition;
use MetaModels\Helper\ToolboxFile;
use MetaModels\Helper\ViewCombinations;
use MetaModels\IMetaModel;
use MetaModels\Render\Setting\IRenderSettingFactory;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This class builds the Contao2 backend view definition.
 */
class Contao2BackendViewDefinitionBuilder
{
    use MetaModelDefinitionBuilderTrait;

    /**
     * The view combinations.
     *
     * @var ViewCombinations
     */
    private $viewCombinations;

    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * The render setting factory.
     *
     * @var IRenderSettingFactory
     */
    private $renderSettingFactory;

    /**
     * The container being built (only set during build phase).
     *
     * @var IMetaModelDataDefinition
     */
    private $container;

    /**
     * The backend view definition (only set during build phase).
     *
     * @var Contao2BackendViewDefinitionInterface
     */
    private $definition;

    /**
     * The input screen (only set during build phase).
     *
     * @var IInputScreen
     */
    private $inputScreen;

    /**
     * The MetaModel that is the scope (only set during build phase).
     *
     * @var IMetaModel
     */
    private $metaModel;

    /**
     * Create a new instance.
     *
     * @param ViewCombinations         $viewCombinations     The view combinations.
     * @param EventDispatcherInterface $dispatcher           The event dispatcher.
     * @param IRenderSettingFactory    $renderSettingFactory The render setting factory.
     */
    public function __construct(
        ViewCombinations $viewCombinations,
        EventDispatcherInterface $dispatcher,
        IRenderSettingFactory $renderSettingFactory
    ) {
        $this->viewCombinations     = $viewCombinations;
        $this->dispatcher           = $dispatcher;
        $this->renderSettingFactory = $renderSettingFactory;
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
    protected function build(IMetaModelDataDefinition $container)
    {
        $this->container   = $container;
        $this->definition  = $this->getOrCreateDefinition();
        $this->inputScreen = $this->viewCombinations->getInputScreenDetails($container->getName());
        $this->metaModel   = $this->inputScreen->getMetaModel();

        $this->parseListing();

        $this->container   = null;
        $this->definition  = null;
        $this->inputScreen = null;
        $this->metaModel   = null;
    }

    /**
     * Get or create the definition.
     *
     * @return Contao2BackendViewDefinitionInterface
     *
     * @throws DcGeneralInvalidArgumentException When the contained view definition is of invalid type.
     */
    private function getOrCreateDefinition()
    {
        if ($this->container->hasDefinition(Contao2BackendViewDefinitionInterface::NAME)) {
            $view = $this->container->getDefinition(Contao2BackendViewDefinitionInterface::NAME);
            if (!$view instanceof Contao2BackendViewDefinitionInterface) {
                throw new DcGeneralInvalidArgumentException(
                    'Configured BackendViewDefinition does not implement Contao2BackendViewDefinitionInterface.'
                );
            }
            return $view;
        }
        $this->container->setDefinition(
            Contao2BackendViewDefinitionInterface::NAME,
            $view = new Contao2BackendViewDefinition()
        );

        return $view;
    }

    /**
     * Parse the listing configuration.
     *
     * @return void
     */
    private function parseListing()
    {
        $listing = $this->definition->getListingConfig();

        if (null === $listing->getRootLabel()) {
            $listing->setRootLabel($this->metaModel->get('name'));
        }

        if (null === $listing->getRootIcon()) {
            $listing->setRootIcon($this->getBackendIcon($this->inputScreen->getIcon()));
        }

        $this->parseListSorting($listing);
        $this->parseListLabel($listing);

        $listing->setShowColumns($this->inputScreen->isShowColumns());
    }

    /**
     * Parse the sorting part of listing configuration.
     *
     * @param ListingConfigInterface $listing The listing configuration.
     *
     * @return void
     */
    private function parseListSorting(ListingConfigInterface $listing)
    {
        $definitions = $listing->getGroupAndSortingDefinition();
        foreach ($this->inputScreen->getGroupingAndSorting() as $information) {
            $definition = $definitions->add();
            $definition->setName($information->getName());
            if ($information->isDefault() && !$definitions->hasDefault()) {
                $definitions->markDefault($definition);
            }

            $this->handleSorting($information, $definition);

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
     * Set the correct sorting value.
     *
     * @param IInputScreenGroupingAndSorting     $information The sorting and group information.
     * @param GroupAndSortingDefinitionInterface $definition  The sorting and group definition.
     *
     * @return void
     */
    private function handleSorting(
        IInputScreenGroupingAndSorting $information,
        GroupAndSortingDefinitionInterface $definition
    ) {
        if ($information->isManualSorting()) {
            $definition
                ->add()
                ->setManualSorting()
                ->setProperty('sorting')
                ->setSortingMode(GroupAndSortingInformationInterface::SORT_ASC);
            return;
        }
        if ($information->getRenderSortAttribute()) {
            $definition
                ->add()
                ->setProperty($information->getRenderSortAttribute())
                ->setSortingMode($information->getRenderSortDirection());
        }
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
     * Generate a 16x16 pixel version of the passed image file. If this can not be done, the default image is returned.
     *
     * @param string $icon The name of the image file.
     *
     * @return null|string
     */
    private function getBackendIcon($icon)
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
     * Parse the sorting part of listing configuration.
     *
     * @param ListingConfigInterface $listing The listing config.
     *
     * @return void
     */
    private function parseListLabel(ListingConfigInterface $listing)
    {
        $providerName = $this->container->getBasicDefinition()->getDataProvider();
        if (!$listing->hasLabelFormatter($providerName)) {
            $formatter = new DefaultModelFormatterConfig();
            $listing->setLabelFormatter($providerName, $formatter);
        } else {
            $formatter = $listing->getLabelFormatter($providerName);
        }

        $renderSetting = $this->renderSettingFactory->createCollection(
            $this->metaModel,
            $this->container->getMetaModelDefinition()->getActiveRenderSetting()
        );

        $formatter->setPropertyNames(
            array_merge($formatter->getPropertyNames(), $renderSetting->getSettingNames())
        );

        $formatter->setFormat(str_repeat('%s ', count($formatter->getPropertyNames())));
    }
}
