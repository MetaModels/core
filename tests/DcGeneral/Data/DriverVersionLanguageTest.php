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

namespace MetaModels\Test\DcGeneral\Data;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Platforms\SQLitePlatform;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Result;
use MetaModels\Attribute\BaseComplex;
use MetaModels\DcGeneral\Data\Driver;
use MetaModels\DcGeneral\Data\Model;
use MetaModels\IItem;
use MetaModels\Item;
use MetaModels\TranslatedMetaModel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * A version has to remember the language of the edit mask - translated attributes yield language dependent widget
 * values (e.g. tags sorted by label), which are checked against each other when the version is loaded.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.Superglobals)
 */
#[CoversClass(Driver::class)]
#[CoversClass(Model::class)]
class DriverVersionLanguageTest extends TestCase
{
    /** @var list<string> The MetaModel language at the time the attribute converted a value. */
    private array $convertedIn = [];

    /** @var list<array{string, array<string, mixed>}> The rows passed to Connection::insert(). */
    private array $inserted = [];

    private TranslatedMetaModel $metaModel;

    #[\Override]
    protected function setUp(): void
    {
        $GLOBALS['TL_LANGUAGE'] = 'de';

        $this->convertedIn = [];
        $this->inserted    = [];
        $this->metaModel   = new TranslatedMetaModel(
            [
                'tableName' => 'mm_test',
                'languages' => ['de' => ['isfallback' => true], 'nl' => ['isfallback' => false]],
            ],
            $this->createStub(EventDispatcherInterface::class),
            $this->createStub(Connection::class)
        );
        $this->metaModel->addAttribute($this->createTagsAttribute());
    }

    #[\Override]
    protected function tearDown(): void
    {
        unset($GLOBALS['TL_LANGUAGE']);
    }

    public function testSaveVersionStoresTheLanguageOfTheModel(): void
    {
        $driver = $this->createDriver();
        $model  = new Model(new Item($this->metaModel, ['id' => '28', 'tags' => ['2', '3']]), 'nl');

        $driver->saveVersion($model, 'admin');

        $data = $this->storedData();
        self::assertSame('nl', $data['#language']);
        self::assertSame(['2', '3'], $data['tags']);
        // The widget values must have been created in the language of the model, not the one of the provider.
        self::assertSame(['nl'], $this->convertedIn);
        // ... and the language is handed back afterwards.
        self::assertSame('de', $this->metaModel->getLanguage());
    }

    public function testSaveVersionUsesTheProviderLanguageForModelsWithoutLanguage(): void
    {
        $driver = $this->createDriver();
        $driver->setCurrentLanguage('nl');

        $driver->saveVersion(new Model(new Item($this->metaModel, ['id' => '28', 'tags' => ['2']])), 'admin');

        self::assertSame('nl', $this->storedData()['#language']);
    }

    public function testGetVersionRestoresTheStoredLanguage(): void
    {
        $driver = $this->createDriver(['#language' => 'nl', 'id' => 28, 'tags' => ['3', '2']]);

        $model = $driver->getVersion('28', 1);

        self::assertInstanceOf(Model::class, $model);
        self::assertSame('nl', $model->getLanguage());
        // The values were checked in the stored language although the provider works in another one.
        self::assertSame(['nl'], \array_unique($this->convertedIn));
        self::assertSame(['3', '2'], $model->getItem()?->get('tags'));
    }

    public function testGetVersionReadsVersionsWithoutStoredLanguage(): void
    {
        $driver = $this->createDriver(['id' => 28, 'tags' => ['2', '3']]);

        $model = $driver->getVersion('28', 1);

        self::assertInstanceOf(Model::class, $model);
        self::assertSame('de', $model->getLanguage());
        self::assertSame(['2', '3'], $model->getItem()?->get('tags'));
    }

    public function testSaveWritesInTheLanguageOfTheModel(): void
    {
        $savedIn = [];
        $item    = $this->createStub(IItem::class);
        $item->method('save')->willReturnCallback(
            function () use (&$savedIn): IItem {
                $savedIn[] = $this->metaModel->getLanguage();

                return $this->createStub(IItem::class);
            }
        );

        $this->createDriver()->save(new Model($item, 'nl'), 1);

        self::assertSame(['nl'], $savedIn);
        self::assertSame('de', $this->metaModel->getLanguage());
    }

    /**
     * Content elements come as model objects - those can not be stored in a version.
     */
    public function testSaveVersionLeavesOutAttributesHandingOutObjects(): void
    {
        $this->metaModel->addAttribute($this->createObjectAttribute());
        $driver = $this->createDriver();
        $model  = new Model(new Item($this->metaModel, ['id' => '28', 'tags' => ['2'], 'elements' => [1]]), 'de');

        $driver->saveVersion($model, 'admin');

        $properties = $this->storedData();
        self::assertSame(['2'], $properties['tags']);
        self::assertArrayNotHasKey('elements', $properties);
    }

    private function createObjectAttribute(): BaseComplex
    {
        $attribute = $this->createStub(BaseComplex::class);
        $attribute->method('getColName')->willReturn('elements');
        $attribute->method('getMetaModel')->willReturn($this->metaModel);
        $attribute->method('valueToWidget')->willReturn([new \stdClass()]);

        return $attribute;
    }

    /**
     * @param array<string, mixed>|null $versionData The data the version table returns, null for none.
     */
    private function createDriver(?array $versionData = null): Driver
    {
        $result = $this->createStub(Result::class);
        $result->method('fetchOne')->willReturn(0);
        $result->method('fetchAssociative')->willReturn(
            null === $versionData ? false : ['data' => \serialize($versionData)]
        );

        $connection = $this->createStub(Connection::class);
        $connection->method('getDatabasePlatform')->willReturn(new SQLitePlatform());
        $connection->method('createQueryBuilder')->willReturnCallback(
            static fn (): QueryBuilder => new QueryBuilder($connection)
        );
        $connection->method('executeQuery')->willReturn($result);
        $connection->method('update')->willReturn(1);
        $connection->method('insert')->willReturnCallback(
            function (string $table, array $data): int {
                $this->inserted[] = [$table, $data];

                return 1;
            }
        );

        $driver = new Driver();
        $driver->setBaseConfig(['source' => 'mm_test', 'metaModel' => $this->metaModel]);
        $driver->setConnection($connection);
        $driver->setDispatcher($this->createStub(EventDispatcherInterface::class));
        $driver->setCurrentLanguage('de');

        return $driver;
    }

    /**
     * Attribute whose widget conversion records the language the MetaModel is in at that moment.
     */
    private function createTagsAttribute(): BaseComplex
    {
        $attribute = $this->createStub(BaseComplex::class);
        $attribute->method('getColName')->willReturn('tags');
        $attribute->method('getMetaModel')->willReturn($this->metaModel);
        $attribute->method('valueToWidget')->willReturnCallback(
            function (mixed $value): mixed {
                $this->convertedIn[] = $this->metaModel->getLanguage();

                return $value;
            }
        );
        $attribute->method('widgetToValue')->willReturnCallback(
            function (mixed $value): mixed {
                $this->convertedIn[] = $this->metaModel->getLanguage();

                return $value;
            }
        );

        return $attribute;
    }

    /**
     * @return array<string, mixed>
     */
    private function storedData(): array
    {
        self::assertCount(1, $this->inserted);
        [$table, $row] = $this->inserted[0];
        self::assertSame('tl_version', $table);

        return \unserialize($row['data'], ['allowed_classes' => false]);
    }
}
