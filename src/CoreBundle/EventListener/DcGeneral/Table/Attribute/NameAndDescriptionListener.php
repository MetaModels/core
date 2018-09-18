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

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\Attribute;

use Contao\StringUtil;
use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\BuildWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\DecodePropertyValueForWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use MetaModels\Attribute\IAttributeFactory;
use MetaModels\Dca\Helper;
use MetaModels\IFactory;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * This class provides the attribute type names.
 */
class NameAndDescriptionListener extends BaseListener
{
    /**
     * The translator.
     *
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * Create a new instance.
     *
     * @param RequestScopeDeterminator $scopeDeterminator The scope determinator.
     * @param IAttributeFactory        $attributeFactory  The attribute factory.
     * @param IFactory                 $factory           The MetaModel factory.
     * @param TranslatorInterface      $translator        The translator.
     */
    public function __construct(
        RequestScopeDeterminator $scopeDeterminator,
        IAttributeFactory $attributeFactory,
        IFactory $factory,
        TranslatorInterface $translator
    ) {
        parent::__construct($scopeDeterminator, $attributeFactory, $factory);
        $this->translator = $translator;
    }

    /**
     * Decode the given value from a serialized language array into the real language array.
     *
     * @param DecodePropertyValueForWidgetEvent $event The event.
     *
     * @return void
     */
    public function decodeValue(DecodePropertyValueForWidgetEvent $event)
    {
        if (!($this->wantToHandle($event) && \in_array($event->getProperty(), ['name', 'description']))) {
            return;
        }

        $metaModel = $this->getMetaModelByModelPid($event->getModel());
        $values    = Helper::decodeLangArray($event->getValue(), $metaModel);

        $event->setValue(unserialize($values, false));
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
        if (!($this->wantToHandle($event) && \in_array($event->getProperty(), ['name', 'description']))) {
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
        if (!($this->wantToHandle($event) && \in_array($event->getProperty()->getName(), ['name', 'description']))) {
            return;
        }

        $metaModel = $this->getMetaModelByModelPid($event->getModel());

        Helper::prepareLanguageAwareWidget(
            $event->getEnvironment(),
            $event->getProperty(),
            $metaModel,
            $this->translator->trans('tl_metamodel_attribute.name_langcode', [], 'contao_tl_metamodel_attribute'),
            $this->translator->trans('tl_metamodel_attribute.name_value', [], 'contao_tl_metamodel_attribute'),
            false,
            StringUtil::deserialize($event->getModel()->getProperty($event->getProperty()->getName()), true)
        );
    }
}
