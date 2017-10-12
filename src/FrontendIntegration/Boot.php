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

namespace MetaModels\FrontendIntegration;

use MetaModels\ContaoIntegration\Boot as BaseBoot;
use MetaModels\Events\MetaModelsBootEvent;

/**
 * This class is used in the frontend to allow loading of data containers.
 */
class Boot extends BaseBoot
{
    /**
     * Boot the system in the frontend.
     *
     * @param MetaModelsBootEvent $event The event.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function perform(MetaModelsBootEvent $event)
    {
        $container = $event->getServiceContainer();

        $viewCombinations = new ViewCombinations($container, $GLOBALS['container']['user']);
        $container->setService($viewCombinations, 'metamodels-view-combinations');

        $this->performBoot($container, $viewCombinations);
    }
}
