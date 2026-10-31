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

namespace MetaModels\CoreBundle\Search\Backend;

use Contao\CoreBundle\Search\Backend\BackendSearch;
use Contao\CoreBundle\Search\Backend\GroupedDocumentIds;
use Contao\CoreBundle\Search\Backend\ReindexConfig;
use MetaModels\IMetaModel;
use MetaModels\ITranslatedMetaModel;
use Psr\Log\LoggerInterface;

/**
 * Keeps the backend search index up to date after an item has been written or deleted.
 *
 * DC General reports changes to the HTTP cache only, which never reaches the listener of Contao that triggers a
 * reindex. Without a running worker or with the backend search turned off, Contao ignores the calls.
 */
final class ItemIndexer
{
    public function __construct(
        private readonly ?BackendSearch $backendSearch,
        private readonly ?LoggerInterface $logger = null,
    ) {
    }

    /**
     * Indexes the item again. Translated MetaModels have one document per language, which is why those are removed
     * first: a language without a searchable value any more would otherwise stay in the index.
     */
    public function update(IMetaModel $metaModel, string $itemId): void
    {
        $this->run(function (BackendSearch $search) use ($metaModel, $itemId): void {
            if ($metaModel instanceof ITranslatedMetaModel) {
                $search->deleteDocuments($this->collect($metaModel, $itemId, true), false);
            }

            $search->reindex((new ReindexConfig())->limitToDocumentIds($this->collect($metaModel, $itemId, false)));
        });
    }

    /**
     * Removes the documents of the item.
     */
    public function remove(IMetaModel $metaModel, string $itemId): void
    {
        $this->run(function (BackendSearch $search) use ($metaModel, $itemId): void {
            $search->deleteDocuments($this->collect($metaModel, $itemId, true));
        });
    }

    /**
     * The provider reads the plain item id when it indexes, but names its documents "<id>@<language>" for translated
     * MetaModels.
     */
    private function collect(IMetaModel $metaModel, string $itemId, bool $withLanguages): GroupedDocumentIds
    {
        $type = ItemProvider::TYPE_PREFIX . $metaModel->getTableName();
        $ids  = new GroupedDocumentIds();
        $ids->addIdToType($type, $itemId);

        if ($withLanguages && $metaModel instanceof ITranslatedMetaModel) {
            foreach ($metaModel->getLanguages() as $language) {
                $ids->addIdToType($type, $itemId . '@' . $language);
            }
        }

        return $ids;
    }

    /**
     * The index is secondary: a failure there must not turn a stored item into an error.
     *
     * @param \Closure(BackendSearch): void $action
     */
    private function run(\Closure $action): void
    {
        if (null === $this->backendSearch) {
            return;
        }

        try {
            $action($this->backendSearch);
        } catch (\Throwable $exception) {
            $this->logger?->warning('Could not update the backend search index: ' . $exception->getMessage());
        }
    }
}
