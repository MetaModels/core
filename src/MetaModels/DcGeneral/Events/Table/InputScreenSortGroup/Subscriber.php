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

namespace MetaModels\DcGeneral\Events\Table\InputScreenSortGroup;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\IdSerializer;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionChain;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use MetaModels\DcGeneral\DataDefinition\Palette\Condition\Property\InputScreenRenderModeIs;
use MetaModels\DcGeneral\Events\BaseSubscriber;
use MetaModels\DcGeneral\Events\BreadCrumb\BreadCrumbInputScreenSortGroup;

/**
 * Handles event operations on tl_metamodel_dca_sortgroup.
 */
class Subscriber extends BaseSubscriber
{
    /**
     * Register all listeners to handle creation of a data container.
     *
     * @return void
     */
    protected function registerEventsInDispatcher()
    {
        $serviceContainer = $this->getServiceContainer();
        $this
            ->addListener(
                GetBreadcrumbEvent::NAME,
                function (GetBreadcrumbEvent $event) use ($serviceContainer) {
                    if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_dca_sortgroup')) {
                        return;
                    }
                    $subscriber = new BreadCrumbInputScreenSortGroup($serviceContainer);
                    $subscriber->getBreadcrumb($event);
                }
            )
            ->addListener(
                GetPropertyOptionsEvent::NAME,
                array($this, 'getAttrOptions')
            )
            ->addListener(
                DecodePropertyValueForWidgetEvent::NAME,
                array($this, 'decodeAttrValue')
            )
            ->addListener(
                EncodePropertyValueFromWidgetEvent::NAME,
                array($this, 'encodeAttrValue')
            )
            ->addListener(
                BuildDataDefinitionEvent::NAME,
                array($this, 'setVisibility')
            );
    }

    /**
     * Retrieve the MetaModel attached to the model filter setting.
     *
     * @param EnvironmentInterface $environment The environment.
     *
     * @return \MetaModels\IMetaModel
     */
    public function getMetaModel(EnvironmentInterface $environment)
    {
        $metaModelId = $this->getDatabase()
            ->prepare('SELECT id FROM tl_metamodel WHERE id=(SELECT pid FROM tl_metamodel_dca WHERE id=?)')
            ->execute(IdSerializer::fromSerialized($environment->getInputProvider()->getParameter('pid'))->getId());

        /** @noinspection PhpUndefinedFieldInspection */
        return $this->getMetaModelById($metaModelId->id);
    }

    /**
     * Provide options for attribute type selection.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     */
    public function getAttrOptions(GetPropertyOptionsEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_dca_sortgroup')
            || (($event->getPropertyName() !== 'rendergroupattr')
                && ($event->getPropertyName() !== 'rendersortattr'))
        ) {
            return;
        }

        $result    = array();
        $metaModel = $this->getMetaModel($event->getEnvironment());

        foreach ($metaModel->getAttributes() as $attribute) {
            $typeName              = $attribute->get('type');
            $strSelectVal          = $metaModel->getTableName() . '_' . $attribute->getColName();
            $result[$strSelectVal] = $attribute->getName() . ' [' . $typeName . ']';
        }

        $event->setOptions($result);
    }


    /**
     * Translates an attribute id to a generated alias.
     *
     * @param DecodePropertyValueForWidgetEvent $event The event.
     *
     * @return void
     */
    public function decodeAttrValue(DecodePropertyValueForWidgetEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_dca_sortgroup')
            || (($event->getProperty() !== 'rendergroupattr')
                && ($event->getProperty() !== 'rendersortattr'))
        ) {
            return;
        }

        $metaModel = self::getMetaModel($event->getEnvironment());
        $value     = $event->getValue();

        if (!($metaModel && $value)) {
            return;
        }

        $attribute = $metaModel->getAttributeById($value);
        if ($attribute) {
            $event->setValue($metaModel->getTableName() . '_' . $attribute->getColName());
        }
    }

    /**
     * Translates an generated alias to the corresponding attribute id.
     *
     * @param EncodePropertyValueFromWidgetEvent $event The event.
     *
     * @return void
     */
    public function encodeAttrValue(EncodePropertyValueFromWidgetEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_dca_sortgroup')
            || (($event->getProperty() !== 'rendergroupattr')
                && ($event->getProperty() !== 'rendersortattr'))
        ) {
            return;
        }

        $metaModel = self::getMetaModel($event->getEnvironment());
        $value     = $event->getValue();

        if (!($metaModel && $value)) {
            return;
        }

        $value = str_replace($metaModel->getTableName() . '_', '', $value);

        $attribute = $metaModel->getAttribute($value);

        if ($attribute) {
            $event->setValue($attribute->get('id'));
        }
    }

    /**
     * Add a visible condition.
     *
     * @param PropertyInterface  $property  The property.
     *
     * @param ConditionInterface $condition The condition to add.
     *
     * @return void
     */
    protected function addCondition(PropertyInterface $property, ConditionInterface $condition)
    {
        $chain = $property->getVisibleCondition();
        if (!($chain
            && ($chain instanceof PropertyConditionChain)
            && $chain->getConjunction() == PropertyConditionChain::AND_CONJUNCTION
        )
        ) {
            if ($property->getVisibleCondition()) {
                $previous = array($property->getVisibleCondition());
            } else {
                $previous = array();
            }

            $chain = new PropertyConditionChain(
                $previous,
                PropertyConditionChain::AND_CONJUNCTION
            );

            $property->setVisibleCondition($chain);
        }

        $chain->addCondition($condition);
    }

    /**
     * Set the visibility condition for the widget.
     *
     * Manipulate the data definition for the property "rendergrouptype" in table "tl_metamodel_dca_sortgroup".
     *
     * @param BuildDataDefinitionEvent $event The event.
     *
     * @return void
     */
    public function setVisibility(BuildDataDefinitionEvent $event)
    {
        foreach ($event->getContainer()->getPalettesDefinition()->getPalettes() as $palette) {
            foreach ($palette->getProperties() as $property) {
                if ($property->getName() != 'rendergrouptype') {
                    continue;
                }

                self::addCondition(
                    $property,
                    new PropertyConditionChain(
                        array(
                            new InputScreenRenderModeIs('flat'),
                            new InputScreenRenderModeIs('parented'),
                        ),
                        PropertyConditionChain::OR_CONJUNCTION
                    )
                );
            }
        }
    }
}
