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
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\DcaSetting;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyValueCondition;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use Doctrine\DBAL\Connection;
use MetaModels\CoreBundle\EventListener\DcGeneral\Table\AbstractPaletteRestrictionListener;
use MetaModels\DcGeneral\DataDefinition\Palette\Condition\Property\AttributeByIdIsOfType;

/**
 * This builds the palette conditions as specified by the configuration.
 */
class PaletteRestrictionListener extends AbstractPaletteRestrictionListener
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
     * Build the data definition palettes.
     *
     * @param BuildDataDefinitionEvent $event The event.
     *
     * @return void
     */
    public function handle(BuildDataDefinitionEvent $event)
    {
        if (($event->getContainer()->getName() !== 'tl_metamodel_dcasetting')) {
            return;
        }

        $palettes = $event->getContainer()->getPalettesDefinition();
        $legend   = null;

        $subSelectPalettes = $this->getSubSelectPalettes();
        foreach ($palettes->getPalettes() as $palette) {
            $condition = new PropertyValueCondition('dcatype', 'attribute');
            $legend    = $this->getLegend('functions', $palette, $legend);
            $property  = $this->getProperty('readonly', $legend);
            $this->addCondition($property, $condition);
            $legend   = $this->getLegend('title', $palette, $legend);
            $property = $this->getProperty('attr_id', $legend);
            $this->addCondition($property, $condition);

            $condition = new PropertyValueCondition('dcatype', 'legend');
            $legend    = $this->getLegend('title', $palette);
            $property  = $this->getProperty('legendtitle', $legend);

            $this->addCondition($property, $condition);
            $property = $this->getProperty('legendhide', $legend);
            $this->addCondition($property, $condition);

            foreach ($subSelectPalettes as $typeName => $paletteInfo) {
                foreach ($paletteInfo as $legendName => $properties) {
                    foreach ($properties as $propertyName) {
                        $condition = new AttributeByIdIsOfType($typeName, $this->connection, 'attr_id');
                        $legend    = $this->getLegend($legendName, $palette);
                        $property  = $this->getProperty($propertyName, $legend);
                        $this->addCondition($property, $condition);
                    }
                }
            }
        }
    }

    /**
     * Obtain the sub select palette settings.
     *
     * @return array
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function getSubSelectPalettes()
    {
        if (!isset($GLOBALS['TL_DCA']['tl_metamodel_dcasetting']['metasubselectpalettes']['attr_id'])) {
            return [];
        }

        return (array) $GLOBALS['TL_DCA']['tl_metamodel_dcasetting']['metasubselectpalettes']['attr_id'];
    }
}
