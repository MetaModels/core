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

namespace MetaModels\CoreBundle\Test\Search\Backend;

use Contao\CoreBundle\Search\Backend\BackendSearch;
use Contao\CoreBundle\Search\Backend\GroupedDocumentIds;
use Contao\CoreBundle\Search\Backend\ReindexConfig;
use MetaModels\CoreBundle\Search\Backend\ItemIndexer;
use MetaModels\IMetaModel;
use MetaModels\ITranslatedMetaModel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

#[CoversClass(ItemIndexer::class)]
final class ItemIndexerTest extends TestCase
{
    private const TYPE = 'metamodels.item.mm_event';

    private function plain(): IMetaModel
    {
        $metaModel = $this->createStub(IMetaModel::class);
        $metaModel->method('getTableName')->willReturn('mm_event');

        return $metaModel;
    }

    private function translated(): ITranslatedMetaModel
    {
        $metaModel = $this->createStub(ITranslatedMetaModel::class);
        $metaModel->method('getTableName')->willReturn('mm_event');
        $metaModel->method('getLanguages')->willReturn(['de', 'en']);

        return $metaModel;
    }

    public function testReindexesTheItemOfAMetaModelWithoutLanguages(): void
    {
        $search = $this->createMock(BackendSearch::class);
        $search->expects(self::never())->method('deleteDocuments');
        $search->expects(self::once())->method('reindex')->with(
            self::callback(
                static fn (ReindexConfig $config): bool => ['7'] === $config->getLimitedDocumentIds()
                    ->getDocumentIdsForType(self::TYPE)
            )
        );

        (new ItemIndexer($search))->update($this->plain(), '7');
    }

    public function testRemovesTheDocumentsOfAllLanguagesBeforeItReindexesATranslatedItem(): void
    {
        $calls  = [];
        $search = $this->createMock(BackendSearch::class);
        $search->expects(self::once())->method('deleteDocuments')->with(
            self::callback(static function (GroupedDocumentIds $ids) use (&$calls): bool {
                $calls[] = 'delete';

                return ['7', '7@de', '7@en'] === $ids->getDocumentIdsForType(self::TYPE);
            }),
            false
        );
        $search->expects(self::once())->method('reindex')->willReturnCallback(
            static function (ReindexConfig $config) use (&$calls): string {
                $calls[] = 'reindex';
                self::assertSame(['7'], $config->getLimitedDocumentIds()->getDocumentIdsForType(self::TYPE));

                return 'job';
            }
        );

        (new ItemIndexer($search))->update($this->translated(), '7');

        self::assertSame(['delete', 'reindex'], $calls);
    }

    public function testRemovesTheDocumentsOfADeletedItem(): void
    {
        $search = $this->createMock(BackendSearch::class);
        $search->expects(self::once())->method('deleteDocuments')->with(
            self::callback(
                static fn (GroupedDocumentIds $ids): bool => ['7', '7@de', '7@en'] === $ids
                    ->getDocumentIdsForType(self::TYPE)
            )
        );

        (new ItemIndexer($search))->remove($this->translated(), '7');
    }

    public function testDoesNothingWithoutTheBackendSearch(): void
    {
        $indexer = new ItemIndexer(null);
        $indexer->update($this->plain(), '7');
        $indexer->remove($this->plain(), '7');

        $this->addToAssertionCount(1);
    }

    public function testLogsAFailureOfTheIndexInsteadOfThrowing(): void
    {
        $search = $this->createStub(BackendSearch::class);
        $search->method('reindex')->willThrowException(new \RuntimeException('queue is gone'));
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects(self::once())->method('warning')->with(self::stringContains('queue is gone'));

        (new ItemIndexer($search, $logger))->update($this->plain(), '7');
    }
}
