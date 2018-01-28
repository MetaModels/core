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

use ContaoCommunityAlliance\DcGeneral\DataDefinition\ConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultPalettesDefinition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\PalettesDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette\DefaultPaletteCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\BooleanCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionChain;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Legend;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Palette;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Property;
use MetaModels\DcGeneral\DataDefinition\IMetaModelDataDefinition;
use MetaModels\DcGeneral\DataDefinition\Palette\Condition\Property\IsVariantAttribute;
use MetaModels\Events\CreatePropertyConditionEvent;
use MetaModels\IFactory;
use MetaModels\IMetaModel;
use MetaModels\ViewCombination\ViewCombination;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This class takes care of the palette building.
 */
class PaletteBuilder
{
    use MetaModelDefinitionBuilderTrait;

    /**
     * The view combinations.
     *
     * @var ViewCombination
     */
    private $viewCombination;

    /**
     * The factory to use.
     *
     * @var IFactory
     */
    private $factory;

    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * Create a new instance.
     *
     * @param ViewCombination          $viewCombination The view combinations.
     * @param IFactory                 $factory         The factory.
     * @param EventDispatcherInterface $dispatcher      The event dispatcher.
     */
    public function __construct(
        ViewCombination $viewCombination,
        IFactory $factory,
        EventDispatcherInterface $dispatcher
    ) {
        $this->viewCombination = $viewCombination;
        $this->factory         = $factory;
        $this->dispatcher      = $dispatcher;
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
        $inputScreen        = $this->viewCombination->getScreen($container->getName());
        $metaModel          = $this->factory->getMetaModel($container->getName());
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

            $legendConditions = $this->buildCondition($legendInfo['condition'], $metaModel);
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
        $propertyName,
        $variantHandling,
        ConditionInterface $condition = null,
        ConditionInterface $legendCondition = null
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
     *
     * @param IMetaModel $metaModel The MetaModel instance.
     *
     * @return null|ConditionInterface
     */
    private function buildCondition($condition, $metaModel)
    {
        if (null === $condition) {
            return null;
        }
        $event = new CreatePropertyConditionEvent($condition, $metaModel);
        $this->dispatcher->dispatch(CreatePropertyConditionEvent::NAME, $event);

        if ($event->getInstance() === null) {
            throw new \RuntimeException(sprintf(
                'Condition of type %s could not be transformed to an instance.',
                $condition['type']
            ));
        }

        return $event->getInstance();
    }
}
