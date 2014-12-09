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

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Backend\AddToUrlEvent;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;

/**
 * Generate a breadcrumb for table tl_metamodel_dcasetting.
 *
 * @package MetaModels\DcGeneral\Events\BreadCrumb
 */
class BreadCrumbInputScreenSortGroup extends BreadCrumbInputScreens
{
    /**
     * {@inheritDoc}
     */
    public function getBreadcrumbElements(EnvironmentInterface $environment, $elements)
    {
        if (!isset($this->inputScreenId)) {
            $this->inputScreenId = $this->extractIdFrom($environment, 'pid');
        }

        $inputScreen = $this->getInputScreen();
        if (!isset($this->metamodelId)) {
            $this->metamodelId = $inputScreen->pid;
        }

        $elements = parent::getBreadcrumbElements($environment, $elements);

        $urlEvent = new AddToUrlEvent(
            sprintf(
                'do=metamodels&table=%s&pid=%s',
                'tl_metamodel_dca_sortgroup',
                $this->seralizeId('tl_metamodel_dca', $this->inputScreenId)
            )
        );
        $environment->getEventDispatcher()->dispatch(ContaoEvents::BACKEND_ADD_TO_URL, $urlEvent);

        $elements[] = array(
            'url' => $urlEvent->getUrl(),
            'text' => sprintf(
                $this->getBreadcrumbLabel($environment, 'tl_metamodel_dca_sortgroup'),
                $inputScreen->name
            ),
            'icon' => $this->getBaseUrl() . '/system/modules/metamodels/assets/images/icons/dca_groupsortsettings.png'
        );

        return $elements;
    }
}
