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
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\ConditionChainInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionChain;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Legend;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\LegendInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PaletteInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Property;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PropertyInterface;

/**
 * This is an abstract base to build palette restrictions for properties.
 */
class AbstractPaletteRestrictionListener
{

    /**
     * Retrieve the legend with the given name.
     *
     * @param string           $name       Name of the legend.
     *
     * @param PaletteInterface $palette    The palette.
     *
     * @param LegendInterface  $prevLegend The previous legend.
     *
     * @return LegendInterface
     */
    protected function getLegend($name, $palette, $prevLegend = null)
    {
        if (!$palette->hasLegend($name)) {
            $palette->addLegend(new Legend($name), $prevLegend);
        }

        return $palette->getLegend($name);
    }

    /**
     * Retrieve a property from a legend or create a new one.
     *
     * @param string          $name   The legend name.
     *
     * @param LegendInterface $legend The legend instance.
     *
     * @return PropertyInterface
     */
    protected function getProperty($name, $legend)
    {
        foreach ($legend->getProperties() as $property) {
            if ($property->getName() === $name) {
                return $property;
            }
        }

        $property = new Property($name);
        $legend->addProperty($property);

        return $property;
    }

    /**
     * Add a condition to a property.
     *
     * @param PropertyInterface  $property  The property.
     *
     * @param ConditionInterface $condition The condition to add.
     *
     * @return void
     */
    protected function addCondition($property, $condition)
    {
        $currentCondition = $property->getVisibleCondition();
        if ((!($currentCondition instanceof ConditionChainInterface))
            || (ConditionChainInterface::OR_CONJUNCTION !== $currentCondition->getConjunction())
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
