<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2018 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\DcaSettingCondition;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ManipulateWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractEnvironmentAwareEvent;
use ContaoCommunityAlliance\DcGeneral\Factory\DcGeneralFactory;
use MetaModels\Attribute\IAttribute;
use MetaModels\IMetaModel;

/**
 * This handles the rendering of models to labels.
 */
class ValueListener extends AbstractListener
{
    /**
     * Provide options for the values contained within a certain attribute.
     *
     * The values get prefixed with 'value_' to ensure numeric values are kept intact.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     */
    public function getValueOptions(GetPropertyOptionsEvent $event)
    {
        if (!$this->wantToHandle($event)) {
            return;
        }

        $model     = $event->getModel();
        $metaModel = $this->getMetaModel($event->getEnvironment());
        $attribute = $metaModel->getAttributeById($model->getProperty('attr_id'));

        if ($attribute) {
            $options = $this->getOptionsViaDcGeneral($metaModel, $event->getEnvironment(), $attribute);
            $mangled = [];
            foreach ((array) $options as $key => $option) {
                $mangled['value_' . $key] = $option;
            }

            $event->setOptions($mangled);
        }
    }

    /**
     * Translates an value to a generated alias to allow numeric values.
     *
     * @param DecodePropertyValueForWidgetEvent $event The event.
     *
     * @return void
     */
    public function decodeValue(DecodePropertyValueForWidgetEvent $event)
    {
        if (!$this->wantToHandle($event)) {
            return;
        }

        if (is_array($event->getValue())) {
            $values = [];

            foreach ($event->getValue() as $value) {
                $values[] = 'value_' . $value;
            }

            // Cut off the 'value_' prefix.
            $event->setValue($values);
        } else {
            $event->setValue('value_' . $event->getValue());
        }
    }

    /**
     * Translates an generated alias to the corresponding value.
     *
     * @param EncodePropertyValueFromWidgetEvent $event The event.
     *
     * @return void
     */
    public function encodeValue(EncodePropertyValueFromWidgetEvent $event)
    {
        if (!$this->wantToHandle($event)) {
            return;
        }

        if (is_array($event->getValue())) {
            $values = [];

            foreach ($event->getValue() as $value) {
                $values[] = substr($value, 6);
            }

            // Cut off the 'value_' prefix.
            $event->setValue($values);
        } else {
            // Cut off the 'value_' prefix.
            $event->setValue(substr($event->getValue(), 6));
        }
    }

    /**
     * Set the the value select to multiple.
     *
     * @param ManipulateWidgetEvent $event The event.
     *
     * @return void
     */
    public function setValueOptionsMultiple(ManipulateWidgetEvent $event)
    {
        if (!$this->wantToHandle($event)) {
            return;
        }

        if ($event->getModel()->getProperty('type') !== 'conditionpropertycontainanyof') {
            return;
        }

        $metaModel = $this->getMetaModel($event->getEnvironment());
        $attribute = $metaModel->getAttributeById($event->getModel()->getProperty('attr_id'));

        if (!($attribute && ($attribute->get('type') == 'tags'))) {
            return;
        }

        $event->getWidget()->multiple = true;
    }

    /**
     * {@inheritDoc}
     */
    protected function wantToHandle(AbstractEnvironmentAwareEvent $event)
    {
        if (!parent::wantToHandle($event)) {
            return false;
        }
        if (method_exists($event, 'getPropertyName') && ('value' !== $event->getPropertyName())) {
            return false;
        }
        if (method_exists($event, 'getProperty')) {
            $property = $event->getProperty();
            if ($property instanceof PropertyInterface) {
                $property = $property->getName();
            }
            if ('value' !== $property) {
                return false;
            }
        }

        return true;
    }

    /**
     * Obtain the values of a property within a dc-general instance.
     *
     * @param IMetaModel           $metaModel   The metamodel instance to obtain the values from.
     *
     * @param EnvironmentInterface $environment The environment used in the input screen table dc-general.
     *
     * @param IAttribute           $attribute   The attribute to obtain the values for.
     *
     * @return array
     */
    private function getOptionsViaDcGeneral($metaModel, $environment, $attribute)
    {
        $factory   = DcGeneralFactory::deriveEmptyFromEnvironment($environment)
            ->setContainerName($metaModel->getTableName());
        $dcGeneral = $factory->createDcGeneral();

        $subEnv = $dcGeneral->getEnvironment();
        $optEv  = new GetPropertyOptionsEvent($subEnv, $subEnv->getDataProvider()->getEmptyModel());
        $optEv->setPropertyName($attribute->getColName());
        $subEnv->getEventDispatcher()->dispatch(GetPropertyOptionsEvent::NAME, $optEv);

        $options = $optEv->getOptions();

        return $options;
    }
}
