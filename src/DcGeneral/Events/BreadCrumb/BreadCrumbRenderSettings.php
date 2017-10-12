<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2017 The MetaModels team.
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
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\DcGeneral\Events\BreadCrumb;

use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;

/**
 * Generate a breadcrumb for table tl_metamodel_rendersettings.
 */
class BreadCrumbRenderSettings extends BreadCrumbMetaModels
{
    /**
     * Id of the render setting.
     *
     * @var int
     */
    protected $renderSettingsId;

    /**
     * Retrieve the render setting.
     *
     * @return object
     */
    protected function getRenderSettings()
    {
        return (object) $this
            ->getServiceContainer()
            ->getDatabase()
            ->prepare('SELECT * FROM tl_metamodel_rendersettings WHERE id=?')
            ->execute($this->renderSettingsId)
            ->row();
    }

    /**
     * {@inheritDoc}
     */
    public function getBreadcrumbElements(EnvironmentInterface $environment, $elements)
    {
        if (!isset($this->metamodelId)) {
            if (!isset($this->renderSettingsId)) {
                $this->metamodelId = $this->extractIdFrom($environment, 'pid');
            } else {
                $this->metamodelId = $this->getRenderSettings()->pid;
            }
        }

        $elements   = parent::getBreadcrumbElements($environment, $elements);
        $elements[] = array(
            'url' => $this->generateUrl(
                'tl_metamodel_rendersettings',
                $this->seralizeId('tl_metamodel', $this->metamodelId)
            ),
            'text' => sprintf(
                $this->getBreadcrumbLabel($environment, 'tl_metamodel_rendersettings'),
                $this->getMetaModel()->getName()
            ),
            'icon' => $this->getBaseUrl() . '/system/modules/metamodels/assets/images/icons/rendersettings.png'
        );

        return $elements;
    }
}
