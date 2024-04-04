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
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\DcaSortGroup;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\ConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionChain;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PropertyInterface;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use Doctrine\DBAL\Connection;
use MetaModels\DcGeneral\DataDefinition\Palette\Condition\Property\InputScreenRenderModeIs;

/**
 * This hides the rendergrouptype for tree views.
 */
class VisibilityConditionBuildingListener
{
    /**
     * Database connection.
     *
     * @var Connection
     */
    private $connection;

    /**
     * PaletteRestrictionListener constructor.
     *
     * @param Connection $connection Database connection.
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Set the visibility condition for the widget.
     *
     * Manipulate the data definition for the property "rendergrouptype" in table "tl_metamodel_dca_sortgroup".
     *
     * @param BuildDataDefinitionEvent $event The event.
     *
     * @return void
     */
    public function handle(BuildDataDefinitionEvent $event)
    {
        if ('tl_metamodel_dca_sortgroup' !== $event->getContainer()->getName()) {
            return;
        }

        foreach ($event->getContainer()->getPalettesDefinition()->getPalettes() as $palette) {
            foreach ($palette->getProperties() as $property) {
                if ($property->getName() != 'rendergrouptype') {
                    continue;
                }

                $this->addCondition(
                    $property,
                    new PropertyConditionChain(
                        array(
                            new InputScreenRenderModeIs('flat', $this->connection),
                            new InputScreenRenderModeIs('parented', $this->connection),
                        ),
                        PropertyConditionChain::OR_CONJUNCTION
                    )
                );
            }
        }
    }

    /**
     * Add a visible condition.
     *
     * @param PropertyInterface  $property  The property.
     *
     * @param ConditionInterface $condition The condition to add.
     *
     * @return void
     */
    private function addCondition(PropertyInterface $property, ConditionInterface $condition)
    {
        $chain = $property->getVisibleCondition();
        if (
            !($chain
            && ($chain instanceof PropertyConditionChain)
            && $chain->getConjunction() == PropertyConditionChain::AND_CONJUNCTION
            )
        ) {
            if ($property->getVisibleCondition()) {
                $previous = array($property->getVisibleCondition());
            } else {
                $previous = array();
            }

            $chain = new PropertyConditionChain(
                $previous,
                PropertyConditionChain::AND_CONJUNCTION
            );

            $property->setVisibleCondition($chain);
        }

        $chain->addCondition($condition);
    }
}
