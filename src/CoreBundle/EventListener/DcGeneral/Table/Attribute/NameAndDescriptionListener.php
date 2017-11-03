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

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\Attribute;

use Contao\StringUtil;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\BuildWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use MetaModels\Dca\Helper;

/**
 * This class provides the attribute type names.
 */
class NameAndDescriptionListener extends BaseListener
{
    /**
     * Decode the given value from a serialized language array into the real language array.
     *
     * @param DecodePropertyValueForWidgetEvent $event The event.
     *
     * @return void
     */
    public function decodeValue(DecodePropertyValueForWidgetEvent $event)
    {
        if (!($this->wantToHandle($event) && in_array($event->getProperty(), ['name', 'description']))) {
            return;
        }

        $metaModel = $this->getMetaModelByModelPid($event->getModel());
        $values    = Helper::decodeLangArray($event->getValue(), $metaModel);

        $event->setValue(unserialize($values));
    }

    /**
     * Encode the given value from a real language array into a serialized language array.
     *
     * @param EncodePropertyValueFromWidgetEvent $event The event.
     *
     * @return void
     */
    public function encodeValue(EncodePropertyValueFromWidgetEvent $event)
    {
        if (!($this->wantToHandle($event) && in_array($event->getProperty(), ['name', 'description']))) {
            return;
        }
        $metaModel = $this->getMetaModelByModelPid($event->getModel());
        $values    = Helper::encodeLangArray($event->getValue(), $metaModel);

        $event->setValue($values);
    }

    /**
     * Build the widget for the MCW.
     *
     * @param BuildWidgetEvent $event The event.
     *
     * @return void
     */
    public function buildWidget(BuildWidgetEvent $event)
    {
        if (!($this->wantToHandle($event) && in_array($event->getProperty()->getName(), ['name', 'description']))) {
            return;
        }

        $metaModel = $this->getMetaModelByModelPid($event->getModel());

        // FIXME: legacy translator in use.
        Helper::prepareLanguageAwareWidget(
            $event->getEnvironment(),
            $event->getProperty(),
            $metaModel,
            $event->getEnvironment()->getTranslator()->translate('name_langcode', 'tl_metamodel_attribute'),
            $event->getEnvironment()->getTranslator()->translate('name_value', 'tl_metamodel_attribute'),
            false,
            StringUtil::deserialize($event->getModel()->getProperty($event->getProperty()->getName()), true)
        );
    }
}
