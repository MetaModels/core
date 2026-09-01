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

use Contao\CoreBundle\String\SimpleTokenParser;
use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\PostDeleteModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostDuplicateModelEvent;
use ContaoCommunityAlliance\DcGeneral\Event\PostPersistModelEvent;
use MetaModels\CoreBundle\Backend\ItemLabelRenderer;
use MetaModels\CoreBundle\EventListener\DcGeneral\LogPersistedItemsListener;
use MetaModels\IFactory;
use MetaModels\IMetaModel;
use MetaModels\ViewCombination\ViewCombination;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * Tests for LogPersistedItemsListener - see ".claude/dcg-systemlog.md" for the background.
 */
#[CoversClass(LogPersistedItemsListener::class)]
final class LogPersistedItemsListenerTest extends TestCase
{
    private function mockModel(string $tableName, string $id, array $properties = []): ModelInterface
    {
        $model = $this->createMock(ModelInterface::class);
        $model->method('getProviderName')->willReturn($tableName);
        $model->method('getId')->willReturn($id);
        $model->method('getPropertiesAsArray')->willReturn($properties);

        return $model;
    }

    private function mockMetaModel(string $name, bool $enableLogging): IMetaModel
    {
        $metaModel = $this->createMock(IMetaModel::class);
        $metaModel->method('getName')->willReturn($name);
        $metaModel->method('get')->with('enableLogging')->willReturn($enableLogging);

        return $metaModel;
    }

    private function mockEnvironment(): EnvironmentInterface
    {
        return $this->createMock(EnvironmentInterface::class);
    }

    /**
     * What CreateHandler actually passes as the original model on a create -
     * DataProviderInterface::getEmptyModel(), not a literal null.
     */
    private function mockEmptyModel(): ModelInterface
    {
        $model = $this->createMock(ModelInterface::class);
        $model->method('getId')->willReturn(null);

        return $model;
    }

    private function listener(
        string $tableName,
        ?IMetaModel $metaModel,
        ?array $screen = null,
        ?LoggerInterface $logger = null,
    ): LogPersistedItemsListener {
        $factory = $this->createMock(IFactory::class);
        $factory->method('collectNames')->willReturn(null === $metaModel ? [] : [$tableName]);
        $factory->method('getMetaModel')->with($tableName)->willReturn($metaModel);

        $viewCombination = $this->createMock(ViewCombination::class);
        $viewCombination->method('getScreen')->with($tableName)->willReturn($screen);

        $tokenParser = $this->createMock(SimpleTokenParser::class);
        $tokenParser->method('parse')->willReturnCallback(
            function (string $pattern, array $tokens): string {
                self::assertSame('##model_name##', $pattern);

                return (string) ($tokens['model_name'] ?? '');
            }
        );

        return new LogPersistedItemsListener(
            $factory,
            $viewCombination,
            new ItemLabelRenderer($tokenParser),
            $logger ?? $this->createMock(LoggerInterface::class)
        );
    }

    public function testLogsCreationWithTheRenderedItemLabel(): void
    {
        $metaModel = $this->mockMetaModel('Employees', true);
        $model     = $this->mockModel('mm_employees', '5', ['name' => 'Jane Doe']);
        $screen    = ['meta' => ['subheadline' => '##model_name##']];

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('info')->with(
            'A new entry "Employees: Jane Doe" has been created'
        );

        $listener = $this->listener('mm_employees', $metaModel, $screen, $logger);
        $listener->onPersist(new PostPersistModelEvent($this->mockEnvironment(), $model, null));
    }

    public function testLogsCreationWhenTheOriginalModelIsAnEmptyPlaceholder(): void
    {
        // The real path (CreateHandler): the original model is never a literal null, only unset.
        $metaModel = $this->mockMetaModel('Employees', true);
        $model     = $this->mockModel('mm_employees', '5', ['name' => 'Jane Doe']);
        $screen    = ['meta' => ['subheadline' => '##model_name##']];

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('info')->with(
            'A new entry "Employees: Jane Doe" has been created'
        );

        $listener = $this->listener('mm_employees', $metaModel, $screen, $logger);
        $listener->onPersist(new PostPersistModelEvent($this->mockEnvironment(), $model, $this->mockEmptyModel()));
    }

    public function testDoesNotLogAnEditOfAnExistingItem(): void
    {
        $metaModel = $this->mockMetaModel('Employees', true);
        $model     = $this->mockModel('mm_employees', '5');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())->method('info');

        $listener = $this->listener('mm_employees', $metaModel, null, $logger);
        $listener->onPersist(new PostPersistModelEvent($this->mockEnvironment(), $model, $model));
    }

    public function testFallsBackToTheRecordIdWithoutAPattern(): void
    {
        $metaModel = $this->mockMetaModel('Employees', true);
        $model     = $this->mockModel('mm_employees', '5');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('info')->with('Deleted entry "Employees: 5"');

        $listener = $this->listener('mm_employees', $metaModel, null, $logger);
        $listener->onDelete(new PostDeleteModelEvent($this->mockEnvironment(), $model));
    }

    public function testLogsDuplicationWithBothLabels(): void
    {
        $metaModel = $this->mockMetaModel('Employees', true);
        $model     = $this->mockModel('mm_employees', '9', ['name' => 'Jane Doe']);
        $source    = $this->mockModel('mm_employees', '5', ['name' => 'John Doe']);
        $screen    = ['meta' => ['subheadline' => '##model_name##']];

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('info')->with(
            'A new entry "Employees: Jane Doe" has been created by duplicating record "Employees: John Doe"'
        );

        $listener = $this->listener('mm_employees', $metaModel, $screen, $logger);
        $listener->onDuplicate(new PostDuplicateModelEvent($this->mockEnvironment(), $model, $source));
    }

    public function testStaysSilentWhenLoggingIsDisabledForTheMetaModel(): void
    {
        $metaModel = $this->mockMetaModel('Employees', false);
        $model     = $this->mockModel('mm_employees', '5');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())->method('info');

        $listener = $this->listener('mm_employees', $metaModel, null, $logger);
        $listener->onDelete(new PostDeleteModelEvent($this->mockEnvironment(), $model));
    }

    public function testIgnoresModelsThatAreNotMetaModelItems(): void
    {
        $model = $this->mockModel('tl_metamodel_rendersettings', '5');

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->never())->method('info');

        // No MetaModel registered for this table - collectNames() stays empty.
        $listener = $this->listener('tl_metamodel_rendersettings', null, null, $logger);
        $listener->onDelete(new PostDeleteModelEvent($this->mockEnvironment(), $model));
    }
}
