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
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\DcGeneral\Events\BreadCrumb;

use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;

/**
 * Generate a breadcrumb for table tl_metamodel_dcasetting.
 *
 * @package MetaModels\DcGeneral\Events\BreadCrumb
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
