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
 * @author     Christopher Boelter <christopher@boelter.eu>
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\DcGeneral\Events\BreadCrumb;

use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;

/**
 * Generate a breadcrumb for table tl_metamodel_dcasetting_condition.
 */
class BreadCrumbInputScreenCondition extends BreadCrumbInputScreenSetting
{
    /**
     * The id of the condition.
     *
     * @var int
     */
    protected $inputScreenSettingConditionId;

    /**
     * Calculate the name of a sub palette attribute.
     *
     * @return object
     */
    protected function getInputScreenSettingCondition()
    {
        return (object) $this
            ->getDatabase()
            ->prepare('SELECT * FROM tl_metamodel_dcasetting_condition WHERE id=?)')
            ->execute($this->inputScreenSettingConditionId)
            ->row();
    }

    /**
     * Calculate the name of a sub palette attribute.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    protected function getConditionAttribute()
    {
        $setting = $this->getInputScreenSetting();

        if ($setting->dcatype == 'attribute') {
            $attribute = (object) $this
                ->getDatabase()
                ->prepare('SELECT * FROM tl_metamodel_attribute WHERE id=?')
                ->execute($setting->attr_id)
                ->row();

            $factory       = $this->getServiceContainer()->getFactory();
            $metaModelName = $factory->translateIdToMetaModelName($attribute->pid);
            $attribute     = $factory->getMetaModel($metaModelName)->getAttributeById($attribute->id);
            if ($attribute) {
                return $attribute->getName();
            }
        } else {
            $title = deserialize($setting->legendtitle, true);
            return isset($title[$GLOBALS['TL_LANGUAGE']]) ? $title[$GLOBALS['TL_LANGUAGE']] : current($title);
        }

        return 'unknown ' . $setting->dcatype;
    }

    /**
     * {@inheritDoc}
     */
    public function getBreadcrumbElements(EnvironmentInterface $environment, $elements)
    {
        if (!isset($this->inputScreenSettingId)) {
            if (!isset($this->inputScreenSettingConditionId)) {
                $this->inputScreenSettingId = $this->extractIdFrom($environment, 'pid');
            } else {
                $this->inputScreenSettingId = $this->getInputScreenSettingCondition()->pid;
            }
        }

        $elements   = parent::getBreadcrumbElements($environment, $elements);
        $elements[] = array(
            'url'  => $this->generateUrl(
                'tl_metamodel_dcasetting_condition',
                $this->seralizeId('tl_metamodel_dcasetting', $this->inputScreenSettingId)
            ),
            'text' => sprintf(
                $this->getBreadcrumbLabel($environment, 'tl_metamodel_dcasetting_condition'),
                $this->getConditionAttribute()
            ),
            'icon' => $this->getBaseUrl() . '/system/modules/metamodels/assets/images/icons/dca_condition.png'
        );

        return $elements;
    }
}
