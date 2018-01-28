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

namespace MetaModels\CoreBundle\DependencyInjection\CompilerPass;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This pass adds the tagged factories to the MetaModels factories.
 */
class CollectFactoriesPass implements CompilerPassInterface
{
    /**
     * The tag name to use for attribute factories.
     */
    const TAG_ATTRIBUTE_FACTORY = 'metamodels.attribute_factory';

    /**
     * The tag name to use for filter factories.
     */
    const TAG_FILTER_FACTORY = 'metamodels.filter_factory';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container)
    {
        $this->collectAttributeFactories($container);
        $this->collectFilterFactories($container);
    }

    /**
     * Collect all tagged attribute factories.
     *
     * @param ContainerBuilder $container The container builder.
     *
     * @return void
     */
    private function collectAttributeFactories($container)
    {
        $attributeFactory = $container->getDefinition('metamodels.attribute_factory');
        foreach (array_keys($container->findTaggedServiceIds(self::TAG_ATTRIBUTE_FACTORY)) as $factory) {
            $attributeFactory->addMethodCall('addTypeFactory', [new Reference($factory)]);
        }
    }

    /**
     * Collect all tagged filter factories.
     *
     * @param ContainerBuilder $container The container builder.
     *
     * @return void
     */
    private function collectFilterFactories($container)
    {
        $attributeFactory = $container->getDefinition('metamodels.filter_setting_factory');
        foreach (array_keys($container->findTaggedServiceIds(self::TAG_FILTER_FACTORY)) as $factory) {
            $attributeFactory->addMethodCall('addTypeFactory', [new Reference($factory)]);
        }
    }
}
