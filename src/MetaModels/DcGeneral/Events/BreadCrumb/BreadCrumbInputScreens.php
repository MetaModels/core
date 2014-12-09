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
 * Generate a breadcrumb for table tl_metamodel_dca.
 *
 * @package MetaModels\DcGeneral\Events\BreadCrumb
 */
class BreadCrumbInputScreens extends BreadCrumbMetaModels
{
    /**
     * Id of the input screen.
     *
     * @var int
     */
    protected $inputScreenId;

    /**
     * Retrieve the input screen database information.
     *
     * @return object
     */
    protected function getInputScreen()
    {
        return (object) $this
            ->getDatabase()
            ->prepare('SELECT id, pid, name FROM tl_metamodel_dca WHERE id=?')
            ->execute($this->inputScreenId)
            ->row();
    }

    /**
     * {@inheritDoc}
     */
    public function getBreadcrumbElements(EnvironmentInterface $environment, $elements)
    {
        if (!isset($this->metamodelId)) {
            if (!isset($this->inputScreenId)) {
                $this->metamodelId = $this->extractIdFrom($environment, 'pid');
            } else {
                $this->metamodelId = $this->getInputScreen()->pid;
            }
        }

        $elements   = parent::getBreadcrumbElements($environment, $elements);
        $elements[] = array(
            'url'  => $this->generateUrl(
                'tl_metamodel_dca',
                $this->seralizeId('tl_metamodel', $this->metamodelId)
            ),
            'text' => sprintf(
                $this->getBreadcrumbLabel($environment, 'tl_metamodel_dca'),
                $this->getMetaModel()->getName()
            ),
            'icon' => $this->getBaseUrl() . '/system/modules/metamodels/assets/images/icons/dca.png'
        );

        return $elements;
    }
}
