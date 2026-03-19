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

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\FilterSetting;

use ContaoCommunityAlliance\DcGeneral\Clipboard\ClipboardInterface;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Filter;
use ContaoCommunityAlliance\DcGeneral\Clipboard\ItemInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPasteButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Controller\ModelCollector;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use MetaModels\Filter\Setting\IFilterSettingFactory;
use MetaModels\Filter\Setting\IFilterSettingTypeFactory;

/**
 * This class takes care of enabling and disabling of the paste button.
 */
class PasteButtonListener
{
    /**
     * The filter setting factory.
     *
     * @var IFilterSettingFactory
     */
    private IFilterSettingFactory $filterFactory;

    private \SplObjectStorage $parents;

    /**
     * Create a new instance.
     *
     * @param IFilterSettingFactory $filterFactory The filter setting factory.
     */
    public function __construct(IFilterSettingFactory $filterFactory)
    {
        $this->filterFactory = $filterFactory;
        $this->parents = new \SplObjectStorage();
    }

    /**
     * Generate the paste button.
     *
     * @param GetPasteButtonEvent $event The event.
     *
     * @return void
     */
    public function handle(GetPasteButtonEvent $event)
    {
        $model = $event->getModel();
        assert($model instanceof ModelInterface);

        if (('tl_metamodel_filtersetting' !== $model->getProviderName())) {
            return;
        }

        $clipboard = $event->getEnvironment()->getClipboard();
        assert($clipboard instanceof ClipboardInterface);

        $filter = Filter::create()->andModelIs(ModelId::fromModel($model))->andActionIs(ItemInterface::CUT);
        // Disable all buttons if there is a circular reference.
        if ($event->isCircularReference() || !$clipboard->isEmpty($filter)) {
            $event
                ->setPasteAfterDisabled(true)
                ->setPasteIntoDisabled(true);

            return;
        }
        $factory = $this->getFactoryFor($model);
        if (null === $factory) {
            // Unknown type, disallow paste.
            $event->setPasteIntoDisabled(true);
            $event->setPasteAfterDisabled(true);
            return;
        }

        // If setting does not support children, omit them.
        if ($model->getId() && !($factory->isNestedType())) {
            $event->setPasteIntoDisabled(true);
        }

        $collector = new ModelCollector($event->getEnvironment());
        if ($factory->isNestedType() && (null !== ($maxChildren = $factory->getMaxChildren()))) {
            if ($maxChildren < count($collector->collectDirectChildrenOf($model))) {
                $event->setPasteIntoDisabled(true);
            }
        }
        if (!$this->parents->contains($model)) {
            $this->parents[$model] = $collector->searchParentOf($model);
        }
        $parent = $this->parents[$model];
        if (!$parent) {
            return;
        }
        $parentFactory = $this->getFactoryFor($parent);
        if (!$parentFactory?->isNestedType() || (null === ($maxChildren = $parentFactory?->getMaxChildren()))) {
            return;
        }
        $siblings = $collector->collectSiblingsOf($model, $parent?->getId());
        // FIXME: Except, if we are already contained and just get moved within parent :(
        if ($maxChildren <= $siblings->length()) {
            $event->setPasteAfterDisabled(true);
        }
    }

    private function getFactoryFor(ModelInterface $model): ?IFilterSettingTypeFactory
    {
        return $this->filterFactory->getTypeFactory($model->getProperty('type'));
    }
}
