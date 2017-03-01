<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2015 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Christopher Boelter <christopher@boelter.eu>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Alexander Menk <a.menk@imi.de>
 * @copyright  2012-2015 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\DcGeneral\DefinitionBuilder;

use Contao\Input;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultModelRelationshipDefinition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\ModelRelationshipDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\FilterBuilder;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\ParentChildCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\ParentChildConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\RootCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\RootConditionInterface;
use MetaModels\BackendIntegration\InputScreen\IInputScreen;
use MetaModels\DcGeneral\DataDefinition\IMetaModelDataDefinition;

/**
 * This class is the abstract base for the condition builders.
 */
abstract class AbstractConditionBuilder
{
    /**
     * The container to populate.
     *
     * @var IMetaModelDataDefinition
     */
    protected $container;

    /**
     * The input screen.
     *
     * @var IInputScreen
     */
    protected $inputScreen;

    /**
     * The model relationship interface.
     *
     * @var ModelRelationshipDefinitionInterface
     */
    protected $definition;

    /**
     * Parse the correct conditions.
     *
     * @param IMetaModelDataDefinition $container   The data container.
     * @param IInputScreen             $inputScreen The input screen.
     *
     * @return void
     *
     * @throws \InvalidArgumentException When the stored definition does not implement the correct interface.
     */
    public static function calculateConditions(IMetaModelDataDefinition $container, IInputScreen $inputScreen)
    {
        if ($container->hasDefinition(ModelRelationshipDefinitionInterface::NAME)) {
            $definition = $container->getDefinition(ModelRelationshipDefinitionInterface::NAME);
        } else {
            $definition = new DefaultModelRelationshipDefinition();

            $container->setDefinition(ModelRelationshipDefinitionInterface::NAME, $definition);
        }

        if (!$definition instanceof ModelRelationshipDefinitionInterface) {
            throw new \InvalidArgumentException('Search element does not implement the correct interface.');
        }

        $instance = new static();

        $instance->container   = $container;
        $instance->inputScreen = $inputScreen;
        $instance->definition  = $definition;
        $instance->calculate();
    }

    /**
     * The real calculating function.
     *
     * @return void
     *
     * @throws \RuntimeException When the conditions can not be determined.
     */
    abstract protected function calculate();

    /**
     * Parse the correct conditions for a MetaModel with variant support.
     *
     * @return void
     */
    protected function addParentCondition()
    {
        if ($this->inputScreen->isStandalone()) {
            return;
        }

        $setter  = [['to_field' => 'pid', 'from_field' => 'id']];
        $inverse = [];

        /** @var ParentChildConditionInterface $relationship */
        $relationship = $this->definition->getChildCondition(
            $this->inputScreen->getParentTable(),
            $this->container->getName()
        );
        if (!$relationship instanceof ParentChildConditionInterface) {
            $relationship = new ParentChildCondition();
            $relationship
                ->setSourceName($this->inputScreen->getParentTable())
                ->setDestinationName($this->container->getName());
            $this->definition->addChildCondition($relationship);
        } else {
            $setter  = array_merge_recursive($setter, $relationship->getSetters());
            $inverse = array_merge_recursive($inverse, $relationship->getInverseFilterArray());
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

    /**
     * Parse the correct conditions for a MetaModel with variant support.
     *
     * @return void
     */
    protected function addHierarchicalConditions()
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
            $setter  = array_merge_recursive($setter, $relationship->getSetters());
            $inverse = array_merge_recursive($inverse, $relationship->getInverseFilterArray());
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

    /**
     * Parse the correct conditions for a MetaModel with variant support.
     *
     * @return RootConditionInterface
     */
    protected function getRootCondition()
    {
        $rootProvider = $this->container->getName();

        if (($relationship = $this->definition->getRootCondition()) === null) {
            $relationship = new RootCondition();
            $relationship
                ->setSourceName($rootProvider);
            $this->definition->setRootCondition($relationship);
        }

        return $relationship;
    }
}
