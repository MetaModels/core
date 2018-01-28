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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Helper;

use MetaModels\Events\MetaModelsBootEvent;
use MetaModels\MetaModelsEvents;

/**
 * Base event listener to boot up a MetaModelServiceContainer.
 */
class SubSystemBoot
{
    /**
     * Local wrapper function to retrieve the current execution mode of Contao.
     *
     * @return string
     */
    protected function getMode()
    {
        return defined('TL_MODE') ? TL_MODE : '';
    }

    /**
     * Check if all MetaModels tables are installed.
     *
     * @param \Contao\Database $database The database.
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function metaModelsTablesPresent($database)
    {
        $tables = array_flip($database->listTables());

        if (!(
            isset($tables['tl_metamodel'])
            && isset($tables['tl_metamodel_dca'])
            && isset($tables['tl_metamodel_dca_sortgroup'])
            && isset($tables['tl_metamodel_dcasetting'])
            && isset($tables['tl_metamodel_dcasetting_condition'])
            && isset($tables['tl_metamodel_attribute'])
            && isset($tables['tl_metamodel_filter'])
            && isset($tables['tl_metamodel_filtersetting'])
            && isset($tables['tl_metamodel_rendersettings'])
            && isset($tables['tl_metamodel_rendersetting'])
            && isset($tables['tl_metamodel_dca_combine'])
        )) {
            return false;
        }

        return true;
    }

    /**
     * Boot up the system and initialize a service container.
     *
     * @param \Pimple $container The dependency injection container.
     *
     * @return void
     */
    public function boot(\Pimple $container)
    {
        /** @var \Contao\Environment $environment */
        $environment = $container['environment'];
        // There is no need to boot in login or install screen.
        if (($environment->get('script') == 'contao/index.php')
            || ($environment->get('script') == 'contao/install.php')) {
            return;
        }

        /** @var \MetaModels\IMetaModelsServiceContainer $container */
        try {
            $container = $container['metamodels-service-container'];
        } catch (\Exception $e) {
            error_log(
                sprintf(
                    'MetaModels startup interrupted: Uncaught exception \'%s\' with message \'%s\' thrown in %s ' .
                    "on line %s\n%s",
                    get_class($e),
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine(),
                    $e->getTraceAsString()
                )
            );
            // ¯\_(ツ)_/¯ - give up do not try to boot.
            return;
        }

        // Ensure all tables are created.
        if (!$this->metaModelsTablesPresent($container->getDatabase())
        ) {
            error_log('MetaModels startup interrupted: Not all MetaModels tables have been created.');
            return;
        }

        $dispatcher = $container->getEventDispatcher();
        $event      = new MetaModelsBootEvent($container);

        $dispatcher->dispatch(MetaModelsEvents::SUBSYSTEM_BOOT, $event);

        if ($mode = $this->getMode()) {
            $eventName = MetaModelsEvents::SUBSYSTEM_BOOT_FRONTEND;

            if ($mode === 'BE') {
                $eventName = MetaModelsEvents::SUBSYSTEM_BOOT_BACKEND;
            }

            $dispatcher->dispatch($eventName, $event);
        }
    }
}
