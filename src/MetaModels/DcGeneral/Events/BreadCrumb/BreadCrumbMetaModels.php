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
use MetaModels\IMetaModel;

/**
 * Generate a breadcrumb for table tl_metamodel.
 *
 * @package MetaModels\DcGeneral\Events\BreadCrumb
 */
class BreadCrumbMetaModels extends BreadCrumbBase
{
    /**
     * The id of the MetaModel.
     *
     * @var int
     */
    protected $metamodelId;

    /**
     * Retrieve the MetaModel instance.
     *
     * @return IMetaModel
     */
    protected function getMetaModel()
    {
        $services      = $this->getServiceContainer();
        $modelFactory  = $services->getFactory();
        $metaModelName = $modelFactory->translateIdToMetaModelName($this->metamodelId);

        return $modelFactory->getMetaModel($metaModelName);
    }

    /**
     * {@inheritDoc}
     */
    public function getBreadcrumbElements(EnvironmentInterface $environment, $elements)
    {
        $elements[] = array(
            'url' => 'contao/main.php?do=metamodels',
            'text' => $this->getBreadcrumbLabel($environment, 'metamodels'),
            'icon' => $this->getBaseUrl() . '/system/modules/metamodels/assets/images/backend/logo.png'
        );

        return $elements;
    }
}
