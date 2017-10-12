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

use ContaoCommunityAlliance\DcGeneral\DataDefinition\ConditionChainInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultPalettesDefinition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\PalettesDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette\DefaultPaletteCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\BooleanCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionChain;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Legend;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Palette;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Property;
use ContaoCommunityAlliance\Translator\StaticTranslator;
use MetaModels\DcGeneral\DataDefinition\IMetaModelDataDefinition;
use MetaModels\DcGeneral\DataDefinition\Palette\Condition\Property\IsVariantAttribute;
use MetaModels\Helper\ViewCombinations;

/**
 * This class takes care of the palette building.
 */
class PaletteBuilder
{
    use MetaModelDefinitionBuilderTrait;

    /**
     * The view combinations.
     *
     * @var ViewCombinations
     */
    private $viewCombinations;

    /**
     * The translator to populate.
     *
     * @var StaticTranslator
     */
    private $translator;

    /**
     * Create a new instance.
     *
     * @param ViewCombinations $viewCombinations The view combinations.
     * @param StaticTranslator $translator       The translator (needed for legend captions).
     */
    public function __construct(ViewCombinations $viewCombinations, StaticTranslator $translator)
    {
        $this->viewCombinations = $viewCombinations;
        $this->translator       = $translator;
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
        $inputScreen = $this->viewCombinations->getInputScreenDetails($container->getName());

        $variantHandling    = $inputScreen->getMetaModel()->hasVariants();
        $palettesDefinition = $this->getOrCreatePaletteDefinition($container);

        $properties = $container->getPropertiesDefinition();

        $palettesDefinition->addPalette($palette = new Palette());
        $palette
            ->setName('default')
            ->setCondition(new DefaultPaletteCondition());

        foreach ($inputScreen->getLegends() as $legendName => $legendInfo) {
            $legend = new Legend($legendName);
            $legend->setInitialVisibility($legendInfo['visible']);
            $palette->addLegend($legend);

            $this->translator->setValue($legendName . '_legend', $legendInfo['name'], $container->getName());

            foreach ($legendInfo['properties'] as $propertyName) {
                $legend->addProperty(
                    $this->createProperty(
                        $properties->getProperty($propertyName),
                        $propertyName,
                        $variantHandling,
                        $inputScreen->getConditionsFor($propertyName)
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
     * @return DefaultPalettesDefinition|\ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefinitionInterface
     */
    private function getOrCreatePaletteDefinition(IMetaModelDataDefinition $container)
    {
        if ($container->hasDefinition(PalettesDefinitionInterface::NAME)) {
            return $container->getDefinition(PalettesDefinitionInterface::NAME);
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
     * @param PropertyInterface            $property        The input screen.
     * @param string                       $propertyName    The property name.
     * @param bool                         $variantHandling The MetaModel instance.
     * @param ConditionChainInterface|null $conditions      The conditions.
     *
     * @return Property
     */
    private function createProperty(
        PropertyInterface $property,
        $propertyName,
        $variantHandling,
        ConditionChainInterface $conditions = null
    ) {
        $paletteProperty = new Property($propertyName);

        $extra = $property->getExtra();

        $chain = new PropertyConditionChain();
        $paletteProperty->setEditableCondition($chain);
        if (isset($extra['readonly'])) {
            $chain->addCondition(new BooleanCondition($extra['readonly']));
        }

        $chain = new PropertyConditionChain();
        $paletteProperty->setVisibleCondition($chain);
        // If variants, do show only if allowed.
        if ($variantHandling) {
            $chain->addCondition(new IsVariantAttribute());
        }
        $chain->addCondition(
            new BooleanCondition(
                !((isset($extra['doNotShow']) && $extra['doNotShow'])
                || (isset($extra['hideInput']) && $extra['hideInput']))
            )
        );

        if (null !== $conditions) {
            $chain->addCondition($conditions);
        }

        return $paletteProperty;
    }
}
