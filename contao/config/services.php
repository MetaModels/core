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

$container['metamodels-service-container.factory.default'] = $container->protect(
    function ($container) {
        $serviceContainer = new MetaModels\MetaModelsServiceContainer();
        $dispatcher       = $container['event-dispatcher'];
        $serviceContainer
            ->setEventDispatcher($dispatcher)
            ->setDatabase($container['database.connection']);

        $attributeFactory = new MetaModels\Attribute\AttributeFactory($serviceContainer);
        $factory          = new MetaModels\Factory($serviceContainer);
        $filterFactory    = new MetaModels\Filter\Setting\FilterSettingFactory($serviceContainer);
        $renderFactory    = new MetaModels\Render\Setting\RenderSettingFactory($serviceContainer);

        $serviceContainer
            ->setAttributeFactory($attributeFactory)
            ->setFactory($factory)
            ->setFilterFactory($filterFactory)
            ->setRenderSettingFactory($renderFactory);

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
