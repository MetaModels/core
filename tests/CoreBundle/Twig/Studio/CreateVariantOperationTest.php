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

namespace MetaModels\Test\CoreBundle\Twig\Studio;

use Contao\CoreBundle\Twig\Loader\ThemeNamespace;
use Contao\CoreBundle\Twig\Studio\Operation\OperationContext;
use MetaModels\CoreBundle\Twig\Studio\CreateAttributeVariantOperation;
use MetaModels\CoreBundle\Twig\Studio\CreateFilterVariantOperation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Tests which templates the Template Studio offers a new variant for.
 */
#[CoversClass(CreateAttributeVariantOperation::class)]
#[CoversClass(CreateFilterVariantOperation::class)]
final class CreateVariantOperationTest extends TestCase
{
    /** @return iterable<string, array{string, string, bool}> */
    public static function attributeProvider(): iterable
    {
        yield 'base template' => ['metamodels/attribute/file', 'attribute', true];
        yield 'underscore in the type name' => ['metamodels/attribute/checkbox_icon', 'attribute', true];
        yield 'variant has no variants' => ['metamodels/attribute/file/special', 'attribute', false];
        yield 'text template' => ['metamodels/attribute/file.text', 'attribute', false];
        yield 'other group' => ['metamodels/item/prerendered', 'attribute', false];
        yield 'contao template' => ['content_element/text', 'attribute', false];
        yield 'filter widget' => ['metamodels/filter/default', 'filter', true];
    }

    #[DataProvider('attributeProvider')]
    public function testCanExecute(string $identifier, string $group, bool $expected): void
    {
        $operation = 'filter' === $group ? new CreateFilterVariantOperation() : new CreateAttributeVariantOperation();
        $context   = new OperationContext(new ThemeNamespace(), $identifier, 'html.twig', null);

        self::assertSame($expected, $operation->canExecute($context));
    }
}
