<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2020 The MetaModels team.
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
 * @copyright  2012-2020 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\DefinitionBuilder;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultBasicDefinition;
use MetaModels\DcGeneral\DataDefinition\IMetaModelDataDefinition;
use MetaModels\IFactory;
use MetaModels\ViewCombination\ViewCombination;

/**
 * This class handles building of the basic definition
 */
class BasicDefinitionBuilder
{
    use MetaModelDefinitionBuilderTrait;

    /**
     * The view combinations.
     *
     * @var ViewCombination
     */
    private $viewCombination;

    /**
     * The factory.
     *
     * @var IFactory
     */
    private $factory;

    /**
     * Create a new instance.
     *
     * @param ViewCombination $viewCombination The view combination.
     * @param IFactory        $factory         The factory.
     */
    public function __construct(ViewCombination $viewCombination, IFactory $factory)
    {
        $this->viewCombination = $viewCombination;
        $this->factory         = $factory;
    }

    /**
     * Build the definition.
     *
     * @param IMetaModelDataDefinition $container The container being built.
     *
     * @return void
     */
    protected function build(IMetaModelDataDefinition $container)
    {
        $inputScreen = $this->viewCombination->getScreen($container->getName());
        if (!$inputScreen) {
            return;
        }
        $meta = $inputScreen['meta'];

        $config = $this->getOrCreateBasicDefinition($container);

        $config->setDataProvider($container->getName());

        $metaModel = $this->factory->getMetaModel($container->getName());
        // If we have variants, override all modes to tree mode.
        if ($metaModel->hasVariants()) {
            $config->setMode(BasicDefinitionInterface::MODE_HIERARCHICAL);
        } elseif ('hierarchical' === $meta['rendermode']) {
            // Hierarchical mode - Records are displayed as tree (see site structure).
            $config->setMode(BasicDefinitionInterface::MODE_HIERARCHICAL);
        } elseif ('parented' === $meta['rendermode']) {
            // Displays the child records of a parent record (see style sheets module).
            $config->setMode(BasicDefinitionInterface::MODE_PARENTEDLIST);
        } elseif ('flat' === $meta['rendermode']) {
            // Flat mode.
            $config->setMode(BasicDefinitionInterface::MODE_FLAT);
        }

        $config
            ->setEditable((bool) $meta['iseditable'])
            ->setCreatable((bool) $meta['iscreatable'])
            ->setDeletable((bool) $meta['isdeleteable']);

        if ($metaModel->hasVariants()) {
            ConditionBuilderWithVariants::calculateConditions($container, $inputScreen);
            return;
        }
        ConditionBuilderWithoutVariants::calculateConditions($container, $inputScreen);
    }

    /**
     * Get or create the basic definition.
     *
     * @param IMetaModelDataDefinition $container The definition being built.
     *
     * @return BasicDefinitionInterface
     */
    private function getOrCreateBasicDefinition(IMetaModelDataDefinition $container)
    {
        if ($container->hasBasicDefinition()) {
            return $container->getBasicDefinition();
        }
        $config = new DefaultBasicDefinition();
        $container->setBasicDefinition($config);

        return $config;
    }
}
