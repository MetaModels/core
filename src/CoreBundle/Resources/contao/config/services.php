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

/** @var \DependencyInjection\Container\PimpleGate $container */
$service = $container->getContainer();

$container->provideSymfonyService('metamodels.attribute_factory');
$container->provideSymfonyService('metamodels.factory');
$container->provideSymfonyService('metamodels.filter_setting_factory');
$container->provideSymfonyService('metamodels.render_setting_factory');
$container->provideSymfonyService('metamodels.cache');

$container['metamodels-service-container'] = $container->share(
    function () use ($service) {
        @trigger_error(
            'The MetaModels service container is deprecated and will get removed - use the symfony DIC directly.',
            E_USER_DEPRECATED
        );
        $serviceContainer = new MetaModels\MetaModelsServiceContainer();
        $dispatcher       = $service->get('event_dispatcher');
        $serviceContainer
            ->setEventDispatcher($dispatcher)
            ->setDatabase($service->get('cca.legacy_dic.contao_database_connection'));

        $serviceContainer
            ->setAttributeFactory($service->get('metamodels.attribute_factory'))
            ->setFactory($service->get('metamodels.factory'))
            ->setFilterFactory($service->get('metamodels.filter_setting_factory'))
            ->setRenderSettingFactory($service->get('metamodels.render_setting_factory'))
            ->setCache($service->get('metamodels.cache'));

        return $serviceContainer;
    }
);
