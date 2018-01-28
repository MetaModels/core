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

use MetaModels\DcGeneral\DataDefinition\Definition\IMetaModelDefinition;
use MetaModels\DcGeneral\DataDefinition\Definition\MetaModelDefinition;
use MetaModels\DcGeneral\DataDefinition\IMetaModelDataDefinition;
use MetaModels\ViewCombination\ViewCombination;

/**
 * This class builds the MetaModel definition.
 */
class MetaModelDefinitionBuilder
{
    use MetaModelDefinitionBuilderTrait;

    /**
     * The view combinations.
     *
     * @var ViewCombination
     */
    private $viewCombination;

    /**
     * Create a new instance.
     *
     * @param ViewCombination $viewCombination The view combination.
     */
    public function __construct(ViewCombination $viewCombination)
    {
        $this->viewCombination = $viewCombination;
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

        if (empty($combination = $this->viewCombination->getCombination($container->getName()))) {
            return;
        }

        if (!$definition->hasActiveRenderSetting()) {
            $definition->setActiveRenderSetting($combination['view_id']);
        }

        if (!$definition->hasActiveInputScreen()) {
            $definition->setActiveInputScreen($combination['dca_id']);
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
