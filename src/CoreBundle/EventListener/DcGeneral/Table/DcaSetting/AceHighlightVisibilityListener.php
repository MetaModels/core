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

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\DcaSetting;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBag;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyCallbackCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionChain;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyValueCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;

/**
 * Replaces the exact-match condition for the "highlight" property's "rte" selector with a prefix match,
 * so that "highlight" is visible for all ACE editor template variants (e.g. "ace", "ace_mm").
 */
class AceHighlightVisibilityListener
{
    /**
     * Replace the PropertyValueCondition('rte', 'ace') on the "highlight" property with a callback
     * condition that matches any rte value starting with 'ace'.
     *
     * @param BuildDataDefinitionEvent $event The event.
     *
     * @return void
     */
    public function handle(BuildDataDefinitionEvent $event): void
    {
        if ($event->getContainer()->getName() !== 'tl_metamodel_dcasetting') {
            return;
        }

        if (!$event->getContainer()->hasPalettesDefinition()) {
            return;
        }

        $palettesDefinition = $event->getContainer()->getPalettesDefinition();

        foreach ($palettesDefinition->getPalettes() as $palette) {
            foreach ($palette->getLegends() as $legend) {
                foreach ($legend->getProperties() as $property) {
                    if ($property->getName() !== 'highlight') {
                        continue;
                    }

                    $this->replaceRteValueCondition($property);
                }
            }
        }
    }

    /**
     * Replace PropertyValueCondition('rte', *) in the property's AND chain with a prefix callback.
     *
     * @param PropertyInterface $property The palette property.
     *
     * @return void
     */
    private function replaceRteValueCondition(PropertyInterface $property): void
    {
        $condition = $property->getVisibleCondition();
        if (!($condition instanceof PropertyConditionChain)) {
            return;
        }

        foreach ($condition->getConditions() as $subCondition) {
            if (
                ($subCondition instanceof PropertyValueCondition)
                && ($subCondition->getPropertyName() === 'rte')
            ) {
                $condition->removeCondition($subCondition);
                $condition->addCondition(
                    new PropertyCallbackCondition(
                        static function (?ModelInterface $model, ?PropertyValueBag $input): bool {
                            if (null !== $input && $input->hasPropertyValue('rte')) {
                                $value = $input->getPropertyValue('rte');
                            } elseif (null !== $model) {
                                $value = $model->getProperty('rte');
                            } else {
                                return false;
                            }

                            return \is_string($value) && \str_starts_with($value, 'ace');
                        }
                    )
                );

                return;
            }
        }
    }
}
