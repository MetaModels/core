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

namespace MetaModels\Test\CoreBundle\DependencyInjection\CompilerPass;

use MetaModels\CoreBundle\DependencyInjection\CompilerPass\CollectSchemaGeneratorsPass;
use MetaModels\Schema\SchemaGenerator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

/**
 * This tests the schema generator collecting.
 *
 * @covers \MetaModels\CoreBundle\DependencyInjection\CompilerPass\CollectSchemaGeneratorsPass
 */
class CollectSchemaGeneratorsPassTest extends TestCase
{
    /**
     * Test that all collectors are found.
     *
     * @return void
     */
    public function testProcess(): void
    {
        $container = new ContainerBuilder();

        $generator = $this
            ->getMockBuilder(Definition::class)
            ->setMethods(['getArgument', 'setArgument'])
            ->getMock();
        $generator
            ->expects($this->once())
            ->method('getArgument')
            ->with(0)
            ->willReturn([new Reference('previous-argument')]);
        $generator
            ->expects($this->once())
            ->method('setArgument')
            ->willReturnCallback(function ($index, $children) {
                $this->assertSame(0, $index);
                $this->assertCount(3, $children);
                $this->assertSame('previous-argument', (string) $children[0]);
                $this->assertSame('child1', (string) $children[1]);
                $this->assertSame('child2', (string) $children[2]);
            });

        $container->setDefinition(SchemaGenerator::class, $generator);

        $child1 = new Definition();
        $child1->addTag(CollectSchemaGeneratorsPass::TAG_NAME, ['priority' => 10]);
        $child2 = new Definition();
        $child2->addTag(CollectSchemaGeneratorsPass::TAG_NAME);

        $container->setDefinition('child2', $child2);
        $container->setDefinition('child1', $child1);

        $pass = new CollectSchemaGeneratorsPass();

        $pass->process($container);
    }
}
