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

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\DcaSetting;

use Contao\StringUtil;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\BuildWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\Properties\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractEnvironmentAwareEvent;
use MetaModels\Dca\Helper;

/**
 * This handles the serialization and deserialization as well as the building of the title widget.
 */
class LegendTitleListener extends AbstractListener
{
    /**
     * Decode the title value.
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

        $metaModel = $this->getMetaModelFromModel($event->getModel());

        $values = Helper::decodeLangArray($event->getValue(), $metaModel);

        $event->setValue(unserialize($values));
    }

    /**
     * Encode the title value.
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

        $metaModel = $this->getMetaModelFromModel($event->getModel());

        $values = Helper::encodeLangArray($event->getValue(), $metaModel);

        $event->setValue($values);
    }

    /**
     * Generate the widget.
     *
     * @param BuildWidgetEvent $event The event.
     *
     * @return void
     */
    public function buildWidget(BuildWidgetEvent $event)
    {
        if (!$this->wantToHandle($event)) {
            return;
        }

        $metaModel = $this->getMetaModelFromModel($event->getModel());

        Helper::prepareLanguageAwareWidget(
            $event->getEnvironment(),
            $event->getProperty(),
            $metaModel,
            $event->getEnvironment()->getTranslator()->translate('name_langcode', 'tl_metamodel_dcasetting'),
            $event->getEnvironment()->getTranslator()->translate('name_value', 'tl_metamodel_dcasetting'),
            false,
            StringUtil::deserialize($event->getModel()->getProperty('legendtitle'), true)
        );
    }

    /**
     * {@inheritDoc}
     */
    protected function wantToHandle(AbstractEnvironmentAwareEvent $event)
    {
        if (!parent::wantToHandle($event)) {
            return false;
        }
        if (method_exists($event, 'getPropertyName') && ('legendtitle' !== $event->getPropertyName())) {
            return false;
        }
        if (method_exists($event, 'getProperty')) {
            $property = $event->getProperty();
            if ($property instanceof PropertyInterface) {
                $property = $property->getName();
            }
            if ('legendtitle' !== $property) {
                return false;
            }
        }

        return true;
    }
}
