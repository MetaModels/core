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
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

declare(strict_types = 1);

namespace MetaModels\CoreBundle\DependencyInjection\CompilerPass;

use MetaModels\Schema\SchemaManager;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This pass adds the tagged schema managers.
 */
class CollectSchemaManagersPass implements CompilerPassInterface
{
    public const TAG_NAME = 'metamodels.schema-manager';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $generator = $container->getDefinition(SchemaManager::class);
        $argument  = $generator->getArgument(0);
        foreach (array_keys($container->findTaggedServiceIds(self::TAG_NAME)) as $child) {
            $argument[] = new Reference($child);
        }
        $generator->setArgument(0, $argument);
    }
}
