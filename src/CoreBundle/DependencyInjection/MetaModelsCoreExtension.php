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
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\CoreBundle\DependencyInjection;

use Doctrine\Common\Cache\ArrayCache;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

/**
 * This is the class that loads and manages the bundle configuration
 */
class MetaModelsCoreExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('config.yml');
        $loader->load('services.yml');
        $loader->load('listeners.yml');
        $loader->load('filter-settings.yml');

        $config = $this->processConfiguration($this->getConfiguration($configs, $container), $configs);
        $this->buildCacheService($container, $config);

        $container->setParameter('metamodels.resource_dir', __DIR__ . '/../Resources');
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        return new Configuration($container->getParameter('kernel.debug'));
    }

    /**
     *
     * @param ContainerBuilder $container
     * @param                  $config
     *
     * @return void
     */
    private function buildCacheService(ContainerBuilder $container, $config)
    {
        // if cache disabled, swap it out with the dummy cache.
        if (!$config['enable_cache']) {
            $cache = $container->getDefinition('metamodels.cache');
            $cache->setClass(ArrayCache::class);
            $cache->setArguments([]);
            $container->setParameter('metamodels.cache_dir', null);
            return;
        }

        $container->setParameter('metamodels.cache_dir', $config['cache_dir']);
    }
}
