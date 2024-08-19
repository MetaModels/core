<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2023 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2023 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle;

use MetaModels\CoreBundle\DependencyInjection\CompilerPass\CollectDoctrineSchemaGeneratorsPass;
use MetaModels\CoreBundle\DependencyInjection\CompilerPass\CollectFactoriesPass;
use MetaModels\CoreBundle\DependencyInjection\CompilerPass\CollectSchemaGeneratorsPass;
use MetaModels\CoreBundle\DependencyInjection\CompilerPass\CollectSchemaManagersPass;
use MetaModels\CoreBundle\DependencyInjection\CompilerPass\PrepareTranslatorPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * This class holds everything together.
 */
class MetaModelsCoreBundle extends Bundle
{
    /**
     * {@inheritDoc}
     */
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new CollectFactoriesPass());
        $container->addCompilerPass(new CollectSchemaGeneratorsPass());
        $container->addCompilerPass(new CollectSchemaManagersPass());
        $container->addCompilerPass(new CollectDoctrineSchemaGeneratorsPass());
        $container->addCompilerPass(new PrepareTranslatorPass(), priority: -64);
    }
}
