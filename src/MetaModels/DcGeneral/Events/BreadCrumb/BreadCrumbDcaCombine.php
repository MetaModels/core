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
 * Generate a breadcrumb for table tl_metamodel_dcacombine.
 *
 * @package MetaModels\DcGeneral\Events\BreadCrumb
 */
class BreadCrumbDcaCombine extends BreadCrumbMetaModels
{
    /**
     * {@inheritDoc}
     */
    public function getBreadcrumbElements(EnvironmentInterface $environment, $elements)
    {
        if (!isset($this->metamodelId)) {
            $this->metamodelId = $this->extractIdFrom($environment, 'pid');
        }

        $elements   = parent::getBreadcrumbElements($environment, $elements);
        $elements[] = array(
            'url' => $this->generateUrl(
                'tl_metamodel_dca_combine',
                $this->seralizeId('tl_metamodel', $this->metamodelId),
                true
            ),
            'text' => sprintf(
                $this->getBreadcrumbLabel($environment, 'tl_metamodel_dca_combine'),
                $this->getMetaModel()->getName()
            ),
            'icon' => $this->getBaseUrl() . '/system/modules/metamodels/assets/images/icons/dca_combine.png'
        );

        return $elements;
    }
}
