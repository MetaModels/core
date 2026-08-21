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

namespace MetaModels\CoreBundle\Test\Backend;

use Contao\CoreBundle\String\SimpleTokenParser;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use MetaModels\CoreBundle\Backend\ItemBreadcrumbBuilder;
use MetaModels\CoreBundle\Backend\ItemLabelRenderer;
use MetaModels\ViewCombination\ViewCombination;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * This tests the breadcrumb trail builder, in particular how it handles a chain that ends at a
 * level with no resolvable screen - either a plain Contao table or a MetaModel the current user
 * has no rights to via tl_metamodel_dca_combine.
 */
#[CoversClass(ItemBreadcrumbBuilder::class)]
final class ItemBreadcrumbBuilderTest extends TestCase
{
    private Connection $connection;

    private ?array $originalBeMod = null;

    protected function setUp(): void
    {
        $this->connection = DriverManager::getConnection(['url' => 'sqlite:///:memory:'], new Configuration());
        $this->connection->executeStatement(
            'CREATE TABLE tl_metamodel (id INTEGER PRIMARY KEY, tableName TEXT, name TEXT)'
        );
        $this->connection->executeStatement('CREATE TABLE mm_child (id INTEGER PRIMARY KEY, pid INTEGER)');
        $this->connection->executeStatement('CREATE TABLE mm_parent (id INTEGER PRIMARY KEY)');

        $this->originalBeMod  = $GLOBALS['BE_MOD'] ?? null;
        $GLOBALS['BE_MOD']    = [];
    }

    protected function tearDown(): void
    {
        if (null === $this->originalBeMod) {
            unset($GLOBALS['BE_MOD']);
        } else {
            $GLOBALS['BE_MOD'] = $this->originalBeMod;
        }
    }

    public function testLinksEveryLevelOfAnOrdinaryChain(): void
    {
        $this->connection->executeStatement('INSERT INTO mm_parent (id) VALUES (2)');
        $this->connection->executeStatement('INSERT INTO mm_child (id, pid) VALUES (5, 2)');

        $builder = $this->builder(
            [
                'mm_child'  => $this->screen('ctable', 'mm_parent', 'Trips', 'Trip'),
                'mm_parent' => $this->screen('standalone', '', 'Employees', 'Employee'),
            ]
        );

        $trail = $builder->build('mm_child', 'mm_parent::2', 'mm_child::5');

        self::assertSame(
            [
                [
                    'table' => 'mm_parent',
                    'label' => 'Employees: Employee',
                    'url'   => 'metamodels.metamodel?tableName=mm_parent',
                ],
                [
                    'table' => 'mm_child',
                    'label' => 'Trips: Trip',
                    'url'   => 'metamodels.metamodel?tableName=mm_parent&table=mm_child&pid=mm_parent%3A%3A2',
                ],
            ],
            $trail
        );
    }

    public function testAContaoTableAtTheEndStaysUnlinkedButDoesNotBreakTheRestOfTheChain(): void
    {
        $builder = $this->builder(['mm_child' => $this->screen('ctable', 'tl_unknown', 'Trips', '')]);

        $trail = $builder->build('mm_child', null, null);

        self::assertSame(
            [
                ['table' => 'tl_unknown', 'label' => 'tl_unknown', 'url' => null],
                ['table' => 'mm_child', 'label' => 'Trips', 'url' => 'metamodels.metamodel?tableName=mm_child'],
            ],
            $trail
        );
    }

    public function testAContaoTableAtTheEndLinksIntoItsOwnBackendModuleWhenOneListsIt(): void
    {
        $GLOBALS['BE_MOD'] = ['content' => ['unknown_module' => ['tables' => ['tl_unknown']]]];

        $builder = $this->builder(['mm_child' => $this->screen('ctable', 'tl_unknown', 'Trips', '')]);

        $trail = $builder->build('mm_child', null, null);

        self::assertSame('contao_backend?do=unknown_module', $trail[0]['url']);
    }

    public function testAMetaModelWithoutRightsIsNamedButStaysUnlinked(): void
    {
        $this->connection->executeStatement(
            "INSERT INTO tl_metamodel (id, tableName, name) VALUES (1, 'mm_hidden', 'Hidden Model')"
        );
        // A module happening to list the table must not turn into a link - a MetaModel without
        // a screen is a rights gap, not a plain Contao table, and Contao's own module is the
        // wrong place to send the user to.
        $GLOBALS['BE_MOD'] = ['content' => ['hidden_module' => ['tables' => ['mm_hidden']]]];

        $builder = $this->builder(['mm_child' => $this->screen('ctable', 'mm_hidden', 'Trips', '')]);

        $trail = $builder->build('mm_child', null, null);

        self::assertSame(
            [
                ['table' => 'mm_hidden', 'label' => 'Hidden Model', 'url' => null],
                ['table' => 'mm_child', 'label' => 'Trips', 'url' => 'metamodels.metamodel?tableName=mm_child'],
            ],
            $trail
        );
    }

    public function testReturnsNothingForAStandaloneListing(): void
    {
        $builder = $this->builder(['mm_child' => $this->screen('standalone', '', 'Trips', '')]);

        self::assertSame([], $builder->build('mm_child', null, null));
    }

    /**
     * @param array{rendertype: string, ptable: string, subheadline: string} $meta
     *
     * @return array{meta: array{rendertype: string, ptable: string, subheadline: string}, label: array<string, string>}
     */
    private function screen(string $rendertype, string $ptable, string $label, string $subheadline): array
    {
        return [
            'meta'  => ['rendertype' => $rendertype, 'ptable' => $ptable, 'subheadline' => $subheadline],
            'label' => ['' => $label],
        ];
    }

    /**
     * @param array<string, array{meta: array<string, string>, label: array<string, string>}> $screens
     */
    private function builder(array $screens): ItemBreadcrumbBuilder
    {
        $viewCombination = $this->createMock(ViewCombination::class);
        $viewCombination->method('getScreen')->willReturnCallback(
            static fn(string $tableName): ?array => $screens[$tableName] ?? null
        );

        $labelRenderer = new ItemLabelRenderer($this->createMock(SimpleTokenParser::class));

        $urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturnCallback(
            static fn(string $route, array $parameters = []): string => $route . '?' . \http_build_query($parameters)
        );

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->method('getCurrentRequest')->willReturn(null);

        return new ItemBreadcrumbBuilder(
            $viewCombination,
            $labelRenderer,
            $this->connection,
            $urlGenerator,
            $requestStack
        );
    }
}
