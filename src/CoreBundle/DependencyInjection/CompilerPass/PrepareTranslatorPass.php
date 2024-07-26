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

use MetaModels\CoreBundle\Translator\MetaModelTranslationLoader;
use MetaModels\CoreBundle\Translator\MetaModelTranslatorConfigurator;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

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
    }
}
