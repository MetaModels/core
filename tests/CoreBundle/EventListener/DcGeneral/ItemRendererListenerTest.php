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

namespace MetaModels\CoreBundle\Test\EventListener\DcGeneral;

use MetaModels\CoreBundle\EventListener\DcGeneral\ItemRendererListener;
use MetaModels\Render\Setting\IRenderSettingFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

#[CoversClass(ItemRendererListener::class)]
class ItemRendererListenerTest extends TestCase
{
    /**
     * @param list<mixed> $values
     *
     * @return list<mixed>
     */
    private function rootRelative(array $values, ?RequestStack $stack): array
    {
        $listener = new ItemRendererListener($this->createStub(IRenderSettingFactory::class), $stack);
        $method = new ReflectionMethod($listener, 'makeSourcesRootRelative');
        $method->setAccessible(true);

        /** @var list<mixed> */
        return $method->invoke($listener, $values);
    }

    public function testPrefixesRelativeImageSources(): void
    {
        $result = $this->rootRelative(
            ['<img src="assets/images/a.png" alt="x">', '<img class="c" src="files/b.svg">'],
            null
        );

        self::assertSame(
            ['<img src="/assets/images/a.png" alt="x">', '<img class="c" src="/files/b.svg">'],
            $result
        );
    }

    public function testKeepsSourcesThatAreAbsoluteAlready(): void
    {
        $values = [
            '<img src="/assets/a.png">',
            '<img src="https://example.org/a.png">',
            '<img src="//cdn.example.org/a.png">',
            '<img src="data:image/png;base64,AAAA">',
            '<a href="files/a.pdf">a</a>',
            null,
            12,
        ];

        self::assertSame($values, $this->rootRelative($values, null));
    }

    public function testPrefixesTheBasePathOfTheRequest(): void
    {
        $stack   = new RequestStack();
        $request = $this->createStub(Request::class);
        $request->method('getBasePath')->willReturn('/sub');
        $stack->push($request);

        self::assertSame(
            ['<img src="/sub/assets/a.png">'],
            $this->rootRelative(['<img src="assets/a.png">'], $stack)
        );
    }
}
