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
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Sven Baumann <baumann.sv@gmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @copyright  2012-2019 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\DcGeneral\DataDefinition\Palette\Condition\Property;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBag;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\LegendInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PropertyInterface;

/**
 * This condition matches as soon as all of the following apply:
 * 1. the MetaModel supports variants.
 * 2. the current item is not a variant base.
 * 3. the attribute is not an invariant attribute.
 */
class IsVariantAttribute implements PropertyConditionInterface
{
    /**
     * {@inheritdoc}
     */
    public function match(
        ModelInterface $model = null,
        PropertyValueBag $input = null,
        PropertyInterface $property = null,
        LegendInterface $legend = null
    ) {
        if ($property === null || $model === null) {
            return false;
        }

        /** @var $model \MetaModels\DcGeneral\Data\Model */

        $nativeItem = $model->getItem();
        $metaModel  = $nativeItem->getMetaModel();

        if ($metaModel->hasVariants() && !$nativeItem->isVariantBase()) {
            return !in_array($property->getName(), array_keys($metaModel->getInVariantAttributes()));
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function __clone()
    {
    }
}
