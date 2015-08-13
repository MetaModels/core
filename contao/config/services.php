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

/** @var Pimple $container */

$container['metamodels-attribute-factory.factory.default'] = $container->protect(
    function () {
        return new MetaModels\Attribute\AttributeFactory();
    }
);

if (!isset($container['metamodels-attribute-factory.factory'])) {
    $container['metamodels-attribute-factory.factory'] =
        $container->raw('metamodels-attribute-factory.factory.default');
}

$container['metamodels-factory.factory.default'] = $container->protect(
    function () {
        return new MetaModels\Factory();
    }
);

if (!isset($container['metamodels-factory.factory'])) {
    $container['metamodels-factory.factory'] =
        $container->raw('metamodels-factory.factory.default');
}

$container['metamodels-filter-setting-factory.factory.default'] = $container->protect(
    function () {
        return new MetaModels\Filter\Setting\FilterSettingFactory();
    }
);

if (!isset($container['metamodels-filter-setting-factory.factory'])) {
    $container['metamodels-filter-setting-factory.factory'] =
        $container->raw('metamodels-filter-setting-factory.factory.default');
}

$container['metamodels-render-setting-factory.factory.default'] = $container->protect(
    function () {
        return new MetaModels\Render\Setting\RenderSettingFactory();
    }
);

if (!isset($container['metamodels-render-setting-factory.factory'])) {
    $container['metamodels-render-setting-factory.factory'] =
        $container->raw('metamodels-render-setting-factory.factory.default');
}

$container['metamodels-cache.factory.default'] = $container->protect(
    function ($container) {
        if ($container['config']->get('bypassCache')) {
            return new \Doctrine\Common\Cache\ArrayCache();
        }

        return new \Doctrine\Common\Cache\FilesystemCache(TL_ROOT . '/system/cache/metamodels');
    }
);

if (!isset($container['metamodels-cache.factory'])) {
    $container['metamodels-cache.factory'] =
        $container->raw('metamodels-cache.factory.default');
}

$container['metamodels-service-container.factory.default'] = $container->protect(
    function ($container) {
        $serviceContainer = new MetaModels\MetaModelsServiceContainer();
        $dispatcher       = $container['event-dispatcher'];
        $serviceContainer
            ->setEventDispatcher($dispatcher)
            ->setDatabase($container['database.connection']);

        $serviceContainer
            ->setAttributeFactory($container['metamodels-attribute-factory.factory']($container))
            ->setFactory($container['metamodels-factory.factory']($container))
            ->setFilterFactory($container['metamodels-filter-setting-factory.factory']($container))
            ->setRenderSettingFactory($container['metamodels-render-setting-factory.factory']($container))
            ->setCache($container['metamodels-cache.factory']($container));

        return $serviceContainer;
    }
);

if (!isset($container['metamodels-service-container.factory'])) {
    $container['metamodels-service-container.factory'] =
        $container->raw('metamodels-service-container.factory.default');
}

$container['metamodels-service-container'] = $container->share(
    function ($container) {
        $factory = $container['metamodels-service-container.factory'];

        return $factory($container);
    }
);
