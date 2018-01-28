<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2018 The MetaModels team.
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
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\DefinitionBuilder;

use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinition;
use ContaoCommunityAlliance\DcGeneral\Contao\DataDefinition\Definition\Contao2BackendViewDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\DefaultModelFormatterConfig;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\GroupAndSortingInformationInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\View\ListingConfigInterface;
use ContaoCommunityAlliance\DcGeneral\Exception\DcGeneralInvalidArgumentException;
use MetaModels\CoreBundle\Assets\IconBuilder;
use MetaModels\DcGeneral\DataDefinition\IMetaModelDataDefinition;
use MetaModels\IFactory;
use MetaModels\IMetaModel;
use MetaModels\Render\Setting\IRenderSettingFactory;
use MetaModels\ViewCombination\ViewCombination;

/**
 * This class builds the Contao2 backend view definition.
 */
class Contao2BackendViewDefinitionBuilder
{
    use MetaModelDefinitionBuilderTrait;

    /**
     * The view combinations.
     *
     * @var ViewCombination
     */
    private $viewCombination;

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
     * @var array
     */
    private $inputScreen;

    /**
     * The MetaModel that is the scope (only set during build phase).
     *
     * @var IMetaModel
     */
    private $metaModel;

    /**
     * The MetaModels factory.
     *
     * @var IFactory
     */
    private $factory;

    /**
     * The icon builder.
     *
     * @var IconBuilder
     */
    private $iconBuilder;

    /**
     * Create a new instance.
     *
     * @param ViewCombination          $viewCombination      The view combinations.
     * @param IFactory                 $factory              The MetaModels factory.
     * @param IRenderSettingFactory    $renderSettingFactory The render setting factory.
     * @param IconBuilder              $iconBuilder          The icon builder.
     */
    public function __construct(
        ViewCombination $viewCombination,
        IFactory $factory,
        IRenderSettingFactory $renderSettingFactory,
        IconBuilder $iconBuilder
    ) {
        $this->viewCombination      = $viewCombination;
        $this->factory              = $factory;
        $this->renderSettingFactory = $renderSettingFactory;
        $this->iconBuilder          = $iconBuilder;
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
        $this->inputScreen = $this->viewCombination->getScreen($container->getName());
        $this->metaModel   = $this->factory->getMetaModel($container->getName());

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
            $listing->setRootIcon($this->iconBuilder->getBackendIcon($this->inputScreen['meta']['backendicon']));
        }

        $this->parseListSorting($listing);
        $this->parseListLabel($listing);

        $listing->setShowColumns((bool) $this->inputScreen['meta']['showColumns']);
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
        foreach ($this->inputScreen['groupSort'] as $information) {
            $definition = $definitions->add();
            $definition->setName($information['name']);
            if ($information['isdefault'] && !$definitions->hasDefault()) {
                $definitions->markDefault($definition);
            }

            $this->handleSorting($information, $definition);

            $groupType = $this->convertRenderGroupType($information['rendergrouptype']);
            if ($groupType !== GroupAndSortingInformationInterface::GROUP_NONE
                && $information['col_name']
            ) {
                $propertyInformation = $definition->add(0);
                $propertyInformation
                    ->setProperty($information['col_name'])
                    ->setGroupingMode($groupType)
                    ->setGroupingLength($information['rendergrouplen'])
                    ->setSortingMode($information['rendersort']);
            }
        }
    }

    /**
     * Set the correct sorting value.
     *
     * @param array                              $information The sorting and group information.
     * @param GroupAndSortingDefinitionInterface $definition  The sorting and group definition.
     *
     * @return void
     */
    private function handleSorting(
        $information,
        GroupAndSortingDefinitionInterface $definition
    ) {
        if ($information['ismanualsort']) {
            $definition
                ->add()
                ->setManualSorting()
                ->setProperty('sorting')
                ->setSortingMode(GroupAndSortingInformationInterface::SORT_ASC);
            return;
        }
        if ($information['col_name']) {
            $definition
                ->add()
                ->setProperty($information['col_name'])
                ->setSortingMode($information['rendersort']);
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
