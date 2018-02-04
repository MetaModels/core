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
 * @author     Christopher Boelter <christopher@boelter.eu>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\Events;

use Contao\StringUtil;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\ConditionChainInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\BooleanCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\NotCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionChain;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyValueCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyVisibleCondition;
use MetaModels\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyContainAnyOfCondition;
use MetaModels\IMetaModel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * This class creates the default instances for property conditions when generating input screens.
 */
class DefaultPropertyConditionCreator
{
    /**
     * The event dispatcher.
     *
     * @var EventDispatcherInterface
     */
    private $dispatcher;

    /**
     * Create a new instance.
     *
     * @param EventDispatcherInterface $dispatcher The event dispatcher.
     */
    public function __construct(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;
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
        // Do not override existing conditions.
        if (null !== $event->getInstance()) {
            return;
        }

        $meta      = $event->getData();
        $metaModel = $event->getMetaModel();

        if (!$metaModel) {
            throw new \RuntimeException('Could not retrieve MetaModel from event.');
        }

        switch ($meta['type']) {
            case 'conditionor':
                $event->setInstance($this->buildOrCondition($event->getData(), $metaModel));
                return;
            case 'conditionand':
                $event->setInstance($this->buildAndCondition($event->getData(), $metaModel));
                return;
            case 'conditionpropertyvalueis':
                $event->setInstance($this->buildPropertyValueCondition($event->getData(), $metaModel));
                return;
            case 'conditionpropertycontainanyof':
                $event->setInstance($this->buildPropertyContainAnyOfCondition($event->getData(), $metaModel));
                return;
            case 'conditionpropertyvisible':
                $event->setInstance($this->buildPropertyVisibleCondition($event->getData(), $metaModel));
                return;
            case 'conditionnot':
                $event->setInstance($this->buildNotCondition($event->getData(), $metaModel));
                return;
            case 'conditionpropertynotpublished':
                $event->setInstance($this->buildBooleanCondition(false));
                return;
            default:
        }
    }

    /**
     * Build an OR condition.
     *
     * @param array      $condition The data.
     * @param IMetaModel $metaModel The MetaModel instance.
     *
     * @return PropertyConditionChain
     */
    private function buildOrCondition(array $condition, IMetaModel $metaModel)
    {
        $children = [];
        if (!empty($condition['children'])) {
            foreach ($condition['children'] as $child) {
                $children[] = $this->convertCondition($child, $metaModel);
            }
        }

        return new PropertyConditionChain($children, ConditionChainInterface::OR_CONJUNCTION);
    }

    /**
     * Build an AND condition.
     *
     * @param array      $condition The data.
     * @param IMetaModel $metaModel The MetaModel instance.
     *
     * @return PropertyConditionChain
     */
    private function buildAndCondition(array $condition, IMetaModel $metaModel)
    {
        $children = [];
        if (!empty($condition['children'])) {
            foreach ($condition['children'] as $child) {
                $children[] = $this->convertCondition($child, $metaModel);
            }
        }

        return new PropertyConditionChain($children, ConditionChainInterface::AND_CONJUNCTION);
    }

    /**
     * Extract the attribute instance from the MetaModel.
     *
     * @param IMetaModel $metaModel   The MetaModel instance.
     *
     * @param string     $attributeId The attribute id.
     *
     * @return string
     *
     * @throws \RuntimeException When the attribute could not be retrieved.
     */
    private function getAttributeName(IMetaModel $metaModel, $attributeId)
    {
        if (null === $attribute = $metaModel->getAttributeById($attributeId)) {
            throw new \RuntimeException(sprintf(
                'Could not retrieve attribute %s from MetaModel %s.',
                $attributeId,
                $metaModel->getTableName()
            ));
        }

        return $attribute->getColName();
    }

    /**
     * Create a property value condition.
     *
     * @param array      $condition The data.
     * @param IMetaModel $metaModel The MetaModel instance.
     *
     * @return PropertyValueCondition
     */
    private function buildPropertyValueCondition(array $condition, IMetaModel $metaModel)
    {
        return new PropertyValueCondition(
            $this->getAttributeName($metaModel, $condition['attr_id']),
            $condition['value']
        );
    }

    /**
     * Create a property contain any of value condition.
     *
     * @param array      $condition The data.
     * @param IMetaModel $metaModel The MetaModel instance.
     *
     * @return PropertyContainAnyOfCondition
     */
    private function buildPropertyContainAnyOfCondition(array $condition, IMetaModel $metaModel)
    {
        return new PropertyContainAnyOfCondition(
            $this->getAttributeName($metaModel, $condition['attr_id']),
            StringUtil::deserialize($condition['value'])
        );
    }

    /**
     * Create a property visible condition.
     *
     * @param array      $condition The data.
     * @param IMetaModel $metaModel The MetaModel instance.
     *
     * @return PropertyVisibleCondition
     */
    private function buildPropertyVisibleCondition(array $condition, IMetaModel $metaModel)
    {
        return new PropertyVisibleCondition($this->getAttributeName($metaModel, $condition['attr_id']));
    }

    /**
     * Create a not condition.
     *
     * @param array      $condition The data.
     * @param IMetaModel $metaModel The MetaModel instance.
     *
     * @return NotCondition
     */
    private function buildNotCondition(array $condition, IMetaModel $metaModel)
    {
        // No children, then return "true".
        if (empty($condition['children'])) {
            return new NotCondition(new BooleanCondition(false));
        }
        if (1 < $count = count($condition['children'])) {
            throw new \InvalidArgumentException('NOT conditions may only contain one child, ' . $count . ' given.');
        }

        return new NotCondition($this->convertCondition($condition['children'][0], $metaModel));
    }

    /**
     * Perform conversion of a sub condition.
     *
     * @param array      $condition The condition to convert.
     * @param IMetaModel $metaModel The MetaModel instance.
     *
     * @return PropertyConditionInterface
     */
    private function convertCondition($condition, IMetaModel $metaModel)
    {
        $event = new CreatePropertyConditionEvent($condition, $metaModel);
        $this->dispatcher->dispatch(CreatePropertyConditionEvent::NAME, $event);

        if (null === $result = $event->getInstance()) {
            throw new \RuntimeException(sprintf(
                'Condition of type %s could not be transformed to an instance.',
                $condition['type']
            ));
        }

        return $result;
    }

    /**
     * Create a boolean condition.
     *
     * @param bool $value Determine the state of the boolean condition.
     *
     * @return BooleanCondition
     */
    private function buildBooleanCondition($value)
    {
        return new BooleanCondition($value);
    }
}
