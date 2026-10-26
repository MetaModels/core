<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2025 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2025 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\Test\Render;

use MetaModels\Render\TwigTemplateNames;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Tests for the mapping between legacy template names and Twig identifiers.
 */
#[CoversClass(TwigTemplateNames::class)]
final class TwigTemplateNamesTest extends TestCase
{
    public function testIdentifierStripsTheLegacyPrefix(): void
    {
        self::assertSame('metamodels/attribute/text', TwigTemplateNames::identifier('mm_attr_text', 'attribute'));
        self::assertSame('metamodels/item/prerendered', TwigTemplateNames::identifier('metamodel_prerendered', 'item'));
        self::assertNull(TwigTemplateNames::identifier('mm_attr_text', ''));
    }

    public function testLegacyNamesAreDerivedFromIdentifiers(): void
    {
        $identifiers = [
            'metamodels/attribute/text',
            'metamodels/attribute/text.text',
            'metamodels/attribute/checkbox_icon',
            'metamodels/item/prerendered',
            'metamodels/filter/default',
            'mm_filter_default',
        ];

        self::assertSame(['mm_attr_text'], TwigTemplateNames::legacyNames($identifiers, 'mm_attr_text'));
        self::assertSame(['mm_attr_checkbox_icon'], TwigTemplateNames::legacyNames($identifiers, 'mm_attr_checkbox'));
        self::assertSame(['metamodel_prerendered'], TwigTemplateNames::legacyNames($identifiers, 'metamodel_'));
        self::assertSame([], TwigTemplateNames::legacyNames($identifiers, 'mm_filter_'));
    }
}
