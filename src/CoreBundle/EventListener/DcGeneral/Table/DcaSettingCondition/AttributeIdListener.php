<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2024 The MetaModels team.
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
 * @copyright  2012-2024 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\DcaSettingCondition;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractEnvironmentAwareEvent;
use Doctrine\DBAL\Connection;
use MetaModels\Attribute\IAttribute;
use MetaModels\CoreBundle\DcGeneral\PropertyConditionFactory;
use MetaModels\CoreBundle\Formatter\SelectAttributeOptionLabelFormatter;
use MetaModels\IFactory;

/**
 * This handles the rendering of models to labels.
 */
class AttributeIdListener extends AbstractConditionFactoryUsingListener
{
    /**
     * The attribute select option label formatter.
     *
     * @var SelectAttributeOptionLabelFormatter
     */
    private SelectAttributeOptionLabelFormatter $labelFormatter;

    /**
     * {@inheritDoc}
     */
    public function __construct(
        RequestScopeDeterminator $scopeDeterminator,
        IFactory $factory,
        Connection $connection,
        PropertyConditionFactory $conditionFactory,
        SelectAttributeOptionLabelFormatter $labelFormatter
    ) {
        parent::__construct($scopeDeterminator, $factory, $connection, $conditionFactory);
        $this->labelFormatter = $labelFormatter;
    }

    /**
     * Prepares an option list with alias => name connection for all attributes.
     *
     * This is used in the attr_id select box.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     */
    public function getAttributeOptions(GetPropertyOptionsEvent $event)
    {
        if (!$this->wantToHandle($event)) {
            return;
        }

        $result        = [];
        $metaModel     = $this->getMetaModel($event->getEnvironment());
        $conditionType = $event->getModel()->getProperty('type');
        foreach ($metaModel->getAttributes() as $attribute) {
            if (!$this->conditionFactory->supportsAttribute($conditionType, $attribute->get('type'))) {
                continue;
            }

            $colName               = $attribute->getColName();
            $strSelectVal          = $metaModel->getTableName() . '_' . $colName;
            $result[$strSelectVal] = $this->labelFormatter->formatLabel($attribute);
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

        if (!$value) {
            $event->setValue(null);
            return;
        }

        $attribute = $metaModel->getAttributeById((int) $value);
        if ($attribute) {
            $event->setValue($metaModel->getTableName() . '_' . $attribute->getColName());
        }
    }

    /**
     * Translates a generated alias to the corresponding attribute id.
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

        if (!$value) {
            return;
        }

        // Cut off the 'mm_xyz_' prefix.
        $value = \substr($value, \strlen($metaModel->getTableName() . '_'));

        $attribute = $metaModel->getAttribute($value);
        assert($attribute instanceof IAttribute);

        $event->setValue($attribute->get('id'));
    }

    /**
     * {@inheritDoc}
     */
    protected function wantToHandle(AbstractEnvironmentAwareEvent $event)
    {
        if (!parent::wantToHandle($event)) {
            return false;
        }
        if (\method_exists($event, 'getPropertyName') && ('attr_id' !== $event->getPropertyName())) {
            return false;
        }
        if (\method_exists($event, 'getProperty') && ('attr_id' !== $event->getProperty())) {
            return false;
        }

        return true;
    }
}
