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

/** @var Pimple $container */

$container['metamodels-attribute-factory.factory'] = $container->share(
    function () {
        return new MetaModels\Attribute\AttributeFactory();
    }
);

$container['metamodels-factory.factory'] = $container->share(
    function () {
        return new MetaModels\Factory();
    }
);

$container['metamodels-filter-setting-factory.factory'] = $container->share(
    function () {
        return new MetaModels\Filter\Setting\FilterSettingFactory();
    }
);

if (!isset($container['metamodels-filter-setting-factory.factory'])) {
    $container['metamodels-filter-setting-factory.factory'] =
        $container->raw('metamodels-filter-setting-factory.factory.default');
}

$container['metamodels-render-setting-factory.factory'] = $container->share(
    function () {
        return new MetaModels\Render\Setting\RenderSettingFactory();
    }
);

$container['metamodels-cache.factory'] = $container->share(
    function ($container) {
        if ($container['config']->get('bypassCache')) {
            return new \Doctrine\Common\Cache\ArrayCache();
        }

        return new \Doctrine\Common\Cache\FilesystemCache(TL_ROOT . '/system/cache/metamodels');
    }
);

$container['metamodels-service-container.factory'] = $container->share(
    function ($container) {
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

        return $serviceContainer;
    }
);

$container['metamodels-service-container'] = $container->share(
    function ($container) {
        $factory = $container['metamodels-service-container.factory'];

        return $factory;
    }
);
