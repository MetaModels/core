<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2022 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @copyright  2012-2022 The MetaModels team.
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
use MetaModels\Attribute\IAliasConverter;
use MetaModels\Attribute\IAttribute;
use MetaModels\IMetaModel;
use MetaModels\ITranslatedMetaModel;

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

        if (null === $attributeId = $model->getProperty('attr_id')) {
            return;
        }

        $attribute = $metaModel->getAttributeById((int) $attributeId);
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
     * Get the pure value like the alias or the id if the attribute is a converted one
     * and convert it in a value for the widget for fitting the option keys.
     *
     * @param DecodePropertyValueForWidgetEvent $event The event.
     *
     * @return void
     */
    public function decodeValue(DecodePropertyValueForWidgetEvent $event)
    {
        if (!$this->wantToHandle($event) || $event->getValue() === null) {
            return;
        }

        $model     = $event->getModel();
        $metaModel = $this->getMetaModel($event->getEnvironment());
        if (null === $attributeId = $model->getProperty('attr_id')) {
            return;
        }
        $attribute       = $metaModel->getAttributeById((int) $attributeId);
        $currentLanguage = $this->extractCurrentLanguageContext($metaModel);

        if (is_array($event->getValue())) {
            $values = [];

            foreach ($event->getValue() as $value) {
                $values[] = $this->idToAlias($value, $attribute, $currentLanguage);
            }

            $event->setValue($values);
        } else {
            $event->setValue($this->idToAlias($event->getValue(), $attribute, $currentLanguage));
        }
    }

    /**
     * Got the data from the widget and make them clean and nice for all the others.
     * Try to remove the value_ prefix or convert a alias to id.
     *
     * @param EncodePropertyValueFromWidgetEvent $event The event.
     *
     * @return void
     */
    public function encodeValue(EncodePropertyValueFromWidgetEvent $event)
    {
        if (!$this->wantToHandle($event) || $event->getValue() === null) {
            return;
        }

        $model     = $event->getPropertyValueBag();
        $metaModel = $this->getMetaModel($event->getEnvironment());
        if (null === $attributeId = $model->getPropertyValue('attr_id')) {
            return;
        }
        $attribute       = $metaModel->getAttributeById((int) $attributeId);
        $currentLanguage = $this->extractCurrentLanguageContext($metaModel);

        if (is_array($event->getValue())) {
            $values = [];

            foreach ($event->getValue() as $value) {
                $values[] = $this->aliasToId($value, $attribute, $currentLanguage);
            }

            $event->setValue($values);
        } else {
            $event->setValue($this->aliasToId($event->getValue(), $attribute, $currentLanguage));
        }
    }

    /**
     * Set the value select to multiple.
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
        $attribute = $metaModel->getAttributeById((int) $event->getModel()->getProperty('attr_id'));

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
     * @param EnvironmentInterface $environment The environment used in the input screen table dc-general.
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
        $subEnv->getEventDispatcher()->dispatch($optEv, GetPropertyOptionsEvent::NAME);

        return $optEv->getOptions();
    }

    /**
     * If the attribute supports the IAliasConverter try to get the id instead of the alias.
     * If it is not an IAliasConverter return the clear value without "value_".
     *
     * @param string     $alias     The alias where we want the id from.
     * @param IAttribute $attribute The attribute.
     * @param string     $language  The language to used for the convertion.
     *
     * @return string The value to be saved.
     */
    private function aliasToId(string $alias, IAttribute $attribute, string $language): string
    {
        if ($attribute instanceof IAliasConverter) {
            $idForAlias = $attribute->getIdForAlias(substr($alias, 6), $language);
            if ($idForAlias !== null) {
                return $idForAlias;
            }
        }

        return substr($alias, 6);
    }

    /**
     * If the attribute supports the IAliasConverter try to get the alias instead of the id.
     * If it is not an IAliasConverter return the values as it is.
     *
     * @param string     $idValue   The id to be used to find the alias.
     * @param IAttribute $attribute The attribute.
     * @param string     $language  The language to used for the convertion.
     *
     * @return string The value to be saved.
     */
    private function idToAlias(string $idValue, IAttribute $attribute, string $language): string
    {
        if (substr($idValue, 0, 6) == 'value_') {
            $idValue = substr($idValue, 6);
        }

        if ($attribute instanceof IAliasConverter) {
            $alias = $attribute->getAliasForId($idValue, $language);
            if ($alias !== null) {
                return 'value_' . $alias;
            }
        }

        return 'value_' . $idValue;
    }

    /**
     * Try to find the right language context.
     *
     * @param \MetaModels\IMetaModel $metaModel The current metamodel for the context.
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    private function extractCurrentLanguageContext(IMetaModel $metaModel): string
    {
        if ($metaModel instanceof ITranslatedMetaModel) {
            return $metaModel->getLanguage();
        }
        // Legacy compatibility fallback for translated metamodels not implementing the interface.
        if ($metaModel->isTranslated(false)) {
            return $metaModel->getActiveLanguage();
        }
        // Use the current backend language then.
        return \str_replace('-', '_', $GLOBALS['TL_LANGUAGE']);
    }
}
