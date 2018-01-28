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

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Breadcrumb;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\UrlBuilder\UrlBuilder;

/**
 * Generate a breadcrumb for table tl_metamodel_dcasetting.
 */
class BreadcrumbFilterSettingListener extends AbstractBreadcrumbListener
{
    use ConnectionTrait;

    /**
     * {@inheritDoc}
     */
    protected function wantToHandle(GetBreadcrumbEvent $event)
    {
        return 'tl_metamodel_filtersetting' === $event->getEnvironment()->getDataDefinition()->getName();
    }

    /**
     * {@inheritDoc}
     */
    protected function getBreadcrumbElements(EnvironmentInterface $environment, BreadcrumbStore $elements)
    {
        if (!$elements->hasId('tl_metamodel_filter')) {
            if (!$elements->hasId('tl_metamodel_filtersetting')) {
                $elements->setId('tl_metamodel_filter', $this->extractIdFrom($environment, 'pid'));
            } else {
                $elements->setId(
                    'tl_metamodel_filter',
                    $this->getRow($elements->getId('tl_metamodel_filtersetting'), 'tl_metamodel_filtersetting')->pid
                );
            }
        }

        parent::getBreadcrumbElements($environment, $elements);

        $builder = UrlBuilder::fromUrl($elements->getUri())
            ->setQueryParameter('do', 'metamodels')
            ->setQueryParameter('table', 'tl_metamodel_filtersetting')
            ->setQueryParameter('pid', ModelId::fromValues('tl_metamodel_filter', $elements->getId('tl_metamodel_filter'))->getSerialized())
            ->unsetQueryParameter('act')
            ->unsetQueryParameter('id');

        $elements->push(
            ampersand($builder->getUrl()),
            sprintf(
                $elements->getLabel('tl_metamodel_filtersetting'),
                $this->getRow($elements->getId('tl_metamodel_filter'), 'tl_metamodel_filter')->name
            ),
            'bundles/metamodelscore/images/icons/filter_setting.png'
        );
    }
}
