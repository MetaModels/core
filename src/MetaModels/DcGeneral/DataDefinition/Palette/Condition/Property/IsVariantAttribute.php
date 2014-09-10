<?php
/**
 * The MetaModels extension allows the creation of multiple collections of custom items,
 * each with its own unique set of selectable attributes, with attribute extendability.
 * The Front-End modules allow you to build powerful listing and filtering of the
 * data in each collection.
 *
 * PHP version 5
 *
 * @package    MetaModels
 * @subpackage Core
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Tristan Lins <tristan.lins@bit3.de>
 * @copyright  The MetaModels team.
 * @license    LGPL.
 * @filesource
 */

namespace MetaModels\DcGeneral\DataDefinition\Palette\Condition\Property;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBag;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\LegendInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PropertyInterface;

/**
 * Condition for the default palette.
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
    )
    {
        /** @var $model \MetaModels\DcGeneral\Data\Model */

        $nativeItem = $model->getItem();
        $metaModel  = $nativeItem->getMetaModel();
        $attribute  = $metaModel->getAttribute($property->getName());

        $attribute->get('isvariant');

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
