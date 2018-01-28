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

use ContaoCommunityAlliance\DcGeneral\Event\PreDeleteModelEvent;

/**
 * This class takes care of deleting an attribute.
 */
class AttributeDeletedListener extends BaseListener
{
    /**
     * Handle the update of an attribute and all attached data.
     *
     * @param PreDeleteModelEvent $event The event.
     *
     * @return void
     */
    public function handle(PreDeleteModelEvent $event)
    {
        if (!$this->wantToHandle($event)) {
            return;
        }

        if ($attribute = $this->createAttributeInstance($event->getModel())) {
            $this->deleteConditionSettings($event);
            $attribute->destroyAUX();
        }
    }

    /**
     * Delete joint condition setting with attribute.
     *
     * @param PreDeleteModelEvent $event The event.
     *
     * @return void
     */
    protected function deleteConditionSettings(PreDeleteModelEvent $event)
    {
        $environment  = $event->getEnvironment();
        $model        = $event->getModel();
        $dataProvider = $environment->getDataProvider('tl_metamodel_dcasetting_condition');

        $conditions = $dataProvider->fetchAll(
            $dataProvider->getEmptyConfig()->setFilter(
                [['operation' => '=', 'property' => 'attr_id', 'value' => $model->getId()]]
            )
        );

        if ($conditions->count() < 1) {
            return;
        }

        $conditionsGeneral            = new \DC_General($dataProvider->getEmptyModel()->getProviderName());
        $conditionsEnvironment        = $conditionsGeneral->getEnvironment();
        $conditionsDataDefinition     = $conditionsEnvironment->getDataDefinition();
        $conditionsPalettesDefinition = $conditionsDataDefinition->getPalettesDefinition();

        /** @var \Iterator $conditionsIterator */
        $conditionsIterator = $conditions->getIterator();
        while ($currentCondition = $conditionsIterator->current()) {
            $conditionPalette    = $conditionsPalettesDefinition->getPaletteByName(
                $currentCondition->getProperty('type')
            );
            $conditionProperties = $conditionPalette->getVisibleProperties(
                $currentCondition
            );

            foreach ($conditionProperties as $conditionProperty) {
                if ($conditionProperty->getName() !== 'attr_id') {
                    continue;
                }

                $dataProvider->delete($currentCondition);
            }

            $conditionsIterator->next();
        }
    }
}
