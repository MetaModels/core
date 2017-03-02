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
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\DcGeneral\DefinitionBuilder;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultBasicDefinition;
use MetaModels\DcGeneral\DataDefinition\IMetaModelDataDefinition;
use MetaModels\Helper\ViewCombinations;

/**
 * This class handles building of the basic definition
 */
class BasicDefinitionBuilder
{
    /**
     * The view combinations.
     *
     * @var ViewCombinations
     */
    private $viewCombinations;

    /**
     * Create a new instance.
     *
     * @param ViewCombinations $viewCombinations The view combinations.
     */
    public function __construct(ViewCombinations $viewCombinations)
    {
        $this->viewCombinations = $viewCombinations;
    }

    /**
     * Build the definition.
     *
     * @param IMetaModelDataDefinition $container The container being built.
     *
     * @return void
     */
    public function build(IMetaModelDataDefinition $container)
    {
        $inputScreen = $this->viewCombinations->getInputScreenDetails($container->getName());

        $config = $this->getOrCreateBasicDefinition($container);

        $config->setDataProvider($container->getName());

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

        if ($inputScreen->getMetaModel()->hasVariants()) {
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
