<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2025 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @copyright  2012-2025 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\DependencyInjection;

use MetaModels\CoreBundle\Attribute\DoctrineSchemaProvider;
use MetaModels\CoreBundle\Contao\Picker\MetaModelsJumpToPickerProvider;
use MetaModels\CoreBundle\DependencyInjection\CompilerPass\CollectDoctrineSchemaGeneratorsPass;
use MetaModels\CoreBundle\Migration\TableCollationMigration;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This is the class that loads and manages the bundle configuration.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class MetaModelsCoreExtension extends Extension implements PrependExtensionInterface
{
    /**
     * The default table options.
     *
     * @var array
     */
    private array $defaultTableOptions = [];

    /**
     * The configuration files.
     *
     * @var string[]
     */
    private static $files = [
        'config.yml',
        'filter-settings.yml',
        'hooks.yml',
        'insert-tags.yml',
        'listeners.yml',
        'property-conditions.yml',
        'services.yml',
        'content-elements.yml',
        'modules.yml',
        'dc-general/breadcrumb.yml',
        'dc-general/definition-builder.yml',
        'dc-general/environment-populator.yml',
        'dc-general/listener.yml',
        'dc-general/table/tl_attribute.yml',
        'dc-general/table/tl_dca.yml',
        'dc-general/table/tl_dca_combine.yml',
        'dc-general/table/tl_dca_sortgroup.yml',
        'dc-general/table/tl_dcasetting.yml',
        'dc-general/table/tl_dcasetting_condition.yml',
        'dc-general/table/tl_filtersetting.yml',
        'dc-general/table/tl_metamodel.yml',
        'dc-general/table/tl_metamodel_rendersetting.yml',
        'dc-general/table/tl_metamodel_rendersettings.yml',
        'dc-general/table/tl_metamodel_searchable_pages.yml',
    ];

    /**
     * {@inheritDoc}
     */
    public function prepend(ContainerBuilder $container): void
    {
        $this->collectDefaultTableOptionsFromDoctrineExtension($container);
    }

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $container->registerAttributeForAutoconfiguration(
            DoctrineSchemaProvider::class,
            static function (ChildDefinition $definition, DoctrineSchemaProvider $attribute): void {
                $attributes = [];
                if (null !== ($priority = $attribute->getPriority())) {
                    $attributes['priority'] = $priority;
                }
                $definition->addTag(CollectDoctrineSchemaGeneratorsPass::TAG_NAME, $attributes);
            }
        );

        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        foreach (self::$files as $file) {
            $loader->load($file);
        }

        $configuration = $this->getConfiguration($configs, $container);
        assert($configuration instanceof Configuration);
        $config = $this->processConfiguration($configuration, $configs);
        $this->buildCacheService($container, $config);

        $container->setParameter('metamodels.resource_dir', __DIR__ . '/../Resources');
        $container->setParameter('metamodels.assets_dir', $config['assets_dir']);
        $container->setParameter('metamodels.assets_web', $config['assets_web']);

        $container->getDefinition(TableCollationMigration::class)
            ->setArgument('$defaultTableOptions', $this->defaultTableOptions);

        $jumpToPicker = $config['picker_jumpto'];
        if (null !== $jumpToPicker) {
            $this->processJumpToPicker($jumpToPicker, $container);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguration(array $config, ContainerBuilder $container): ?ConfigurationInterface
    {
        $projectDir = $container->getParameter('kernel.project_dir');
        assert(\is_string($projectDir));

        return new Configuration((bool) $container->getParameter('kernel.debug'), $projectDir);
    }

    /**
     * Build the cache service.
     *
     * @param ContainerBuilder $container The container builder.
     * @param array            $config    The configuration.
     *
     * @return void
     */
    private function buildCacheService(ContainerBuilder $container, array $config): void
    {
        // If cache disabled, swap it out with the dummy cache.
        if (!$config['enable_cache']) {
            $cache = $container->getDefinition('metamodels.cache_internal');
            $cache->setClass(ArrayAdapter::class);
            $cache->setArguments([]);
            $container->setParameter('metamodels.cache_dir', null);

            return;
        }

        $container->setParameter('metamodels.cache_dir', $config['cache_dir']);
    }

    /**
     * Collect the default table options from the doctrine extension.
     *
     * @param ContainerBuilder $container The container builder.
     *
     * @return void
     */
    private function collectDefaultTableOptionsFromDoctrineExtension(ContainerBuilder $container): void
    {
        if (!isset($container->getExtensions()['doctrine'])) {
            $this->defaultTableOptions = [
                'charset'    => 'utf8mb4',
                'collate'    => 'utf8mb4_unicode_ci',
                'engine'     => 'InnoDB',
                'row_format' => 'DYNAMIC',
            ];

            return;
        }

        $defaultTableOptions = [[]];
        foreach ($container->getExtensionConfig('doctrine') as $doctrineConfig) {
            if (!isset($doctrineConfig['dbal']['connections']['default']['default_table_options'])) {
                continue;
            }

            $defaultTableOptions[] = $doctrineConfig['dbal']['connections']['default']['default_table_options'];
        }
        $this->defaultTableOptions = \array_merge(...$defaultTableOptions);
    }

    /** @param array<string, array{render_setting: string, priority: int, icon: ?string}> $jumpToPicker */
    private function processJumpToPicker(mixed $jumpToPicker, ContainerBuilder $container): void
    {
        $menuFactory = new Reference('knp_menu.factory');
        $router      = new Reference('router');
        $translator  = new Reference('translator');
        foreach ($jumpToPicker as $metaModelName => $config) {
            $definition = new Definition(MetaModelsJumpToPickerProvider::class);
            $definition->setArgument('$menuFactory', $menuFactory);
            $definition->setArgument('$router', $router);
            $definition->setArgument('$translator', $translator);
            $definition->setArgument('$tableName', $metaModelName);
            $definition->setArgument('$renderSettingId', $config['render_setting']);
            $definition->setArgument('$linkIcon', $config['icon']);
            $definition->addTag('contao.picker_provider', ['priority' => $config['priority']]);

            $container->setDefinition('metamodels_jump_to_picker_' . $metaModelName, $definition);
        }
    }
}
