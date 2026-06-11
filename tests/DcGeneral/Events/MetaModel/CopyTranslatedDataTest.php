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
use MetaModels\Attribute\ITranslated;
use MetaModels\Attribute\ITranslatedWithFallbackControl;
use MetaModels\DcGeneral\Events\MetaModel\CopyTranslatedData;
use MetaModels\IFactory;
use MetaModels\IMetaModel;
use MetaModels\ITranslatedMetaModel;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 */
#[CoversClass(\MetaModels\DcGeneral\Events\MetaModel\CopyTranslatedData::class)]
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
        $environment = $this->createMock(EnvironmentInterface::class);

        $sourceModel = $this->createMock(ModelInterface::class);
        $sourceModel->method('getId')->willReturn($sourceId);

        $newModel = $this->createMock(ModelInterface::class);
        $newModel->method('getId')->willReturn($newId);
        $newModel->method('getProviderName')->willReturn($providerName);

        return new PostDuplicateModelEvent($environment, $newModel, $sourceModel);
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
        $factory = $this->createMock(IFactory::class);
        $factory->method('getMetaModel')->with($providerName)->willReturn($metaModel);

        return new CopyTranslatedData($factory);
    }

    /**
     * Nothing happens when source and new ID are identical.
     */
    public function testHandleDoesNothingWhenIdsAreEqual(): void
    {
        /** @var ITranslatedMetaModel&MockObject $metaModel */
        $metaModel = $this->createMock(ITranslatedMetaModel::class);
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
        $metaModel = $this->createMock(ITranslatedMetaModel::class);
        $metaModel->expects(self::never())->method('getLanguages');

        $listener = $this->buildListener($metaModel);
        $listener->handle($this->buildEvent('', '99'));
    }

    /**
     * Nothing happens when new ID is empty.
     */
    public function testHandleDoesNothingWhenNewIdIsEmpty(): void
    {
        /** @var ITranslatedMetaModel&MockObject $metaModel */
        $metaModel = $this->createMock(ITranslatedMetaModel::class);
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
        $metaModel = $this->createMock(IMetaModel::class);
        $metaModel->expects(self::never())->method('getAttributes');

        $listener = $this->buildListener($metaModel);
        $listener->handle($this->buildEvent('1', '2'));
    }

    /**
     * Attributes that only implement ITranslated (not ITranslatedWithFallbackControl) are skipped.
     */
    public function testHandleSkipsAttributesWithoutFallbackControlInterface(): void
    {
        /** @var ITranslated&MockObject $attribute */
        $attribute = $this->createMock(ITranslated::class);
        $attribute->expects(self::never())->method('setTranslatedDataFor');

        /** @var ITranslatedMetaModel&MockObject $metaModel */
        $metaModel = $this->createMock(ITranslatedMetaModel::class);
        $metaModel->method('getLanguages')->willReturn(['de', 'en']);
        $metaModel->method('getAttributes')->willReturn([$attribute]);

        $listener = $this->buildListener($metaModel);
        $listener->handle($this->buildEvent('1', '2'));
    }

    /**
     * Languages for which the attribute holds no data are skipped (applyTranslatedDataFor not called).
     */
    public function testHandleSkipsLanguagesWithNoData(): void
    {
        /** @var ITranslatedWithFallbackControl&MockObject $attribute */
        $attribute = $this->createMock(ITranslatedWithFallbackControl::class);
        $attribute->method('getTranslatedDataForWithoutFallback')->willReturn([]);
        $attribute->expects(self::never())->method('applyTranslatedDataFor');

        /** @var ITranslatedMetaModel&MockObject $metaModel */
        $metaModel = $this->createMock(ITranslatedMetaModel::class);
        $metaModel->method('getLanguages')->willReturn(['de', 'en']);
        $metaModel->method('getAttributes')->willReturn([$attribute]);

        $listener = $this->buildListener($metaModel);
        $listener->handle($this->buildEvent('1', '2'));
    }

    /**
     * Happy path: for each language and attribute with data, applyTranslatedDataFor is called with the new ID.
     */
    public function testHandleCopiesDataForAllLanguages(): void
    {
        $sourceId = '10';
        $newId    = '99';

        $dataDE = ['item_id' => $sourceId, 'langcode' => 'de', 'value' => 'Hallo'];
        $dataEN = ['item_id' => $sourceId, 'langcode' => 'en', 'value' => 'Hello'];

        /** @var ITranslatedWithFallbackControl&MockObject $attribute */
        $attribute = $this->createMock(ITranslatedWithFallbackControl::class);
        $attribute->method('getTranslatedDataForWithoutFallback')->willReturnMap([
            [[$sourceId], 'de', [$sourceId => $dataDE]],
            [[$sourceId], 'en', [$sourceId => $dataEN]],
        ]);

        $attribute->expects(self::exactly(2))->method('applyTranslatedDataFor')->willReturnCallback(
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
        $metaModel = $this->createMock(ITranslatedMetaModel::class);
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

        /** @var ITranslatedWithFallbackControl&MockObject $attribute */
        $attribute = $this->createMock(ITranslatedWithFallbackControl::class);
        $attribute->method('getTranslatedDataForWithoutFallback')->willReturnMap([
            [[$sourceId], 'de', [$sourceId => $dataDE]],
            [[$sourceId], 'en', []],
        ]);

        $attribute->expects(self::once())->method('applyTranslatedDataFor')
            ->with([$newId => $dataDE], 'de');

        /** @var ITranslatedMetaModel&MockObject $metaModel */
        $metaModel = $this->createMock(ITranslatedMetaModel::class);
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

        /** @var ITranslatedWithFallbackControl&MockObject $attribute1 */
        $attribute1 = $this->createMock(ITranslatedWithFallbackControl::class);
        $attribute1->method('getTranslatedDataForWithoutFallback')->willReturn([$sourceId => $data1]);
        $attribute1->expects(self::once())->method('applyTranslatedDataFor')->with([$newId => $data1], 'de');

        /** @var ITranslatedWithFallbackControl&MockObject $attribute2 */
        $attribute2 = $this->createMock(ITranslatedWithFallbackControl::class);
        $attribute2->method('getTranslatedDataForWithoutFallback')->willReturn([$sourceId => $data2]);
        $attribute2->expects(self::once())->method('applyTranslatedDataFor')->with([$newId => $data2], 'de');

        /** @var ITranslatedMetaModel&MockObject $metaModel */
        $metaModel = $this->createMock(ITranslatedMetaModel::class);
        $metaModel->method('getLanguages')->willReturn(['de']);
        $metaModel->method('getAttributes')->willReturn([$attribute1, $attribute2]);

        $listener = $this->buildListener($metaModel);
        $listener->handle($this->buildEvent($sourceId, $newId));
    }
}
