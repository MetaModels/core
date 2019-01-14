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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\DcGeneral\Events\BreadCrumb;

use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;

/**
 * Generate a breadcrumb for table tl_metamodel_filtersetting.
 */
class BreadCrumbFilterSetting extends BreadCrumbFilter
{
    /**
     * {@inheritDoc}
     */
    public function getBreadcrumbElements(EnvironmentInterface $environment, $elements)
    {
        if (!isset($this->filterId)) {
            $this->filterId = $this->extractIdFrom($environment, 'pid');
        }

        if (!isset($this->metamodelId)) {
            $parent = $this
                ->getDatabase()
                ->prepare('SELECT id, pid, name FROM tl_metamodel_filter WHERE id=?')
                ->execute($this->filterId);

            $this->metamodelId = $parent->pid;
        }

        $filterSetting = $this->getFilter();

        $elements   = parent::getBreadcrumbElements($environment, $elements);
        $elements[] = array(
            'url' => $this->generateUrl(
                'tl_metamodel_filtersetting',
                $this->seralizeId('tl_metamodel_filter', $this->filterId)
            ),
            'text' => sprintf(
                $this->getBreadcrumbLabel($environment, 'tl_metamodel_filtersetting'),
                $filterSetting->name
            ),
            'icon' => $this->getBaseUrl() . '/system/modules/metamodels/assets/images/icons/filter_setting.png'
        );

        return $elements;
    }
}
