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
 */
class PropertyValueCondition implements PropertyConditionInterface
{
    /**
     * The property name.
     *
     * @var string
     */
    protected $propertyName;

    /**
     * The expected property value.
     *
     * @var mixed
     */
    protected $propertyValue;

    /**
     * Use strict compare mode.
     *
     * @var bool
     */
    protected $strict;

    /**
     * The metamodels of the current context.
     *
     * @var IMetaModel
     */
    protected $metaModels;

    /**
     * Create a new instance.
     *
     * @param string $propertyName  The name of the property.
     * @param mixed  $propertyValue The value of the property to match.
     * @param bool   $strict        Flag if the comparison shall be strict (type safe).
     */
    public function __construct($propertyName = '', $propertyValue = null, $strict = false)
    {
        $this->propertyName  = (string) $propertyName;
        $this->propertyValue = $propertyValue;
        $this->strict        = (bool) $strict;
    }

    /**
     * Get the metamodels.
     *
     * @return IMetaModel
     */
    public function getMetaModels(): IMetaModel
    {
        return $this->metaModels;
    }

    /**
     * Set the metamodels.
     *
     * @param IMetaModel $metaModels
     */
    public function setMetaModels(IMetaModel $metaModels): void
    {
        $this->metaModels = $metaModels;
    }

    /**
     * Set the property name.
     *
     * @param string $propertyName The property name.
     *
     * @return \ContaoCommunityAlliance\DcGeneral\DataDefinition\Palette\Condition\Property\PropertyValueCondition
     */
    public function setPropertyName($propertyName)
    {
        $this->propertyName = (string) $propertyName;

        return $this;
    }

    /**
     * Retrieve the property name.
     *
     * @return string
     */
    public function getPropertyName()
    {
        return $this->propertyName;
    }

    /**
     * Set the property value to match.
     *
     * @param mixed $propertyValue The value.
     *
     * @return PropertyValueCondition
     */
    public function setPropertyValue($propertyValue)
    {
        $this->propertyValue = $propertyValue;

        return $this;
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
     * Set the flag if the comparison shall be strict (type safe).
     *
     * @param boolean $strict The flag.
     *
     * @return PropertyValueCondition
     */
    public function setStrict($strict)
    {
        $this->strict = (bool) $strict;

        return $this;
    }

    /**
     * Retrieve the flag if the comparison shall be strict (type safe).
     *
     * @return boolean
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public function getStrict()
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
        $attribute = $this->metaModels->getAttribute($this->propertyName);
        if ($this->metaModels instanceof ITranslatedMetaModel) {
            $currentLanguage = $this->metaModels->getLanguage();
        } else {
            $currentLanguage = $this->metaModels->getActiveLanguage();
        }

        /*
         * Okay her comes the twist. We need the mode, this is the only way to get the metamodels context we need.
         * If we have it we can call the Alias to ID convert function, since all the options of a select or
         * other attributes are alias values. But the condition to check if the field is visibil or not is the ID.
         * So we musst translate the alias back to the id and check.
         */
        if ($input && $input->hasPropertyValue($this->propertyName)) {
            $value = $input->getPropertyValue($this->propertyName);
            if ($value && $attribute instanceof IAliasConverter) {
                $value = $attribute->getIdForAlias($value, $currentLanguage) ?? $value;
            }
        } elseif ($model && $model instanceof Model) {
            $value = $model->getProperty($this->propertyName);
            if ($value && $attribute instanceof IAliasConverter) {
                $value = $attribute->getIdForAlias($value, $currentLanguage) ?? $value;
            }
        } else {
            return false;
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
