<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2019 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Attribute;

use Contao\System;

trait ManagedAttributeTrait
{
    private function isManagedAttribute(string $type): bool
    {
        $container = System::getContainer();
        return in_array(
            $type,
            $container->hasParameter('metamodels.managed-schema-type-names')
                ? ($container->getParameter('metamodels.managed-schema-type-names') ?? [])
                : [],
            true
        );
    }

    private function triggerDeprecationIsUnmanagedAttribute(string $class, string $method): void
    {
        // @codingStandardsIgnoreStart
        @trigger_error(
            'Class "' . $class . '" should be changed to a managed attribute and skip calling method "' . $method .
            '".',
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd
    }

    private function triggerDeprecationShouldNotCallManaged(string $class, string $method): void
    {
        // @codingStandardsIgnoreStart
        @trigger_error(
            'Class "' . $class . '" is a managed attribute you should not call "' . $method . '".',
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd
    }
}
