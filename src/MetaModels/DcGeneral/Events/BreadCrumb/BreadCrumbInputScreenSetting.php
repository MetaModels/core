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
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Christopher Boelter <c.boelter@cogizz.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\DcGeneral\Events\BreadCrumb;

use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;

/**
 * Generate a breadcrumb for table tl_metamodel_dcasetting.
 */
class BreadCrumbInputScreenSetting extends BreadCrumbInputScreens
{
    /**
     * Id of the input screen setting.
     *
     * @var int
     */
    protected $inputScreenSettingId;

    /**
     * Retrieve the input screen database information.
     *
     * @return object
     */
    protected function getInputScreenSetting()
    {
        return (object) $this
            ->getDatabase()
            ->prepare('SELECT * FROM tl_metamodel_dcasetting WHERE id=?')
            ->execute($this->inputScreenSettingId)
            ->row();
    }

    /**
     * {@inheritDoc}
     */
    public function getBreadcrumbElements(EnvironmentInterface $environment, $elements)
    {
        if (!isset($this->inputScreenId)) {
            if (!isset($this->inputScreenSettingId)) {
                $this->inputScreenId = $this->extractIdFrom($environment, 'pid');
            } else {
                $this->inputScreenId = $this->getInputScreenSetting()->pid;
            }
        }

        $elements   = parent::getBreadcrumbElements($environment, $elements);
        $elements[] = array(
            'url' => $this->generateUrl(
                'tl_metamodel_dcasetting',
                $this->seralizeId('tl_metamodel_dca', $this->inputScreenId)
            ),
            'text' => sprintf(
                $this->getBreadcrumbLabel($environment, 'tl_metamodel_dcasetting'),
                $this->getInputScreen()->name
            ),
            'icon' => $this->getBaseUrl() . '/system/modules/metamodels/assets/images/icons/dca_setting.png'
        );

        return $elements;
    }
}
