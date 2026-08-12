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
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\InputProviderInterface;

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
    #[\Override]
    protected function wantToHandle(GetBreadcrumbEvent $event)
    {
        $dataDefinition = $event->getEnvironment()->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        return 'tl_metamodel' === $dataDefinition->getName();
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    protected function getBreadcrumbElements(EnvironmentInterface $environment, BreadcrumbStore $elements)
    {
        $elements->push(
            $this->generate('metamodels.configuration', []),
            'tl_metamodel',
            'bundles/metamodelscore/images/backend/mm_logo_small.svg'
        );

        $this->anchorEditedMetaModel($environment, $elements);
    }

    /**
     * Anchor the MetaModel being edited, so that the shortcuts to its sibling views show up here.
     *
     * On the edit mask of a MetaModel the record is known and worth linking away from. In the
     * list of all MetaModels no single record is being worked on and the parameter is absent,
     * which is what keeps the shortcuts out of there.
     *
     * Only reached when no sub table has anchored the MetaModel already: those set the id before
     * they hand over to this listener, and their "id" parameter names a record of their own table
     * rather than a MetaModel. The provider name is checked all the same - a stray parameter must
     * not be mistaken for a MetaModel id.
     *
     * @param EnvironmentInterface $environment The environment in use.
     * @param BreadcrumbStore      $elements    The elements generated so far.
     *
     * @return void
     */
    private function anchorEditedMetaModel(EnvironmentInterface $environment, BreadcrumbStore $elements): void
    {
        if ($elements->hasId('tl_metamodel')) {
            return;
        }

        $inputProvider = $environment->getInputProvider();
        assert($inputProvider instanceof InputProviderInterface);

        if (!$inputProvider->hasParameter('id')) {
            return;
        }

        try {
            $modelId = ModelId::fromSerialized((string) $inputProvider->getParameter('id'));
        } catch (\Exception) {
            return;
        }

        if ('tl_metamodel' !== $modelId->getDataProviderName()) {
            return;
        }

        $elements->setId('tl_metamodel', (string) $modelId->getId());
    }
}
