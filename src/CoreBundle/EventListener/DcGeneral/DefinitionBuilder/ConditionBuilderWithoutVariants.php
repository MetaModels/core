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

use Contao\Input;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\FilterBuilder;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\ParentChildCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\ParentChildConditionInterface;

/**
 * This class is for the hierarchical model condition builders.
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
    protected function calculate(): void
    {
        if ($this->inputScreen['meta']['rendertype'] !== 'standalone') {
            if ($this->container->getBasicDefinition()->getMode() == BasicDefinitionInterface::MODE_HIERARCHICAL) {
                throw new \RuntimeException('Hierarchical mode with parent table is not supported yet.');
            }
        }

        $this->addHierarchicalConditions();
        $this->addParentCondition();
    }

    /**
     * Parse the correct conditions for a MetaModel with hierarchical support.
     *
     * @return void
     */
    protected function addHierarchicalConditions(): void
    {
        // Not hierarchical? Get out.
        if ($this->container->getBasicDefinition()->getMode() !== BasicDefinitionInterface::MODE_HIERARCHICAL) {
            return;
        }

        $relationship = $this->getRootCondition();

        // NOTE: this might bear problems when the definition will get serialized as the input value will not change.
        if (Input::get('pid')) {
            $parentValue = ModelId::fromSerialized(Input::get('pid'))->getId();
        } else {
            $parentValue = '0';
        }

        if (!$relationship->getSetters()) {
            $relationship
                ->setSetters([['property' => 'pid', 'value' => $parentValue]]);
        }

        $builder = FilterBuilder::fromArrayForRoot((array) $relationship->getFilterArray())->getFilter();

        $builder->andPropertyEquals('pid', $parentValue);

        $relationship
            ->setFilterArray($builder->getAllAsArray());

        $setter  = [['to_field' => 'pid', 'from_field' => 'id']];
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
            $setter  = \array_merge_recursive($setter, $relationship->getSetters());
            $inverse = \array_merge_recursive($inverse, $relationship->getInverseFilterArray());
        }

        // For tl_ prefix, the only unique target can be the id?
        // maybe load parent dc and scan for unique in config then.
        $relationship
            ->setFilterArray(
                FilterBuilder::fromArray($relationship->getFilterArray())
                    ->getFilter()
                    ->andRemotePropertyEquals('pid', 'id')
                    ->getAllAsArray()
            )
            ->setSetters($setter)
            ->setInverseFilterArray($inverse);
    }
}
