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
 * Generate a breadcrumb for table tl_metamodel_dcasetting_condition.
 *
 * @package MetaModels\DcGeneral\Events\BreadCrumb
 */
class BreadCrumbInputScreenCondition extends BreadCrumbInputScreenSetting
{
    /**
     * Calculate the name of a sub palette attribute.
     *
     * @param int $pid The id of the input screen.
     *
     * @return \MetaModels\Attribute\IAttribute|string
     */
    protected function getConditionAttribute($pid)
    {
        $parent = $this
            ->getDatabase()
            ->prepare('SELECT id, pid
                FROM tl_metamodel_attribute
                WHERE id=(SELECT attr_id FROM tl_metamodel_dcasetting WHERE id=?)')
            ->execute($pid);

        if ($parent->id) {
            $factory       = $this->getServiceContainer()->getFactory();
            $metaModelName = $factory->translateIdToMetaModelName($parent->pid);

            return $factory->getMetaModel($metaModelName)->getAttributeById($parent->id);
        }
        return 'unknown';
    }

    /**
     * {@inheritDoc}
     */
    public function getBreadcrumbElements(EnvironmentInterface $environment, $elements)
    {
        $pid       = $this->extractIdFrom($environment, 'pid');
        $attribute = $this->getConditionAttribute($pid);

        if (!isset($this->inputScreenId)) {
            $this->inputScreenId = $this
                ->getDatabase()
                ->prepare('SELECT pid FROM tl_metamodel_dcasetting WHERE id=?')
                ->execute($pid)
                ->pid;
        }

        $inputScreen = $this->getInputScreen();
        if (!isset($this->metamodelId)) {
            $this->metamodelId = $inputScreen->pid;
        }

        $elements = parent::getBreadcrumbElements($environment, $elements);
        $urlEvent = new AddToUrlEvent(
            sprintf(
                'do=metamodels&table=%s&pid=%s',
                'tl_metamodel_dcasetting_condition',
                $this->seralizeId('tl_metamodel_dcasetting', $this->metamodelId)
            )
        );
        $environment->getEventDispatcher()->dispatch(ContaoEvents::BACKEND_ADD_TO_URL, $urlEvent);

        $elements[] = array(
            'url'  => $urlEvent->getUrl(),
            'text' => sprintf(
                $this->getBreadcrumbLabel($environment, 'tl_metamodel_dcasetting_condition'),
                $attribute->getName()
            ),
            'icon' => $this->getBaseUrl() . '/system/modules/metamodels/assets/images/icons/dca_condition.png'
        );

        return $elements;
    }
}
