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
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\EventListener\DcGeneral\Table\DcaCombine;

use ContaoCommunityAlliance\DcGeneral\Contao\View\Contao2BackendView\Event\EncodePropertyValueFromWidgetEvent;
use ContaoCommunityAlliance\DcGeneral\Data\DataProviderInterface;
use ContaoCommunityAlliance\DcGeneral\Data\ModelManipulator;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Definition\PropertiesDefinitionInterface;

/**
 * The class fix the type safety values.
 */
class FixTypeSafetyListener
{
    /**
     * Handle event to update the empty type safety values for DCA combinations.
     *
     * @param EncodePropertyValueFromWidgetEvent $event The event.
     *
     * @return void
     */
    public function handle(EncodePropertyValueFromWidgetEvent $event)
    {
        if (('tl_metamodel_dca_combine' !== $event->getEnvironment()->getDataDefinition()->getName())
            || ('rows' !== $event->getProperty())) {
            return;
        }

        $environment  = $event->getEnvironment();
        $dataProvider = $environment->getDataProvider();
        $properties   = $environment->getDataDefinition()->getPropertiesDefinition();

        $values = (array) $event->getValue();
        foreach ($values as $row => $current) {
            $values[$row] = $this->updateValues($current, $properties, $dataProvider);
        }

        $event->setValue($values);
    }

    /**
     * Update type safety values.
     *
     * @param array                         $values       The values for update.
     * @param PropertiesDefinitionInterface $properties   The properties.
     * @param DataProviderInterface         $dataProvider The data provider.
     *
     * @return array
     */
    private function updateValues(
        array &$values,
        PropertiesDefinitionInterface $properties,
        DataProviderInterface $dataProvider
    ) {
        foreach ($values as $propertyName => $propertyValue) {
            if (($dataProvider->getIdProperty() === $propertyName)
                || ($dataProvider->getGroupColumnProperty() === $propertyName)
                || ($dataProvider->getSortingColumnProperty() === $propertyName)
                || ($dataProvider->getTimeStampProperty() === $propertyName)
                || !$properties->hasProperty($propertyName)
            ) {
                continue;
            }

            $values[$propertyName] =
                ModelManipulator::sanitizeValue($properties->getProperty($propertyName), $propertyValue);
        }

        return $values;
    }
}
