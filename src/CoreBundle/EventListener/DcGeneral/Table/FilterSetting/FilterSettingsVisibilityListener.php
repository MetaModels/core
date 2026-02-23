<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2026 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2026 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\FilterSetting;

use ContaoCommunityAlliance\DcGeneral\Contao\RequestScopeDeterminator;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ConditionChainInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\NotCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionChain;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use MetaModels\Attribute\IAliasConverter;
use MetaModels\DcGeneral\DataDefinition\Palette\Condition\Property\FilterSettingAttributeInstanceOfCondition;
use MetaModels\Filter\Setting\IFilterSettingFactory;

final readonly class FilterSettingsVisibilityListener
{
    public function __construct(
        private RequestScopeDeterminator $scopeMatcher,
        private IFilterSettingFactory $filterSettingFactory,
    ) {
    }

    public function __invoke(BuildDataDefinitionEvent $event): void
    {
        if (!$this->scopeMatcher->currentScopeIsBackend()) {
            return;
        }

        $container = $event->getContainer();

        if ('tl_metamodel_filtersetting' !== $container->getName()) {
            return;
        }

        $palettes = $container->getPalettesDefinition();
        foreach ($palettes->getPalettes() as $palette) {
            foreach ($palette->getProperties() as $property) {
                if ('label_attr_id' !== $property->getName()) {
                    continue;
                }
                $condition = new FilterSettingAttributeInstanceOfCondition(
                    $this->filterSettingFactory,
                    IAliasConverter::class
                );

                $this->addCondition($property, new NotCondition($condition));
            }
        }
    }

    private function addCondition(PropertyInterface $property, ConditionInterface $condition): void
    {
        $currentCondition = $property->getVisibleCondition();
        if (
            (!($currentCondition instanceof ConditionChainInterface))
            || ($currentCondition->getConjunction() != ConditionChainInterface::OR_CONJUNCTION)
        ) {
            if ($currentCondition === null) {
                $currentCondition = new PropertyConditionChain([$condition]);
            } else {
                $currentCondition = new PropertyConditionChain([$currentCondition, $condition]);
            }
            $currentCondition->setConjunction(ConditionChainInterface::OR_CONJUNCTION);
            $property->setVisibleCondition($currentCondition);
        } else {
            $currentCondition->addCondition($condition);
        }
    }
}
