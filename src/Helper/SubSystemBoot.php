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
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Helper;

use Contao\Environment;
use Contao\System;
use MetaModels\Events\MetaModelsBootEvent;
use MetaModels\MetaModelsEvents;

/**
 * Base event listener to boot up a MetaModelServiceContainer.
 */
class SubSystemBoot
{
    /**
     * Boot up the system.
     *
     * @return void
     */
    public function boot()
    {
        /** @var Environment $environment */
        $environment = System::getContainer()->get('contao.framework')->getAdapter(Environment::class);
        $script      = explode('?', $environment->get('relativeRequest'), 2)[0];

        // There is no need to boot in login or install screen.
        if (($script == 'contao/login') || ($script == 'contao/install')) {
            return;
        }
        $tableManipulator = System::getContainer()->get('metamodels.table_manipulator');
        // Ensure all tables are created.
        foreach ([
            'tl_metamodel',
            'tl_metamodel_dca',
            'tl_metamodel_dca_sortgroup',
            'tl_metamodel_dcasetting',
            'tl_metamodel_dcasetting_condition',
            'tl_metamodel_attribute',
            'tl_metamodel_filter',
            'tl_metamodel_filtersetting',
            'tl_metamodel_rendersettings',
            'tl_metamodel_rendersetting',
            'tl_metamodel_dca_combine',
        ] as $table) {
            try {
                $tableManipulator->checkTableExists($table);
            } catch (\Exception $exception) {
                System::getContainer()
                    ->get('logger')
                    ->error(
                        'MetaModels startup interrupted. ' .
                        ' Not all MetaModels tables have been created (missing: ' . $table . ').'
                    );
                return;
            }
        }

        $event = new MetaModelsBootEvent();
        $this->tryDispatch(MetaModelsEvents::SUBSYSTEM_BOOT, $event);

        $determinator = System::getContainer()->get('cca.dc-general.scope-matcher');
        switch (true) {
            case $determinator->currentScopeIsFrontend():
                $this->tryDispatch(MetaModelsEvents::SUBSYSTEM_BOOT_FRONTEND, $event);
                break;
            case $determinator->currentScopeIsBackend():
                $this->tryDispatch(MetaModelsEvents::SUBSYSTEM_BOOT_BACKEND, $event);
                break;
            default:
        }
    }

    /**
     * Test if the event must get dispatched, if so, trigger deprecation and dispatch then.
     *
     * @param string              $eventName The event name.
     * @param MetaModelsBootEvent $event     The event payload.
     *
     * @return void
     */
    private function tryDispatch(string $eventName, MetaModelsBootEvent $event)
    {
        $dispatcher = System::getContainer()->get('event_dispatcher');
        if ($dispatcher->hasListeners($eventName)) {
            // @codingStandardsIgnoreStart
            @trigger_error('Event "' . $eventName . '" has been deprecated - Use registered services.', E_USER_DEPRECATED);
            // @codingStandardsIgnoreEnd
            $dispatcher->dispatch($eventName, $event);
        }
    }
}
