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

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\FilterSetting;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ContainerInterface;
use MetaModels\Attribute\IAttribute;
use MetaModels\CoreBundle\Formatter\SelectAttributeOptionLabelFormatter;
use MetaModels\Filter\Setting\IFilterSettingFactory;

/**
 * This class provides the attribute options and encodes and decodes the attribute id.
 */
class AttributeListener
{
    /**
     * The filter setting factory.
     *
     * @var IFilterSettingFactory
     */
    private IFilterSettingFactory $filterFactory;

    /**
     * The attribute select option label formatter.
     *
     * @var SelectAttributeOptionLabelFormatter
     */
    private SelectAttributeOptionLabelFormatter $labelFormatter;

    /**
     * Create a new instance.
     *
     * @param IFilterSettingFactory               $filterFactory  The filter setting factory.
     * @param SelectAttributeOptionLabelFormatter $labelFormatter The attribute select option label formatter.
     */
    public function __construct(
        IFilterSettingFactory $filterFactory,
        SelectAttributeOptionLabelFormatter $labelFormatter
    ) {
        $this->filterFactory  = $filterFactory;
        $this->labelFormatter = $labelFormatter;
    }

    /**
     * Provide options for default selection.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     */
    public function getOptions(GetPropertyOptionsEvent $event): void
    {
        $dataDefinition = $event->getEnvironment()->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        if (
            ('tl_metamodel_filtersetting' !== $dataDefinition->getName())
            || ('attr_id' !== $event->getPropertyName())
            || null !== $event->getOptions()
        ) {
            return;
        }

        $result      = [];
        $model       = $event->getModel();
        $metaModel   = $this->filterFactory->createCollection($model->getProperty('fid'))->getMetaModel();
        $typeFactory = $this->filterFactory->getTypeFactory($model->getProperty('type'));

        $typeFilter = null;
        if ($typeFactory) {
            $typeFilter = $typeFactory->getKnownAttributeTypes();
        }

        foreach ($metaModel->getAttributes() as $attribute) {
            if (null !== $typeFilter && (!\in_array((string) $attribute->get('type'), $typeFilter))) {
                continue;
            }

            $strSelectVal          = $metaModel->getTableName() . '_' . $attribute->getColName();
            $result[$strSelectVal] = $this->labelFormatter->formatLabel($attribute);
        }

        $event->setOptions($result);
    }

    /**
     * Translates an attribute id to a generated alias {@see getAttributeNames()}.
     *
     * @param DecodePropertyValueForWidgetEvent $event The event.
     *
     * @return void
     */
    public function decodeValue(DecodePropertyValueForWidgetEvent $event): void
    {
        $dataDefinition = $event->getEnvironment()->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        if (
            ('tl_metamodel_filtersetting' !== $dataDefinition->getName())
            || ('attr_id' !== $event->getProperty())
        ) {
            return;
        }

        $model     = $event->getModel();
        $metaModel = $this->filterFactory->createCollection($model->getProperty('fid'))->getMetaModel();
        $value     = $event->getValue();

        if (!$value) {
            return;
        }

        $attribute = $metaModel->getAttributeById((int) $value);
        if ($attribute) {
            $event->setValue($metaModel->getTableName() . '_' . $attribute->getColName());
        }
    }

    /**
     * Translates an generated alias {@see getAttributeNames()} to the corresponding attribute id.
     *
     * @param EncodePropertyValueFromWidgetEvent $event The event.
     *
     * @return void
     */
    public function encodeValue(EncodePropertyValueFromWidgetEvent $event): void
    {
        $dataDefinition = $event->getEnvironment()->getDataDefinition();
        assert($dataDefinition instanceof ContainerInterface);

        if (
            ('tl_metamodel_filtersetting' !== $dataDefinition->getName())
            || ('attr_id' !== $event->getProperty())
        ) {
            return;
        }

        $model     = $event->getModel();
        $metaModel = $this->filterFactory->createCollection($model->getProperty('fid'))->getMetaModel();
        $value     = $event->getValue();

        if (!$value) {
            return;
        }

        $value = \substr($value, \strlen($metaModel->getTableName() . '_'));

        $attribute = $metaModel->getAttribute($value);
        assert($attribute instanceof IAttribute);
        $event->setValue($attribute->get('id'));
    }
}
