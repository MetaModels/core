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
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2025 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\Test;

use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\System;
use MetaModels\Filter\Setting\IFilterSettingFactory;
use MetaModels\IFactory;
use MetaModels\ItemList;
use MetaModels\Filter\FilterUrlBuilder;
use MetaModels\Render\Setting\IRenderSettingFactory;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * Test the base attribute.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects) The tearDown() reflection-based container reset
 *     added a dependency, pushing this already-broad test class over the threshold.
 */
#[CoversClass(\MetaModels\ItemList::class)]
final class ItemListTest extends TestCase
{
    /**
     * Contao\System::setContainer() stores its argument in a process-wide static property with no
     * public way to unset it - reset it via reflection so the mock container this test installs
     * (whose service map only covers "contao.routing.scope_matcher" and "request_stack") doesn't
     * leak into whatever other test class PHPUnit runs next in the same process.
     */
    protected function tearDown(): void
    {
        $property = new \ReflectionProperty(System::class, 'objContainer');
        $property->setValue(null, null);

        parent::tearDown();
    }

    /** @SuppressWarnings(PHPMD.Superglobals) */
    public function testGetOutputFormat(): void
    {
        $factory              = $this->createMock(IFactory::class);
        $filterFactory        = $this->createMock(IFilterSettingFactory::class);
        $renderSettingFactory = $this->createMock(IRenderSettingFactory::class);
        $eventDispatcher      = $this->createMock(EventDispatcherInterface::class);
        $filterUrlBuilder     = $this->getMockBuilder(FilterUrlBuilder::class)->disableOriginalConstructor()->getMock();
        $itemlist             = new ItemList(
            $factory,
            $filterFactory,
            $renderSettingFactory,
            $eventDispatcher,
            $filterUrlBuilder
        );

        if (!defined('TL_MODE')) {
            define('TL_MODE', 'FE');
        }

        if (TL_MODE !== 'FE') {
            self::markTestSkipped('Test assumes that TL_MODE is set to "FE"');
        }

        $scopeMatcher = $this
            ->getMockBuilder(ScopeMatcher::class)
            ->onlyMethods(['isFrontendRequest'])
            ->disableOriginalConstructor()
            ->getMock();
        $scopeMatcher->method('isFrontendRequest')->willReturn(true);

        $requestStack = new RequestStack();

        $mockContainer = $this->createMock(ContainerInterface::class);
        $mockContainer
            ->method('get')
            ->willReturnCallback(fn(string $service) => match ($service) {
                'contao.routing.scope_matcher' => $scopeMatcher,
                'request_stack' => $requestStack,
            });
        System::setContainer($mockContainer);

        $requestStack->push($this->mockRequestWithPage(null));
        self::assertSame('text', $itemlist->getOutputFormat());
        $requestStack->pop();

        $requestStack->push($this->mockRequestWithPage(null));
        $itemlist->overrideOutputFormat('json');
        self::assertSame('json', $itemlist->getOutputFormat());
        $requestStack->pop();

        $requestStack->push($this->mockRequestWithPage((object) ['outputFormat' => 'xhtml']));
        $itemlist->overrideOutputFormat(null);
        self::assertSame('xhtml', $itemlist->getOutputFormat());
        $requestStack->pop();

        $requestStack->push($this->mockRequestWithPage((object) ['outputFormat' => null]));
        self::assertSame('html5', $itemlist->getOutputFormat());
        $requestStack->pop();
    }

    private function mockRequestWithPage(?object $page): Request
    {
        $request = Request::create('');
        $request->attributes->set('pageModel', $page);

        return $request;
    }
}
