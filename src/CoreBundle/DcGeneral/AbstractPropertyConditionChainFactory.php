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

namespace MetaModels\CoreBundle\DcGeneral;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionInterface;
use MetaModels\IMetaModel;

/**
 * This factory creates AND property conditions.
 */
abstract class AbstractPropertyConditionChainFactory implements NestablePropertyConditionFactoryInterface
{
    /**
     * The factory for building child conditions.
     *
     * @var PropertyConditionFactory
     */
    private $factory;

    /**
     * Create a new instance.
     *
     * @param PropertyConditionFactory $factory The condition factory to use.
     */
    public function __construct(PropertyConditionFactory $factory)
    {
        $this->factory = $factory;
    }

    /**
     * {@inheritDoc}
     */
    public function buildCondition(array $configuration, IMetaModel $metaModel)
    {
        $children = [];
        if (!empty($configuration['children'])) {
            foreach ($configuration['children'] as $child) {
                $children[] = $this->convertCondition($child, $metaModel);
            }
        }

        return $this->createCondition($children);
    }

    /**
     * Create a condition with the passed children.
     *
     * @param PropertyConditionInterface[] $children The child conditions.
     *
     * @return PropertyConditionInterface
     */
    abstract protected function createCondition(array $children);

    /**
     * {@inheritDoc}
     */
    public function maxChildren()
    {
        return -1;
    }

    /**
     * Perform conversion of a sub condition.
     *
     * @param array      $configuration The condition to convert.
     * @param IMetaModel $metaModel     The MetaModel instance.
     *
     * @return PropertyConditionInterface
     *
     * @throws \RuntimeException Throws condition of type could not be transformed to an instance.
     */
    private function convertCondition($configuration, IMetaModel $metaModel)
    {
        return $this->factory->createCondition($configuration, $metaModel);
    }
}
