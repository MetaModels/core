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

namespace MetaModels\Test\Render;

use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\CoreBundle\Twig\Interop\ContextFactory;
use Contao\CoreBundle\Twig\Loader\ContaoFilesystemLoader;
use MetaModels\Render\TwigTemplateSurrogate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Twig\Environment;

/**
 * Tests for the Twig surrogate that renders a Twig template in place of the legacy MetaModels PHP template.
 */
#[CoversClass(TwigTemplateSurrogate::class)]
final class TwigTemplateSurrogateTest extends TestCase
{
    /**
     * Build a surrogate with the given (mocked) Twig environment and filesystem loader.
     *
     * The context factory is used with a real instance because it is final and cannot be mocked; for plain array
     * data (as used in these tests) it returns the data unchanged.
     */
    private function buildSurrogate(Environment $twig, ContaoFilesystemLoader $loader): TwigTemplateSurrogate
    {
        return new TwigTemplateSurrogate(
            $twig,
            $loader,
            new ContextFactory($this->createStub(ScopeMatcher::class))
        );
    }

    public function testReturnsNullForNonHtml5Format(): void
    {
        $twig   = $this->createMock(Environment::class);
        $loader = $this->createMock(ContaoFilesystemLoader::class);
        $twig->expects(self::never())->method('render');
        $loader->expects(self::never())->method('exists');

        self::assertNull($this->buildSurrogate($twig, $loader)->render('mm_attr_text', 'attribute', 'text', []));
    }

    public function testReturnsNullForEmptyGroup(): void
    {
        $twig   = $this->createMock(Environment::class);
        $loader = $this->createMock(ContaoFilesystemLoader::class);
        $twig->expects(self::never())->method('render');
        $loader->expects(self::never())->method('exists');

        self::assertNull($this->buildSurrogate($twig, $loader)->render('mm_attr_text', '', 'html5', []));
    }

    public function testReturnsNullWhenTwigTemplateDoesNotExist(): void
    {
        $twig   = $this->createMock(Environment::class);
        $loader = $this->createMock(ContaoFilesystemLoader::class);
        $loader->expects(self::once())->method('exists')
            ->with('@Contao/metamodels/attribute/text.html.twig')
            ->willReturn(false);
        $twig->expects(self::never())->method('render');

        self::assertNull($this->buildSurrogate($twig, $loader)->render('mm_attr_text', 'attribute', 'html5', []));
    }

    public function testReturnsNullWhenLegacyHtml5OverrideHasPrecedence(): void
    {
        $twig   = $this->createMock(Environment::class);
        $loader = $this->createMock(ContaoFilesystemLoader::class);
        $loader->method('exists')->willReturn(true);
        $loader->method('getFirst')
            ->with('metamodels/attribute/text')
            ->willReturn('@Contao/metamodels/attribute/text.html5');
        $twig->expects(self::never())->method('render');

        self::assertNull($this->buildSurrogate($twig, $loader)->render('mm_attr_text', 'attribute', 'html5', []));
    }

    public function testRendersTwigTemplateWhenItExists(): void
    {
        $twig   = $this->createMock(Environment::class);
        $loader = $this->createMock(ContaoFilesystemLoader::class);
        $loader->method('exists')
            ->with('@Contao/metamodels/attribute/text.html.twig')
            ->willReturn(true);
        $loader->method('getFirst')
            ->with('metamodels/attribute/text')
            ->willReturn('@Contao/metamodels/attribute/text.html.twig');
        $twig->expects(self::once())->method('render')
            ->with('@Contao/metamodels/attribute/text.html.twig', ['value' => 'x'])
            ->willReturn('<rendered>');

        self::assertSame(
            '<rendered>',
            $this->buildSurrogate($twig, $loader)->render('mm_attr_text', 'attribute', 'html5', ['value' => 'x'])
        );
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: string}>
     */
    public static function provideIdentifierMapping(): array
    {
        return [
            'attribute strips the mm_attr_ prefix'      =>
                ['mm_attr_text', 'attribute', '@Contao/metamodels/attribute/text.html.twig'],
            'filter strips the mm_filteritem_ prefix'   =>
                ['mm_filteritem_default', 'filter', '@Contao/metamodels/filter/default.html.twig'],
            'filter strips the mm_filter_ prefix'       =>
                ['mm_filter_default', 'filter', '@Contao/metamodels/filter/default.html.twig'],
            'item strips the metamodel_ prefix'         =>
                ['metamodel_prerendered', 'item', '@Contao/metamodels/item/prerendered.html.twig'],
            'item strips the mm_ prefix'                =>
                ['mm_default', 'item', '@Contao/metamodels/item/default.html.twig'],
            'a name without a known prefix is kept'     =>
                ['custom', 'attribute', '@Contao/metamodels/attribute/custom.html.twig'],
        ];
    }

    #[DataProvider('provideIdentifierMapping')]
    public function testBuildsTwigIdentifierFromGroupAndName(
        string $name,
        string $group,
        string $expectedCandidate
    ): void {
        $twig   = $this->createMock(Environment::class);
        $loader = $this->createMock(ContaoFilesystemLoader::class);
        $twig->expects(self::never())->method('render');
        $loader->expects(self::once())->method('exists')->with($expectedCandidate)->willReturn(false);

        self::assertNull($this->buildSurrogate($twig, $loader)->render($name, $group, 'html5', []));
    }
}
