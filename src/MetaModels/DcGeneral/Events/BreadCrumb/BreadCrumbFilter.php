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
 * Generate a breadcrumb for table tl_metamodel_filter.
 *
 * @package MetaModels\DcGeneral\Events\BreadCrumb
 */
class BreadCrumbFilter extends BreadCrumbMetaModels
{
    /**
     * The id of the filter setting.
     *
     * @var int
     */
    protected $filterId;

    /**
     * Get the filter setting data base row object.
     *
     * @return object
     */
    protected function getFilter()
    {
        return (object) $this
            ->getDatabase()
            ->prepare('SELECT id, pid, name FROM tl_metamodel_filter WHERE id=?')
            ->execute($this->filterId)
            ->row();
    }

    /**
     * {@inheritDoc}
     */
    public function getBreadcrumbElements(EnvironmentInterface $environment, $elements)
    {
        $input = $environment->getInputProvider();
        if (!$this->isActiveTable('tl_metamodel_filter', $input)) {
            $this->filterId = $this->extractIdFrom($environment, 'pid');
        } else {
            $this->metamodelId = $this->extractIdFrom($environment, 'pid');
        }

        if (!isset($this->metamodelId)) {
            $this->metamodelId = $this->getFilter()->pid;
        }

        $elements   = parent::getBreadcrumbElements($environment, $elements);
        $elements[] = array(
            'url' => $this->generateUrl(
                'tl_metamodel_filter',
                $this->seralizeId('tl_metamodel', $this->metamodelId)
            ),
            'text' => sprintf(
                $this->getBreadcrumbLabel($environment, 'tl_metamodel_filter'),
                $this->getMetaModel()->getName()
            ),
            'icon' => $this->getBaseUrl() . '/system/modules/metamodels/assets/images/icons/filter.png'
        );

        return $elements;
    }
}
