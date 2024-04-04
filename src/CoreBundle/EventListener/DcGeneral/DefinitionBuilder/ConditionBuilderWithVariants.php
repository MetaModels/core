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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\DefinitionBuilder;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\FilterBuilder;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\ParentChildCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\ParentChildConditionInterface;

/**
 * This class is for the variant model condition builders.
 * The variant model is a special form of the hierarchy model.
 */
class ConditionBuilderWithVariants extends AbstractConditionBuilder
{
    /**
     * The real calculating function.
     *
     * @return void
     */
    protected function calculate(): void
    {
        // Basic conditions.
        $this->addVariantConditions();
        $this->addParentCondition();

        // Conditions for metamodels variants.
        $relationship = $this->getRootCondition();
        $relationship->setSetters(array_merge_recursive(
            [['property' => 'varbase', 'value' => '1']],
            $relationship->getSetters()
        ));
    }

    /**
     * Parse the correct conditions for a MetaModel with variant support.
     *
     * @return void
     */
    protected function addVariantConditions(): void
    {
        // Not hierarchical? Get out.
        if ($this->container->getBasicDefinition()->getMode() !== BasicDefinitionInterface::MODE_HIERARCHICAL) {
            return;
        }

        $relationship = $this->getRootCondition();

        $builder = FilterBuilder::fromArrayForRoot($relationship->getFilterArray())->getFilter();

        $builder->andPropertyEquals('varbase', 1);

        $relationship->setFilterArray($builder->getAllAsArray());

        $setter  = [
            ['to_field' => 'varbase', 'value' => '0'],
            ['to_field' => 'vargroup', 'from_field' => 'vargroup']
        ];
        $inverse = [];

        $relationship = $this->definition->getChildCondition($this->container->getName(), $this->container->getName());

        if ($relationship === null) {
            $relationship = new ParentChildCondition();
            $relationship
                ->setSourceName($this->container->getName())
                ->setDestinationName($this->container->getName());
            $this->definition->addChildCondition($relationship);
        } else {
            $setter  = \array_merge_recursive($setter, $relationship->getSetters());
            $inverse = \array_merge_recursive($inverse, $relationship->getInverseFilterArray());
        }

        $relationship
            ->setFilterArray(
                FilterBuilder::fromArray($relationship->getFilterArray())
                    ->getFilter()
                    ->getBuilder()
                    ->encapsulateOr()
                    ->andRemotePropertyEquals('vargroup', 'vargroup')
                    ->andRemotePropertyEquals('vargroup', 'id')
                    ->andRemotePropertyEquals('varbase', '0', true)
                    ->getAllAsArray()
            )
            ->setSetters($setter)
            ->setInverseFilterArray($inverse);
    }
}
