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
     * {@inheritDoc}
     */
    public function getBreadcrumbElements(EnvironmentInterface $environment, $elements)
    {
        if (!isset($this->renderSettingId)) {
            $this->renderSettingId = $this->extractIdFrom($environment, 'pid');
        }

        if (!isset($this->renderSettingsId)) {
            $parent = $this
                ->getDatabase()
                ->prepare('SELECT pid, name FROM tl_metamodel_rendersettings WHERE id=?')
                ->execute($this->renderSettingId);

            $this->renderSettingsId = $parent->pid;
        }

        $elements       = parent::getBreadcrumbElements($environment, $elements);
        $renderSettings = $this->getRenderSettings();

        $elements[] = array(
            'url' => $this->generateUrl(
                'tl_metamodel_rendersetting',
                $this->seralizeId('tl_metamodel_rendersetting', $this->renderSettingId)
            ),
            'text' => sprintf(
                $this->getBreadcrumbLabel($environment, 'tl_metamodel_rendersetting'),
                $renderSettings->get('name')
            ),
            'icon' => $this->getBaseUrl() . '/system/modules/metamodels/assets/images/icons/rendersetting.png'
        );

        return $elements;
    }
}
