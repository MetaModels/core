<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2019 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\DcGeneral\DefinitionBuilder;

use MetaModels\DcGeneral\DataDefinition\Definition\IMetaModelDefinition;
use MetaModels\DcGeneral\DataDefinition\Definition\MetaModelDefinition;
use MetaModels\DcGeneral\DataDefinition\IMetaModelDataDefinition;
use MetaModels\Helper\ViewCombinations;

/**
 * This class builds the MetaModel definition.
 */
class MetaModelDefinitionBuilder
{
    use MetaModelDefinitionBuilderTrait;

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
    protected function build(IMetaModelDataDefinition $container)
    {
        $definition = $this->createOrGetDefinition($container);

        if (!$definition->hasActiveRenderSetting()) {
            $definition->setActiveRenderSetting($this->viewCombinations->getRenderSetting($container->getName()));
        }

        if (!$definition->hasActiveInputScreen()) {
            $definition->setActiveInputScreen($this->viewCombinations->getInputScreen($container->getName()));
        }
    }

    /**
     * Create or get the MetaModel definition.
     *
     * @param IMetaModelDataDefinition $container The container being built.
     *
     * @return IMetaModelDefinition
     */
    private function createOrGetDefinition(IMetaModelDataDefinition $container)
    {
        if ($container->hasMetaModelDefinition()) {
            return $container->getMetaModelDefinition();
        }

        $container->setMetaModelDefinition($definition = new MetaModelDefinition());

        return $definition;
    }
}
