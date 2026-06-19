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

namespace MetaModels\Test\DcGeneral\Events\MetaModel;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\PostDuplicateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostPasteModelEvent;
use MetaModels\Attribute\IAttribute;
use MetaModels\Attribute\ITranslated;
use MetaModels\DcGeneral\Events\MetaModel\CopyTranslatedData;
use MetaModels\IFactory;
use MetaModels\IMetaModel;
use MetaModels\ITranslatedMetaModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \MetaModels\DcGeneral\Events\MetaModel\CopyTranslatedData
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class CopyTranslatedDataTest extends TestCase
{
    /**
     * Build a PostDuplicateModelEvent with the given IDs and provider name.
     *
     * @param string $sourceId     The source item ID.
     * @param string $newId        The new item ID.
     * @param string $providerName The MetaModel table name.
     *
     * @return PostDuplicateModelEvent
     */
    private function buildEvent(
        string $sourceId,
        string $newId,
        string $providerName = 'mm_test'
    ): PostDuplicateModelEvent {
        $sourceModel = $this->getMockForAbstractClass(ModelInterface::class);
        $sourceModel->method('getId')->willReturn($sourceId);

        $newModel = $this->getMockForAbstractClass(ModelInterface::class);
        $newModel->method('getId')->willReturn($newId);
        $newModel->method('getProviderName')->willReturn($providerName);

        return $this->buildDuplicateEvent($sourceModel, $newModel);
    }

    /**
     * Build a PostDuplicateModelEvent for the given source and new model.
     *
     * @param ModelInterface $sourceModel The source model.
     * @param ModelInterface $newModel    The duplicated (new) model.
     *
     * @return PostDuplicateModelEvent
     */
    private function buildDuplicateEvent(
        ModelInterface $sourceModel,
        ModelInterface $newModel
    ): PostDuplicateModelEvent {
        $environment = $this->getMockForAbstractClass(EnvironmentInterface::class);

        return new PostDuplicateModelEvent($environment, $newModel, $sourceModel);
    }

    /**
     * Build a PostPasteModelEvent for the given model.
     *
     * @param ModelInterface $model The pasted (persisted) model.
     *
     * @return PostPasteModelEvent
     */
    private function buildPasteEvent(ModelInterface $model): PostPasteModelEvent
    {
        $environment = $this->getMockForAbstractClass(EnvironmentInterface::class);

        return new PostPasteModelEvent($environment, $model);
    }

    /**
     * Build a CopyTranslatedData listener with the given MetaModel (or null for "not found").
     *
     * @param IMetaModel|null $metaModel The MetaModel to return from factory, or null.
     * @param string          $providerName The provider name used for factory lookup.
     *
     * @return CopyTranslatedData
     */
    private function buildListener(?IMetaModel $metaModel, string $providerName = 'mm_test'): CopyTranslatedData
    {
        /** @var IFactory&MockObject $factory */
        $factory = $this->getMockForAbstractClass(IFactory::class);
        $factory->method('getMetaModel')->with($providerName)->willReturn($metaModel);

        return new CopyTranslatedData($factory);
    }

    /**
     * Nothing happens when source and new ID are identical.
     */
    public function testHandleDoesNothingWhenIdsAreEqual(): void
    {
        /** @var ITranslatedMetaModel&MockObject $metaModel */
        $metaModel = $this->getMockForAbstractClass(ITranslatedMetaModel::class);
        $metaModel->expects(self::never())->method('getLanguages');

        $listener = $this->buildListener($metaModel);
        $listener->handle($this->buildEvent('42', '42'));
    }

    /**
     * Nothing happens when source ID is empty.
     */
    public function testHandleDoesNothingWhenSourceIdIsEmpty(): void
    {
        /** @var ITranslatedMetaModel&MockObject $metaModel */
        $metaModel = $this->getMockForAbstractClass(ITranslatedMetaModel::class);
        $metaModel->expects(self::never())->method('getLanguages');

        $listener = $this->buildListener($metaModel);
        $listener->handle($this->buildEvent('', '99'));
    }

    /**
     * When the new ID is empty (clipboard / manual sorting path), the copy is deferred: nothing is copied on the
     * duplicate event itself.
     */
    public function testHandleDefersCopyWhenNewIdIsEmpty(): void
    {
        /** @var ITranslatedMetaModel&MockObject $metaModel */
        $metaModel = $this->getMockForAbstractClass(ITranslatedMetaModel::class);
        $metaModel->expects(self::never())->method('getLanguages');

        $listener = $this->buildListener($metaModel);
        $listener->handle($this->buildEvent('42', ''));
    }

    /**
     * Nothing happens when the MetaModel does not implement ITranslatedMetaModel.
     */
    public function testHandleDoesNothingForNonTranslatedMetaModel(): void
    {
        /** @var IMetaModel&MockObject $metaModel */
        $metaModel = $this->getMockForAbstractClass(IMetaModel::class);
        $metaModel->expects(self::never())->method('getAttributes');

        $listener = $this->buildListener($metaModel);
        $listener->handle($this->buildEvent('1', '2'));
    }

    /**
     * Attributes that do not implement ITranslated are skipped.
     */
    public function testHandleSkipsNonTranslatedAttributes(): void
    {
        /** @var IAttribute&MockObject $attribute */
        $attribute = $this->getMockForAbstractClass(IAttribute::class);

        /** @var ITranslatedMetaModel&MockObject $metaModel */
        $metaModel = $this->getMockForAbstractClass(ITranslatedMetaModel::class);
        $metaModel->method('getLanguages')->willReturn(['de', 'en']);
        // The attributes are inspected for both languages; a non-translated attribute is simply skipped without error.
        $metaModel->expects(self::exactly(2))->method('getAttributes')->willReturn([$attribute]);

        $listener = $this->buildListener($metaModel);
        $listener->handle($this->buildEvent('1', '2'));
    }

    /**
     * Languages for which the attribute holds no data are skipped (setTranslatedDataFor not called).
     */
    public function testHandleSkipsLanguagesWithNoData(): void
    {
        /** @var ITranslated&MockObject $attribute */
        $attribute = $this->getMockForAbstractClass(ITranslated::class);
        $attribute->method('getTranslatedDataFor')->willReturn([]);
        $attribute->expects(self::never())->method('setTranslatedDataFor');

        /** @var ITranslatedMetaModel&MockObject $metaModel */
        $metaModel = $this->getMockForAbstractClass(ITranslatedMetaModel::class);
        $metaModel->method('getLanguages')->willReturn(['de', 'en']);
        $metaModel->method('getAttributes')->willReturn([$attribute]);

        $listener = $this->buildListener($metaModel);
        $listener->handle($this->buildEvent('1', '2'));
    }

    /**
     * Happy path: for each language and attribute with data, setTranslatedDataFor is called with the new ID.
     */
    public function testHandleCopiesDataForAllLanguages(): void
    {
        $sourceId = '10';
        $newId    = '99';

        $dataDE = ['item_id' => $sourceId, 'langcode' => 'de', 'value' => 'Hallo'];
        $dataEN = ['item_id' => $sourceId, 'langcode' => 'en', 'value' => 'Hello'];

        /** @var ITranslated&MockObject $attribute */
        $attribute = $this->getMockForAbstractClass(ITranslated::class);
        $attribute->method('getTranslatedDataFor')->willReturnMap([
            [[$sourceId], 'de', [$sourceId => $dataDE]],
            [[$sourceId], 'en', [$sourceId => $dataEN]],
        ]);

        $attribute->expects(self::exactly(2))->method('setTranslatedDataFor')->willReturnCallback(
            static function (array $arrValues, string $lang) use ($newId, $dataDE, $dataEN): void {
                self::assertArrayHasKey($newId, $arrValues, "New ID must be the array key for lang={$lang}");
                if ('de' === $lang) {
                    self::assertSame($dataDE, $arrValues[$newId]);
                } else {
                    self::assertSame($dataEN, $arrValues[$newId]);
                }
            }
        );

        /** @var ITranslatedMetaModel&MockObject $metaModel */
        $metaModel = $this->getMockForAbstractClass(ITranslatedMetaModel::class);
        $metaModel->method('getLanguages')->willReturn(['de', 'en']);
        $metaModel->method('getAttributes')->willReturn([$attribute]);

        $listener = $this->buildListener($metaModel);
        $listener->handle($this->buildEvent($sourceId, $newId));
    }

    /**
     * Only languages that actually have data are saved; others are silently skipped.
     */
    public function testHandleCopiesOnlyLanguagesWithActualData(): void
    {
        $sourceId = '5';
        $newId    = '7';

        $dataDE = ['item_id' => $sourceId, 'langcode' => 'de', 'value' => 'Hallo'];

        /** @var ITranslated&MockObject $attribute */
        $attribute = $this->getMockForAbstractClass(ITranslated::class);
        $attribute->method('getTranslatedDataFor')->willReturnMap([
            [[$sourceId], 'de', [$sourceId => $dataDE]],
            [[$sourceId], 'en', []],
        ]);

        $attribute->expects(self::once())->method('setTranslatedDataFor')
            ->with([$newId => $dataDE], 'de');

        /** @var ITranslatedMetaModel&MockObject $metaModel */
        $metaModel = $this->getMockForAbstractClass(ITranslatedMetaModel::class);
        $metaModel->method('getLanguages')->willReturn(['de', 'en']);
        $metaModel->method('getAttributes')->willReturn([$attribute]);

        $listener = $this->buildListener($metaModel);
        $listener->handle($this->buildEvent($sourceId, $newId));
    }

    /**
     * Multiple attributes per language are each handled independently.
     */
    public function testHandleProcessesMultipleAttributes(): void
    {
        $sourceId = '3';
        $newId    = '8';

        $data1 = ['item_id' => $sourceId, 'langcode' => 'de', 'value' => 'Attr1'];
        $data2 = ['item_id' => $sourceId, 'langcode' => 'de', 'value' => 'Attr2'];

        /** @var ITranslated&MockObject $attribute1 */
        $attribute1 = $this->getMockForAbstractClass(ITranslated::class);
        $attribute1->method('getTranslatedDataFor')->willReturn([$sourceId => $data1]);
        $attribute1->expects(self::once())->method('setTranslatedDataFor')->with([$newId => $data1], 'de');

        /** @var ITranslated&MockObject $attribute2 */
        $attribute2 = $this->getMockForAbstractClass(ITranslated::class);
        $attribute2->method('getTranslatedDataFor')->willReturn([$sourceId => $data2]);
        $attribute2->expects(self::once())->method('setTranslatedDataFor')->with([$newId => $data2], 'de');

        /** @var ITranslatedMetaModel&MockObject $metaModel */
        $metaModel = $this->getMockForAbstractClass(ITranslatedMetaModel::class);
        $metaModel->method('getLanguages')->willReturn(['de']);
        $metaModel->method('getAttributes')->willReturn([$attribute1, $attribute2]);

        $listener = $this->buildListener($metaModel);
        $listener->handle($this->buildEvent($sourceId, $newId));
    }

    /**
     * Clipboard / manual sorting path: the duplicate event has no new ID yet, so the copy is deferred and runs on the
     * subsequent post-paste event with the persisted ID.
     */
    public function testHandlePostPasteCopiesDeferredData(): void
    {
        $sourceId = '10';
        $newId    = '99';

        $dataDE = ['item_id' => $sourceId, 'langcode' => 'de', 'value' => 'Hallo'];

        /** @var ITranslated&MockObject $attribute */
        $attribute = $this->getMockForAbstractClass(ITranslated::class);
        $attribute->method('getTranslatedDataFor')->willReturn([$sourceId => $dataDE]);
        $attribute->expects(self::once())->method('setTranslatedDataFor')->with([$newId => $dataDE], 'de');

        /** @var ITranslatedMetaModel&MockObject $metaModel */
        $metaModel = $this->getMockForAbstractClass(ITranslatedMetaModel::class);
        $metaModel->method('getLanguages')->willReturn(['de']);
        $metaModel->method('getAttributes')->willReturn([$attribute]);

        $sourceModel = $this->getMockForAbstractClass(ModelInterface::class);
        $sourceModel->method('getId')->willReturn($sourceId);

        // The clipboard path reuses the very same model instance: it has no id on duplicate and the real id on paste.
        $newModel = $this->getMockForAbstractClass(ModelInterface::class);
        $newModel->method('getId')->willReturnOnConsecutiveCalls('', $newId);
        $newModel->method('getProviderName')->willReturn('mm_test');

        $listener = $this->buildListener($metaModel);
        $listener->handle($this->buildDuplicateEvent($sourceModel, $newModel));
        $listener->handlePostPaste($this->buildPasteEvent($newModel));
    }

    /**
     * Post-paste events for models that were not duplicated through this listener (e.g. plain moves) are ignored.
     */
    public function testHandlePostPasteIgnoresUnknownModel(): void
    {
        /** @var ITranslatedMetaModel&MockObject $metaModel */
        $metaModel = $this->getMockForAbstractClass(ITranslatedMetaModel::class);
        $metaModel->expects(self::never())->method('getLanguages');

        $model = $this->getMockForAbstractClass(ModelInterface::class);
        $model->method('getId')->willReturn('99');
        $model->method('getProviderName')->willReturn('mm_test');

        $listener = $this->buildListener($metaModel);
        $listener->handlePostPaste($this->buildPasteEvent($model));
    }

    /**
     * A deferred copy is consumed only once: a second post-paste event for the same model does nothing.
     */
    public function testHandlePostPasteConsumesDeferredEntryOnlyOnce(): void
    {
        $sourceId = '10';
        $newId    = '99';

        $dataDE = ['item_id' => $sourceId, 'langcode' => 'de', 'value' => 'Hallo'];

        /** @var ITranslated&MockObject $attribute */
        $attribute = $this->getMockForAbstractClass(ITranslated::class);
        $attribute->method('getTranslatedDataFor')->willReturn([$sourceId => $dataDE]);
        $attribute->expects(self::once())->method('setTranslatedDataFor')->with([$newId => $dataDE], 'de');

        /** @var ITranslatedMetaModel&MockObject $metaModel */
        $metaModel = $this->getMockForAbstractClass(ITranslatedMetaModel::class);
        $metaModel->method('getLanguages')->willReturn(['de']);
        $metaModel->method('getAttributes')->willReturn([$attribute]);

        $sourceModel = $this->getMockForAbstractClass(ModelInterface::class);
        $sourceModel->method('getId')->willReturn($sourceId);

        $newModel = $this->getMockForAbstractClass(ModelInterface::class);
        $newModel->method('getId')->willReturnOnConsecutiveCalls('', $newId, $newId);
        $newModel->method('getProviderName')->willReturn('mm_test');

        $listener = $this->buildListener($metaModel);
        $listener->handle($this->buildDuplicateEvent($sourceModel, $newModel));
        $listener->handlePostPaste($this->buildPasteEvent($newModel));
        // Second post-paste for the same model must not copy again (setTranslatedDataFor still expected exactly once).
        $listener->handlePostPaste($this->buildPasteEvent($newModel));
    }
}
