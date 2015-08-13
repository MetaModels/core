<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2015 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Christopher Boelter <christopher@boelter.eu>
 * @copyright  2012-2015 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0
 * @filesource
 */

namespace MetaModels\Events;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\ConditionChainInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\BooleanCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\NotCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionChain;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyValueCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyVisibleCondition;
use MetaModels\Attribute\IAttribute;
use MetaModels\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyContainAnyOfCondition;
use MetaModels\IMetaModel;

/**
 * This class creates the default instances for property conditions when generating input screens.
 */
class DefaultPropertyConditionCreator
{
    /**
     * Extract the attribute instance from the MetaModel.
     *
     * @param IMetaModel $metaModel   The MetaModel instance.
     *
     * @param string     $attributeId The attribute id.
     *
     * @return IAttribute
     *
     * @throws \RuntimeException When the attribute could not be retrieved.
     */
    private function getAttributeFromMetaModel(IMetaModel $metaModel, $attributeId)
    {
        $attribute = $metaModel->getAttributeById($attributeId);

        if (!$attribute) {
            throw new \RuntimeException(sprintf(
                'Could not retrieve attribute %s from MetaModel %s.',
                $attributeId,
                $metaModel->getTableName()
            ));
        }

        return $attribute;
    }

    /**
     * Create the property conditions.
     *
     * @param CreatePropertyConditionEvent $event The event.
     *
     * @return void
     *
     * @throws \RuntimeException When no MetaModel is attached to the event or any other important information could
     *                           not be retrieved.
     */
    public function handle(CreatePropertyConditionEvent $event)
    {
        $meta      = $event->getData();
        $metaModel = $event->getMetaModel();

        if (!$metaModel) {
            throw new \RuntimeException('Could not retrieve MetaModel from event.');
        }

        switch ($meta['type']) {
            case 'conditionor':
                $event->setInstance(new PropertyConditionChain(array(), ConditionChainInterface::OR_CONJUNCTION));
                break;
            case 'conditionand':
                $event->setInstance(new PropertyConditionChain(array(), ConditionChainInterface::AND_CONJUNCTION));
                break;
            case 'conditionpropertyvalueis':
                $attribute = $this->getAttributeFromMetaModel($metaModel, $meta['attr_id']);

                // FIXME: For checkboxes the meta value is wrong here as it will compare "" == "0".
                $event->setInstance(new PropertyValueCondition(
                    $attribute->getColName(),
                    $meta['value']
                ));
                break;
            case 'conditionpropertycontainanyof':
                $attribute = $this->getAttributeFromMetaModel($metaModel, $meta['attr_id']);

                $event->setInstance(new PropertyContainAnyOfCondition(
                    $attribute->getColName(),
                    deserialize($meta['value'])
                ));
                break;
            case 'conditionpropertyvisible':
                $attribute = $this->getAttributeFromMetaModel($metaModel, $meta['attr_id']);

                $event->setInstance(new PropertyVisibleCondition($attribute->getColName()));
                break;

            case 'conditionnot':
                $event->setInstance(new NotCondition(new BooleanCondition(false)));
                break;
            default:
        }
    }
}
