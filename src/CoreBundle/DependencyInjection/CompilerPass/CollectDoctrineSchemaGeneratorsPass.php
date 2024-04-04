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

declare(strict_types=1);

namespace MetaModels\CoreBundle\DependencyInjection\CompilerPass;

use MetaModels\Schema\Doctrine\DoctrineSchemaGenerator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * This pass adds the tagged schema generators to the doctrine engine.
 */
class CollectDoctrineSchemaGeneratorsPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    public const TAG_NAME = 'metamodels.schema-generator.doctrine';

    /**
     * {@inheritDoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $generator = $container->getDefinition(DoctrineSchemaGenerator::class);
        $generator->setArgument(
            0,
            array_merge($generator->getArgument(0), $this->findAndSortTaggedServices(self::TAG_NAME, $container))
        );
    }
}
