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
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Events;

use Contao\System;
use MetaModels\IMetaModelsServiceContainer;
use MetaModels\MetaModelsServiceContainer;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * This event is triggered when a metamodels sub system is booted.
 *
 * It holds the MetaModels service container to use.
 *
 * @see MetaModelsEvents::SUBSYSTEM_BOOT
 * @see MetaModelsEvents::SUBSYSTEM_BOOT_FRONTEND
 * @see MetaModelsEvents::SUBSYSTEM_BOOT_BACKEND
 *
 * @deprecated
 */
class MetaModelsBootEvent extends Event
{
    /**
     * Retrieve the service container.
     *
     * @return IMetaModelsServiceContainer
     *
     * @deprecated
     *
     * @psalm-suppress DeprecatedInterface
     */
    public function getServiceContainer(): IMetaModelsServiceContainer
    {
        // @codingStandardsIgnoreStart
        @trigger_error(
            'The service container has been deprecated - use services from symfony instead.',
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd

        /** @psalm-suppress DeprecatedClass */
        $serviceContainer = System::getContainer()->get(MetaModelsServiceContainer::class);
        assert($serviceContainer instanceof IMetaModelsServiceContainer);

        return $serviceContainer;
    }
}
