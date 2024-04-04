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

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\Attribute;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractEnvironmentAwareEvent;
use ContaoCommunityAlliance\Translator\TranslatorInterface;
use MetaModels\Attribute\IAttributeFactory;
use MetaModels\Attribute\IAttributeTypeFactory;
use MetaModels\IFactory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * This class provides the attribute type names.
 */
class GetAttributeTypeListener extends BaseListener
{
    /**
     * The request stack.
     *
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * Create a new instance.
     *
     * @param RequestStack             $requestStack      The request stack.
     * @param RequestScopeDeterminator $scopeDeterminator The scope determinator.
     * @param IAttributeFactory        $attributeFactory  The attribute factory.
     * @param IFactory                 $factory           The factory.
     */
    public function __construct(
        RequestStack $requestStack,
        RequestScopeDeterminator $scopeDeterminator,
        IAttributeFactory $attributeFactory,
        IFactory $factory
    ) {
        parent::__construct($scopeDeterminator, $attributeFactory, $factory);
        $this->requestStack = $requestStack;
    }

    /**
     * Provide options for attribute type selection.
     *
     * @param GetPropertyOptionsEvent $event The event.
     *
     * @return void
     */
    public function getOptions(GetPropertyOptionsEvent $event)
    {
        if (!$this->wantToHandle($event)) {
            return;
        }

        $translator = $event->getEnvironment()->getTranslator();
        assert($translator instanceof TranslatorInterface);

        $objMetaModel = $this->getMetaModelByModelPid($event->getModel());
        $flags        = IAttributeFactory::FLAG_ALL_UNTRANSLATED;

        /** @psalm-suppress DeprecatedMethod */
        if ($objMetaModel->isTranslated()) {
            $flags |= IAttributeFactory::FLAG_INCLUDE_TRANSLATED;
        }

        $options      = [];
        $optionsTrans = [];
        foreach ($this->attributeFactory->getTypeNames($flags) as $attributeType) {
            // Differentiate translated and simple.
            $typeFactory = $this->attributeFactory->getTypeFactory($attributeType);
            assert($typeFactory instanceof IAttributeTypeFactory);
            if ($typeFactory->isTranslatedType()) {
                $optionsTrans[$attributeType] = $translator->translate(
                    'typeOptions.' . $attributeType,
                    'tl_metamodel_attribute'
                );
            } else {
                $options[$attributeType] = $translator->translate(
                    'typeOptions.' . $attributeType,
                    'tl_metamodel_attribute'
                );
            }
        }
        \asort($options);

        // Add translated attributes.
        /** @psalm-suppress DeprecatedMethod */
        if ($objMetaModel->isTranslated()) {
            \asort($optionsTrans);
            $options = \array_merge($options, $optionsTrans);
        }

        $event->setOptions($options);
    }

    /**
     * Test if we want to handle the event.
     *
     * @param AbstractEnvironmentAwareEvent $event The event to evaluate.
     *
     * @return bool
     */
    protected function wantToHandle(AbstractEnvironmentAwareEvent $event)
    {
        /** @var GetPropertyOptionsEvent $event */
        if (!parent::wantToHandle($event)) {
            return false;
        }
        if ($event->getPropertyName() !== 'type') {
            return false;
        }

        $request = $this->requestStack->getCurrentRequest();
        assert($request instanceof Request);

        if ($request->request->get('act', null) === 'select' && !$event->getModel()->getId()) {
            return false;
        }

        return true;
    }
}
