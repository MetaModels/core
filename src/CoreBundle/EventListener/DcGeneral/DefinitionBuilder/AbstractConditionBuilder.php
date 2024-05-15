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

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\DefaultModelRelationshipDefinition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\ModelRelationshipDefinitionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\FilterBuilder;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\ParentChildCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\ParentChildConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\RootCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ModelRelationship\RootConditionInterface;
use MetaModels\DcGeneral\DataDefinition\IMetaModelDataDefinition;

/**
 * This class is the abstract base for the hierarchical/variant model condition builders.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractConditionBuilder
{
    /**
     * The container to populate.
     *
     * @var IMetaModelDataDefinition
     */
    protected IMetaModelDataDefinition $container;

    /**
     * The input screen.
     *
     * @var array
     */
    protected array $inputScreen;

    /**
     * The model relationship interface.
     *
     * @var ModelRelationshipDefinitionInterface
     */
    protected ModelRelationshipDefinitionInterface $definition;

    /**
     * Parse the correct conditions.
     *
     * @param IMetaModelDataDefinition $container   The data container.
     * @param array                    $inputScreen The input screen.
     *
     * @return void
     *
     * @throws \InvalidArgumentException When the stored definition does not implement the correct interface.
     */
    public static function calculateConditions(IMetaModelDataDefinition $container, array $inputScreen)
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

        $instance = new static($container, $inputScreen, $definition);
        $instance->calculate();
    }

    /**
     * @param IMetaModelDataDefinition             $container
     * @param array                                $inputScreen
     * @param ModelRelationshipDefinitionInterface $definition
     */
    final public function __construct(
        IMetaModelDataDefinition $container,
        array $inputScreen,
        ModelRelationshipDefinitionInterface $definition
    ) {
        $this->container   = $container;
        $this->inputScreen = $inputScreen;
        $this->definition  = $definition;
    }

    /**
     * The real calculating function.
     *
     * @return void
     *
     * @throws \RuntimeException When the conditions can not be determined.
     */
    abstract protected function calculate(): void;

    /**
     * Parse the correct conditions for a MetaModel with variant support.
     *
     * @return void
     */
    protected function addParentCondition(): void
    {
        if ($this->inputScreen['meta']['rendertype'] === 'standalone') {
            return;
        }

        $setter  = [['to_field' => 'pid', 'from_field' => 'id']];
        $inverse = [];

        /** @var ParentChildConditionInterface $relationship */
        $relationship = $this->definition->getChildCondition(
            $this->inputScreen['meta']['ptable'],
            $this->container->getName()
        );
        if (!$relationship instanceof ParentChildConditionInterface) {
            $relationship = new ParentChildCondition();
            $relationship
                ->setSourceName($this->inputScreen['meta']['ptable'])
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

    /**
     * Parse the root conditions for a MetaModel with hierarchical/variant support.
     *
     * @return RootConditionInterface
     */
    protected function getRootCondition(): RootConditionInterface
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
