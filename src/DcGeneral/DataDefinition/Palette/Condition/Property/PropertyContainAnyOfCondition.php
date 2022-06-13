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
 * @author     Christopher Boelter <christopher@boelter.eu>
 * @author     Christian Schiffler <c.schiffler@cyberspectrum.de>
 * @author     Sven Baumann <baumann.sv@gmail.com>
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
class PropertyContainAnyOfCondition implements PropertyConditionInterface
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
     * The metamodel of the current context.
     *
     * @var IMetaModel
     */
    protected $metaModel;

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
     * Get the metamodel.
     *
     * @return IMetaModel
     */
    public function getMetaModel(): IMetaModel
    {
        return $this->metaModel;
    }

    /**
     * Set the metamodel.
     *
     * @param IMetaModel $metaModel
     */
    public function setMetaModel(IMetaModel $metaModel): void
    {
        $this->metaModel = $metaModel;
    }

    /**
     * Set the property name.
     *
     * @param string $propertyName The property name.
     *
     * @return PropertyContainAnyOfCondition
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
     * @return PropertyContainAnyOfCondition
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
     * @return PropertyContainAnyOfCondition
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
        $attribute = $this->metaModel->getAttribute($this->propertyName);

        if ($this->metaModel instanceof ITranslatedMetaModel) {
            $currentLanguage = $this->metaModel->getLanguage();
        } else {
            $currentLanguage = $this->metaModel->getActiveLanguage();
        }

        if ($input && $input->hasPropertyValue($this->propertyName)) {
            $values = $input->getPropertyValue($this->propertyName);
        } elseif ($model instanceof Model) {
            $values = $model->getProperty($this->propertyName);
        } else {
            return false;
        }

        if (!$values || !\is_array($values)) {
            return false;
        }

        foreach ($values as $value) {
            if ($value && $attribute instanceof IAliasConverter) {
                $value = $attribute->getIdForAlias($value, $currentLanguage) ?? $value;
            }

            if (\in_array($value, $this->propertyValue, $this->strict)) {
                return true;
            }
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function __clone()
    {
    }
}
