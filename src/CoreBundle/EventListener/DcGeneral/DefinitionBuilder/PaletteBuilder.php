<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2023 The MetaModels team.
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
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\DefinitionBuilder;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\ConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultPalettesDefinition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\PalettesDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette\DefaultPaletteCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\BooleanCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionChain;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Legend;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Palette;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Property;
use MetaModels\CoreBundle\DcGeneral\PropertyConditionFactory;
use MetaModels\DcGeneral\DataDefinition\IMetaModelDataDefinition;
use MetaModels\DcGeneral\DataDefinition\Palette\Condition\Property\IsVariantAttribute;
use MetaModels\IFactory;
use MetaModels\IMetaModel;
use MetaModels\ViewCombination\ViewCombination;

/**
 * This class takes care of the palette building.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class PaletteBuilder
{
    use MetaModelDefinitionBuilderTrait;

    /**
     * The view combinations.
     *
     * @var ViewCombination
     */
    private ViewCombination $viewCombination;

    /**
     * The factory to use.
     *
     * @var IFactory
     */
    private IFactory $factory;

    /**
     * The property condition factory.
     *
     * @var PropertyConditionFactory
     */
    private PropertyConditionFactory $conditionFactory;

    /**
     * Create a new instance.
     *
     * @param ViewCombination          $viewCombination  The view combinations.
     * @param IFactory                 $factory          The factory.
     * @param PropertyConditionFactory $conditionFactory The condition factory.
     */
    public function __construct(
        ViewCombination $viewCombination,
        IFactory $factory,
        PropertyConditionFactory $conditionFactory
    ) {
        $this->viewCombination  = $viewCombination;
        $this->factory          = $factory;
        $this->conditionFactory = $conditionFactory;
    }

    /**
     * Parse and build the backend view definition for the old Contao2 backend view.
     *
     * @param IMetaModelDataDefinition $container The data container.
     *
     * @return void
     */
    protected function build(IMetaModelDataDefinition $container)
    {
        if (null === ($inputScreen = $this->viewCombination->getScreen($container->getName()))) {
            return;
        }

        $metaModel = $this->factory->getMetaModel($container->getName());
        assert($metaModel instanceof IMetaModel);

        $variantHandling    = $metaModel->hasVariants();
        $palettesDefinition = $this->getOrCreatePaletteDefinition($container);

        $properties = $container->getPropertiesDefinition();

        $palettesDefinition->addPalette($palette = new Palette());
        $palette
            ->setName('default')
            ->setCondition(new DefaultPaletteCondition());

        foreach ($inputScreen['legends'] as $legendName => $legendInfo) {
            $legend = new Legend($legendName);
            $legend->setInitialVisibility(!$legendInfo['hide']);
            $palette->addLegend($legend);

            $legendConditions = $this->buildCondition(($legendInfo['condition'] ?? null), $metaModel);
            foreach ($legendInfo['properties'] as $property) {
                $legend->addProperty(
                    $this->createProperty(
                        $properties->getProperty($property['name']),
                        $property['name'],
                        $variantHandling,
                        $this->buildCondition($property['condition'], $metaModel),
                        $legendConditions
                    )
                );
            }
        }
    }

    /**
     * Retrieve or create the palette definition.
     *
     * @param IMetaModelDataDefinition $container The container.
     *
     * @return PalettesDefinitionInterface
     */
    private function getOrCreatePaletteDefinition(IMetaModelDataDefinition $container): PalettesDefinitionInterface
    {
        if ($container->hasDefinition(PalettesDefinitionInterface::NAME)) {
            $definition = $container->getDefinition(PalettesDefinitionInterface::NAME);
            assert($definition instanceof PalettesDefinitionInterface);

            return $definition;
        }

        $container->setDefinition(
            PalettesDefinitionInterface::NAME,
            $palettesDefinition = new DefaultPalettesDefinition()
        );

        return $palettesDefinition;
    }

    /**
     * Create a property for the palette.
     *
     * @param PropertyInterface       $property        The input screen.
     * @param string                  $propertyName    The property name.
     * @param bool                    $variantHandling The MetaModel instance.
     * @param ConditionInterface|null $condition       The condition.
     * @param ConditionInterface|null $legendCondition The condition.
     *
     * @return Property
     */
    private function createProperty(
        PropertyInterface $property,
        string $propertyName,
        bool $variantHandling,
        ConditionInterface $condition = null,
        ConditionInterface $legendCondition = null
    ): Property {
        $paletteProperty = new Property($propertyName);

        $extra = $property->getExtra();

        $chain = new PropertyConditionChain();
        $paletteProperty->setEditableCondition($chain);
        if (isset($extra['readonly'])) {
            $chain->addCondition(new BooleanCondition($extra['readonly']));
        }
        // If variants, enable editing only if allowed.
        if ($variantHandling) {
            $chain->addCondition(new IsVariantAttribute());
        }

        $chain = new PropertyConditionChain();
        $paletteProperty->setVisibleCondition($chain);
        $chain->addCondition(
            new BooleanCondition(
                !((isset($extra['doNotShow']) && $extra['doNotShow'])
                || (isset($extra['hideInput']) && $extra['hideInput']))
            )
        );

        if (null !== $condition) {
            $chain->addCondition($condition);
        }
        if (null !== $legendCondition) {
            $chain->addCondition($legendCondition);
        }

        return $paletteProperty;
    }

    /**
     * Build the conditions for the passed condition array.
     *
     * @param array|null $condition The condition information.
     * @param IMetaModel $metaModel The MetaModel instance.
     *
     * @return null|ConditionInterface
     *
     * @throws \RuntimeException Throws if condition type not be transformed to an instance.
     */
    private function buildCondition(?array $condition, IMetaModel $metaModel): ?ConditionInterface
    {
        if (null === $condition) {
            return null;
        }

        return $this->conditionFactory->createCondition($condition, $metaModel);
    }
}
