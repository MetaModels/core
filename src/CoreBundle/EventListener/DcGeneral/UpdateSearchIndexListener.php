<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2026 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2026 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\CoreBundle\EventListener\DcGeneral;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Event\PostDeleteModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostDuplicateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostPersistModelEvent;
use MetaModels\CoreBundle\Search\Backend\ItemIndexer;
use MetaModels\IFactory;
use MetaModels\IMetaModel;

/**
 * Updates the backend search index when an item is saved, duplicated or deleted in the backend.
 */
final class UpdateSearchIndexListener
{
    public function __construct(
        private readonly IFactory $factory,
        private readonly ItemIndexer $indexer,
    ) {
    }

    public function onPersist(PostPersistModelEvent $event): void
    {
        $this->update($event->getModel());
    }

    public function onDuplicate(PostDuplicateModelEvent $event): void
    {
        $this->update($event->getModel());
    }

    public function onDelete(PostDeleteModelEvent $event): void
    {
        $this->forItem(
            $event->getModel(),
            fn (IMetaModel $metaModel, string $id) => $this->indexer->remove($metaModel, $id)
        );
    }

    private function update(ModelInterface $model): void
    {
        $this->forItem(
            $model,
            fn (IMetaModel $metaModel, string $id) => $this->indexer->update($metaModel, $id)
        );
    }

    /**
     * @param callable(IMetaModel, string): void $action
     */
    private function forItem(ModelInterface $model, callable $action): void
    {
        $tableName = $model->getProviderName();
        $id        = $model->getId();
        if (null === $id || '' === (string) $id || !\in_array($tableName, $this->factory->collectNames(), true)) {
            return;
        }

        $metaModel = $this->factory->getMetaModel($tableName);
        if (null !== $metaModel) {
            $action($metaModel, (string) $id);
        }
    }
}
