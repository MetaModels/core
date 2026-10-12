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

namespace MetaModels\Test\CoreBundle\Search\Backend;

use MetaModels\CoreBundle\Search\Backend\ItemProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Contao 5.7.15 replaced the token by the user as first argument of isDocumentGranted() within the 5.7 series.
 * Loading the class fails with a fatal error if the declaration is incompatible to the installed Contao version, the
 * union has to keep it compatible with the versions before and after that change.
 */
#[CoversClass(ItemProvider::class)]
final class ItemProviderTest extends TestCase
{
    public function testAcceptsTheTokenAndTheUserAsFirstArgumentOfIsDocumentGranted(): void
    {
        $type = (new \ReflectionMethod(ItemProvider::class, 'isDocumentGranted'))->getParameters()[0]->getType();

        self::assertInstanceOf(\ReflectionUnionType::class, $type);
        $names = \array_map(static fn (\ReflectionNamedType $part): string => $part->getName(), $type->getTypes());
        \sort($names);
        self::assertSame([TokenInterface::class, UserInterface::class], $names);
    }
}
