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
 * Generate a breadcrumb for table tl_metamodel_dcasetting.
 *
 * @psalm-suppress PropertyNotSetInConstructor
 */
class BreadcrumbDcaSettingListener extends AbstractBreadcrumbListener
{
    use ConnectionTrait;

    /**
     * {@inheritDoc}
     */
    protected function wantToHandle(GetBreadcrumbEvent $event)
    {
        $dataDefinition = $event->getEnvironment()->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        return 'tl_metamodel_dcasetting' === $dataDefinition->getName();
    }

    /**
     * {@inheritDoc}
     */
    protected function getBreadcrumbElements(EnvironmentInterface $environment, BreadcrumbStore $elements)
    {
        if (!$elements->hasId('tl_metamodel_dca')) {
            if (!$elements->hasId('tl_metamodel_dcasetting')) {
                $elements->setId('tl_metamodel_dca', $this->extractIdFrom($environment, 'pid'));
            } else {
                $elements->setId(
                    'tl_metamodel_dca',
                    $this->getRow($elements->getId('tl_metamodel_dcasetting') ?? '', 'tl_metamodel_dcasetting')->pid
                );
            }
        }

        parent::getBreadcrumbElements($environment, $elements);

        $builder = UrlBuilder::fromUrl($elements->getUri())
            ->setQueryParameter('table', 'tl_metamodel_dcasetting')
            ->setQueryParameter(
                'pid',
                ModelId::fromValues('tl_metamodel_dca', $elements->getId('tl_metamodel_dca'))
                    ->getSerialized()
            )
            ->unsetQueryParameter('act')
            ->unsetQueryParameter('id');

        $dcaId = $elements->getId('tl_metamodel_dca');
        $elements->push(
            StringUtil::ampersand($builder->getUrl()),
            \sprintf(
                $elements->getLabel('tl_metamodel_dcasetting'),
                (null !== $dcaId) ? $this->getRow($dcaId, 'tl_metamodel_dca')->name : ''
            ),
            'bundles/metamodelscore/images/icons/dca_setting.png'
        );
    }
}
