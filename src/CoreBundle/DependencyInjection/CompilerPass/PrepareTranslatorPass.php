<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2024 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types=1);

namespace MetaModels\CoreBundle\DependencyInjection\CompilerPass;

use MetaModels\BackendIntegration\PurgeTranslator;
use MetaModels\CoreBundle\Translator\MetaModelTranslationLoader;
use MetaModels\CoreBundle\Translator\MetaModelTranslatorConfigurator;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

use function array_keys;
use function array_pop;

final class PrepareTranslatorPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    public const TAG_NAME = 'metamodels.translation-loader';

    public function process(ContainerBuilder $container): void
    {
        $definition = $container->getDefinition('translator.default');
        if (Translator::class !== $definition->getClass()) {
            return;
        }
        $previousConfigurator = $definition->getConfigurator();
        $definition->setConfigurator(new Reference(MetaModelTranslatorConfigurator::class));
        if (null !== $previousConfigurator) {
            $container->getDefinition(MetaModelTranslatorConfigurator::class)->setArgument(
                '$previous',
                $previousConfigurator
            );
        }
        $generator = $container->getDefinition(MetaModelTranslationLoader::class);
        $generator->setArgument(
            '$loaders',
            array_merge(
                $generator->getArgument('$loaders'),
                $this->findAndSortTaggedServices(self::TAG_NAME, $container)
            )
        );

        // We need to keep us "first" to allow others to override the values from our loader.
        /** @var array<string, list<string>> $loaders */
        $loaders = $definition->getArgument(3);
        $keys    = array_keys($loaders);
        $last    = array_pop($keys);

        if ($last === MetaModelTranslationLoader::class) {
            $value   = array_pop($loaders);
            $loaders = [MetaModelTranslationLoader::class => $value] + $loaders;
            $definition->replaceArgument(3, $loaders);
        }

        if ($container->hasDefinition(PurgeTranslator::class)) {
            $options  = $definition->getArgument(4);
            $cacheDir = $options['cache_dir'] ?? null;
            if (null !== $cacheDir) {
                $purger = $container->getDefinition(PurgeTranslator::class);
                $purger->replaceArgument('$cacheDir', $cacheDir);
            }
        }
    }
}
