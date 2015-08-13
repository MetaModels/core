<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2015 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2015 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\FrontendIntegration;

use MetaModels\Events\MetaModelsBootEvent;

/**
 * This class is used in the frontend to build the menu.
 */
class Boot
{
    /**
     * Boot the system in the frontend.
     *
     * @param MetaModelsBootEvent $event The event.
     *
     * @return void
     */
    public function perform(MetaModelsBootEvent $event)
    {
        // Perform frontend boot tasks.
    }
}
