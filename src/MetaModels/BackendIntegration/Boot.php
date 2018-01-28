<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2018 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Andreas Isaak <info@andreas-isaak.de>
 * @author     Andreas Nölke <zero@brothers-project.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\BackendIntegration;

use MetaModels\ContaoIntegration\Boot as BaseBoot;
use MetaModels\DcGeneral\Events\Subscriber;
use MetaModels\Events\MetaModelsBootEvent;

/**
 * This class is used in the backend to build the menu etc.
 */
class Boot extends BaseBoot
{
    /**
     * When we are within Contao >= 3.1, we have to override the file picker class.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function overrideFilePicker()
    {
        if (\Environment::get('scriptName') == (TL_PATH . '/contao/file.php')
            && \Input::get('mmfilepicker')
        ) {
            $GLOBALS['BE_FFL']['fileSelector'] = 'MetaModels\Widgets\FileSelectorWidget';
        }
    }

    /**
     * Boot the system in the backend.
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

        $menuBuilder = new BackendModuleBuilder($container, $viewCombinations);
        $menuBuilder->export();

        $this->performBoot($container, $viewCombinations);

        // Register the global subscriber.
        new Subscriber($container);

        $this->overrideFilePicker();
    }
}
