<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2024 The MetaModels team.
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
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\DcaSettingCondition;

use ContaoCommunityAlliance\DcGeneral\Clipboard\ClipboardInterface;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Filter;
use ContaoCommunityAlliance\DcGeneral\Clipboard\ItemInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPasteButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Controller\ModelCollector;
use ContaoCommunityAlliance\DcGeneral\Controller\RelationshipManager;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\BasicDefinitionInterface;

/**
 * This handles the type options for conditions.
 */
class PasteButtonListener extends AbstractConditionFactoryUsingListener
{
    /**
     * Generate the paste button.
     *
     * @param GetPasteButtonEvent $event The event.
     *
     * @return void
     */
    public function handle(GetPasteButtonEvent $event)
    {
        if (!$this->wantToHandle($event)) {
            return;
        }

        $environment = $event->getEnvironment();
        $model       = $event->getModel();
        assert($model instanceof ModelInterface);
        $clipboard = $environment->getClipboard();
        assert($clipboard instanceof ClipboardInterface);
        // Disable all buttons if there is a circular reference.
        if (
            $clipboard->fetch(
                Filter::create()->andActionIs(ItemInterface::CUT)->andModelIs(ModelId::fromModel($model))
            )
        ) {
            $event
                ->setPasteAfterDisabled(true)
                ->setPasteIntoDisabled(true);

            return;
        }

        $typeName = $model->getProperty('type');
        // If setting does not support children, omit them.
        if ($model->getId() && !$this->conditionFactory->supportsNesting($typeName)) {
            $event->setPasteIntoDisabled(true);
            $this->testParent($model, $event);
            return;
        }

        $collector = new ModelCollector($environment);
        if (!$this->acceptsAnotherChild($model, $collector)) {
            $event->setPasteIntoDisabled(true);
        }
        $this->testParent($model, $event);
    }

    /**
     * Test if a model a parent.
     *
     * @param ModelInterface      $model The model that shall be checked.
     * @param GetPasteButtonEvent $event The event.
     *
     * @return void
     */
    private function testParent(ModelInterface $model, GetPasteButtonEvent $event): void
    {
        $environment = $event->getEnvironment();
        $definition  = $environment->getDataDefinition();
        assert($definition instanceof ContainerInterface);

        $mode          = $definition->getBasicDefinition()->getMode() ?? BasicDefinitionInterface::MODE_FLAT;
        $relationships = new RelationshipManager($definition->getModelRelationshipDefinition(), $mode);
        $collector     = new ModelCollector($environment);

        if (
            !$relationships->isRoot($model)
            && ($parent = $collector->searchParentOf($model))
            && !$this->acceptsAnotherChild($parent, $collector)
        ) {
            $event->setPasteAfterDisabled(true);
        }
    }

    /**
     * Test if a model accepts another child.
     *
     * @param ModelInterface $model     The model that shall be checked.
     * @param ModelCollector $collector The collector to use.
     *
     * @return bool
     */
    public function acceptsAnotherChild(ModelInterface $model, ModelCollector $collector)
    {
        $conditionType = $model->getProperty('type');
        if (!$this->conditionFactory->supportsNesting($conditionType)) {
            return false;
        }
        if (-1 === ($max = $this->conditionFactory->maxChildren($conditionType))) {
            return true;
        }

        return \count($collector->collectDirectChildrenOf($model)) < $max;
    }
}
