<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2021 The MetaModels team.
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
 * @copyright  2012-2021 The MetaModels team.
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

/**
 * Test the base attribute.
 *
 * @covers \MetaModels\ItemList
 */
final class ItemListTest extends TestCase
{
    /** @SuppressWarnings(PHPMD.Superglobals) */
    public function testGetOutputFormat(): void
    {
        $factory              = $this->getMockForAbstractClass(IFactory::class);
        $filterFactory        = $this->getMockForAbstractClass(IFilterSettingFactory::class);
        $renderSettingFactory = $this->getMockForAbstractClass(IRenderSettingFactory::class);
        $eventDispatcher      = $this->getMockForAbstractClass(EventDispatcherInterface::class);
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

        $mockContainer = $this->getMockForAbstractClass(ContainerInterface::class);
        $mockContainer
            ->method('get')
            ->willReturnCallback(fn(string $service) => match ($service) {
                'contao.routing.scope_matcher' => $scopeMatcher,
                'request_stack' => null,
            });
        System::setContainer($mockContainer);

        $GLOBALS['objPage'] = null;
        self::assertSame('text', $itemlist->getOutputFormat());

        $itemlist->overrideOutputFormat('json');
        self::assertSame('json', $itemlist->getOutputFormat());

        $itemlist->overrideOutputFormat(null);
        $GLOBALS['objPage'] = (object) ['outputFormat' => 'xhtml'];

        self::assertSame('xhtml', $itemlist->getOutputFormat());

        $GLOBALS['objPage'] = (object) ['outputFormat' => null];
        self::assertSame('html5', $itemlist->getOutputFormat());
    }
}
