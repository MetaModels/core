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

/** @var \DependencyInjection\Container\PimpleGate $container */
$service = $container->getContainer();

$container->provideSymfonyService('metamodels-attribute-factory.factory');
$container->provideSymfonyService('metamodels-factory.factory');
$container->provideSymfonyService('metamodels-filter-setting-factory.factory');
$container->provideSymfonyService('metamodels-render-setting-factory.factory');

// Fixme what cache system we use?
$cacheFactory = null;
if ($container['config']->get('bypassCache')) {
    $cacheFactory = new \Doctrine\Common\Cache\ArrayCache();
} else {
    $cacheFactory = new \Doctrine\Common\Cache\FilesystemCache(TL_ROOT . '/system/cache/metamodels');
}
$container->provideSymfonyService('metamodels-cache.factory');
$service->set(
    'metamodels-cache.factory',
    $cacheFactory
);

// Fixme build an factory for metamodels service container.
$serviceContainer = new MetaModels\MetaModelsServiceContainer();
$dispatcher       = $container['event-dispatcher'];
$serviceContainer
    ->setEventDispatcher($dispatcher)
    ->setDatabase($container['database.connection']);

$serviceContainer
    ->setAttributeFactory($container['metamodels-attribute-factory.factory'])
    ->setFactory($container['metamodels-factory.factory'])
    ->setFilterFactory($container['metamodels-filter-setting-factory.factory'])
    ->setRenderSettingFactory($container['metamodels-render-setting-factory.factory'])
    ->setCache($container['metamodels-cache.factory']);
$container->provideSymfonyService('metamodels-service-container.factory');
$service->set(
    'metamodels-service-container.factory',
    $serviceContainer
);

$container->provideSymfonyService('metamodels-service-container');
$service->set(
    'metamodels-service-container',
    $service->get('metamodels-service-container.factory')
);
