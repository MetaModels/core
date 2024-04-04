<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2024 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Test\Filter\Setting;

use Contao\CoreBundle\Framework\Adapter;
use Contao\CoreBundle\Framework\ContaoFramework;
use Contao\Input;
use Contao\PageModel;
use Contao\Session as ContaoSession;
use Contao\InsertTags;
use Doctrine\DBAL\Connection;
use MetaModels\CoreBundle\Contao\InsertTag\ReplaceParam;
use MetaModels\CoreBundle\Contao\InsertTag\ReplaceTableName;
use MetaModels\CoreBundle\Contao\InsertTag\ResolveLanguageTag;
use MetaModels\Filter\Filter;
use MetaModels\Filter\Setting\CustomSql;
use MetaModels\Filter\Setting\ICollection;
use MetaModels\IMetaModel;
use MetaModels\IMetaModelsServiceContainer;
use MetaModels\Test\AutoLoadingTestCase;
use ReflectionProperty;
use Symfony\Component\DependencyInjection\Container;
use MetaModels\Filter\Rules\SimpleQuery;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Unit test for testing the CustomSql filter setting.
 *
 * @covers \MetaModels\Filter\Setting\CustomSql
 * @covers \MetaModels\CoreBundle\Contao\InsertTag\ReplaceParam
 * @covers \MetaModels\CoreBundle\Contao\InsertTag\ReplaceTableName
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CustomSqlTest extends AutoLoadingTestCase
{
    /**
     * Mock a CustomSql with parseInsertTags disabled.
     *
     * @param array                 $properties The initialization data.
     * @param string                $tableName  The table name of the MetaModel to mock.
     * @param array<string, object> $services   The services to inject.
     *
     * @return CustomSql
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    private function mockCustomSql(
        array $properties = [],
        string $tableName = 'mm_unittest',
        array $services = []
    ): CustomSql {
        $metaModel = $this->getMockForAbstractClass(IMetaModel::class);
        $metaModel->method('getTableName')->willReturn($tableName);

        $filterSetting = $this->getMockForAbstractClass(ICollection::class);
        $filterSetting->method('getMetaModel')->willReturn($metaModel);

        if (!isset($services[InsertTags::class])) {
            $services[InsertTags::class] = $insertTags = $this
                ->getMockBuilder(InsertTags::class)
                ->onlyMethods(['replace'])
                ->disableOriginalConstructor()
                ->getMock();

            $insertTags->method('replace')->willReturnCallback(function ($buffer) {
                return str_replace(
                    ['{{', '::', '}}'],
                    ['<contao_insert_tag>', '<sep>', '</contao_insert_tag>'],
                    $buffer
                );
            });
        }
        if (!isset($services[Connection::class])) {
            $services[Connection::class] = $this
                ->getMockBuilder(Connection::class)
                ->disableOriginalConstructor()
                ->getMock();
        }
        if (!isset($services[Input::class])) {
            $services[Input::class] = $this
                ->getMockBuilder(Adapter::class)
                ->disableOriginalConstructor()
                ->addMethods(['cookie', 'get', 'post'])
                ->getMock();
        }
        if (!isset($services[Session::class])) {
            $services[Session::class] = $this->mockSession([]);
        }
        if (!isset($services[PageModel::class])) {
            $services[PageModel::class] = $this
                ->getMockBuilder(PageModel::class)
                ->disableOriginalConstructor()
                ->onlyMethods([])
                ->getMock();
            $services[PageModel::class]->language = 'en';
        }
        if (!isset($services[Request::class])) {
            $services[Request::class] = new Request();
            $services[Request::class]->attributes->set('pageModel', $services[PageModel::class]);
            $services[Request::class]->attributes->set('_locale', $services[PageModel::class]->language);
            $services[Request::class]->setSession($services[Session::class]);
        }
        if (!isset($services[RequestStack::class])) {
            $services[RequestStack::class] = new RequestStack();
            $services[RequestStack::class]->push($services[Request::class]);
        }

        if (!isset($services[ContaoSession::class])) {
            $services[ContaoSession::class] = $this->mockLegacySession([]);
        }
        if (!isset($services[IMetaModelsServiceContainer::class])) {
            $services[IMetaModelsServiceContainer::class] = $this
                ->getMockBuilder(IMetaModelsServiceContainer::class)
                ->disableOriginalConstructor()
                ->getMockForAbstractClass();
        }
        if (!isset($services[ContaoFramework::class])) {
            $services[ContaoFramework::class] = $this
                ->getMockBuilder(ContaoFramework::class)
                ->disableOriginalConstructor()
                ->onlyMethods(['getAdapter'])
                ->getMock();
            $services[ContaoFramework::class]->method('getAdapter')->willReturnCallback(
                fn (string $class) => match ($class) {
                    Input::class => $services[Input::class],
                    default => throw new \RuntimeException('Override ContaoFramework instance'),
                }
            );
        }
        $services[ReplaceTableName::class] = new ReplaceTableName();
        $services[ReplaceParam::class] = new ReplaceParam(
            $services[ContaoFramework::class],
            $services[RequestStack::class]
        );
        $services[ResolveLanguageTag::class] = new ResolveLanguageTag($services[RequestStack::class]);

        $container = new Container();
        foreach (CustomSql::getSubscribedServices() as $serviceId) {
            $container->set($serviceId, $services[$serviceId]);
        }

        return new CustomSql($filterSetting, $properties, $container);
    }

    private function mockLegacySession(array $values): ContaoSession
    {
        $session = $this
            ->getMockBuilder(ContaoSession::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['get'])
            ->getMock();

        $map = [];
        foreach ($values as $key => $value) {
            // key => value
            $map[] = [$key, $value];
        }
        $session->method('get')->willReturnMap($map);


        return $session;
    }

    private function mockSession(array $values): Session
    {
        $session = $this
            ->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getBag'])
            ->getMock();

        $map = [];
        foreach ($values as $key => $value) {
            // key, default => value
            $map[] = [$key, null, $value];
        }
        $sessionBag = $this->getMockForAbstractClass(AttributeBagInterface::class);
        $sessionBag->method('get')->willReturnMap($map);

        $session->method('getBag')->with('contao_frontend')->willReturn($sessionBag);

        return $session;
    }

    /**
     * Internal convenience method to the generating method and to extract the values from the filter rule.
     *
     * @param CustomSql             $instance  The instance.
     * @param array<string, string> $filterUrl The filter url to process.
     *
     * @return array{sql: string, params: array}
     */
    protected function generateSql(CustomSql $instance, array $filterUrl = []): array
    {
        $filter = new Filter($this->getMockForAbstractClass(IMetaModel::class));

        $instance->prepareRules($filter, $filterUrl);

        $reflection = new ReflectionProperty($filter, 'arrFilterRules');
        $reflection->setAccessible(true);
        $rules = $reflection->getValue($filter);

        $reflection = new ReflectionProperty(SimpleQuery::class, 'queryString');
        $reflection->setAccessible(true);
        $sql = $reflection->getValue($rules[0]);

        $reflection = new ReflectionProperty(SimpleQuery::class, 'params');
        $reflection->setAccessible(true);
        $params = $reflection->getValue($rules[0]);

        return ['sql' => $sql, 'params' => $params];
    }

    /**
     * Run the test and assert that the generated values match the expected ones.
     *
     * @param CustomSql $setting            The filter setting to test.
     * @param string    $expectedSql        The expected Sql query.
     * @param array     $expectedParameters The expected parameters.
     * @param array     $filterUrl          The filter url to process.
     * @param string    $message            An optional message to display upon failure.
     *
     * @return void
     */
    protected function assertGeneratedSqlIs(
        CustomSql $setting,
        string $expectedSql,
        array $expectedParameters,
        array $filterUrl,
        string $message = ''
    ): void {
        $sql = $this->generateSql($setting, $filterUrl);

        self::assertEquals($expectedSql, $sql['sql'], $message);
        self::assertEquals($expectedParameters, $sql['params'], $message);
    }

    /**
     * Test a literal query.
     */
    public function testPlain(): void
    {
        $setting = $this->mockCustomSql(['customsql' => 'SELECT id FROM mm_mymetamodel WHERE page_id=1'], 'tableName');

        $this->assertGeneratedSqlIs($setting, 'SELECT id FROM mm_mymetamodel WHERE page_id=1', [], []);
    }

    /**
     * Test table name replacement.
     */
    public function testTableName(): void
    {
        $setting = $this->mockCustomSql(['customsql' => 'SELECT id FROM {{table}} WHERE page_id=1'], 'tableName');

        $this->assertGeneratedSqlIs($setting, 'SELECT id FROM tableName WHERE page_id=1', [], []);
    }

    /**
     * Test insert tag replacement.
     */
    public function testInsertTags(): void
    {
        $setting = $this->mockCustomSql(
            ['customsql' => 'SELECT id FROM tableName WHERE page_id={{page::id}}'],
            'tableName'
        );

        $this->assertGeneratedSqlIs(
            $setting,
            'SELECT id FROM tableName WHERE page_id=<contao_insert_tag>page<sep>id</contao_insert_tag>',
            [],
            []
        );
    }

    /**
     * Test secure insert tag replacement.
     */
    public function testSecureInsertTags(): void
    {
        $setting = $this->mockCustomSql(
            ['customsql' => 'SELECT id FROM tableName WHERE page_id={{secure::page::id}}'],
            'tableName'
        );

        $this->assertGeneratedSqlIs(
            $setting,
            'SELECT id FROM tableName WHERE page_id=?',
            ['<contao_insert_tag>page<sep>id</contao_insert_tag>'],
            []
        );
    }

    /**
     * Test request variable replacement.
     */
    public function testRequestGetWithEmptyParameter(): void
    {
        $input = $this
            ->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->addMethods(['cookie', 'get', 'post'])
            ->getMock();
        $input->expects(self::once())->method('get')->with('category')->willReturn(null);

        $setting = $this->mockCustomSql(
            ['customsql' => 'SELECT id FROM tableName WHERE catname={{param::get?name=category&default=defaultcat}}'],
            'tableName',
            [Input::class => $input]
        );

        $this->assertGeneratedSqlIs(
            $setting,
            'SELECT id FROM tableName WHERE catname=?',
            ['defaultcat'],
            [],
            'See https://github.com/MetaModels/core/issues/376'
        );
    }

    /**
     * Test request variable replacement with insert tag.
     */
    public function testRequestGetWithEmptyParameterAndInsertTag(): void
    {
        $input = $this
            ->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->addMethods(['cookie', 'get', 'post'])
            ->getMock();
        $input->expects(self::once())->method('get')->with('category')->willReturn(null);

        $setting = $this->mockCustomSql(
            ['customsql' =>
                 'SELECT id FROM tableName WHERE catname={{param::get?name=category&default={{page::alias}}}}'],
            'tableName',
            [Input::class => $input]
        );

        $this->assertGeneratedSqlIs(
            $setting,
            'SELECT id FROM tableName WHERE catname=?',
            ['<contao_insert_tag>page<sep>alias</contao_insert_tag>'],
            [],
            'See https://github.com/MetaModels/core/issues/880'
        );
    }

    /**
     * Test request variable replacement.
     */
    public function testRequestGetParameter(): void
    {
        $input = $this
            ->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->addMethods(['cookie', 'get', 'post'])
            ->getMock();
        $input
            ->expects(self::once())
            ->method('get')
            ->with('category')
            ->willReturn('category name');

        $setting = $this->mockCustomSql(
            ['customsql' => 'SELECT id FROM tableName WHERE catname={{param::get?name=category&default=defaultcat}}'],
            'tableName',
            [Input::class => $input]
        );

        $this->assertGeneratedSqlIs($setting, 'SELECT id FROM tableName WHERE catname=?', ['category name'], []);
    }

    /**
     * Test request variable replacement.
     */
    public function testRequestPostParameter(): void
    {
        $input = $this
            ->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->addMethods(['cookie', 'get', 'post'])
            ->getMock();
        $input
            ->expects($this->once())
            ->method('post')
            ->with('category')
            ->willReturn('category name');

        $setting = $this->mockCustomSql(
            ['customsql' => 'SELECT id FROM tableName WHERE catname={{param::post?name=category}}'],
            'tableName',
            [Input::class => $input]
        );

        $this->assertGeneratedSqlIs($setting, 'SELECT id FROM tableName WHERE catname=?', ['category name'], []);
    }

    /**
     * Test request variable replacement.
     */
    public function testRequestCookie(): void
    {
        $input = $this
            ->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->addMethods(['cookie', 'get', 'post'])
            ->getMock();
        $input
            ->expects(self::once())
            ->method('cookie')
            ->with('category')
            ->willReturn('category name');

        $setting = $this->mockCustomSql(
            ['customsql' => 'SELECT id FROM tableName WHERE catname={{param::cookie?name=category}}'],
            'tableName',
            [Input::class => $input]
        );

        $this->assertGeneratedSqlIs($setting, 'SELECT id FROM tableName WHERE catname=?', ['category name'], []);
    }

    /**
     * Test variable replacement via session value.
     */
    public function testValueFromSession(): void
    {
        $setting = $this->mockCustomSql(
            ['customsql' => 'SELECT id FROM tableName WHERE catname={{param::session?name=category}}'],
            'tableName',
            [Session::class => $this->mockSession(['category' => 'category name'])]
        );

        $this->assertGeneratedSqlIs($setting, 'SELECT id FROM tableName WHERE catname=?', ['category name'], []);
    }

    /**
     * Test variable replacement via session value.
     */
    public function testValueFromSessionAggregated(): void
    {
        $setting = $this->mockCustomSql(
            ['customsql' => 'SELECT id FROM tableName WHERE catname IN ({{param::session?name=category&aggregate}})'],
            'tableName',
            [Session::class => $this->mockSession(['category' => ['first', 'second']])]
        );

        $this->assertGeneratedSqlIs(
            $setting,
            'SELECT id FROM tableName WHERE catname IN (?)',
            ['first,second'],
            []
        );
    }

    /**
     * Test request variable replacement.
     */
    public function testValueFromSessionEmpty(): void
    {
        $setting = $this->mockCustomSql(
            ['customsql' =>
                 'SELECT id FROM tableName WHERE catname={{param::session?name=category&default=defaultcat}}'],
            'tableName',
            [Session::class => $this->mockSession(['category' => null])]
        );

        $this->assertGeneratedSqlIs(
            $setting,
            'SELECT id FROM tableName WHERE catname=?',
            ['defaultcat'],
            []
        );
    }

    /**
     * Test variable replacement via session value.
     */
    public function testValueFromFilterUrl(): void
    {
        $setting = $this->mockCustomSql(
            ['customsql' => 'SELECT id FROM tableName WHERE catname={{param::filter?name=category}}'],
            'tableName'
        );

        $this->assertGeneratedSqlIs(
            $setting,
            'SELECT id FROM tableName WHERE catname=?',
            ['category name'],
            ['category' => 'category name']
        );
    }

    /**
     * Test request variable replacement.
     */
    public function testRequestParameterAggregated(): void
    {
        $input = $this
            ->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->addMethods(['cookie', 'get', 'post'])
            ->getMock();
        $input
            ->expects(self::once())
            ->method('get')
            ->with('categories')
            ->willReturn(['first', 'second']);

        $setting = $this->mockCustomSql(
            ['customsql' => 'SELECT id FROM tableName WHERE catname IN ({{param::get?name=categories&aggregate}})'],
            'tableName',
            [Input::class => $input]
        );

        $this->assertGeneratedSqlIs(
            $setting,
            'SELECT id FROM tableName WHERE catname IN (?)',
            ['first,second'],
            []
        );
    }

    /**
     * Test request variable replacement.
     */
    public function testRequestParameterAggregatedSet(): void
    {
        $input = $this
            ->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->addMethods(['cookie', 'get', 'post'])
            ->getMock();
        $input
            ->expects(self::once())
            ->method('get')
            ->with('ids')
            ->willReturn(['1', '2']);

        $setting = $this->mockCustomSql(
            ['customsql' => 'SELECT id FROM tableName WHERE catids IN ({{param::get?name=ids&aggregate=set}})'],
            'tableName',
            [Input::class => $input]
        );

        $this->assertGeneratedSqlIs($setting, 'SELECT id FROM tableName WHERE catids IN (?,?)', ['1','2'], []);
    }

    /**
     * Test with an empty string value.
     */
    public function testWithEmptyStringValue(): void
    {
        $setting = $this->mockCustomSql(
            ['customsql' => 'SELECT id FROM {{table}} WHERE otherparam={{param::filter?name=otherparam}}'],
            'tableName'
        );

        $this->assertGeneratedSqlIs(
            $setting,
            'SELECT id FROM tableName WHERE otherparam=?',
            [''],
            ['otherparam' => '']
        );
    }

    /**
     * Test with a numeric zero value.
     */
    public function testWithZeroValue(): void
    {
        $setting = $this->mockCustomSql(
            ['customsql' => 'SELECT id FROM {{table}} WHERE otherparam={{param::filter?name=otherparam}}'],
            'tableName'
        );

        $this->assertGeneratedSqlIs(
            $setting,
            'SELECT id FROM tableName WHERE otherparam=?',
            [0],
            ['otherparam' => 0]
        );
    }

    /**
     * Test with a null value (not passed as param value).
     */
    public function testWithNullValue(): void
    {
        $setting = $this->mockCustomSql(
            ['customsql' => 'SELECT id FROM {{table}} WHERE otherparam={{param::filter?name=otherparam}}'],
            'tableName'
        );

        $this->assertGeneratedSqlIs(
            $setting,
            'SELECT id FROM tableName WHERE otherparam=NULL',
            [],
            ['otherparam' => null]
        );
    }

    /**
     * Test with a null value (not passed as param value) and a default.
     */
    public function testWithNullValueAndDefault(): void
    {
        $setting = $this->mockCustomSql(
            ['customsql' => 'SELECT id FROM {{table}} WHERE otherparam={{param::filter?name=otherparam&default=xyz}}'],
            'tableName'
        );

        $this->assertGeneratedSqlIs(
            $setting,
            'SELECT id FROM tableName WHERE otherparam=?',
            ['xyz'],
            ['otherparam' => null]
        );
    }

    public function issue1495IfLangProvider(): \Iterator
    {
        yield [
            'sql' => '{{iflng::de}}1{{iflng::en}}3{{iflng::nl}}2{{iflng::es}}4{{iflng::el}}5{{iflng}}',
            'language' => 'en',
            'exp_sql' => '3',
        ];
        yield [
            'sql' => '{{iflng::de}}1{{iflng::en}}3{{iflng::nl}}2{{iflng::es}}4{{iflng::el}}5{{iflng}}',
            'language' => 'de',
            'exp_sql' => '1',
        ];
        yield [
            'sql' => '{{iflng::de}}1{{iflng::en}}3{{iflng::nl}}2{{iflng::es}}4{{iflng::el}}5{{iflng}}',
            'language' => 'nl',
            'exp_sql' => '2',
        ];
        yield [
            'sql' => '{{iflng::de}}1{{iflng::en}}3{{iflng::nl}}2{{iflng::es}}4{{iflng::el}}5{{iflng}}',
            'language' => 'es',
            'exp_sql' => '4',
        ];
        yield [
            'sql' => '{{iflng::de}}1{{iflng::en}}3{{iflng::nl}}2{{iflng::es}}4{{iflng::el}}5{{iflng}}',
            'language' => 'el',
            'exp_sql' => '5',
        ];
        yield [
            'sql' => '{{iflng::de}}1{{iflng::en}}3{{iflng::nl}}2{{iflng::es}}4{{iflng::el}}5{{iflng}}',
            'language' => 'cn',
            'exp_sql' => '',
        ];

        yield [
            'sql' => '{{ifnlng::de}}1{{ifnlng::en}}3{{ifnlng::nl}}2{{ifnlng::es}}4{{ifnlng::el}}5{{iflng}}',
            'language' => 'en',
            'exp_sql' => '1245',
        ];
        yield [
            'sql' => '{{ifnlng::de}}1{{ifnlng::en}}3{{ifnlng::nl}}2{{ifnlng::es}}4{{ifnlng::el}}5{{iflng}}',
            'language' => 'de',
            'exp_sql' => '3245',
        ];
        yield [
            'sql' => '{{ifnlng::de}}1{{ifnlng::en}}3{{ifnlng::nl}}2{{ifnlng::es}}4{{ifnlng::el}}5{{iflng}}',
            'language' => 'nl',
            'exp_sql' => '1345',
        ];
        yield [
            'sql' => '{{ifnlng::de}}1{{ifnlng::en}}3{{ifnlng::nl}}2{{ifnlng::es}}4{{ifnlng::el}}5{{iflng}}',
            'language' => 'es',
            'exp_sql' => '1325',
        ];
        yield [
            'sql' => '{{ifnlng::de}}1{{ifnlng::en}}3{{ifnlng::nl}}2{{ifnlng::es}}4{{ifnlng::el}}5{{iflng}}',
            'language' => 'el',
            'exp_sql' => '1324',
        ];
        yield [
            'sql' => '{{ifnlng::de}}1{{ifnlng::en}}3{{ifnlng::nl}}2{{ifnlng::es}}4{{ifnlng::el}}5{{iflng}}',
            'language' => 'cn',
            'exp_sql' => '13245',
        ];
        yield [
            'sql' => 'SELECT id FROM {{table}}
WHERE alias = {{iflng::de}}moe-yer-ss-hans-herbert-oeaeue' .
                '{{iflng::en}}3{{iflng::nl}}2{{iflng::es}}4{{iflng::el}}5{{iflng}}',
            'language' => 'de',
            'exp_sql' => 'SELECT id FROM tableName
WHERE alias = moe-yer-ss-hans-herbert-oeaeue',
        ];
    }

    /**
     * https://github.com/MetaModels/core/issues/1495
     *
     * @dataProvider issue1495IfLangProvider
     */
    public function testIssue1495IfLang(string $sql, string $language, string $expectedSql): void
    {
        $pageModel = $this
            ->getMockBuilder(PageModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();
        $pageModel->language = $language;

        $setting = $this->mockCustomSql(
            ['customsql' => $sql],
            'tableName',
            [PageModel::class => $pageModel]
        );

        $this->assertGeneratedSqlIs($setting, $expectedSql, [], []);
    }

    /**
     * https://github.com/MetaModels/core/issues/1495#issuecomment-1448085843
     */
    public function testIssue1495NoteDmolineus1448085843(): void
    {
        $setting = $this->mockCustomSql(
            ['customsql' => <<<EOF
                    SELECT * FROM {{table}}
                    WHERE IF (
                        {{param::get?name=act}} = 'edit',
                        (
                            pid = (
                                SELECT unterkunft_region_land
                                FROM mm_unterkunft
                                WHERE id=SUBSTRING_INDEX({{param::get?name=id}}, '::', -1)
                            )
                        ),
                        (pid != '')
                    )
                EOF
            ],
            'tableName'
        );

        $this->assertGeneratedSqlIs(
            $setting,
            <<<EOF
                    SELECT * FROM tableName
                    WHERE IF (
                        NULL = 'edit',
                        (
                            pid = (
                                SELECT unterkunft_region_land
                                FROM mm_unterkunft
                                WHERE id=SUBSTRING_INDEX(NULL, '::', -1)
                            )
                        ),
                        (pid != '')
                    )
                EOF,
            [],
            []
        );
    }

    /**
     * https://github.com/MetaModels/core/issues/1495#issuecomment-1448085843
     */
    public function testIssue1495NoteDmolineus1448085843WithParameters(): void
    {
        $input = $this
            ->getMockBuilder(Adapter::class)
            ->disableOriginalConstructor()
            ->addMethods(['cookie', 'get', 'post'])
            ->getMock();
        $input
            ->expects(self::exactly(2))
            ->method('get')
            ->willReturnCallback(fn ($name) => ['act' => 'edit', 'id' => '10'][$name]);

        $setting = $this->mockCustomSql(
            ['customsql' => <<<EOF
                    SELECT * FROM {{table}}
                    WHERE IF (
                        {{param::get?name=act}} = 'edit',
                        (
                            pid = (
                                SELECT unterkunft_region_land
                                FROM mm_unterkunft
                                WHERE id=SUBSTRING_INDEX({{param::get?name=id}}, '::', -1)
                            )
                        ),
                        (pid != '')
                    )
                EOF
            ],
            'tableName',
            [Input::class => $input]
        );

        $this->assertGeneratedSqlIs(
            $setting,
            <<<EOF
                    SELECT * FROM tableName
                    WHERE IF (
                        ? = 'edit',
                        (
                            pid = (
                                SELECT unterkunft_region_land
                                FROM mm_unterkunft
                                WHERE id=SUBSTRING_INDEX(?, '::', -1)
                            )
                        ),
                        (pid != '')
                    )
                EOF,
            ['edit', '10'],
            []
        );
    }
}
