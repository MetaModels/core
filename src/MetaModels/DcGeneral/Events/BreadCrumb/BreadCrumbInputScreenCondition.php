<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Christopher Boelter <c.boelter@cogizz.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\DcGeneral\Events\BreadCrumb;

use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;

/**
 * Generate a breadcrumb for table tl_metamodel_dcasetting_condition.
 *
 * @package MetaModels\DcGeneral\Events\BreadCrumb
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
            $title = deserialize($setting->legendtitle);
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
