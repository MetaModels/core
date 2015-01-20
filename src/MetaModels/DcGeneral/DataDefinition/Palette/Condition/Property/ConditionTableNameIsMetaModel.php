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
 * Condition to check if the current table in the model is a metamodel or not.
 */
class ConditionTableNameIsMetaModel implements PropertyConditionInterface
{
    /**
     * The name of the property in the passed model which contains the table name.
     *
     * @var string
     */
    protected $tablePropertyName;

    /**
     * The expected property value.
     *
     * @var mixed
     */
    protected $desiredValue;

    /**
     * Create a new instance.
     *
     * @param string $tableProperty The name of the property in the passed model which contains the table name.
     *
     * @param string $desiredValue  The desired value, true if the table shall be a MetaModel, false otherwise.
     */
    public function __construct($tableProperty, $desiredValue)
    {
        $this->tablePropertyName = $tableProperty;
        $this->desiredValue      = $desiredValue;
    }

    /**
     * Set the desired value.
     *
     * @param string $desiredValue The desired value.
     *
     * @return ConditionTableNameIsMetaModel
     */
    public function setDesiredValue($desiredValue)
    {
        $this->desiredValue = $desiredValue;

        return $this;
    }

    /**
     * Retrieve the desired value.
     *
     * @return mixed
     */
    public function getDesiredValue()
    {
        return $this->desiredValue;
    }

    /**
     * Set the name of the property in the passed model which contains the table name.
     *
     * @param string $tablePropertyName The name of the property.
     *
     * @return ConditionTableNameIsMetaModel
     */
    public function setTablePropertyName($tablePropertyName)
    {
        $this->tablePropertyName = $tablePropertyName;

        return $this;
    }

    /**
     * Retrieve the name of the property in the passed model which contains the table name.
     *
     * @return string
     */
    public function getTablePropertyName()
    {
        return $this->tablePropertyName;
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
        $propertyName = $this->getTablePropertyName();
        if ($input && $input->hasPropertyValue($propertyName)) {
            $value = $input->getPropertyValue($propertyName);
        } elseif ($model) {
            $value = $model->getProperty($propertyName);
        } else {
            return false;
        }

        return $this->desiredValue == (substr($value, 0, 3) === 'mm_');
    }

    /**
     * {@inheritdoc}
     */
    public function __clone()
    {
    }
}
