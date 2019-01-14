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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Events;

use MetaModels\IMetaModelsServiceContainer;
use Symfony\Component\EventDispatcher\Event;

/**
 * This event is triggered when a metamodels sub system is booted.
 *
 * It holds the MetaModels service container to use.
 *
 * @see MetaModelsEvents::SUBSYSTEM_BOOT
 * @see MetaModelsEvents::SUBSYSTEM_BOOT_FRONTEND
 * @see MetaModelsEvents::SUBSYSTEM_BOOT_BACKEND
 */
class MetaModelsBootEvent extends Event
{
    /**
     * Retrieve the service container.
     *
     * @return IMetaModelsServiceContainer
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function getServiceContainer()
    {
        // @codingStandardsIgnoreStart
        @trigger_error(
            'The service container has been deprecated - use services from symfony instead.',
            E_USER_DEPRECATED
        );
        // @codingStandardsIgnoreEnd
        return $GLOBALS['container']['metamodels-service-container'];
    }
}
