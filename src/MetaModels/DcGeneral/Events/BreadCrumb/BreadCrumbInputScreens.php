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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\DcGeneral\Events\BreadCrumb;

use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;

/**
 * Generate a breadcrumb for table tl_metamodel_dca.
 */
class BreadCrumbInputScreens extends BreadCrumbMetaModels
{
    /**
     * Id of the input screen.
     *
     * @var int
     */
    protected $inputScreenId;

    /**
     * Retrieve the input screen database information.
     *
     * @return object
     */
    protected function getInputScreen()
    {
        return (object) $this
            ->getDatabase()
            ->prepare('SELECT id, pid, name FROM tl_metamodel_dca WHERE id=?')
            ->execute($this->inputScreenId)
            ->row();
    }

    /**
     * {@inheritDoc}
     */
    public function getBreadcrumbElements(EnvironmentInterface $environment, $elements)
    {
        if (!isset($this->metamodelId)) {
            if (!isset($this->inputScreenId)) {
                $this->metamodelId = $this->extractIdFrom($environment, 'pid');
            } else {
                $this->metamodelId = $this->getInputScreen()->pid;
            }
        }

        $elements   = parent::getBreadcrumbElements($environment, $elements);
        $elements[] = array(
            'url'  => $this->generateUrl(
                'tl_metamodel_dca',
                $this->seralizeId('tl_metamodel', $this->metamodelId)
            ),
            'text' => sprintf(
                $this->getBreadcrumbLabel($environment, 'tl_metamodel_dca'),
                $this->getMetaModel()->getName()
            ),
            'icon' => $this->getBaseUrl() . '/system/modules/metamodels/assets/images/icons/dca.png'
        );

        return $elements;
    }
}
