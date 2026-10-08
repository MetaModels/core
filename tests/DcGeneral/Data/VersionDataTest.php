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

use MetaModels\DcGeneral\Data\VersionData;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * The stored form of a version.
 */
#[CoversClass(VersionData::class)]
class VersionDataTest extends TestCase
{
    public function testKeepsLanguageAndPropertiesWhenSerialized(): void
    {
        $data = new VersionData('nl', ['title' => 'Bertram', 'tags' => ['2', '3']]);

        $restored = VersionData::fromSerialized($data->serialize(), 'de');

        self::assertSame('nl', $restored?->language);
        self::assertSame(['title' => 'Bertram', 'tags' => ['2', '3']], $restored->properties);
    }

    public function testReadsVersionsWithoutStoredLanguageAsPlainPropertyList(): void
    {
        $restored = VersionData::fromSerialized(\serialize(['id' => 28, 'title' => 'Bertram']), 'de');

        self::assertSame('de', $restored?->language);
        self::assertSame(['id' => 28, 'title' => 'Bertram'], $restored->properties);
    }

    public function testStoresTheLanguageBesideThePropertiesForTheContaoComparison(): void
    {
        $values = \unserialize((new VersionData('nl', ['title' => 'Bertram', 'tags' => ['2']]))->serialize());

        self::assertSame(['#language' => 'nl', 'title' => 'Bertram', 'tags' => ['2']], $values);
    }

    public function testReadsTheNestedFormOfEarlyDevelopmentVersions(): void
    {
        $restored = VersionData::fromSerialized(
            \serialize(['#language' => 'nl', '#properties' => ['title' => 'Bertram']]),
            'de'
        );

        self::assertSame('nl', $restored?->language);
        self::assertSame(['title' => 'Bertram'], $restored->properties);
    }

    public function testUsesTheFallbackLanguageWhenTheStoredOneIsInvalid(): void
    {
        $data = \serialize(['#language' => ['nl'], 'title' => 'Bertram']);

        self::assertSame('de', VersionData::fromSerialized($data, 'de')?->language);
    }

    public function testRejectsDataThatIsNoArray(): void
    {
        self::assertNull(VersionData::fromSerialized(\serialize('text'), 'de'));
    }

    public function testDoesNotInstantiateClasses(): void
    {
        $restored = VersionData::fromSerialized(\serialize(['title' => new \stdClass()]), 'de');

        self::assertFalse(VersionData::isVersionable($restored?->properties['title']));
    }

    public function testObjectsAreNotVersionableNeitherNested(): void
    {
        self::assertTrue(VersionData::isVersionable(['a' => ['b' => 'text', 'c' => 3]]));
        self::assertFalse(VersionData::isVersionable(new \stdClass()));
        self::assertFalse(VersionData::isVersionable(['a' => [new \stdClass()]]));
    }
}
