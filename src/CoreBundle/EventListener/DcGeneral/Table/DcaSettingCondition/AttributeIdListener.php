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
 * @copyright  2012-2017 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\DcaSettingCondition;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractEnvironmentAwareEvent;

/**
 * This handles the rendering of models to labels.
 */
class AttributeIdListener extends AbstractListener
{
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
        if (!$this->wantToHandle($event)) {
            return;
        }

        $result            = [];
        $metaModel         = $this->getMetaModel($event->getEnvironment());
        $conditionType     = $event->getModel()->getProperty('type');
        $allowedAttributes = $GLOBALS['METAMODELS']['inputscreen_conditions'][$conditionType]['attributes'];
        // FIXME: $allowedAttributes should be obtained from factory or parameter.
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
     * Translates an attribute id to a generated alias.
     *
     * @param DecodePropertyValueForWidgetEvent $event The event.
     *
     * @return void
     */
    public function decodeAttributeValue(DecodePropertyValueForWidgetEvent $event)
    {
        if (!$this->wantToHandle($event)) {
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
        if (!$this->wantToHandle($event)) {
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
     * {@inheritDoc}
     */
    protected function wantToHandle(AbstractEnvironmentAwareEvent $event)
    {
        if (!parent::wantToHandle($event)) {
            return false;
        }
        if (method_exists($event, 'getPropertyName') && ('attr_id' !== $event->getPropertyName())) {
            return false;
        }
        if (method_exists($event, 'getProperty') && ('attr_id' !== $event->getProperty())) {
            return false;
        }

        return true;
    }
}
