<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2019 The MetaModels team.
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
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\FilterSetting;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
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
    private $filterFactory;

    /**
     * Create a new instance.
     *
     * @param IFilterSettingFactory $filterFactory The filter setting factory.
     */
    public function __construct(IFilterSettingFactory $filterFactory)
    {
        $this->filterFactory = $filterFactory;
    }

    /**
     * Provide options for default selection.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     */
    public function getOptions(GetPropertyOptionsEvent $event)
    {
        if (('tl_metamodel_filtersetting' !== $event->getEnvironment()->getDataDefinition()->getName())
            || ('attr_id' !== $event->getPropertyName())) {
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
            $typeName = $attribute->get('type');

            if ($typeFilter && (!in_array($typeName, $typeFilter))) {
                continue;
            }

            $strSelectVal          = $metaModel->getTableName() .'_' . $attribute->getColName();
            $result[$strSelectVal] = $attribute->getName() . ' [' . $typeName . ']';
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
    public function decodeValue(DecodePropertyValueForWidgetEvent $event)
    {
        if (('tl_metamodel_filtersetting' !== $event->getEnvironment()->getDataDefinition()->getName())
            || ('attr_id' !== $event->getProperty())) {
            return;
        }

        $model     = $event->getModel();
        $metaModel = $this->filterFactory->createCollection($model->getProperty('fid'))->getMetaModel();
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
     * Translates an generated alias {@see getAttributeNames()} to the corresponding attribute id.
     *
     * @param EncodePropertyValueFromWidgetEvent $event The event.
     *
     * @return void
     */
    public function encodeValue(EncodePropertyValueFromWidgetEvent $event)
    {
        if (('tl_metamodel_filtersetting' !== $event->getEnvironment()->getDataDefinition()->getName())
            || ('attr_id' !== $event->getProperty())) {
            return;
        }

        $model     = $event->getModel();
        $metaModel = $this->filterFactory->createCollection($model->getProperty('fid'))->getMetaModel();
        $value     = $event->getValue();

        if (!($metaModel && $value)) {
            return;
        }

        $value = substr($value, strlen($metaModel->getTableName() . '_'));

        $attribute = $metaModel->getAttribute($value);

        if ($attribute) {
            $event->setValue($attribute->get('id'));
        }
    }
}
