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
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\SearchablePages;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\NotCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionChain;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyValueCondition;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;

/**
 * This builds the palette conditions as specified by the configuration.
 */
class PaletteRestrictionListener
{
    /**
     * Build the data definition palettes.
     *
     * @param BuildDataDefinitionEvent $event The event.
     *
     * @return void
     */
    public function handle(BuildDataDefinitionEvent $event)
    {
        if (($event->getContainer()->getName() !== 'tl_metamodel_searchable_pages')) {
            return;
        }

        foreach ($event->getContainer()->getPalettesDefinition()->getPalettes() as $palette) {
            foreach ($palette->getProperties() as $property) {
                if ($property->getName() != 'filterparams') {
                    continue;
                }

                $chain = $property->getVisibleCondition();
                if (!($chain
                    && ($chain instanceof PropertyConditionChain)
                    && $chain->getConjunction() == PropertyConditionChain::AND_CONJUNCTION
                )
                ) {
                    $chain = new PropertyConditionChain(
                        $chain ?: array(),
                        PropertyConditionChain::AND_CONJUNCTION
                    );

                    $property->setVisibleCondition($chain);
                }

                $chain->addCondition(new NotCondition(new PropertyValueCondition('filter', 0)));
                break;
            }
        }
    }
}
