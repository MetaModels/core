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
use MetaModels\Attribute\BaseComplex;
use MetaModels\DcGeneral\Data\Driver;
use MetaModels\DcGeneral\Data\Model;
use MetaModels\Item;
use MetaModels\TranslatedMetaModel;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Whether a version is a new one is decided by what the edit mask shows, not by the internal item values.
 *
 * @SuppressWarnings(PHPMD.Superglobals)
 */
#[CoversClass(Driver::class)]
class DriverSameModelsTest extends TestCase
{
    private TranslatedMetaModel $metaModel;

    private Driver $driver;

    #[\Override]
    protected function setUp(): void
    {
        $GLOBALS['TL_LANGUAGE'] = 'de';

        $this->metaModel = new TranslatedMetaModel(
            ['languages' => ['de' => ['isfallback' => true], 'nl' => ['isfallback' => false]]],
            $this->createStub(EventDispatcherInterface::class),
            $this->createStub(Connection::class)
        );
        $this->metaModel->addAttribute($this->createTextAttribute());

        $this->driver = new Driver();
        $this->driver->setBaseConfig(['source' => 'mm_test', 'metaModel' => $this->metaModel]);
    }

    #[\Override]
    protected function tearDown(): void
    {
        unset($GLOBALS['TL_LANGUAGE']);
    }

    /**
     * A saved item holds the raw database row, one built from a version the output of widgetToValue().
     */
    public function testModelsShowingTheSameValueAreTheSame(): void
    {
        $saved   = $this->createModel(['id' => 243, 'tstamp' => 1, 'item_id' => 28, 'value' => 'Bertram'], 'de');
        $rebuilt = $this->createModel(['tstamp' => 2, 'item_id' => '28', 'value' => 'Bertram'], 'de');

        self::assertTrue($this->driver->sameModels($saved, $rebuilt));
    }

    public function testModelsShowingDifferentValuesAreNotTheSame(): void
    {
        $saved   = $this->createModel(['id' => 243, 'tstamp' => 1, 'value' => 'Bertram'], 'de');
        $changed = $this->createModel(['tstamp' => 2, 'value' => 'Bertrand'], 'de');

        self::assertFalse($this->driver->sameModels($saved, $changed));
    }

    /**
     * The widget value of a translated attribute depends on the language, it is read in the one of each model.
     */
    public function testEachModelIsReadInItsOwnLanguage(): void
    {
        $german = $this->createModel(['value' => 'Bertram'], 'de');
        $dutch  = $this->createModel(['value' => 'Bertram'], 'nl');

        self::assertFalse($this->driver->sameModels($german, $dutch));
        self::assertSame('de', $this->metaModel->getLanguage());
    }

    /**
     * Objects (content elements) are neither stored in a version nor compared - they would never match.
     */
    public function testAttributesHandingOutObjectsAreIgnored(): void
    {
        $elements = $this->createStub(BaseComplex::class);
        $elements->method('getColName')->willReturn('elements');
        $elements->method('valueToWidget')->willReturnCallback(static fn (): array => [new \stdClass()]);
        $this->metaModel->addAttribute($elements);

        $first  = $this->createModel(['value' => 'Bertram'], 'de');
        $second = $this->createModel(['value' => 'Bertram'], 'de');

        self::assertTrue($this->driver->sameModels($first, $second));
    }

    /**
     * @param array<string, mixed> $row      The internal value of the attribute "title".
     * @param string               $language The language of the model.
     */
    private function createModel(array $row, string $language): Model
    {
        return new Model(new Item($this->metaModel, ['id' => '28', 'title' => $row]), $language);
    }

    /**
     * Attribute showing the text of the row, with the language appended - like a translated attribute would.
     */
    private function createTextAttribute(): BaseComplex
    {
        $attribute = $this->createStub(BaseComplex::class);
        $attribute->method('getColName')->willReturn('title');
        $attribute->method('valueToWidget')->willReturnCallback(
            fn (array $row): string => $row['value'] . '@' . $this->metaModel->getLanguage()
        );

        return $attribute;
    }
}
