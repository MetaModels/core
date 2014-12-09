<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
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
    function () {
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
