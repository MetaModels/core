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
use MetaModels\Render\Setting\Factory;
use MetaModels\Render\Setting\ICollection;

/**
 * Generate a breadcrumb for table tl_metamodel_rendersettings.
 *
 * @package MetaModels\DcGeneral\Events\BreadCrumb
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
     * @return ICollection
     */
    protected function getRenderSettings()
    {
        return Factory::byId($this->getMetaModel(), $this->renderSettingsId);
    }

    /**
     * {@inheritDoc}
     */
    public function getBreadcrumbElements(EnvironmentInterface $environment, $elements)
    {
        if (!isset($this->metamodelId)) {
            $this->metamodelId = $this->extractIdFrom($environment, 'pid');
        }

        $elements = parent::getBreadcrumbElements($environment, $elements);

        $elements[] = array(
            'url' => sprintf(
                'contao/main.php?do=metamodels&table=%s&pid=%s',
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
