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
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\DefinitionBuilder;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;

/**
 * This class is the abstract base for the condition builders.
 */
class ConditionBuilderWithoutVariants extends AbstractConditionBuilder
{
    /**
     * The real calculating function.
     *
     * @return void
     *
     * @throws \RuntimeException When the conditions can not be determined yet.
     */
    protected function calculate()
    {
        if ($this->inputScreen['meta']['rendertype'] !== 'standalone') {
            if ($this->container->getBasicDefinition()->getMode() == BasicDefinitionInterface::MODE_HIERARCHICAL) {
                throw new \RuntimeException('Hierarchical mode with parent table is not supported yet.');
            }
        }

        $this->addHierarchicalConditions();
        $this->addParentCondition();
    }
}
