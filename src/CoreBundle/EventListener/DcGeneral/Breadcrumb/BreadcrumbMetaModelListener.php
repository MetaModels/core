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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Breadcrumb;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;

/**
 * Generate a breadcrumb for table tl_metamodel.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class BreadcrumbMetaModelListener extends AbstractBreadcrumbListener
{
    use GetMetaModelTrait;

    /**
     * {@inheritDoc}
     */
    protected function wantToHandle(GetBreadcrumbEvent $event)
    {
        $dataDefinition = $event->getEnvironment()->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        return 'tl_metamodel' === $dataDefinition->getName();
    }

    /**
     * {@inheritDoc}
     */
    protected function getBreadcrumbElements(EnvironmentInterface $environment, BreadcrumbStore $elements)
    {
        $elements->push(
            '/contao/metamodels',
            'tl_metamodel',
            'bundles/metamodelscore/images/backend/logo.png'
        );
    }
}
