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

use Contao\StringUtil;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\UrlBuilder\UrlBuilder;

/**
 * Generate a breadcrumb for table tl_metamodel_searchable_pages.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class BreadcrumbSearchablePagesListener extends AbstractBreadcrumbListener
{
    use GetMetaModelTrait;
    use ConnectionTrait;

    /**
     * {@inheritDoc}
     */
    protected function wantToHandle(GetBreadcrumbEvent $event)
    {
        $dataDefinition = $event->getEnvironment()->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        return 'tl_metamodel_searchable_pages' === $dataDefinition->getName();
    }

    /**
     * {@inheritDoc}
     */
    protected function getBreadcrumbElements(EnvironmentInterface $environment, BreadcrumbStore $elements)
    {
        if (!$elements->hasId('tl_metamodel')) {
            if (!$elements->hasId('tl_metamodel_searchable_pages')) {
                $elements->setId('tl_metamodel', $this->extractIdFrom($environment, 'pid'));
            } else {
                $elements->setId(
                    'tl_metamodel',
                    $this->getRow(
                        $elements->getId('tl_metamodel_searchable_pages') ?? '',
                        'tl_metamodel_searchable_pages'
                    )->pid
                );
            }
        }

        parent::getBreadcrumbElements($environment, $elements);

        $builder = UrlBuilder::fromUrl($elements->getUri())
            ->setQueryParameter('table', 'tl_metamodel_searchable_pages')
            ->setQueryParameter(
                'pid',
                ModelId::fromValues(
                    'tl_metamodel',
                    $elements->getId('tl_metamodel')
                )->getSerialized()
            )
            ->unsetQueryParameter('act')
            ->unsetQueryParameter('id');

        $modelId = $elements->getId('tl_metamodel');
        $elements->push(
            StringUtil::ampersand($builder->getUrl()),
            \sprintf(
                $elements->getLabel('tl_metamodel_searchable_pages'),
                (null !== $modelId) ? $this->getMetaModel($modelId)->getName() : ''
            ),
            'bundles/metamodelscore/images/icons/searchable_pages.png'
        );
    }
}
