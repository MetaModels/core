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

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\GetPropertyOptionsEvent;
use ContaoCommunityAlliance\DcGeneral\Event\AbstractEnvironmentAwareEvent;
use MetaModels\Attribute\IAttributeFactory;
use MetaModels\IFactory;
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

        $translator   = $event->getEnvironment()->getTranslator();
        $objMetaModel = $this->getMetaModelByModelPid($event->getModel());
        $flags        = IAttributeFactory::FLAG_ALL_UNTRANSLATED;

        if ($objMetaModel->isTranslated()) {
            $flags |= IAttributeFactory::FLAG_INCLUDE_TRANSLATED;
        }

        $options = [];

        foreach ($this->attributeFactory->getTypeNames($flags) as $attributeType) {
            // Might be translated+complex or translated+simple.
            if ($this->attributeFactory->getTypeFactory($attributeType)->isTranslatedType()
                && !$objMetaModel->isTranslated()
            ) {
                continue;
            }

            $options[$attributeType] = $translator->translate(
                'typeOptions.' . $attributeType,
                'tl_metamodel_attribute'
            );
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
        if ($request->request->get('act', null) === 'select' && !$event->getModel()->getId()) {
            return false;
        }

        return true;
    }
}
