<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2017 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Christopher Boelter <christopher@boelter.eu>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\DcGeneral\Events\Table\InputScreenCondition;

use ContaoCommunityAlliance\Contao\Bindings\ContaoEvents;
use ContaoCommunityAlliance\Contao\Bindings\Events\Image\GenerateHtmlEvent;
use ContaoCommunityAlliance\DcGeneral\Clipboard\Filter;
use ContaoCommunityAlliance\DcGeneral\Clipboard\ItemInterface;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetBreadcrumbEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPasteButtonEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ModelToLabelEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\ManipulateWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Controller\ModelCollector;
use ContaoCommunityAlliance\DcGeneral\Data\ModelId;
use ContaoCommunityAlliance\DcGeneral\EnvironmentInterface;
use ContaoCommunityAlliance\DcGeneral\Factory\DcGeneralFactory;
use ContaoCommunityAlliance\Translator\TranslatorInterface;
use MetaModels\Attribute\IAttribute;
use MetaModels\DcGeneral\Events\BaseSubscriber;
use MetaModels\DcGeneral\Events\BreadCrumb\BreadCrumbInputScreenCondition;
use MetaModels\IMetaModel;

/**
 * Handles event operations on tl_metamodel_dcasetting_condition.
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
                    if (($event->getEnvironment()->getDataDefinition()->getName()
                        !== 'tl_metamodel_dcasetting_condition')
                    ) {
                        return;
                    }
                    $subscriber = new BreadCrumbInputScreenCondition($serviceContainer);
                    $subscriber->getBreadcrumb($event);
                }
            )
            ->addListener(
                ModelToLabelEvent::NAME,
                array($this, 'handleModelToLabel')
            )
            ->addListener(
                GetPasteButtonEvent::NAME,
                array($this, 'generatePasteButton')
            )
            ->addListener(
                GetPropertyOptionsEvent::NAME,
                array($this, 'getTypeOptions')
            )
            ->addListener(
                GetPropertyOptionsEvent::NAME,
                array($this, 'getAttributeOptions')
            )
            ->addListener(
                ManipulateWidgetEvent::NAME,
                array($this, 'setValueOptionsMultiple')
            )
            ->addListener(
                DecodePropertyValueForWidgetEvent::NAME,
                array($this, 'decodeAttributeValue')
            )
            ->addListener(
                EncodePropertyValueFromWidgetEvent::NAME,
                array($this, 'encodeAttributeValue')
            )
            ->addListener(
                GetPropertyOptionsEvent::NAME,
                array($this, 'getValueOptions')
            )
            ->addListener(
                DecodePropertyValueForWidgetEvent::NAME,
                array($this, 'decodeValueValue')
            )
            ->addListener(
                EncodePropertyValueFromWidgetEvent::NAME,
                array($this, 'encodeValueValue')
            );
    }

    /**
     * Retrieve the MetaModel attached to the model condition setting.
     *
     * @param EnvironmentInterface $interface The environment.
     *
     * @return \MetaModels\IMetaModel
     */
    public function getMetaModel(EnvironmentInterface $interface)
    {
        $metaModelId = $this
            ->getDatabase()
            ->prepare('SELECT id FROM tl_metamodel WHERE
                id=(SELECT pid FROM tl_metamodel_dca WHERE
                id=(SELECT pid FROM tl_metamodel_dcasetting WHERE id=?))')
            ->execute(ModelId::fromSerialized($interface->getInputProvider()->getParameter('pid'))->getId());

        return $this->getMetaModelById($metaModelId->id);
    }

    /**
     * Retrieve the label text for a condition setting or the default one.
     *
     * @param TranslatorInterface $translator The environment in use.
     *
     * @param string              $type       The type of the element.
     *
     * @return string
     */
    public function getLabelText(TranslatorInterface $translator, $type)
    {
        $label = $translator->translate('typedesc.' . $type, 'tl_metamodel_dcasetting_condition');
        if ($label == 'typedesc.' . $type) {
            $label = $translator->translate(
                'typedesc._default_',
                'tl_metamodel_dcasetting_condition'
            );
            if ($label == 'typedesc._default_') {
                return $type;
            }
        }
        return $label;
    }

    /**
     * Render the html for the input screen condition.
     *
     * @param ModelToLabelEvent $event The event.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function handleModelToLabel(ModelToLabelEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_dcasetting_condition')) {
            return;
        }

        $environment    = $event->getEnvironment();
        $translator     = $environment->getTranslator();
        $model          = $event->getModel();
        $metaModels     = $this->getMetaModel($environment);
        $attribute      = $metaModels->getAttributeById($model->getProperty('attr_id'));
        $type           = $model->getProperty('type');
        $parameterValue = (is_array($model->getProperty('value')) ? implode(', ', $model->getProperty('value'))
            : $model->getProperty('value'));
        $name           = $translator->translate('conditionnames.' . $type, 'tl_metamodel_dcasetting_condition');

        $image = $GLOBALS['METAMODELS']['attributes'][$type]['image'];
        if (!$image || !file_exists(TL_ROOT . '/' . $image)) {
            $image = 'system/modules/metamodels/assets/images/icons/filter_default.png';
        }

        /** @var GenerateHtmlEvent $imageEvent */
        $imageEvent = $event->getEnvironment()->getEventDispatcher()->dispatch(
            ContaoEvents::IMAGE_GET_HTML,
            new GenerateHtmlEvent($image)
        );

        $event
            ->setLabel($this->getLabelText($translator, $type))
            ->setArgs(array(
                $imageEvent->getHtml(),
                $name,
                $attribute ? $attribute->getName() : '',
                $parameterValue
            ));
    }

    /**
     * Generate the paste button.
     *
     * @param GetPasteButtonEvent $event The event.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function generatePasteButton(GetPasteButtonEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_dcasetting_condition')) {
            return;
        }

        $environment = $event->getEnvironment();
        $model       = $event->getModel();
        $clipboard   = $environment->getClipboard();
        // Disable all buttons if there is a circular reference.
        if ($clipboard->fetch(
            Filter::create()->andActionIs(ItemInterface::CUT)->andModelIs(ModelId::fromModel($model))
        )) {
            $event
                ->setPasteAfterDisabled(true)
                ->setPasteIntoDisabled(true);

            return;
        }

        $flags = $GLOBALS['METAMODELS']['inputscreen_conditions'][$model->getProperty('type')];
        // If setting does not support children, omit them.
        if ($model->getId() &&
            (!$flags['nestingAllowed'])
        ) {
            $event->setPasteIntoDisabled(true);
            return;
        }

        $collector = new ModelCollector($environment);
        if (isset($flags['maxChildren']) && count($collector->collectChildrenOf($model)) > $flags['maxChildren']) {
            $event->setPasteIntoDisabled(true);
        }
    }

    /**
     * Provide options for property condition types.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function getTypeOptions(GetPropertyOptionsEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_dcasetting_condition')
        || ($event->getPropertyName() !== 'type')) {
            return;
        }

        $translator = $event->getEnvironment()->getTranslator();
        $options    = array();

        foreach (array_keys((array) $GLOBALS['METAMODELS']['inputscreen_conditions']) as $condition) {
            $options[$condition] = $translator->translate(
                'conditionnames.' . $condition,
                'tl_metamodel_dcasetting_condition'
            );
        }

        $event->setOptions($options);
    }

    /**
     * Prepares an option list with alias => name connection for all attributes.
     *
     * This is used in the attr_id select box.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function getAttributeOptions(GetPropertyOptionsEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_dcasetting_condition')
            || ($event->getPropertyName() !== 'attr_id')) {
            return;
        }


        $result            = array();
        $metaModel         = $this->getMetaModel($event->getEnvironment());
        $conditionType     = $event->getModel()->getProperty('type');
        $allowedAttributes = $GLOBALS['METAMODELS']['inputscreen_conditions'][$conditionType]['attributes'];

        foreach ($metaModel->getAttributes() as $attribute) {
            if (is_array($allowedAttributes) && !in_array($attribute->get('type'), $allowedAttributes)) {
                continue;
            }

            $typeName              = $attribute->get('type');
            $strSelectVal          = $metaModel->getTableName() .'_' . $attribute->getColName();
            $result[$strSelectVal] = $attribute->getName() . ' [' . $typeName . ']';
        }

        $event->setOptions($result);
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
        if (!(($event->getEnvironment()->getDataDefinition()->getName() == 'tl_metamodel_dcasetting_condition')
            && ($event->getProperty()->getName() == 'value')
            && $event->getModel()->getProperty('type') == 'conditionpropertycontainanyof')) {
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
     * Translates an attribute id to a generated alias.
     *
     * @param DecodePropertyValueForWidgetEvent $event The event.
     *
     * @return void
     */
    public function decodeAttributeValue(DecodePropertyValueForWidgetEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_dcasetting_condition')
            || ($event->getProperty() !== 'attr_id')) {
            return;
        }

        $metaModel = $this->getMetaModel($event->getEnvironment());
        $value     = $event->getValue();

        if (!($metaModel && $value)) {
            return;
        }

        $attribute = $metaModel->getAttributeById($value);
        if ($attribute) {
            $event->setValue($metaModel->getTableName() .'_' . $attribute->getColName());
        }
    }

    /**
     * Translates an generated alias to the corresponding attribute id.
     *
     * @param EncodePropertyValueFromWidgetEvent $event The event.
     *
     * @return void
     */
    public function encodeAttributeValue(EncodePropertyValueFromWidgetEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_dcasetting_condition')
            || ($event->getProperty() !== 'attr_id')) {
            return;
        }

        $metaModel = $this->getMetaModel($event->getEnvironment());
        $value     = $event->getValue();

        if (!($metaModel && $value)) {
            return;
        }

        // Cut off the 'mm_xyz_' prefix.
        $value = substr($value, strlen($metaModel->getTableName() . '_'));

        $attribute = $metaModel->getAttribute($value);

        if ($attribute) {
            $event->setValue($attribute->get('id'));
        }
    }

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
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_dcasetting_condition')
            || ($event->getPropertyName() !== 'value')) {
            return;
        }

        $model     = $event->getModel();
        $metaModel = $this->getMetaModel($event->getEnvironment());
        $attribute = $metaModel->getAttributeById($model->getProperty('attr_id'));

        if ($attribute) {
            $options = $this->getOptionsViaDcGeneral($metaModel, $event->getEnvironment(), $attribute);
            $mangled = array();
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
    public function decodeValueValue(DecodePropertyValueForWidgetEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_dcasetting_condition')
            || ($event->getProperty() !== 'value')) {
            return;
        }

        if (is_array($event->getValue())) {
            $values = array();

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
    public function encodeValueValue(EncodePropertyValueFromWidgetEvent $event)
    {
        if (($event->getEnvironment()->getDataDefinition()->getName() !== 'tl_metamodel_dcasetting_condition')
            || ($event->getProperty() !== 'value')) {
            return;
        }

        if (is_array($event->getValue())) {
            $values = array();

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
        $factory   = new DcGeneralFactory();
        $dcGeneral = $factory
            ->setContainerName($metaModel->getTableName())
            ->setEventDispatcher($environment->getEventDispatcher())
            ->setTranslator($environment->getTranslator())
            ->createDcGeneral();

        $subEnv = $dcGeneral->getEnvironment();
        $optEv  = new GetPropertyOptionsEvent($subEnv, $subEnv->getDataProvider()->getEmptyModel());
        $optEv->setPropertyName($attribute->getColName());
        $subEnv->getEventDispatcher()->dispatch(GetPropertyOptionsEvent::NAME, $optEv);

        $options = $optEv->getOptions();

        return $options;
    }
}
