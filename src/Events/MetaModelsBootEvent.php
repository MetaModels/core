<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2017 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
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
 *
 * @see MetaModelsEvents::SUBSYSTEM_BOOT_FRONTEND
 *
 * @see MetaModelsEvents::SUBSYSTEM_BOOT_BACKEND
 */
class MetaModelsBootEvent extends Event
{
    /**
     * Retrieve the service container.
     *
     * @return IMetaModelsServiceContainer
     */
    public function getServiceContainer()
    {
        return $GLOBALS['container']['metamodels-service-container'];
    }
}
