<?php

/**
 * This file is part of MetaModels/core.
 *
 * (c) 2012-2022 The MetaModels team.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * This project is provided in good faith and hope to be usable by anyone.
 *
 * @package    MetaModels/core
 * @author     Stefan Heimes <stefan_heimes@hotmail.com>
 * @author     Ingolf Steinhardt <info@e-spin.de>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @copyright  2012-2022 The MetaModels team.
 * @license    https://github.com/MetaModels/core/blob/master/LICENSE LGPL-3.0-or-later
 * @filesource
 */

namespace MetaModels\DcGeneral\DataDefinition\Palette\Condition\Property;

use ContaoCommunityAlliance\DcGeneral\Data\ModelInterface;
use ContaoCommunityAlliance\DcGeneral\Data\PropertyValueBag;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyConditionInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\LegendInterface;
use ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\PropertyInterface;
use MetaModels\Attribute\IAliasConverter;
use MetaModels\DcGeneral\Data\Model;
use MetaModels\IMetaModel;
use MetaModels\ITranslatedMetaModel;

/**
 * Condition checking that the value of a property is the same as a passed value.
 *
 * Okay her comes the twist. We need this class, this is the only way to get the metamodels context we needed.
 * If we have it we can call the Alias to ID convert function, since all the options of a select or
 * other attributes are alias values. But the condition to check if the field is visibility or not use the ID.
 * So we must translate the alias back to the id and check.
 */
class PropertyValueCondition implements PropertyConditionInterface
{
    /**
     * The property name.
     *
     * @var string
     */
    private string $propertyName;

    /**
     * The expected property value.
     *
     * @var mixed
     */
    private $propertyValue;

    /**
     * Use strict compare mode.
     *
     * @var bool
     */
    private bool $strict;

    /**
     * The metamodel of the current context.
     *
     * @var IMetaModel
     */
    private IMetaModel $metaModel;

    /**
     * Create a new instance.
     *
     * @param string $propertyName  The name of the property.
     * @param mixed  $propertyValue The value of the property to match.
     * @param bool   $strict        Flag if the comparison shall be strict (type safe).
     */
    public function __construct(string $propertyName, $propertyValue, bool $strict, IMetaModel $metaModel)
    {
        $this->propertyName  = $propertyName;
        $this->propertyValue = $propertyValue;
        $this->strict        = $strict;
        $this->metaModel     = $metaModel;
    }

    /**
     * Get the metamodel.
     *
     * @return IMetaModel
     */
    public function getMetaModel(): IMetaModel
    {
        return $this->metaModel;
    }

    /**
     * Retrieve the property name.
     *
     * @return string
     */
    public function getPropertyName(): string
    {
        return $this->propertyName;
    }

    /**
     * Retrieve the property value to match.
     *
     * @return mixed
     */
    public function getPropertyValue()
    {
        return $this->propertyValue;
    }

    /**
     * Retrieve the flag if the comparison shall be strict (type safe).
     *
     * @return boolean
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getStrict(): bool
    {
        return $this->strict;
    }

    /**
     * {@inheritdoc}
     */
    public function match(
        ModelInterface $model = null,
        PropertyValueBag $input = null,
        PropertyInterface $property = null,
        LegendInterface $legend = null
    ) {
        $attribute = $this->metaModel->getAttribute($this->propertyName);

        if ($this->metaModel instanceof ITranslatedMetaModel) {
            $currentLanguage = $this->metaModel->getLanguage();
        } else {
            /** @psalm-suppress DeprecatedMethod */
            $currentLanguage = $this->metaModel->getActiveLanguage();
        }

        if ($input && $input->hasPropertyValue($this->propertyName)) {
            $value = $input->getPropertyValue($this->propertyName);
        } elseif ($model instanceof Model) {
            $value = $model->getProperty($this->propertyName);
        } else {
            return false;
        }

        if ($value && $attribute instanceof IAliasConverter) {
            $value = ($attribute->getIdForAlias($value, $currentLanguage) ?? $value);
        }

        return $this->strict ? ($value === $this->propertyValue) : ($value == $this->propertyValue);
    }

    /**
     * {@inheritdoc}
     */
    public function __clone()
    {
    }
}
