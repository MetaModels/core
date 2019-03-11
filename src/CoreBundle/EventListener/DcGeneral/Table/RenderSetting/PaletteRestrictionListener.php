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

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\RenderSetting;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\ConditionChainInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Palette\PaletteConditionChain;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PaletteInterface;
use ContaoCommunityAlliance\DcGeneral\Factory\Event\BuildDataDefinitionEvent;
use Doctrine\DBAL\Connection;
use MetaModels\CoreBundle\EventListener\DcGeneral\Table\AbstractPaletteRestrictionListener;
use MetaModels\DcGeneral\DataDefinition\Palette\Condition\Palette\RenderSettingAttributeIs as PaletteCondition;
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
     *
     * @SuppressWarnings(PHPMD.Superglobals)
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    public function handle(BuildDataDefinitionEvent $event)
    {
        if (($event->getContainer()->getName() !== 'tl_metamodel_rendersetting')) {
            return;
        }

        $palettes = $event->getContainer()->getPalettesDefinition();

        foreach ($palettes->getPalettes() as $palette) {
            if ($palette->getName() !== 'default') {
                $paletteCondition = $palette->getCondition();
                if (!($paletteCondition instanceof ConditionChainInterface)
                    || ($paletteCondition->getConjunction() !== PaletteConditionChain::OR_CONJUNCTION)
                ) {
                    $paletteCondition = new PaletteConditionChain(
                        $paletteCondition ? array($paletteCondition) : array(),
                        PaletteConditionChain::OR_CONJUNCTION
                    );
                    $palette->setCondition($paletteCondition);
                }
                $paletteCondition->addCondition(new PaletteCondition($palette->getName(), 1, $this->connection));
            }

            $this->buildMetaPaletteConditions(
                $palette,
                (array) $GLOBALS['TL_DCA']['tl_metamodel_rendersetting']['metapalettes']
            );
        }
    }

    /**
     * Apply conditions for meta palettes of the certain render setting types.
     *
     * @param PaletteInterface $palette      The palette.
     * @param array            $metaPalettes The meta palette information.
     *
     * @return void
     */
    private function buildMetaPaletteConditions($palette, $metaPalettes)
    {
        foreach ($metaPalettes as $typeName => $paletteInfo) {
            if ('default' === $typeName) {
                continue;
            }

            if (preg_match('#^(\w+) extends (\w+)$#', $typeName, $matches)) {
                $typeName = $matches[1];
            }

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
