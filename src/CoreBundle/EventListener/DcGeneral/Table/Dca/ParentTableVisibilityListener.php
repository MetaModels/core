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

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\Dca;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionChain;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyValueCondition;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;

/**
 * This hides the parent table widget when the input screen is not in "ctable" mode.
 */
class ParentTableVisibilityListener
{
    /**
     * Set the visibility condition for the widget.
     *
     * @param BuildDataDefinitionEvent $event The event.
     *
     * @return void
     */
    public function handle(BuildDataDefinitionEvent $event)
    {
        foreach ($event->getContainer()->getPalettesDefinition()->getPalettes() as $palette) {
            foreach ($palette->getProperties() as $property) {
                if ($property->getName() != 'ptable') {
                    continue;
                }

                $chain = $property->getVisibleCondition();
                if (!($chain
                    && ($chain instanceof PropertyConditionChain)
                    && $chain->getConjunction() == PropertyConditionChain::AND_CONJUNCTION
                )) {
                    $chain = new PropertyConditionChain(
                        array($property->getVisibleCondition()),
                        PropertyConditionChain::AND_CONJUNCTION
                    );

                    $property->setVisibleCondition($chain);
                }

                $chain->addCondition(new PropertyValueCondition('rendertype', 'ctable'));
            }
        }
    }
}
