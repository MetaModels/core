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

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\BooleanCondition;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\NotCondition;
use MetaModels\IMetaModel;

/**
 * This builds not conditions.
 */
class NotConditionFactory implements NestablePropertyConditionFactoryInterface
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
     *
     * @throws \InvalidArgumentException When there are more than one children.
     */
    public function buildCondition(array $configuration, IMetaModel $metaModel)
    {
        // No children, then return "true".
        if (empty($configuration['children'])) {
            return new NotCondition(new BooleanCondition(false));
        }
        if (1 < $count = count($configuration['children'])) {
            throw new \InvalidArgumentException('NOT conditions may only contain one child, ' . $count . ' given.');
        }

        return new NotCondition($this->factory->createCondition($configuration['children'][0], $metaModel));
    }

    /**
     * Return 1.
     *
     * @return int
     */
    public function maxChildren()
    {
        return 1;
    }
}
