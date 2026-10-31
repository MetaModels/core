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

namespace MetaModels\CoreBundle\Test\EventListener\DcGeneral;

use Contao\CoreBundle\Search\Backend\BackendSearch;
use Contao\CoreBundle\Search\Backend\GroupedDocumentIds;
use Contao\CoreBundle\Search\Backend\ReindexConfig;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\PostDeleteModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostDuplicateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostPersistModelEvent;
use MetaModels\CoreBundle\EventListener\DcGeneral\UpdateSearchIndexListener;
use MetaModels\CoreBundle\Search\Backend\ItemIndexer;
use MetaModels\IFactory;
use MetaModels\IMetaModel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[CoversClass(UpdateSearchIndexListener::class)]
final class UpdateSearchIndexListenerTest extends TestCase
{
    private const TYPE = 'metamodels.item.mm_event';

    private function model(string $table, ?string $id): ModelInterface
    {
        $model = $this->createStub(ModelInterface::class);
        $model->method('getProviderName')->willReturn($table);
        $model->method('getId')->willReturn($id);

        return $model;
    }

    private function listener(MockObject&BackendSearch $search): UpdateSearchIndexListener
    {
        $metaModel = $this->createStub(IMetaModel::class);
        $metaModel->method('getTableName')->willReturn('mm_event');
        $factory = $this->createStub(IFactory::class);
        $factory->method('collectNames')->willReturn(['mm_event']);
        $factory->method('getMetaModel')->willReturn($metaModel);

        return new UpdateSearchIndexListener($factory, new ItemIndexer($search));
    }

    private function reindexOf(string $id): \Closure
    {
        return static fn (ReindexConfig $config): bool => [$id] === $config->getLimitedDocumentIds()
            ->getDocumentIdsForType(self::TYPE);
    }

    public function testIndexesAnItemThatWasSaved(): void
    {
        $search = $this->createMock(BackendSearch::class);
        $search->expects(self::once())->method('reindex')->with(self::callback($this->reindexOf('7')));
        $search->expects(self::never())->method('deleteDocuments');

        $this->listener($search)->onPersist(new PostPersistModelEvent(
            $this->createStub(EnvironmentInterface::class),
            $this->model('mm_event', '7'),
            $this->model('mm_event', '7')
        ));
    }

    public function testIndexesAnItemThatWasDuplicated(): void
    {
        $search = $this->createMock(BackendSearch::class);
        $search->expects(self::once())->method('reindex')->with(self::callback($this->reindexOf('8')));

        $this->listener($search)->onDuplicate(new PostDuplicateModelEvent(
            $this->createStub(EnvironmentInterface::class),
            $this->model('mm_event', '8'),
            $this->model('mm_event', '7')
        ));
    }

    public function testRemovesAnItemThatWasDeleted(): void
    {
        $search = $this->createMock(BackendSearch::class);
        $search->expects(self::never())->method('reindex');
        $search->expects(self::once())->method('deleteDocuments')->with(
            self::callback(
                static fn (GroupedDocumentIds $ids): bool => ['7'] === $ids->getDocumentIdsForType(self::TYPE)
            )
        );

        $this->listener($search)->onDelete(new PostDeleteModelEvent(
            $this->createStub(EnvironmentInterface::class),
            $this->model('mm_event', '7')
        ));
    }

    public function testLeavesOtherTablesAndModelsWithoutIdAlone(): void
    {
        $search = $this->createMock(BackendSearch::class);
        $search->expects(self::never())->method('reindex');
        $search->expects(self::never())->method('deleteDocuments');
        $listener    = $this->listener($search);
        $environment = $this->createStub(EnvironmentInterface::class);

        $listener->onPersist(new PostPersistModelEvent($environment, $this->model('tl_content', '7'), null));
        $listener->onPersist(new PostPersistModelEvent($environment, $this->model('mm_event', null), null));
        $listener->onDelete(new PostDeleteModelEvent($environment, $this->model('tl_content', '7')));
    }
}
