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
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\DcGeneral\Events\BreadCrumb;

use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;

/**
 * Generate a breadcrumb for table tl_metamodel_rendersettings.
 *
 * @package MetaModels\DcGeneral\Events\BreadCrumb
 */
class BreadCrumbRenderSetting extends BreadCrumbRenderSettings
{
    /**
     * Id of the render setting.
     *
     * @var int
     */
    protected $renderSettingId;

    /**
     * Retrieve the render setting.
     *
     * @return object
     */
    protected function getRenderSettingItem()
    {
        return (object) $this
            ->getServiceContainer()
            ->getDatabase()
            ->prepare('SELECT * FROM tl_metamodel_rendersetting WHERE id=?')
            ->execute($this->renderSettingId)
            ->row();
    }

    /**
     * {@inheritDoc}
     */
    public function getBreadcrumbElements(EnvironmentInterface $environment, $elements)
    {
        if (!isset($this->renderSettingsId)) {
            if (!isset($this->renderSettingId)) {
                $this->renderSettingsId = $this->extractIdFrom($environment, 'pid');
            } else {
                $this->renderSettingsId = $this->getRenderSettingItem()->pid;
            }
        }

        $elements       = parent::getBreadcrumbElements($environment, $elements);
        $renderSettings = $this->getRenderSettings();

        $elements[] = array(
            'url' => $this->generateUrl(
                'tl_metamodel_rendersetting',
                $this->seralizeId('tl_metamodel_rendersettings', $this->renderSettingsId)
            ),
            'text' => sprintf(
                $this->getBreadcrumbLabel($environment, 'tl_metamodel_rendersetting'),
                $renderSettings->name
            ),
            'icon' => $this->getBaseUrl() . '/system/modules/metamodels/assets/images/icons/rendersetting.png'
        );

        return $elements;
    }
}
