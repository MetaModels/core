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

use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\FilterBuilder;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\ParentChildCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\ParentChildConditionInterface;

/**
 * This class is the abstract base for the condition builders.
 */
class ConditionBuilderWithVariants extends AbstractConditionBuilder
{
    /**
     * The real calculating function.
     *
     * @return void
     */
    protected function calculate()
    {
        // Basic conditions.
        $this->addHierarchicalConditions();
        $this->addParentCondition();

        // Conditions for metamodels variants.
        $relationship = $this->getRootCondition();
        $relationship->setSetters(array_merge_recursive(
            [['property' => 'varbase', 'value' => '1']],
            $relationship->getSetters()
        ));

        $builder = FilterBuilder::fromArrayForRoot((array) $relationship->getFilterArray())->getFilter();

        $builder->andPropertyEquals('varbase', 1);

        $relationship->setFilterArray($builder->getAllAsArray());

        $setter  = [
            ['to_field' => 'varbase', 'value' => '0'],
            ['to_field' => 'vargroup', 'from_field' => 'vargroup']
        ];
        $inverse = [];

        /** @var ParentChildConditionInterface $relationship */
        $relationship = $this->definition->getChildCondition($this->container->getName(), $this->container->getName());

        if ($relationship === null) {
            $relationship = new ParentChildCondition();
            $relationship
                ->setSourceName($this->container->getName())
                ->setDestinationName($this->container->getName());
            $this->definition->addChildCondition($relationship);
        } else {
            $setter  = array_merge_recursive($setter, $relationship->getSetters());
            $inverse = array_merge_recursive($inverse, $relationship->getInverseFilterArray());
        }

        $relationship
            ->setFilterArray(
                FilterBuilder::fromArray($relationship->getFilterArray())
                    ->getFilter()
                    ->getBuilder()
                    ->encapsulateOr()
                    ->andRemotePropertyEquals('vargroup', 'vargroup')
                    ->andRemotePropertyEquals('vargroup', 'id')
                    ->andRemotePropertyEquals('varbase', 0, true)
                    ->getAllAsArray()
            )
            ->setSetters($setter)
            ->setInverseFilterArray($inverse);
    }
}
