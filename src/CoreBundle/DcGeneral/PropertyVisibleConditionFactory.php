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
 * @package    MetaModels/core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2018 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\CoreBundle\DcGeneral;

use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyVisibleCondition;
use MetaModels\IMetaModel;

/**
 * This builds property visible conditions.
 */
class PropertyVisibleConditionFactory extends AbstractAttributeConditionFactory
{
    /**
     * {@inheritDoc}
     */
    public function buildCondition(array $configuration, IMetaModel $metaModel)
    {
        return new PropertyVisibleCondition($this->attributeIdToName($metaModel, $configuration['attr_id']));
    }

    /**
     * {@inheritDoc}
     */
    public function supportsAttribute($attribute)
    {
        return true;
    }
}
